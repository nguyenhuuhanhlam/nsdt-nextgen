<?
require_once('../_global.inc');

$con = mysql_connect(_MYSQL_HOST, _MYSQL_USER, _MYSQL_PASSWORD);
mysql_select_db(_MYSQL_DATABASE, $con);

$ma_nhanvien = $_POST['ma_nhanvien'];

$q = 	'SELECT ns_nhansu.ho, ns_nhansu.ten, ns_nhansu.ma_nhanvien, ns_nhansu.id_donvi, ns_donvi.donvi
		FROM ns_nhansu JOIN ns_donvi ON ns_nhansu.id_donvi = ns_donvi.id_donvi
		WHERE ma_nhanvien LIKE "%'.$ma_nhanvien.'"';

$result = mysql_query($q);

$entry = mysql_fetch_array($result);

$responseText = array('ma_nhanvien'=>$entry['ma_nhanvien'],
				'hoten'=>$entry['ho']." ".$entry['ten'],'id_donvi'=>$entry['id_donvi'],'donvi'=>$entry['donvi']);
				
mysql_close($con);

print json_encode($responseText);
die;
?>