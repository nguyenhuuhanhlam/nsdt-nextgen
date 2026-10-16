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
	@unlink('../mcc/tmp/@chamcong_thang.json');
	$T = new _Time();
	$dataMonth = array();
	$numOfDays = null;
	
	/*ANALYSIS
	*/
	if(!$fromDate){exit('{E}Chua nhap ngay bat dau 1');}
	if(!$toDate){exit('{E}Chua nhap ngay ket thuc');}
	
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
	}else{echo '{E}Dữ liệu ngày tháng không tồn tại!'; mysql_close($_connect); die;}
	
/*LOADFILE
*/	
	if($_frm_){
		$q = 'DELETE FROM ___mcc_log_thang';
		mysql_query($q);
		$q = 'DELETE FROM ___mcc_log_thang_ao';
		mysql_query($q);
		
		foreach($workingDayList as $day){
			$f = _MCC3_LOG_FOLDER.'/'.$day.'.txt';
			
			if(file_exists($f)){
			
				$q = 'LOAD DATA INFILE "'.$f.'" INTO TABLE ___mcc_log_thang
				FIELDS TERMINATED BY " " LINES TERMINATED BY "\r\n"
				(ngaythangnam,ma_nhanvien,h1,h2,h3,h4,h5,h6,kieuthaisan)';
				
				if(!mysql_query($q)){exit('{E}Xem lại file chấm công.');}
				
			}else{exit('{E}Không tìm thấy file chấm công.');}
		}
		// $q ='
			// INSERT INTO ___mcc_log_thang_ao(ma_nhanvien,ngaythangnam,kieuthaisan,giohanhchanh,giolamthem,giochunhat,tongthoigian,tongngay)
			// SELECT
				// ___mcc_log_thang.ma_nhanvien,ngaythangnam,kieuthaisan,
				// maternityFixWorkingTime(workingTime(h1,h2,h3,h4,h5,h6,"07:00:00",(SELECT closingTime),(SELECT ns_nhansu.id_donvi),(SELECT disable)),(select kieuthaisan)) AS officeTime,
				// maternityFixOverTime(overTime(h2,h4,h6,(SELECT closingTime),(SELECT ns_nhansu.id_donvi),(SELECT disable)), (SELECT kieuthaisan)) partTime,
				// holidayTime_thaisan((SELECT h1),(SELECT h2),h3,h4,h5,h6,"07:00:00",(SELECT closingTime),(SELECT holiday),(SELECT ns_nhansu.id_donvi),(SELECT kieuthaisan),(SELECT disable)) AS holidayTime,
				// ADDTIME((SELECT officeTime),(SELECT partTime)) AS timeofDay,
				// dayCount((SELECT officeTime)) AS nDay
				// FROM ___mcc_log_thang
				// RIGHT JOIN ns_nhansu	ON ___mcc_log_thang.ma_nhanvien=ns_nhansu.ma_nhanvien
				// JOIN ___mcc_working_day	ON day = ngaythangnam
				// WHERE ngaythangnam BETWEEN "'.$fromDate.'" AND "'.$toDate.'"';
			//echo $q;
			//die;
			//mysql_query($q);
		echo '{I}Đọc dữ liệu thành công.';
		
	}
	
/*CALCULATION
*/		
	else{
		if($unitList){
			$q = '
				SELECT ma_nhanvien,FullName,donvi,NCCCN,NCHC,
				(SELECT x.THC) + (SELECT x.TLT) AS TTG,THC,TLT,TCN,BCN,0 AS TCD,0 AS TPN,gioitinh,"" AS chuyento,"" AS nghiviec,
				IF ((SELECT x.khamthai)>=20,1,0) AS KT,(SELECT TTG) + (SELECT TCN) AS TTGCCN,THCCCN,TLTCCN
			FROM (
				SELECT
					___mcc_log_thang.ma_nhanvien,
					CONCAT(ho," ",ten) AS FullName,
					ns_nhansu.id_donvi,donvi,
					tim_thaisan(ns_nhansu.ma_nhanvien,ngaythangnam) AS kieuthaisan,
					GROUP_CONCAT(DAYOFYEAR(ngaythangnam) ORDER BY ngaythangnam) AS dayList,
					ROUND(TIME_TO_SEC(maternityFixWorkingTime(workingTime1(h1,h2,h3,h4,h5,h6,"07:15:00",(SELECT closingTime),(SELECT ns_nhansu.id_donvi),(SELECT disable)),(select kieuthaisan)))/3600,2) AS workingTime,
					ROUND(TIME_TO_SEC(maternityFixOverTime(overtimeCalc(h1,h2,h3,h4,h5,h6,(SELECT closingTime),(SELECT ns_nhansu.id_donvi),(SELECT disable)), (SELECT kieuthaisan)))/3600,2) AS overTime,
					ROUND(holidayTime((SELECT workingTime),(SELECT overTime),(SELECT holiday)),2) AS holidayTime,
					dayCount((SELECT workingTime)) AS nDay,
					(SELECT workingTime) + (SELECT overTime) AS timeofDay,
					SUM((SELECT nDay)) AS NCCCN,
					SUM(IF(holiday=0,(SELECT nDay),0)) AS NCHC,
					SUM(IF(holiday=0,(SELECT workingTime),0)) AS THC,
					SUM(IF(holiday=0,(SELECT overTime),0)) AS TLT,
					SUM((SELECT workingTime)) AS THCCCN,
					SUM((SELECT overTime)) AS TLTCCN,
					SUM((SELECT holidayTime)) AS TCN,
					SUM(IF (holiday=0 and ((SELECT kieuthaisan) in (2,3)), thaisan_themgio((SELECT workingTime),(SELECT kieuthaisan)), 0)) AS BCN,
					SUM(IF (holiday=0 and ((SELECT kieuthaisan) in (1,2)), thaisan_themgio((SELECT workingTime),(SELECT kieuthaisan)), 0)) AS khamthai,
					IF (SUM((SELECT kieuthaisan) IN (1,2)),"-",gioitinh) AS gioitinh
				FROM ___mcc_log_thang
				JOIN ns_nhansu
					ON ___mcc_log_thang.ma_nhanvien=ns_nhansu.ma_nhanvien
				JOIN ___mcc_working_day	ON ___mcc_working_day.day = ___mcc_log_thang.ngaythangnam 
				INNER JOIN ns_donvi
					ON ns_nhansu.id_donvi=ns_donvi.id_donvi
				WHERE ns_nhansu.id_donvi IN ('.$unitList.')
				GROUP BY ___mcc_log_thang.ma_nhanvien
				ORDER BY ns_nhansu.id_donvi,ns_nhansu.id_chucvu)x';
// echo $q;
// die; 
			$r = mysql_query($q);
			$numofRows = mysql_affected_rows($_connect);
			while($e=mysql_fetch_array($r,MYSQL_ASSOC)){
			
				$dataMonth[$e['ma_nhanvien']] = array(
						'hoten'=>$e['FullName'],
						'dv'=>$e['donvi'],
						'cc'=>'20%',
						'ncccn'=>$e['NCCCN'],
						'nchc'=>$e['NCHC'],
						'ttgccn'=>$e['TTGCCN'],
						'thcccn'=>$e['THCCCN'],
						'tltccn'=>$e['TLTCCN'],
						'ttg'=>$e['TTG'],
						'thc'=>$e['THC'],
						'tlt'=>$e['TLT'],
						'tcn'=>$e['TCN'],
						'bcn'=>$e['BCN'],
						'tcd'=>$e['TCD'],
						'tpn'=>$e['TPN'],
						'gt'=>$e['gioitinh'],
						'ct'=>$e['chuyento'],
						'nv'=>$e['nghiviec'],
						'kt'=>$e['KT']
						);
			}
			
			/*[3] Tìm trừ chuyên cần + cập nhật!
			*/
			$q = 'SELECT ma_nhanvien,truchuyencan FROM ns_chuyencan WHERE ngay_tru between "'.$fromDate.'" AND "'.$toDate.'" GROUP BY ma_nhanvien';
			$r = mysql_query($q);
			if(mysql_affected_rows($_connect)){
				while($e=mysql_fetch_array($r)){
					if(isset($dataMonth[$e['ma_nhanvien']])){
						$dataMonth[$e['ma_nhanvien']]['cc']=20-$e['truchuyencan'].'%';
					}
				}
			}
			
			//print_r ($dataMonth);die;
			/* cat nhat phep nam
			*/
			$q = 'SELECT ma_nhanvien, SUM(songaynghi) AS pn FROM ns_xinphep
				WHERE id_phepnam = 4 AND (ngay_batdau between "'.$fromDate.'" AND "'.$toDate.'")
				GROUP BY ma_nhanvien';
				// echo $q;
				// die;
			$r = mysql_query($q);
			
			while($e=mysql_fetch_array($r)){
				if( isset($dataMonth[$e['ma_nhanvien']]) ){
					$dataMonth[$e['ma_nhanvien']]['tpn'] = $e['pn'];
				}
			}
			
			//print_r ($dataMonth);die;
			/* tim che do NEW			
			*/
			$q = 'SELECT ma_nhanvien, SUM(songaynghi) AS cd FROM ns_xinphep
				WHERE id_phepnam = 3 AND (ngay_batdau between "'.$fromDate.'" AND "'.$toDate.'")
				GROUP BY ma_nhanvien';
			$r = mysql_query($q);
			while($e=mysql_fetch_array($r)){
				if( isset($dataMonth[$e['ma_nhanvien']]) ){
					$dataMonth[$e['ma_nhanvien']]['tcd'] = $e['cd'];
				}
			}
			//print_r ($dataMonth);die;
			/* tim CHUYEN DON VI		
			*/
			$q = 'SELECT ma_nhanvien FROM thaydoi_donvi
				WHERE ngay_batdau between "'.$fromDate.'" AND "'.$toDate.'"
				GROUP BY ma_nhanvien';
				// echo $q;
				// die;
			$r = mysql_query($q);
			while($e=mysql_fetch_array($r)){
				if( isset($dataMonth[$e['ma_nhanvien']]) ){
					$dataMonth[$e['ma_nhanvien']]['ct'] = "ct" ;
				}
			}
			
/*VIEW
*/

			$htmlTable = '<style scoped>
			             .default th {background-color:#f4faff; border:solid 1px #A8D4FF;vertical-align:middle;}
                         tr.odd {background-color:#EEEEEE;}
                         tr.header{height:20px}
                         .default td {border:solid 1px #CCCCCC; vertical-align:middle; text-align:center; height:20px}
                         td.empFullname {padding-left:10px; text-align:left}
                         </style>';

			$htmlTable .=   '<table class="default" width="100%">
							<tr class="header">
							<th>#</th>
                            <th>MNV</th>
                            <th>HOTEN</th>
                            <th>DONVI</th>
                            <th>HCC</th>
							<th>NCCCN</th>
							<th>NCHC</th>';
            
			$htmlTable .= '<th>TTGCCN</th><th>THCCCN</th><th>TLTCCN</th><th>TTG</th><th>THC</th><th>TLT</th><th>TCN</th><th>B_CN</th><th>CD</th><th>PN</th><th>GT</th><th>CT</th><th>NV</th><th>KT</th>';
			$htmlTable .= '</tr>';
			
			$k = null;
			$n = 0;
			for($i=0;$i<count($dataMonth);$i++){
			    
				if(!$k) $k=$dataMonth[key($dataMonth)]['dv'];
				if($k==$dataMonth[key($dataMonth)]['dv'])
				$n++;
				else { $k=$dataMonth[key($dataMonth)]['dv']; $n=1;}
                $odd = (int)($i%2) ? '' : 'class="odd"'; 
                $htmlTable .= '<tr '.$odd.'>';
				$htmlTable .= '<td>'.$n.'</td>';
				$htmlTable .= '<td>'.key($dataMonth).'</td>';
				$htmlTable .= '<td class="empFullname">'.$dataMonth[key($dataMonth)]['hoten'].'</td>';
				$htmlTable .= '<td>'.$dataMonth[key($dataMonth)]['dv'].'</td>';
				$htmlTable .= '<td>'.$dataMonth[key($dataMonth)]['cc'].'</td>';
				$htmlTable .= '<td>'.$dataMonth[key($dataMonth)]['ncccn'].'</td>';
				
				$htmlTable .= '<td>'.$dataMonth[key($dataMonth)]['nchc'].'</td>';
				$htmlTable .= '<td>'.$dataMonth[key($dataMonth)]['ttgccn'].'</td>';
				$htmlTable .= '<td>'.$dataMonth[key($dataMonth)]['thcccn'].'</td>';
				$htmlTable .= '<td>'.$dataMonth[key($dataMonth)]['tltccn'].'</td>';
				$htmlTable .= '<td>'.$dataMonth[key($dataMonth)]['ttg'].'</td>';
				$htmlTable .= '<td>'.$dataMonth[key($dataMonth)]['thc'].'</td>';
				$htmlTable .= '<td>'.$dataMonth[key($dataMonth)]['tlt'].'</td>';
				
				$htmlTable .= '<td>'.$dataMonth[key($dataMonth)]['tcn'].'</td>';
				$htmlTable .= '<td>'.$dataMonth[key($dataMonth)]['bcn'].'</td>';
				$htmlTable .= '<td>'.$dataMonth[key($dataMonth)]['tcd'].'</td>';
				$htmlTable .= '<td>'.$dataMonth[key($dataMonth)]['tpn'].'</td>';
				
				$htmlTable .= '<td>'.$dataMonth[key($dataMonth)]['gt'].'</td>';
				$htmlTable .= '<td>'.$dataMonth[key($dataMonth)]['ct'].'</td>';
				$htmlTable .= '<td>'.$dataMonth[key($dataMonth)]['nv'].'</td>';
				$htmlTable .= '<td>'.$dataMonth[key($dataMonth)]['kt'].'</td>';
				$htmlTable .= '</tr>';		
				next($dataMonth);
			} 
			$htmlTable .= '</table>';
            print($htmlTable);
            file_put_contents('tmp/@chamcong_thang.json',json_encode($dataMonth));
					
		}else echo 'Chưa chọn đơn vị';
	}

	mysql_close($_connect);
?>