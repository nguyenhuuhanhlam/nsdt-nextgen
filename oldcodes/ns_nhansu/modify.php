<?
require_once('../_global.inc');
require_once('../_nsdt/nsdt_php.php');

$responseText = null;
$ma_nhanvien		= $_POST['ma_nhanvien'];
$ma_the 			= $_POST['ma_the'];
$ma_the_sua 		= trim($_POST['ma_the_sua']);
$gioitinh 			= $_POST['gioitinh'];
$ho 				= $_POST['ho'];
$ten 				= $_POST['ten'];
$noicapcmnd 		= $_POST['noicapcmnd'];
$ngaysinh 			= ngaysinh($_POST['ngaysinh']);
$id_quoctich 		= $_POST['id_quoctich'];
$id_dantoc 			= $_POST['id_dantoc'];
$socmnd 			= $_POST['socmnd'];
$noisinh 			= $_POST['noisinh'];
$ngaycapcmnd 		= nsdt_inputDate($_POST['ngaycapcmnd']);
$noisinh 			= $_POST['noisinh'];
$nguyenquan 		= $_POST['nguyenquan'];
$diachithuongtru 	= $_POST['diachithuongtru'];
$diachicungu 		= $_POST['diachicungu'];
$id_hocvan 			= $_POST['id_hocvan'];
$id_honnhan 		= $_POST['id_honnhan'];
$id_congviec 		= $_POST['id_congviec'];
$hesoluong 			= $_POST['hesoluong'];
$ngayvaolam 		= nsdt_inputDate($_POST['ngayvaolam']);
$ngayhetthuviec 	= nsdt_inputDate($_POST['ngayhetthuviec']);
$btkt 				= $_POST['btkt'];
$ma_the_xuly = null;
$connect = mysql_connect(_MYSQL_HOST, _MYSQL_USER, _MYSQL_PASSWORD);
mysql_select_db(_MYSQL_DATABASE, $connect);

if($ma_the_sua=='') $ma_the_xuly = 'NULL'; else {
	if($ma_the_sua==$ma_the) $ma_the_xuly = $ma_the_sua;
	else {
		$q = 'SELECT ma_the FROM ns_nhansu WHERE ma_the='.$ma_the_sua.'';
		mysql_query($q);
		if(mysql_affected_rows($connect)){
			$responseText .= 'Mã thẻ đã tồn tại!';
		} else $ma_the_xuly = $ma_the_sua;
	}
}
$q = 	'UPDATE ns_nhansu SET
		ma_the 		= '.$ma_the_xuly.',
		ho				= "'.$ho.'",
		ten				= "'.$ten.'",
		socmnd			= "'.$socmnd.'",
		ngaysinh		= "'.$ngaysinh.'",
		gioitinh		= "'.$gioitinh.'",
		ngayvaolam		= "'.$ngayvaolam.'",
		ngayhetthuviec	= "'.$ngayhetthuviec.'",
		id_quoctich		= '.$id_quoctich.',
		id_dantoc		= '.$id_dantoc.',
		ngaycapcmnd		= "'.$ngaycapcmnd.'",
		noicapcmnd		= '.$noicapcmnd.',
		noisinh			= '.$noisinh.',
		id_hocvan		= '.$id_hocvan.',
		id_honnhan		= '.$id_honnhan.',			
		nguyenquan		= "'.$nguyenquan.'",
		diachithuongtru	= "'.$diachithuongtru.'",
		diachicungu		= "'.$diachicungu.'",
		id_congviec		= '.$id_congviec.',
		hesoluong		= '.$hesoluong.',
		btkt			= '.$btkt.'
		WHERE ma_nhanvien = "'.trim($ma_nhanvien).'"';
		// echo $q;
		// die;
	if ($responseText == null){
	if	(mysql_query($q)){
		$responseText = '1';
	}else 
		$responseText = 'Không cập nhật dữ liệu vào bảng!';
}
print $responseText;
mysql_close($connect);

?>