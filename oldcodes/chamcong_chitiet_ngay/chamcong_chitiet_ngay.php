<?
require_once('../_global.inc');
require_once('../_nsdt/nsdt_php.php');

$_frm_ = (int)$_POST['_frm_'];
$fromDate = nsdt_inputDate($_POST['fromDate']);
$toDate = nsdt_inputDate($_POST['toDate']);
$unitList = ($_POST['unitList']!='null') ? implode(',',$_POST['unitList']) : null;
// $ma_nhanvien = trim($_POST['ma_nhanvien']);
@unlink('../chamcong_chitiet_ngay/chamcong_chitiet_ngay.json');
@unlink('../chamcong_chitiet_ngay/columnName.json');
if(!$fromDate){exit('{E}Chưa nhập ngày bắt đầu.');}
if(!$toDate){exit('{E}Chưa nhập ngày kết thúc.');}
	$_connect = mysql_connect(_MYSQL_HOST, _MYSQL_USER, _MYSQL_PASSWORD);
	mysql_select_db(_MYSQL_DATABASE, $_connect);
	
/*LOADFILE
*/	
	if($_frm_){
		$q='CREATE TABLE IF NOT EXISTS chamcong_chitiet_ngay(ngaythangnam DATE NOT NULL, ma_nhanvien VARCHAR(16) NOT NULL)COLLATE = "utf8_general_ci" ENGINE=MEMORY';
		mysql_query($q);
		$q = 'DELETE FROM chamcong_chitiet_ngay';
		mysql_query($q);
		
		$q = '
		SELECT 	DISTINCT day AS workingDayList
		FROM mcc_working_day
				WHERE day BETWEEN "'.$fromDate.'" AND "'.$toDate.'"
				ORDER BY day';
				
		$r=mysql_query($q);
		$workingDayList = array();
		while ($e=mysql_fetch_array($r,MYSQL_ASSOC)){
		array_push($workingDayList,$e['workingDayList']);
		}

		foreach($workingDayList as $day){
		$f = _MCC_LOG_FOLDER.'/'.$day.'.txt';

			if(file_exists($f)){
				$q = 'LOAD DATA INFILE "'.$f.'" INTO TABLE chamcong_chitiet_ngay
				FIELDS TERMINATED BY " " LINES TERMINATED BY "\r\n"
				(ma_nhanvien,@H1,@H2,@H3,@H4,@H5,@H6)
				SET
				chamcong_chitiet_ngay.ngaythangnam="'.$day.'"';

				if(!mysql_query($q)){echo '{E}-File dữ liệu không load được';die;}
				
				
			}else { echo '{E}-File dữ liệu không tìm thấy'; die;}
		}
		echo '{I}-Load File thành công';
}
else{
	// lay danh sach ngay
	$q = 'SELECT dt FROM calendar WHERE dt BETWEEN "'.$fromDate.'" AND "'.$toDate.'"';
		$r=mysql_query($q);
		$dtList = array();
		$columnName = array();
		while ($e=mysql_fetch_array($r,MYSQL_ASSOC)){
			$dtList[] = 'sum(if(ngaythangnam="'.$e['dt'].'",1,0)) as "'.(int)substr($e['dt'],-2).'"';
			$columnName[] = (int)substr($e['dt'],-2);
		}
	$q = 'SELECT ma_nhanvien, concat(ho," ",ten) as hoten, donvi, 0 as tc, ';
	
	$q .= implode(',', $dtList);
		
	$q .= ' FROM chamcong_chitiet_ngay
			RIGHT JOIN ns_nhansu USING (ma_nhanvien)
			JOIN ns_donvi ON ns_donvi.id_donvi = ns_nhansu.id_donvi
			WHERE 
			ns_donvi.id_donvi IN ('.$unitList.')
			GROUP BY ns_nhansu.ma_nhanvien
			ORDER BY ns_nhansu.id_donvi';
// echo $q;
// die;
		$r = mysql_query($q);				
		
		$data = array();
		while($e = mysql_fetch_array($r,MYSQL_ASSOC)){
			$data[$e['ma_nhanvien']] = $e;
		}
		//print_r($data);die;
		
	//Cap nhat ngay nghi ben ns_xinphep
	$q ='SELECT ns_nhansu.ma_nhanvien,
		GROUP_CONCAT(DATE_FORMAT(ngay_batdau,"%e") ORDER BY ngay_batdau) AS ngaynghi,
		GROUP_CONCAT((id_phepnam) ORDER BY ngay_batdau) AS id_phepnam,
		GROUP_CONCAT((songaynghi) ORDER BY ngay_batdau) AS songaynghi
		FROM ns_xinphep
		JOIN ns_nhansu USING(ma_nhanvien)
		WHERE ngay_batdau between "'.$fromDate.'" and "'.$toDate.'" and ns_nhansu.id_donvi IN ('.$unitList.')
		GROUP BY ns_nhansu.ma_nhanvien';
// echo $q; die;
	$r=mysql_query($q);
	
		$ngaynghiList = array();
		while ($e=mysql_fetch_array($r,MYSQL_ASSOC)){
			$ngaynghiList[] = array($e['ma_nhanvien'],explode(",",$e['ngaynghi']),explode(",",$e['songaynghi']),explode(",",$e['id_phepnam']));
		}			

		foreach($ngaynghiList as $n){
			foreach($n[1] as $m){
				$data[$n[0]][$m] = current($n[2])>=1 ? kieuphep(current($n[3])) : 1-(current($n[2]));
				next($n[2]);
				next($n[3]);
			}
		}
		
	foreach ($data as $d){
		$data[$d['ma_nhanvien']]['tc'] = array_sum(array_slice($d,4));
	}
	
file_put_contents('../chamcong_chitiet_ngay/dtList.json',json_encode($columnName));	
file_put_contents('../chamcong_chitiet_ngay/chamcong_chitiet_ngay.json',json_encode($data));	
	echo '<style scoped>.default td {border:solid 1px #A8D4FF;}
			.default th {background-color:#f4faff; border:solid 1px #A8D4FF;}
			tr.header{height:20px}
			.ngaycong {width:22px; text-align:center}
			.nghi {width:22px; text-align:center; background-color:#ffaaaa}
			.su {background-color:#ffaaaa}
		</style>';  
        echo    '<table class="default" width="1100">';
		echo '<tr class="header">';
			echo '<th>STT</th><th>MNV</th><th>HOTEN</th><th>DV</th>';
			foreach ($columnName as $c){
					echo '<th class="ngaycong">'.$c.'</th>';
				}
			echo '<th>TC</th>';
		echo '</tr>';
		$i=0;
        foreach($data as $r){
            echo '<tr>';
			echo '<td>'.++$i.'</td>';
            echo '<td>'.$r['ma_nhanvien'].'</td>';
			echo '<td>'.$r['hoten'].'</td>';
			echo '<td>'.$r['donvi'].'</td>';
			foreach ($columnName as $c){
				echo '<td>'.$r[$c] .'</td>';
			}
			echo '<td>'.$r['tc'].'</td>';
            echo '</tr>';
        }
        echo '</table>';
	}
	mysql_close($_connect);
?>