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
	$dataMonth = array();
	$numOfDays = null;
	
	/*ANALYSIS
	*/
	if(!$fromDate){exit('{E}Chua nhap ngay bat dau');}
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
	}else{echo '{E}du lieu ngay thang khong ton tai'; mysql_close($_connect); die;}
	
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
		$q = 'DELETE FROM ___mcc_log_month';
		mysql_query($q);
		$q = 'ALTER TABLE ___mcc_log_month AUTO_INCREMENT=1';
		mysql_query($q);
	
		foreach($workingDayList as $day){
			$f = _MCC_LOG_FOLDER.'/'.$day.'.txt';
			
			if(file_exists($f)){
				$q = 'LOAD DATA INFILE "'.$f.'" INTO TABLE ___mcc_log_month
				FIELDS TERMINATED BY " " LINES TERMINATED BY "\r\n"
				(ma_nhanvien, @H1,@H2,@H3,@H4,@H5,@H6)
				SET
				___mcc_log_month.h1=@H1,
				___mcc_log_month.h2=@H2,
				___mcc_log_month.h3=@H3,
				___mcc_log_month.h4=@H4,
				___mcc_log_month.h5=@H5,
				___mcc_log_month.h6=@H6,
				___mcc_log_month.ngaythangnam="'.$day.'"';
				
				if(!mysql_query($q)){exit('{E}Xem lại file chấm công.');}
				
			}else{exit('{E}Không tìm thấy file chấm công.');}
		}
		
		echo '{I}Đọc dữ liệu thành công.';
	}
	
/*CALCULATION
*/		
	else{
		if($unitList){
			$q = '
			SELECT
				COUNT(ngaythangnam) AS nDays,gioitinh,
				___mcc_log_month.ma_nhanvien,
				CONCAT(ho," ",ten) AS FullName,
				ns_nhansu.id_donvi,donvi,tim_thaisan(ns_nhansu.ma_nhanvien,ngaythangnam) AS kieuthaisan,
				GROUP_CONCAT(DAYOFYEAR(ngaythangnam) ORDER BY ngaythangnam) AS dayList,
				officeTime_thaisan(h1,h2,h3,h4,h5,h6,"07:20:00",(SELECT closingTime),(SELECT holiday),(SELECT ns_nhansu.id_donvi),(SELECT kieuthaisan),(SELECT disable)) AS officeTime,
                partTime_thaisan(h2,h4,h6,(SELECT closingTime),(SELECT holiday),(SELECT ns_nhansu.id_donvi),(SELECT kieuthaisan),(SELECT disable)) AS partTime,
				holidayTime_thaisan(h1,h2,h3,h4,h5,h6,"07:20:00",(SELECT closingTime),(SELECT holiday),(SELECT ns_nhansu.id_donvi),(SELECT kieuthaisan),(SELECT disable)) AS holidayTime,
                ADDTIME((SELECT officeTime),(SELECT partTime)) AS timeofDay,
				GROUP_CONCAT((SELECT officeTime) ORDER BY ngaythangnam) AS timeOfficeList,
				GROUP_CONCAT((SELECT partTime) ORDER BY ngaythangnam) AS partTimeList,
				SEC_TO_TIME(SUM((SELECT TIME_TO_SEC(holidayTime)))) AS holidayTimeTotal,
				SEC_TO_TIME(SUM((SELECT TIME_TO_SEC(timeofDay)))) AS timeofDayTotal,
				
				dayCount((SELECT officeTime)) AS nDay,
				SUM((SELECT nDay)) AS nDays
				
			FROM ___mcc_log_month
			JOIN ns_nhansu
				ON ___mcc_log_month.ma_nhanvien=ns_nhansu.ma_nhanvien
			JOIN ___mcc_working_day
				ON day=ngaythangnam
			INNER JOIN ns_donvi
				ON ns_nhansu.id_donvi=ns_donvi.id_donvi
			WHERE ns_nhansu.id_donvi IN ('.$unitList.')
			GROUP BY ___mcc_log_month.ma_nhanvien
			ORDER BY ns_nhansu.id_donvi';
			echo $q;die;
			$r = mysql_query($q);
			$numofRows = mysql_affected_rows($_connect);
			while($e=mysql_fetch_array($r,MYSQL_ASSOC)){
				$emp_dayList = explode(',',$e['dayList']);
				$timeOfficeList = explode(',',$e['timeOfficeList']);
				$partTimeList = explode(',',$e['partTimeList']);
				
				$dataMonth[$e['ma_nhanvien']] = array(
						$e['FullName'],$e['donvi'],
						'20%',$timeOfficeList,
						$partTimeList,
						$T->hmShow($T->hmAdd($e['timeofDayTotal'],$e['holidayTimeTotal'])),
						$T->hmSum($timeOfficeList),
						$T->hmSum($partTimeList),0,$e['nDays'],$emp_dayList,$T->hmShow($e['holidayTimeTotal']),$e['gioitinh'],0,0
						);
			}
			
			/*[2] Tìm thai sản + cập nhật!
			*/
			//$q = $pregnancySearch($unitList,$fromDate,$toDate);
			
			$q = 'SELECT ma_nhanvien FROM ns_xuly_thaisan
				WHERE ( ("'.$fromDate.'" < ngay_batdau) OR ("'.$fromDate.'" BETWEEN ngay_batdau AND ngay_ketthuc) )
				AND ( ("'.$toDate.'" BETWEEN ngay_batdau AND ngay_ketthuc) OR ("'.$toDate.'" > ngay_ketthuc) )
				AND id_thaisan IN (2,3)
				GROUP BY ma_nhanvien';
				
			$r = mysql_query($q);
			if(mysql_affected_rows($_connect)){
				while($e=mysql_fetch_array($r)){
					if(isset($dataMonth[$e['ma_nhanvien']])){
						$dataMonth[$e['ma_nhanvien']][8] = $dataMonth[$e['ma_nhanvien']][9] < $numOfDays ? 1 : 0;
						
						//tinh toan lai gio hanh chanh
						//$pregString = explode(',',$e['pregnancyData']);
						//$n = 0;
						//foreach($pregString as $p){
							//$d = explode('|',$p);//ngaydau,ngaycuoi,kieu
							//pregnancyReCalculation($dataMonth[$e['ma_nhanvien']][10],$holidayList,$dataMonth[$e['ma_nhanvien']][3],$dataMonth[$e['ma_nhanvien']][4],$d,$n);
						//}
						
						//$dataMonth[$e['ma_nhanvien']][8]=$n;
						
						//cap nhat lai tong gio hanh chanh
						//$dataMonth[$e['ma_nhanvien']][6] = $T->hmSum($dataMonth[$e['ma_nhanvien']][3]);
						//tong gio lam them
						//$dataMonth[$e['ma_nhanvien']][7] = $T->hmSum($dataMonth[$e['ma_nhanvien']][4]);
						
						//tong gio
						//$dataMonth[$e['ma_nhanvien']][5] = $T->hmAdd($dataMonth[$e['ma_nhanvien']][6],$dataMonth[$e['ma_nhanvien']][11]);
						//$dataMonth[$e['ma_nhanvien']][5] = $T->hmAdd($dataMonth[$e['ma_nhanvien']][5],$dataMonth[$e['ma_nhanvien']][7]);
					
						//cap nhat che do
						//$dataMonth[$e['ma_nhanvien']][13] = 1;
					}
				}
			}
			
			/*[3] Tìm trừ chuyên cần + cập nhật!
			*/
			$q = $diligentSearch($unitList,$fromDate,$toDate);
			$r = mysql_query($q);
			if(mysql_affected_rows($_connect)){
				while($e=mysql_fetch_array($r)){
					if(isset($dataMonth[$e['ma_nhanvien']])){
						$dataMonth[$e['ma_nhanvien']][2]=20-$e['truchuyencan'].'%';
					}
				}
			}
			
			
			/* cat nhat phep nam
			*/
			$q = 'SELECT ma_nhanvien, SUM(nghiphepnam) AS pn FROM ns_xinphep
				WHERE ( ("'.$fromDate.'" < ngay_batdau) OR ("'.$fromDate.'" BETWEEN ngay_batdau AND ngay_ketthuc) )
				AND ( ("'.$toDate.'" BETWEEN ngay_batdau AND ngay_ketthuc) OR ("'.$toDate.'" > ngay_ketthuc) )
				GROUP BY ma_nhanvien';
			$r = mysql_query($q);
			
			while($e=mysql_fetch_array($r)){
				if( isset($dataMonth[$e['ma_nhanvien']]) ){
					$dataMonth[$e['ma_nhanvien']][14] = $e['pn'];
				}
			}
			
			/* tim che do NEW			
			*/
			$q = 'CALL tinh_chedo("'.$fromDate.'","'.$toDate.'")';
			$r = mysql_query($q);
			while($e=mysql_fetch_array($r)){
				if( isset($dataMonth[$e['ma_nhanvien']]) ){
					$dataMonth[$e['ma_nhanvien']][13] = $e['cd'];
				}
			}
			
			/*tim xin phep -> CD
			*/
			// $q = $timxinphep('2,3,4',$fromDate,$toDate);
			// $r = mysql_query($q);
			// $_24 = $_3 = 0;
			// if(mysql_affected_rows($_connect)){
				// while($e=mysql_fetch_array($r)){
					// if(isset($dataMonth[$e['ma_nhanvien']])){
							// switch($e['id_phepnam']){
								// case 2:
								// case 4:
									
									// $q = 'SELECT intersectDays("'.$fromDate.'","'.$toDate.'","'.$e['ngay_batdau'].'","'.$e['ngay_ketthuc'].'")';
									// $r = mysql_query($q);
									// $idays = mysql_fetch_row($r);
									
									// if($e['songaytru']<3) $_24 = ($idays[0]>3) ? 3 : $idays[0];
										
								// break;
								
								// case 3:
									// if(!$e['songaytru']) $_3 = 1;
								// break;
							// }
							// $dataMonth[$e['ma_nhanvien']][13]=$_24+$_3;
						// }
					// }
				// }			
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
							<th>GT</th>
                            <th>DONVI</th>
                            <th>CC</th>
							<th>N</th>';
            
			$htmlTable .= '<th>TG</th><th>TGHC</th><th>LT</th><th>CN</th><th>B_CN</th><th>CD</th><th>PN</th>';
			$htmlTable .= '</tr>';
			
			$k = null;
			$n = 0;
			for($i=0;$i<count($dataMonth);$i++){
			    
				if(!$k) $k=$dataMonth[key($dataMonth)][1];
				if($k==$dataMonth[key($dataMonth)][1]) $n++; else { $k=$dataMonth[key($dataMonth)][1]; $n=1; }
				
                $odd = (int)($i%2) ? '' : 'class="odd"'; 
                
                $htmlTable .= '<tr '.$odd.'>';
				$htmlTable .= '<td>'.$n.'</td>';
				$htmlTable .= '<td>'.key($dataMonth).'</td>';
				$htmlTable .= '<td class="empFullname">'.$dataMonth[key($dataMonth)][0].'</td>';
				$htmlTable .= '<td>'.$dataMonth[key($dataMonth)][12].'</td>';
				$htmlTable .= '<td>'.$dataMonth[key($dataMonth)][1].'</td>';
				$htmlTable .= '<td>'.$dataMonth[key($dataMonth)][2].'</td>';
				$htmlTable .= '<td>'.$dataMonth[key($dataMonth)][9].'</td>';
				
				$htmlTable .= '<td>'.$dataMonth[key($dataMonth)][5].'</td>';
				$htmlTable .= '<td>'.$dataMonth[key($dataMonth)][6].'</td>';
				$htmlTable .= '<td>'.$dataMonth[key($dataMonth)][7].'</td>';
				$htmlTable .= '<td>'.$dataMonth[key($dataMonth)][11].'</td>';
				$htmlTable .= '<td>'.$dataMonth[key($dataMonth)][8].'</td>';
				$htmlTable .= '<td>'.$dataMonth[key($dataMonth)][13].'</td>';
				$htmlTable .= '<td>'.$dataMonth[key($dataMonth)][14].'</td>';
				$htmlTable .= '</tr>';
				
				next($dataMonth);
			} 
			$htmlTable .= '</table>';
            print($htmlTable);
            
            file_put_contents('tmp/dbMonth.json',json_encode($dataMonth)); 
/*
*/			
		}else echo 'Chưa chọn đơn vị';
	}
	
	mysql_close($_connect);
?>