<?
require_once('../_global.inc');
$id_xuly_thaisan = $_POST['id_xuly_thaisan'];

$_conn = mysql_connect(_MYSQL_HOST, _MYSQL_USER, _MYSQL_PASSWORD);
mysql_select_db(_MYSQL_DATABASE, $_conn);
mysql_query("SET NAMES 'UTF8'");

	$q = 'DELETE FROM ns_xuly_thaisan WHERE id_xuly_thaisan='.$id_xuly_thaisan;
	if (mysql_query($q)) echo "Xóa thành công"; else echo "Không tồn tại ID này";

mysql_close($_conn);
?>