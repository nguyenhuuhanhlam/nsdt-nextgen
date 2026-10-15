<?
require_once('../_global.inc');
$responseText = null;
$thaisan = mb_convert_case(mb_ereg_replace ('/\s+/', chr(32), trim($_POST['thaisan'])), MB_CASE_TITLE, "UTF-8");

$con_A = mysql_connect(_MYSQL_HOST, _MYSQL_USER, _MYSQL_PASSWORD);
mysql_select_db(_MYSQL_DATABASE, $con_A);
mysql_query("SET NAMES 'UTF8'");
		
if ($thaisan == null){
	$responseText .= 'Tên không được rỗng' .chr(13);
}else{
	$q = 'INSERT INTO ns_thaisan(thaisan) VALUES("'.$thaisan.'")';
	if(mysql_query($q)) $responseText = '1'; else $responseText = 'Trùng tên';
}
print $responseText;
mysql_close($con_A);
?>