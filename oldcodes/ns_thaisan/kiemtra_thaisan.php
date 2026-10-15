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
	
	/*ANALYSIS
	*/
	if(!$fromDate){exit('{E}-Chưa nhập ngày bắt đầu.');}
	if(!$toDate){exit('{E}-Chưa nhập ngày kết thúc.');}
	
	$_connect = mysql_connect(_MYSQL_HOST, _MYSQL_USER, _MYSQL_PASSWORD);
	mysql_select_db(_MYSQL_DATABASE, $_connect);
	
	$q = '
	SELECT 	GROUP_CONCAT(DISTINCT day) AS workingDayList,
			GROUP_CONCAT(DISTINCT DAYOFYEAR(day)) AS workingDayINTList
	FROM mcc_working_day
			WHERE day BETWEEN "'.$fromDate.'" AND "'.$toDate.'"
            ORDER BY day';

	$r=mysql_query($q);
	$e=mysql_fetch_array($r,MYSQL_ASSOC);
	
	if($e['workingDayList']){
		$workingDayList=explode(',',$e['workingDayList']);
		$numOfDays=count($workingDayList);
		$workingDayINTList=explode(',',$e['workingDayINTList']);
	}else{echo '{E}-du lieu ngay thang khong ton tai'; mysql_close($_connect); die;}
	

/*LOADFILE
*/	
	if($_frm_){
		$q='CREATE TABLE IF NOT EXISTS mcc_log_thaisan(ngaythangnam DATE NOT NULL,
		ma_nhanvien VARCHAR(16) NOT NULL,
		h1 TIME NULL DEFAULT NULL,
		h2 TIME NULL DEFAULT NULL,
		h3 TIME NULL DEFAULT NULL,
		h4 TIME NULL DEFAULT NULL,
		h5 TIME NULL DEFAULT NULL,
		h6 TIME NULL DEFAULT NULL)COLLATE="utf8_general_ci" ENGINE=MEMORY;';
		mysql_query($q);
		$q = 'DELETE FROM mcc_log_thaisan';
		mysql_query($q);
		$q = 'ALTER TABLE mcc_log_thaisan AUTO_INCREMENT=1';
		mysql_query($q);
	
		foreach($workingDayList as $day){
			$f = _MCC_LOG_FOLDER.'/'.$day.'.txt';
			
			if(file_exists($f)){
				$q = 'LOAD DATA INFILE "'.$f.'" INTO TABLE mcc_log_thaisan
				FIELDS TERMINATED BY " " LINES TERMINATED BY "\r\n"
				(ma_nhanvien, @H1,@H2,@H3,@H4,@H5,@H6)
				SET
				mcc_log_thaisan.h1=@H1,
				mcc_log_thaisan.h2=@H2,
				mcc_log_thaisan.h3=@H3,
				mcc_log_thaisan.h4=@H4,
				mcc_log_thaisan.h5=@H5,
				mcc_log_thaisan.h6=@H6,
				mcc_log_thaisan.ngaythangnam="'.$day.'"';
			
				if(!mysql_query($q)){exit('{E}-Xem lại file chấm công.');}
				
			}else{exit('{E}-Không tìm thấy file chấm công.');}
		}
		
		echo '{I}-Đọc dữ liệu thành công.';
		$q ='SELECT
				COUNT(ngaythangnam) AS nDays,
				mcc_log_thaisan.ma_nhanvien,
				CONCAT(ho," ",ten) AS FullName,
				ns_nhansu.id_donvi,donvi,
				tim_thaisan(ns_nhansu.ma_nhanvien,ngaythangnam) AS kieuthaisan,
				maternityFixWorkingTime(workingTime(h1,h2,h3,h4,h5,h6,"07:00:00",(SELECT closingTime),(SELECT ns_nhansu.id_donvi),(SELECT disable)),(select kieuthaisan)) AS workingTime,
				maternityFixOverTime( overTime(h2,h4,h6,(SELECT closingTime),(SELECT ns_nhansu.id_donvi),(SELECT disable)), (SELECT kieuthaisan)) AS overTime,
				dayCount((SELECT workingTime)) AS nDay,
				ADDTIME((SELECT workingTime),(SELECT overTime)) AS timeofDay,
				SUM(IF(holiday=0,(SELECT nDay),0)) AS NCHC
			FROM mcc_log_thaisan
			JOIN ns_nhansu
				ON mcc_log_thaisan.ma_nhanvien=ns_nhansu.ma_nhanvien
			JOIN mcc_working_day
				ON day=ngaythangnam AND ns_nhansu.id_donvi=mcc_working_day.id_donvi
			INNER JOIN ns_donvi
				ON ns_nhansu.id_donvi=ns_donvi.id_donvi
			WHERE ns_nhansu.id_donvi IN (8)
			GROUP BY mcc_log_thaisan.ma_nhanvien
			ORDER BY ns_nhansu.id_donvi,ns_nhansu.id_chucvu';
	}
	
/*CALCULATION
*/		
	else{
		if($unitList){
			$q = '';
			// echo $q;
			die;
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
						$T->hmSum($partTimeList),'-',$e['nDays'],$emp_dayList,$T->hmShow($e['holidayTimeTotal']),$e['gioitinh'],0
						);
			}
			
			/*[2] Tìm thai sản + cập nhật!
			*/
			$q = $pregnancySearch($unitList,$fromDate,$toDate);
			$r = mysql_query($q);
			if(mysql_affected_rows($_connect)){
				while($e=mysql_fetch_array($r)){
					if(isset($dataMonth[$e['ma_nhanvien']])){
						
						//tinh toan lai gio hanh chanh
						$pregString = explode(',',$e['pregnancyData']);
						$n = 0;
						foreach($pregString as $p){
							$d = explode('|',$p);//ngaydau,ngaycuoi,kieu
							pregnancyReCalculation($dataMonth[$e['ma_nhanvien']][10],$holidayList,$dataMonth[$e['ma_nhanvien']][3],$dataMonth[$e['ma_nhanvien']][4],$d,$n);
						}
						
						$dataMonth[$e['ma_nhanvien']][8]=$n;
						
						//cap nhat lai tong gio hanh chanh
						$dataMonth[$e['ma_nhanvien']][6] = $T->hmSum($dataMonth[$e['ma_nhanvien']][3]);
						//tong gio lam them
						$dataMonth[$e['ma_nhanvien']][7] = $T->hmSum($dataMonth[$e['ma_nhanvien']][4]);
						
						//tong gio
						$dataMonth[$e['ma_nhanvien']][5] = $T->hmAdd($dataMonth[$e['ma_nhanvien']][6],$dataMonth[$e['ma_nhanvien']][11]);
						$dataMonth[$e['ma_nhanvien']][5] = $T->hmAdd($dataMonth[$e['ma_nhanvien']][5],$dataMonth[$e['ma_nhanvien']][7]);
					
						//cap nhat che do
						$dataMonth[$e['ma_nhanvien']][13] = 1;
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
			/*tim xin phep -> CD
			*/
			$q = $timxinphep('2,3,4',$fromDate,$toDate);
			$r = mysql_query($q);
			$_24 = $_3 = 0;
			if(mysql_affected_rows($_connect)){
				while($e=mysql_fetch_array($r)){
					if(isset($dataMonth[$e['ma_nhanvien']])){
							switch($e['id_phepnam']){
								case 2:
								case 4:
									
									$q = 'SELECT intersectDays("'.$fromDate.'","'.$toDate.'","'.$e['ngay_batdau'].'","'.$e['ngay_ketthuc'].'")';
									$r = mysql_query($q);
									$idays = mysql_fetch_row($r);
									
									if($e['songaytru']<3) $_24 = ($idays[0]>3) ? 3 : $idays[0];
										
								break;
								
								case 3:
									if(!$e['songaytru']) $_3 = 1;
								break;
							}
							$dataMonth[$e['ma_nhanvien']][13]=$_24+$_3;
						}
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
			
			$htmlTable .=   '<input type="button" value="toPDF" onclick="window.open(\'mcc/topdf.month.php\',\'_blank\')">
                            <input type="button" value="toExcel" onclick="window.open(\'mcc/toexcel.month.php\',\'_blank\')">
                            <table class="default" width="100%">';
			$htmlTable .=   '<tr class="header">
							<th>#</th>
                            <th>MNV</th>
                            <th>HOTEN</th>
							<th>GT</th>
                            <th>DONVI</th>
                            <th>CC</th>
							<th>N</th>';
            
			
			$htmlTable .= '<th>TG</th><th>TGHC</th><th>LT</th><th>CN</th><th>B_CN</th><th>CD</th>';
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