<?
require_once('../_global.inc');

$con = mysql_connect(_MYSQL_HOST, _MYSQL_USER, _MYSQL_PASSWORD);
mysql_select_db(_MYSQL_DATABASE, $con);
$ma_nhanvien = $_POST['ma_nhanvien'];
$q='SELECT ns_nhansu.ma_nhanvien FROM ns_nhansu	WHERE ma_nhanvien = "'.$ma_nhanvien.'"';
mysql_query($q);
echo $responseText = mysql_affected_rows($con) ? 'Trùng mã nhân viên' : '';
mysql_close($con);
?>