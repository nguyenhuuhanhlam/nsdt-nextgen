<?
require_once('../_global.inc');
require_once('../_nsdt/nsdt_php.php');
$responseText 	= null;
$ma_nhanvien 	= trim($_POST['ma_nhanvien']);
$id_donvi		= $_POST['id_donvi'];
$id_thaisan 	= $_POST['id_thaisan'];
$ngay_batdau	= nsdt_inputDate($_POST['ngay_batdau']);
$ngay_ketthuc	= nsdt_inputDate($_POST['ngay_ketthuc']);
//$t = count(dateRange($ngay_batdau,$ngay_ketthuc))-1;

$con_A = mysql_connect(_MYSQL_HOST, _MYSQL_USER, _MYSQL_PASSWORD);
mysql_select_db(_MYSQL_DATABASE, $con_A);

if($ma_nhanvien == null) $responseText .= '- Chưa nhập ID nhân viên.'.chr(13);
if($id_thaisan == null) $responseText .= '- Chưa nhập tình trạng thai sản.'.chr(13);
if($ngay_batdau == null) $responseText .= '- Chưa nhập ngày bắt đầu.'.chr(13);
if($ngay_ketthuc == null)$responseText .= '- Chưa nhập ngày kết thúc.'.chr(13);
if(nsdt_dateGt($ngay_batdau,$ngay_ketthuc)) $responseText .= '- Ngày kết thúc không được nhỏ hơn.'.chr(13);
if ($responseText == null){
		$q = 'SELECT sum(timeSegValid("'.$ngay_batdau.'","'.$ngay_ketthuc.'",ngay_batdau,ngay_ketthuc)) as error
			FROM ns_xuly_thaisan
			WHERE ma_nhanvien = "'.$ma_nhanvien.'"';
		$r = mysql_query($q);
		while($e = mysql_fetch_array($r)){
		if($e['error']>0){
				$responseText .= 'Trùng ngày thai sản!';}
		else{

		$q = 'SELECT count(dt) AS songaynghi FROM calendar  WHERE dt BETWEEN "'.$ngay_batdau.'" AND "'.$ngay_ketthuc.'" AND dn <> "Su" AND isHoliday <> 1';
		$result = mysql_query($q);
		while($entry = mysql_fetch_array($result)){
			$songaynghi = $entry['songaynghi'];}
		
		$q = 'INSERT INTO ns_xuly_thaisan(ma_nhanvien,id_thaisan,
								ngay_batdau,ngay_ketthuc,songaynghi)
			VALUES ("'.$ma_nhanvien.'",
					'.$id_thaisan.',
					"'.$ngay_batdau.'",
					"'.$ngay_ketthuc.'",
					'.$songaynghi.')';
	
		if	(mysql_query($q)){
			$responseText = '1';
		}else 
			$responseText = 'Nhập liệu bị lỗi';
	}}}
print $responseText;
mysql_close($con_A);
?>