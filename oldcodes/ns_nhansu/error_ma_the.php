<?
require_once('../_global.inc');

$con = mysql_connect(_MYSQL_HOST, _MYSQL_USER, _MYSQL_PASSWORD);
mysql_select_db(_MYSQL_DATABASE, $con);
$ma_the = $_POST['ma_the'];
$q='SELECT ns_nhansu.ma_the FROM ns_nhansu	WHERE ma_the = "'.$ma_the.'"';
mysql_query($q);
echo $responseText = mysql_affected_rows($con) ? 'Trùng mã thẻ' : '';
mysql_close($con);
?>