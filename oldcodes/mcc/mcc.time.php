<?
	session_start();
	require_once('../_global.inc');
	require_once('../_nsdt/nsdt_php.php');
	require_once('../_nsdt/nsdt_mysql.php');
	require_once('../_nsdt/_time.class.php');
	
	/*POST+INIT
	*/
	$_frm_ = (int)$_POST['_frm_'];
	$fromDate = nsdt_inputDate($_POST['fromDate']);
	$toDate = nsdt_inputDate($_POST['toDate']);
	$unitList = ($_POST['unitList']!='null') ? implode(',',$_POST['unitList']) : null;
	@unlink('../mcc/tmp/dbWeek.json');
	@unlink('../mcc/tmp/dbWeek_dayname.json');
	$T = new _Time();
	$dataWeek = array();
	
	/*ANALYSIS
	*/
	if(!$fromDate){exit('{E}-Chưa nhập ngày bắt đầu.');}
	if(!$toDate){exit('{E}-Chưa nhập ngày kết thúc.');}
	
	$_connect = mysql_connect(_MYSQL_HOST, _MYSQL_USER, _MYSQL_PASSWORD);
	mysql_select_db(_MYSQL_DATABASE, $_connect);
	
	$q = '
	SELECT 	GROUP_CONCAT(DISTINCT day ORDER BY day) AS workingDayList,
			GROUP_CONCAT(DISTINCT DAYOFYEAR(day) ORDER BY day) AS workingDayINTList
	FROM mcc_working_day
			WHERE day BETWEEN "'.$fromDate.'" AND "'.$toDate.'"';

	$r=mysql_query($q);
	$e=mysql_fetch_array($r,MYSQL_ASSOC);
	
	if($e['workingDayList']){
		$workingDayList=explode(',',$e['workingDayList']);
		//$numOfDays=count($workingDayList);
		$workingDayINTList=explode(',',$e['workingDayINTList']);
	}else{echo '{E}-Du lieu ngay thang khong ton tai!'; mysql_close($_connect); die;}	
	
/*LOADFILE
*/	
	if($_frm_){
		$q = 'DELETE FROM mcc_log';
		mysql_query($q);
	
		foreach($workingDayList as $day){
			$f = _MCC_LOG_FOLDER.'/'.$day.'.txt';
			
			if(file_exists($f)){
				$q = 'LOAD DATA INFILE "'.$f.'" INTO TABLE mcc_log
				FIELDS TERMINATED BY " " LINES TERMINATED BY "\r\n"
				(ma_nhanvien,@H1,@H2,@H3,@H4,@H5,@H6)
				SET
				mcc_log.h1=(@H1),
				mcc_log.h2=(@H2),
				mcc_log.h3=(@H3),
				mcc_log.h4=(@H4),
				mcc_log.h5=(@H5),
				mcc_log.h6=(@H6),
				mcc_log.ngaythangnam="'.$day.'"';
				
				//echo $q;die;
				
				if(!mysql_query($q)){echo '{E}-File dữ liệu không load được';die;}
				
			}else{echo '{E}-File dữ liệu không tìm thấy';die;}
		}
		
		echo '{I}-OK';
	}
	
/*CALCULATION
*/		
	else{
		if($unitList){
		$q = 'SELECT
				mcc_log.ma_nhanvien,
				CONCAT(ho," ",ten) AS FullName,
				ns_nhansu.id_donvi,donvi,
				GROUP_CONCAT(DAYOFYEAR(ngaythangnam) ORDER BY ngaythangnam) AS dayList,
				tim_thaisan(ns_nhansu.ma_nhanvien,ngaythangnam) AS kieuthaisan,
				ROUND(TIME_TO_SEC(maternityFixWorkingTime(workingTime(h1,h2,h3,h4,h5,h6,"07:15:00",(SELECT closingTime),(SELECT ns_nhansu.id_donvi),(SELECT disable)),(select kieuthaisan)))/3600,2) AS workingTime,
				ROUND(TIME_TO_SEC(maternityFixOverTime(overTime(h2,h4,h6,(SELECT closingTime),(SELECT ns_nhansu.id_donvi),(SELECT disable)), (SELECT kieuthaisan)))/3600,2) AS overTime,
				ROUND(TIME_TO_SEC(holidayTime((SELECT workingTime),(SELECT overTime),(SELECT holiday)))/3600,2) AS holidayTime,
				(SELECT workingTime)+(SELECT overTime) AS timeofDay,
				GROUP_CONCAT((SELECT workingTime) ORDER BY ngaythangnam) AS timeOfficeList,
				GROUP_CONCAT((SELECT overTime) ORDER BY ngaythangnam) AS partTimeList,
				SUM((SELECT holidayTime)) AS holidayTimeTotal,
				SUM((SELECT timeofDay)) AS timeofDayTotal,
				dayCount((SELECT workingTime)) AS nDay,
				SUM((SELECT nDay)) AS nDays,
				SUM(IF(holiday=0,(SELECT nDay),0)) AS nWDays,
				SUM(IF (holiday=0 and (SELECT kieuthaisan in (2,3)), thaisan_themgio((select workingTime),(select kieuthaisan)), 0)) AS giothemthaisan,
				if ((SELECT overTime) > 1.50,1.50,(SELECT overTime)) AS lamthemtruoc,
				if ((SELECT overTime) >1.50,(SELECT overTime) - 1.50,0.00) AS lamthemsau,
				SUM(IF(holiday=0,(SELECT overTime),0)) AS TLT,
				SUM(IF(holiday=0,(SELECT lamthemtruoc),0)) AS lamthemtruocTotal,
				SUM(IF (holiday=0,(SELECT lamthemsau),0)) AS lamthemsauTotal,
				SUM(IF(holiday=0,(SELECT workingTime),0)) AS THC			
				FROM mcc_log
				JOIN ns_nhansu
				ON mcc_log.ma_nhanvien=ns_nhansu.ma_nhanvien
				JOIN mcc_working_day
				ON day=ngaythangnam AND ns_nhansu.id_donvi=mcc_working_day.id_donvi
				INNER JOIN ns_donvi
				ON ns_nhansu.id_donvi=ns_donvi.id_donvi
				WHERE ns_nhansu.id_donvi IN ('.$unitList.')
				GROUP BY mcc_log.ma_nhanvien
				ORDER BY ns_nhansu.id_donvi,ns_nhansu.id_chucvu';

				//echo $q;die;
			
			$r = mysql_query($q);
			$numofRows = mysql_affected_rows($_connect);
			while($e=mysql_fetch_array($r,MYSQL_ASSOC)){
				$emp_dayList = nsdt_array_EqPos($workingDayINTList,explode(',',$e['dayList']));
				$timeOfficeList = nsdt_array_xPos($emp_dayList,explode(',',$e['timeOfficeList']));
				$partTimeList = nsdt_array_xPos($emp_dayList,explode(',',$e['partTimeList']));
				
				//$timeOfDays = $T->hmShow($e['timeofDayTotal']);
				$timeOfDays = $e['timeofDayTotal'];
				$dataWeek[$e['ma_nhanvien']] = array(
					'fullname'=>$e['FullName'],
					'donvi'=>$e['donvi'],
					'chuyencan'=>'20%',
					'timeOfficeList'=>$timeOfficeList,
					'partTimeList'=>$partTimeList,
					'TCTG'=>$timeOfDays,
					'THC'=>$e['THC'],
					'TLT'=>$e['TLT'],
					'BCN'=>$e['giothemthaisan'],
					$emp_dayList,
					'NCN'=>$e['nDays'],
					'CN'=>$e['holidayTimeTotal'],
					'LTT'=>$e['lamthemtruocTotal'],
					'LTS'=>$e['lamthemsauTotal'],
					'NHC'=>$e['nWDays'],
					'TTG'=>$e['THC'] + $e['lamthemtruocTotal']
					);
			}
			
			/* Tìm trừ chuyên cần + cập nhật!
			*/
			$q = 'SELECT ma_nhanvien,truchuyencan FROM ns_chuyencan WHERE ngay_tru between "'.$fromDate.'" AND "'.$toDate.'" GROUP BY ma_nhanvien';
			$r = mysql_query($q);
			if(mysql_affected_rows($_connect)){
				while($e=mysql_fetch_array($r)){
					if(isset($dataWeek[$e['ma_nhanvien']])){
						$dataWeek[$e['ma_nhanvien']]['chuyencan']=20-$e['truchuyencan'].'%';
					}
				}
			}
/*VIEW
*/
			$htmlTable = '<style scoped>						
			             .default th {background-color:#f4faff; border:solid 1px #A8D4FF;vertical-align:middle;}
                         tr.odd {background-color:#EEEEEE;}
                         tr.header{height:20px}
                         .default td {border:solid 1px #CCCCCC; vertical-align:middle; text-align:center;}
                         td.empFullname {padding-left:10px; text-align:left}
                         </style>';
			
			$htmlTable .=   '<table class="default" width="100%">
							<tr class="header">
							<th>#</th>
                            <th>MNV</th>
                            <th>HOTEN</th>
                            <th>DONVI</th>
                            ';
            
		foreach($workingDayList as $Day)
		$htmlTable .= '<th>'.nsdt_dmFormat($Day).'</th>';
			$htmlTable .= '<th>CC</th>
						<th>NCN</th>
						<th>NHC</th>
						<th>TCTG</th>
						<th>TTG</th>
						<th>THC</th>
						<th>TLT</th>
						<th>LTT</th>
						<th>CN</th>
						<th>LTS</th>
						<th>B_CN</th>';
			$htmlTable .= '</tr>';
			
			$k = null;
			$n = 0;
			for($i=0;$i<count($dataWeek);$i++){
			    
				if(!$k) $k=$dataWeek[key($dataWeek)]['donvi'];
				if($k==$dataWeek[key($dataWeek)]['donvi']) $n++; else { $k=$dataWeek[key($dataWeek)]['donvi']; $n=1; }
				
                $odd = (int)($i%2) ? '' : 'class="odd"'; 
                
                $htmlTable .= '<tr '.$odd.'>';
				$htmlTable .= '<td rowspan="2">'.$n.'</td>';
				$htmlTable .= '<td rowspan="2">'.key($dataWeek).'</td>';//MNV
				$htmlTable .= '<td rowspan="2" class="empFullname">'.$dataWeek[key($dataWeek)]['fullname'].'</td>';//HOTEN
				$htmlTable .= '<td rowspan="2">'.$dataWeek[key($dataWeek)]['donvi'].'</td>';//DONVI
				foreach($dataWeek[key($dataWeek)]['timeOfficeList'] as $t){
                    //if($t!=='00:00:00') $htmlTable .= '<td>'.$T->hmNullShow($t).'</td>';
                    //else 
					$htmlTable .= '<td>'.$t.'</td>';
				}
				$htmlTable .= '<td rowspan="2">'.$dataWeek[key($dataWeek)]['chuyencan'].'</td>';
				$htmlTable .= '<td rowspan="2">'.$dataWeek[key($dataWeek)]['NCN'].'</td>';//NCN
				$htmlTable .= '<td rowspan="2">'.$dataWeek[key($dataWeek)]['NHC'].'</td>';
				$htmlTable .= '<td rowspan="2">'.$dataWeek[key($dataWeek)]['TCTG'].'</td>';
				$htmlTable .= '<td rowspan="2">'.$dataWeek[key($dataWeek)]['TTG'].'</td>';
				$htmlTable .= '<td rowspan="2">'.$dataWeek[key($dataWeek)]['THC'].'</td>';
				$htmlTable .= '<td rowspan="2">'.$dataWeek[key($dataWeek)]['TLT'].'</td>';
				$htmlTable .= '<td rowspan="2">'.$dataWeek[key($dataWeek)]['LTT'].'</td>';
				$htmlTable .= '<td rowspan="2">'.$dataWeek[key($dataWeek)]['CN'].'</td>';
				$htmlTable .= '<td rowspan="2">'.$dataWeek[key($dataWeek)]['LTS'].'</td>';
				$htmlTable .= '<td rowspan="2">'.$dataWeek[key($dataWeek)]['BCN'].'</td>';
				//$htmlTable .= '<td rowspan="2">'.$dataWeek[key($dataWeek)]['TLT'].'</td>';
				$htmlTable .= '</tr>';
				
				$htmlTable .= '<tr '.$odd.'>';
				foreach($dataWeek[key($dataWeek)]['partTimeList'] as $t) $htmlTable .= '<td>'.$t.'</td>';
				$htmlTable .= '</tr>';
				
				next($dataWeek);
			} 
			$htmlTable .= '</table>';
            print($htmlTable);
            
            file_put_contents('tmp/dbWeek.json',json_encode($dataWeek));			 
			file_put_contents('tmp/dbWeek_dayname.json',json_encode($workingDayList));
/*
*/			
		}else echo '{E}- Chưa chọn đơn vị';
	}
	
	mysql_close($_connect);
?>