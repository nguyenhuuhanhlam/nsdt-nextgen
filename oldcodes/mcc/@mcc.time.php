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
	$T = new _Time();
	$dataWeek = array();
	
	/*ANALYSIS
	*/
	if(!$fromDate){exit('{E}Chưa nhập ngày bắt đầu.');}
	if(!$toDate){exit('{E}Chưa nhập ngày kết thúc.');}
	
	$_connect = mysql_connect(_MYSQL_HOST, _MYSQL_USER, _MYSQL_PASSWORD);
	mysql_select_db(_MYSQL_DATABASE, $_connect);
	
	$q = '
	SELECT 	GROUP_CONCAT(DISTINCT day) AS workingDayList,
			GROUP_CONCAT(DISTINCT DAYOFYEAR(day)) AS workingDayINTList
	FROM ___mcc_working_day
			WHERE day BETWEEN "'.$fromDate.'" AND "'.$toDate.'"
            ORDER BY day';

	$r=mysql_query($q);
	$e=mysql_fetch_array($r,MYSQL_ASSOC);
	
	if($e['workingDayList']){
		$workingDayList=explode(',',$e['workingDayList']);
		$numOfDays=count($workingDayList);
		$workingDayINTList=explode(',',$e['workingDayINTList']);
	}else{echo '{E}-Du lieu ngay thang khong ton tai!'; mysql_close($_connect); die;}
	
	//tim danh sach ngay nghi
	$q = '	SELECT GROUP_CONCAT(DISTINCT DAYOFYEAR(day)) AS holidayList
			FROM ___mcc_working_day
			WHERE day BETWEEN "'.$fromDate.'" AND "'.$toDate.'" AND holiday=1
            ORDER BY day';
	$r=mysql_query($q);
	$e=mysql_fetch_array($r,MYSQL_ASSOC);
	
	if($e['holidayList']){
		$holidayList=explode(',',$e['holidayList']);
	}else $holidayList = null;
	
/*LOADFILE
*/	
	if($_frm_){
		$q = 'DELETE FROM ___mcc_log';
		mysql_query($q);
	
		foreach($workingDayList as $day){
			//load file du lieu
			$f = _MCC_LOG_FOLDER.'/'.$day.'.txt';
			
			if(file_exists($f)){
				$q = 'LOAD DATA INFILE "'.$f.'" INTO TABLE ___mcc_log
				FIELDS TERMINATED BY " " LINES TERMINATED BY "\r\n"
				(ma_nhanvien,@H1,@H2,@H3,@H4,@H5,@H6)
				SET
				___mcc_log.h1=(@H1),
				___mcc_log.h2=(@H2),
				___mcc_log.h3=(@H3),
				___mcc_log.h4=(@H4),
				___mcc_log.h5=(@H5),
				___mcc_log.h6=(@H6),
				___mcc_log.ngaythangnam="'.$day.'"';
				
				if(!mysql_query($q)){echo '{E}-File dữ liệu không load được';die;}
				
			}else{echo '{E}-File dữ liệu không tìm thấy';die;}
		}
		
		echo '{I}-OK';
	}
	
/*CALCULATION
*/		
	else{
		if($unitList){
			$q = '
			SELECT
	___mcc_log.ma_nhanvien,
	CONCAT(ho," ",ten) AS FullName,
	ns_nhansu.id_donvi,donvi,
	GROUP_CONCAT(DAYOFYEAR(ngaythangnam) ORDER BY ngaythangnam) AS dayList,
	tim_thaisan(ns_nhansu.ma_nhanvien,ngaythangnam) AS kieuthaisan,
	officeTime_thaisan(h1,h2,h3,h4,h5,h6,"07:15:00",(SELECT closingTime),(SELECT holiday),(SELECT ns_nhansu.id_donvi),(select kieuthaisan),(SELECT disable)) AS officeTime,
	partTime_thaisan(h2,h4,h6,(SELECT closingTime),(SELECT holiday),(SELECT ns_nhansu.id_donvi),(select kieuthaisan),(SELECT disable)) AS partTime,
	holidayTime_thaisan(h1,h2,h3,h4,h5,h6,"07:15:00",(SELECT closingTime),(SELECT holiday),(SELECT ns_nhansu.id_donvi),(SELECT kieuthaisan),(SELECT disable)) AS holidayTime,
	ADDTIME((SELECT officeTime),(SELECT partTime)) AS timeofDay,
	GROUP_CONCAT((SELECT officeTime) ORDER BY ngaythangnam) AS timeOfficeList,
	GROUP_CONCAT((SELECT partTime) ORDER BY ngaythangnam) AS partTimeList,
	SEC_TO_TIME(SUM((SELECT TIME_TO_SEC(holidayTime)))) AS holidayTimeTotal,
	SEC_TO_TIME(SUM((SELECT TIME_TO_SEC(timeofDay)))) AS timeofDayTotal,
	dayCount((SELECT officeTime)) AS nDay,
	SUM((SELECT nDay)) AS nDays,
	SUM( thaisan_themgio((select officeTime),(select kieuthaisan)) ) as giothemthaisan				
FROM ___mcc_log
	JOIN ns_nhansu
		ON ___mcc_log.ma_nhanvien=ns_nhansu.ma_nhanvien
	JOIN ___mcc_working_day
		ON day=ngaythangnam
	INNER JOIN ns_donvi
		ON ns_nhansu.id_donvi=ns_donvi.id_donvi
	WHERE ns_nhansu.id_donvi IN ('.$unitList.')
	GROUP BY ___mcc_log.ma_nhanvien
	ORDER BY ns_nhansu.id_donvi';
	// echo $q;
// die;	
			$r = mysql_query($q);
			$numofRows = mysql_affected_rows($_connect);
			while($e=mysql_fetch_array($r,MYSQL_ASSOC)){
				$emp_dayList = nsdt_array_EqPos($workingDayINTList,explode(',',$e['dayList']));
				$timeOfficeList = nsdt_array_xPos($emp_dayList,explode(',',$e['timeOfficeList']));
				$partTimeList = nsdt_array_xPos($emp_dayList,explode(',',$e['partTimeList']));
				
				$timeOfDays = $T->hmShow($e['timeofDayTotal']);
				
				$dataWeek[$e['ma_nhanvien']] = array(
					$e['FullName'],$e['donvi'],
					'20%',$timeOfficeList,
					$partTimeList,
					$timeOfDays,
					$T->hmSum($timeOfficeList),
					$T->hmSum($partTimeList),$e['giothemthaisan'],$e['nDays'],$emp_dayList,$T->hmShow($e['holidayTimeTotal'])
					);
			}
			
			/* Tìm trừ chuyên cần + cập nhật!
			*/
			$q = $diligentSearch($unitList,$fromDate,$toDate);
			$r = mysql_query($q);
			if(mysql_affected_rows($_connect)){
				while($e=mysql_fetch_array($r)){
					if(isset($dataWeek[$e['ma_nhanvien']])){
						$dataWeek[$e['ma_nhanvien']][2]=20-$e['truchuyencan'].'%';
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
                            <th>CC</th>
							<th>N</th>';
            
			foreach($workingDayList as $Day) $htmlTable .= '<th>'.nsdt_dmFormat($Day).'</th>';
			$htmlTable .= '<th>TG</th><th>TGHC</th><th>LT</th><th>CN</th><th>B_CN</th>';
			$htmlTable .= '</tr>';
			
			$k = null;
			$n = 0;
			for($i=0;$i<count($dataWeek);$i++){
			    
				if(!$k) $k=$dataWeek[key($dataWeek)][1];
				if($k==$dataWeek[key($dataWeek)][1]) $n++; else { $k=$dataWeek[key($dataWeek)][1]; $n=1; }
				
                $odd = (int)($i%2) ? '' : 'class="odd"'; 
                
                $htmlTable .= '<tr '.$odd.'>';
				$htmlTable .= '<td rowspan="2">'.$n.'</td>';
				$htmlTable .= '<td rowspan="2">'.key($dataWeek).'</td>';
				$htmlTable .= '<td rowspan="2" class="empFullname">'.$dataWeek[key($dataWeek)][0].'</td>';
				$htmlTable .= '<td rowspan="2">'.$dataWeek[key($dataWeek)][1].'</td>';
				$htmlTable .= '<td rowspan="2">'.$dataWeek[key($dataWeek)][2].'</td>';
				$htmlTable .= '<td rowspan="2">'.$dataWeek[key($dataWeek)][9].'</td>';
				foreach($dataWeek[key($dataWeek)][3] as $t){
                    if($t!=='00:00:00') $htmlTable .= '<td>'.$T->hmNullShow($t).'</td>';
                    else $htmlTable .= '<td style="background-color:#FFFFAA">'.$T->hmNullShow($t).'</td>';
				}
				$htmlTable .= '<td rowspan="2">'.$dataWeek[key($dataWeek)][5].'</td>';
				$htmlTable .= '<td rowspan="2">'.$dataWeek[key($dataWeek)][6].'</td>';
				$htmlTable .= '<td rowspan="2">'.$dataWeek[key($dataWeek)][7].'</td>';
				$htmlTable .= '<td rowspan="2">'.$dataWeek[key($dataWeek)][11].'</td>';
				$htmlTable .= '<td rowspan="2">'.$dataWeek[key($dataWeek)][8].'</td>';
				$htmlTable .= '</tr>';
				
				$htmlTable .= '<tr '.$odd.'>';
				foreach($dataWeek[key($dataWeek)][4] as $t) $htmlTable .= '<td>'.$T->hmNullShow($t).'</td>';
				$htmlTable .= '</tr>';
				
				next($dataWeek);
			} 
			$htmlTable .= '</table>';
            print($htmlTable);
            
            file_put_contents('tmp/dbWeek.json',json_encode($dataWeek));			 
/*
*/			
		}else echo '{E}- Chưa chọn đơn vị';
	}
	
	mysql_close($_connect);
?>