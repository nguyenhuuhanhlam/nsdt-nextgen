<?
require_once('../_global.inc');
require_once('../_nsdt/nsdt_php.php');
$responseText = null;

$con = mysql_connect(_MYSQL_HOST, _MYSQL_USER, _MYSQL_PASSWORD);
mysql_select_db(_MYSQL_DATABASE, $con);

$ma_nhanvien = trim($_POST['ma_nhanvien']);
$ma_the = isset($_POST['ma_the'])? $_POST['ma_the'] : null;
$ho = mb_convert_case(mb_ereg_replace ('/\s+/', chr(32), trim($_POST['ho'])), MB_CASE_TITLE, "UTF-8");
$ten = mb_convert_case(mb_ereg_replace ('/\s+/', chr(32), trim($_POST['ten'])), MB_CASE_TITLE, "UTF-8");
$socmnd = trim($_POST['socmnd']);
$ngaysinh = ngaysinh($_POST['ngaysinh']);
$gioitinh = $_POST['gioitinh'];
$id_quoctich = $_POST['id_quoctich'];
$id_dantoc = $_POST['id_dantoc'];
$ngaycapcmnd = nsdt_inputDate($_POST['ngaycapcmnd']);
$noicapcmnd = $_POST['noicapcmnd'];
$noisinh = $_POST['noisinh'];
$id_hocvan = $_POST['id_hocvan'];
$id_honnhan = $_POST['id_honnhan'];
$id_hdld = $_POST['id_hdld'];
$nguyenquan = mb_convert_case(mb_ereg_replace ('/\s+/', chr(32), trim($_POST['nguyenquan'])), MB_CASE_TITLE, "UTF-8");
$diachithuongtru1 = mb_convert_case(mb_ereg_replace ('/\s+/', chr(32), trim($_POST['diachithuongtru1'])), MB_CASE_TITLE, "UTF-8");
$diachithuongtru2 = mb_convert_case(mb_ereg_replace ('/\s+/', chr(32), trim($_POST['diachithuongtru2'])), MB_CASE_TITLE, "UTF-8");
$diachithuongtru3 = mb_convert_case(mb_ereg_replace ('/\s+/', chr(32), trim($_POST['diachithuongtru3'])), MB_CASE_TITLE, "UTF-8");
$diachithuongtru4 = mb_convert_case(mb_ereg_replace ('/\s+/', chr(32), trim($_POST['diachithuongtru4'])), MB_CASE_TITLE, "UTF-8");
$diachithuongtru = $diachithuongtru1.'-'.$diachithuongtru2.'-'.$diachithuongtru3.'-'.$diachithuongtru4;
$diachicungu1 = mb_convert_case(mb_ereg_replace ('/\s+/', chr(32), trim($_POST['diachicungu1'])), MB_CASE_TITLE, "UTF-8");
$diachicungu2 = mb_convert_case(mb_ereg_replace ('/\s+/', chr(32), trim($_POST['diachicungu2'])), MB_CASE_TITLE, "UTF-8");
$diachicungu3 = mb_convert_case(mb_ereg_replace ('/\s+/', chr(32), trim($_POST['diachicungu3'])), MB_CASE_TITLE, "UTF-8");
$diachicungu4 = mb_convert_case(mb_ereg_replace ('/\s+/', chr(32), trim($_POST['diachicungu4'])), MB_CASE_TITLE, "UTF-8");
$diachicungu = $diachicungu1.'-'.$diachicungu2.'-'.$diachicungu3.'-'.$diachicungu4;
$id_donvi = $_POST['id_donvi'];
$id_chucvu = $_POST['id_chucvu'];
$id_congviec = $_POST['id_congviec'];
$ngayvaolam = nsdt_inputDate($_POST['ngayvaolam']);
$ngayhetthuviec = nsdt_inputDate($_POST['ngayhetthuviec']);
$hesoluong = $_POST['hesoluong'];
$btkt = $_POST['btkt'];

if ($ma_nhanvien == null) $responseText .= '- Chưa có mã nhân viên.'.chr(13);
if ($ho == null) $responseText .= '- Chưa có họ nhân viên.'.chr(13);
if ($ten == null) $responseText .= '- Chưa có tên nhân viên.'.chr(13);
if ($ngaysinh == null) $responseText .= '- Chưa nhập ngày sinh hoặc ngày không tồn tại.'.chr(13);
if ($gioitinh == null) $responseText .= '- Chưa nhập số gioi tinh.'.chr(13);
if ($id_quoctich == null) $responseText .= '- Chưa nhập Quốc tịch.'.chr(13);
if ($socmnd == null) $responseText .= '- Chưa nhập số cmnd.'.chr(13);
if ($id_dantoc == null) $responseText .= '- Chưa nhập dân tộc.'.chr(13);
if ($ngaycapcmnd == null) $responseText .= '- Chưa nhập ngày cấp cmnd hoặc ngày không tồn tại.'.chr(13);
if ($noicapcmnd == null) $responseText .= '- Chưa nhập nơi cấp cmnd.'.chr(13);
if ($noisinh == null) $responseText .= '- Chưa nhập nơi sinh.'.chr(13);
if ($id_hocvan == null) $responseText .= '- Chưa nhập trình độ học vấn.'.chr(13);
if ($id_honnhan == null) $responseText .= '- Chưa nhập tình trạng hôn nhân.'.chr(13);
if ($nguyenquan == null) $responseText .= '- Chưa nhập nguyên quán.'.chr(13);
if ($diachithuongtru1 == null) $responseText .= '- Chưa nhập địa chỉ thường trú.'.chr(13);
if ($diachicungu == null) $responseText .= '- Chưa nhập địa chỉ cư ngụ.'.chr(13);
if ($id_donvi == null) $responseText .= '- Chưa nhập đơn vị.'.chr(13);
if ($id_chucvu == null) $responseText .= '- Chưa nhập chức vụ.'.chr(13);
if ($id_congviec == null) $responseText .= '- Chưa nhập công việc.'.chr(13);
if ($ngayvaolam == null) $responseText .= '- Chưa nhập ngày vào làm hoặc ngày không tồn tại.'.chr(13);
if ($ngayhetthuviec == null) $responseText .= '- Chưa nhập ngày kết thúc thử việc hoặc ngày không tồn tại.'.chr(13);
if(nsdt_dateGt($ngayvaolam,$ngayhetthuviec)) $responseText .= '- Ngày kết thúc không được nhỏ hơn.'.chr(13);
if ($id_hdld == null) $responseText .= '- Chưa nhập loại HĐLĐ.'.chr(13);
if ($btkt>1) $responseText .= '- BT/KT chỉ có giá trị 0 hoặc 1.'.chr(13);
	if($ma_the){
	$q = 'INSERT INTO ns_nhansu(ma_nhanvien,
					ma_the, ho, ten, socmnd, ngaysinh,
					gioitinh, id_quoctich, id_dantoc, ngaycapcmnd, noicapcmnd,
					noisinh, id_hocvan, id_honnhan, nguyenquan, diachithuongtru,
					diachicungu, id_donvi, id_chucvu, id_congviec, ngayvaolam, ngayhetthuviec, hesoluong,id_hdld, id_nghidaihan,ngay_nghiviec,btkt)
		VALUES(
					"'.$ma_nhanvien.'",
					'.$ma_the.',
					"'.$ho.'",
					"'.$ten.'",
					'.$socmnd.',
					"'.$ngaysinh.'",
					"'.$gioitinh.'",
					'.$id_quoctich.',
					'.$id_dantoc.',
					"'.$ngaycapcmnd.'",
					'.$noicapcmnd.',
					'.$noisinh.',
					'.$id_hocvan.',
					'.$id_honnhan.',
					"'.$nguyenquan.'",
					"'.$diachithuongtru.'",
					"'.$diachicungu.'",
					'.$id_donvi.',
					'.$id_chucvu.',
					'.$id_congviec.',
					"'.$ngayvaolam.'",
					"'.$ngayhetthuviec.'",
					'.$hesoluong.',
					'.$id_hdld.',
					0,null,'.$btkt.')';}
	else{
	$q = 'INSERT INTO ns_nhansu(ma_nhanvien,
					ho, ten, socmnd, ngaysinh,
					gioitinh, id_quoctich, id_dantoc, ngaycapcmnd, noicapcmnd,
					noisinh, id_hocvan, id_honnhan, nguyenquan, diachithuongtru,
					diachicungu, id_donvi, id_chucvu, id_congviec, ngayvaolam, ngayhetthuviec, hesoluong,id_hdld,id_nghidaihan, ngay_nghiviec,btkt)
		VALUES(
					"'.$ma_nhanvien.'",
					"'.$ho.'",
					"'.$ten.'",
					"'.$socmnd.'",
					"'.$ngaysinh.'",
					"'.$gioitinh.'",
					'.$id_quoctich.',
					'.$id_dantoc.',
					"'.$ngaycapcmnd.'",
					'.$noicapcmnd.',
					'.$noisinh.',
					'.$id_hocvan.',
					'.$id_honnhan.',
					"'.$nguyenquan.'",
					"'.$diachithuongtru.'",
					"'.$diachicungu.'",
					'.$id_donvi.',
					'.$id_chucvu.',
					'.$id_congviec.',
					"'.$ngayvaolam.'",
					"'.$ngayhetthuviec.'",
					'.$hesoluong.',
					'.$id_hdld.',
					0,null,,'.$btkt.')';}
			 // echo $q;
			 // die;
if ($responseText == null){
	if	(mysql_query($q)){
		$q ='INSERT INTO thaydoi_hdld (ma_nhanvien,id_hdld,ngay_batdau,ngay_ketthuc)
			VALUES("'.$ma_nhanvien.'",'.$id_hdld.',"'.$ngayvaolam.'","'.$ngayhetthuviec.'")';
			mysql_query($q);
		$q ='INSERT INTO thechamcong (ma_nhanvien,ma_the,ngay_batdau,sotien,lydo)
			VALUES("'.$ma_nhanvien.'",'.$ma_the.',"'.$ngayvaolam.'",0,"Người mới vào")';
			mysql_query($q);
		$q ='INSERT INTO ns_tncn (ma_nhanvien,maso_tncn,ngaycap_maso,ngay_batdau,lydo)
			VALUES("'.$ma_nhanvien.'",null,null,null,null)';
			mysql_query($q);
		$responseText = '1';
	}else 
		$responseText = 'Không cập nhật dữ liệu vào bảng, vui lòng xem lại cách nhập!';
}
print $responseText;
mysql_close($con);
?>