<?
	session_start();
	require_once('../_global.inc');
	require_once('../_nsdt/_time.class.php');
	
	$ngaythangnam = $_POST['ngaythangnam'];
	$_n = (int)$_POST['_n'];
	
	if($ngaythangnam){
		
		$_connect = mysql_connect(_MYSQL_HOST, $_SESSION['@username'], $_SESSION['@password']);
		mysql_select_db(_MYSQL_DATABASE, $_connect);
		
		$ngaythangnam = date('Y-m-d', strtotime($ngaythangnam));
		$q = 'SELECT * FROM ___mcc_working_day WHERE day="'.$ngaythangnam.'"';
		$result = mysql_query($q);
		
		/*kiem tra ngay xu ly co ton tai tren workingday*/
		$chk_workingDay = mysql_affected_rows($_connect);
		if(!$chk_workingDay){exit('{E}-Chưa có giờ đóng chuyền cho ngày này.'); mysql_close($_connect); die;}
		
		if($_n){
			$len_giotrongngay = $_n * 8 + $_n-1;
			$q2 = ' AND LENGTH(gio_trongngay)='.$len_giotrongngay;
		} else $q2 = null;
		
		$q = 'SELECT * FROM ___mcc_analysis
		JOIN ns_nhansu ON ___mcc_analysis.ma_the = ns_nhansu.ma_the
		INNER JOIN ns_donvi ON ns_donvi.id_donvi = ns_nhansu.id_donvi
		WHERE ngaythangnam="'.$ngaythangnam.'"'.$q2.' ORDER BY ns_nhansu.id_donvi';
		
		////chuẩn bị dữ liệu xuất ra slickgrid
		$result = mysql_query($q);
	
		$D = array();
		$i=0;
		while($entry = mysql_fetch_array($result)){
			$gio_trongngay = explode(chr(32), $entry['gio_trongngay']);
			$n = count($gio_trongngay);
			$i++;
			$a = array(	'I'=>$i,
						'N'=>$entry['ma_nhanvien'],
						'F'=>$entry['ho'].' '.$entry['ten'],
						'G'=>$entry['donvi']);
			$k = 1;
			foreach($gio_trongngay as $gio){
				$a['H'.$k] = substr($gio, 0, 5);
				$k++;
			}
			array_push($D, $a);
		}
		echo json_encode($D);
	
	mysql_close($_connect);
	} else { exit('{E}-Chưa chọn ngày tháng.');}
?>