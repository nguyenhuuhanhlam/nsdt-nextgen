<?
require_once('../_global.inc');
$responseText 	= null;
$id_thaisan = $_POST['id_thaisan'];
$thaisan = mb_convert_case(mb_ereg_replace ('/\s+/', chr(32), trim($_POST['thaisan'])), MB_CASE_TITLE, "UTF-8");

$con_A = mysql_connect(_MYSQL_HOST, _MYSQL_USER, _MYSQL_PASSWORD);
mysql_select_db(_MYSQL_DATABASE, $con_A);
mysql_query("SET NAMES 'UTF8'");
		
if ($thaisan == null) $responseText .= 'Tên không được rỗng.' .chr(13);
else{
	$q = 'UPDATE ns_thaisan SET thaisan="'.$thaisan.'" WHERE id_thaisan='.$id_thaisan;
	if ($responseText == null){
	if	(mysql_query($q)){
		$responseText = '1';
	}else
		$responseText = 'Cập nhật dữ liệu bị lỗi, vui lòng xem lại cách nhập';
	}
}
print $responseText;		
mysql_close($con_A);
?>