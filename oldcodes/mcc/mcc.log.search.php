<?
require_once('../_global.inc');
$_connect = mysql_connect(_MYSQL_HOST, _MYSQL_USER, _MYSQL_PASSWORD);
mysql_select_db(_MYSQL_DATABASE, $_connect);
$q = 'SELECT ma_nhanvien FROM ns_nhansu WHERE ma_nhanvien LIKE "%'.$_POST['ma_nhanvien'].'"';
$r = mysql_query($q);
if(mysql_affected_rows($_connect)){
    $row = mysql_fetch_array($r);
    echo $row[0];
}
?>