<?
require_once('../_global.inc');
require_once('../_nsdt/nsdt_php.php');
$responseText 	= null;
$id_xuly_thaisan		= $_POST['id_xuly_thaisan'];
$ma_nhanvien 	= $_POST['ma_nhanvien'];
$id_thaisan	 	= $_POST['id_thaisan'];
$ngay_batdau	= nsdt_inputDate($_POST['ngay_batdau']);
$ngay_ketthuc	= nsdt_inputDate($_POST['ngay_ketthuc']);

$con_A = mysql_connect(_MYSQL_HOST, _MYSQL_USER, _MYSQL_PASSWORD);
mysql_select_db(_MYSQL_DATABASE, $con_A);
mysql_query("SET NAMES 'UTF8'");
		
if ($ma_nhanvien == null){$responseText .= 'Chưa có id nhân viên' .chr(13);}
if ($id_thaisan == null){$responseText .= 'Chưa nhập thai sản' .chr(13);}
	//tính ngày nghỉ
	$q = 'SELECT count(dt) AS songaynghi FROM calendar  WHERE dt BETWEEN "'.$ngay_batdau.'" AND "'.$ngay_ketthuc.'" AND dn <> "Su" AND isHoliday <> 1';
	$result = mysql_query($q);
	while($entry = mysql_fetch_array($result)){
		$songaynghi = $entry['songaynghi'];}
	// update
		$q = 'UPDATE ns_xuly_thaisan SET
				
			id_thaisan		= '.$id_thaisan.',
			ngay_batdau		= "'.$ngay_batdau.'",
			ngay_ketthuc	= "'.$ngay_ketthuc.'",
			songaynghi		='.$songaynghi.'
			WHERE ma_nhanvien = "'.$ma_nhanvien.'" AND id_xuly_thaisan='.$id_xuly_thaisan.'';

if ($responseText == null){
	if	(mysql_query($q)){
		echo '1';
	}else 
		$responseText = 'Cập nhật dữ liệu bị lỗi, vui lòng xem lại cách nhập';
}
	print $responseText;	
mysql_close($con_A);
?>