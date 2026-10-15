<?
require_once('../_tcpdf/tcpdf.php');
require_once('../_global.inc');
require_once('../_nsdt/nsdt_php.php');
$con = mysql_connect(_MYSQL_HOST, _MYSQL_USER, _MYSQL_PASSWORD);
mysql_select_db(_MYSQL_DATABASE, $con);
	$q ='SELECT ma_nhanvien,UPPER(CONCAT(ho," ",ten)) AS fullname,ngaysinh,UPPER(gioitinh) AS gioitinh,UPPER(donvi) AS donvi,ma_the,UPPER(chucvu) AS chucvu,UPPER(congviec) AS congviec,ngayvaolam, UPPER(hocvan) AS hocvan,UPPER(nguyenquan) AS nguyenquan,UPPER(diachicungu) AS diachicungu,socmnd,ngaycapcmnd,UPPER(nc.tinhthanh) AS noicapcmnd, right(ma_nhanvien,4) AS sohdld FROM ns_nhansu 
	JOIN ns_donvi USING(id_donvi)
	JOIN ns_chucvu USING(id_chucvu)
	JOIN ns_congviec USING(id_congviec)
	JOIN ns_hocvan USING(id_hocvan)
	JOIN ns_tinhthanh AS nc ON ns_nhansu.noicapcmnd=nc.id_tinhthanh
	WHERE ma_nhanvien LIKE "%1754"';
	$result = mysql_query($q);
	$entry = mysql_fetch_array($result);
	$a = ['ma_nhanvien'=>$entry['ma_nhanvien'],'fullname'=>$entry['fullname'],'ngaysinh'=>$entry['ngaysinh'],'sex'=>$entry['gioitinh'],'donvi'=>$entry['donvi'],'ma_the'=>$entry['ma_the'],'chucvu'=>$entry['chucvu'],'congviec'=>$entry['congviec'],'ngayvaolam'=>$entry['ngayvaolam'],'hocvan'=>$entry['hocvan'],'nguyenquan'=>$entry['nguyenquan'],'diachicungu'=>$entry['diachicungu'],'socmnd'=>$entry['socmnd'],'ngaycapcmnd'=>$entry['ngaycapcmnd'],'noicapcmnd'=>$entry['noicapcmnd'],'sohdld'=>$entry['sohdld']];
	//echo $q; die;
class MYPDF extends TCPDF {
    public function Header() {
    }
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('freesans', 'I', 8);
        
    }
}
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->SetMargins(15, 25, 15);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setCellHeightRatio(2.0);
$pdf->SetFont('freesans', '', 11, '', true);
$pdf->AddPage('P', 'A4');

$html = <<<EOD
<table width="100%">
<tr><td width="680">MÃ NHÂN SỰ : $a[ma_nhanvien]</td></tr>
<tr><td width="100">HỌ VÀ TÊN:</td><td width="400">$a[fullname]</td></tr>
<tr><td width="250">NGÀY SINH:$a[ngaysinh]</td><td width="70"></td><td width="250">GIỚI TÍNH: $a[sex]</td></tr>
<tr><td width="250">ĐƠN VỊ: $a[donvi]</td><td width="70"></td><td width="300">MÃ SỐ THẺ: $a[ma_the]</td></tr>
<tr><td width="250">CHỨC VỤ: $a[chucvu]</td><td width="70"></td><td width="300">CÔNG VIỆC: $a[congviec]</td></tr>
<tr><td width="250">NGÀY VÀO CÔNG TY: $a[ngayvaolam]</td><td width="70"></td><td width="300">HỌC VẤN: $a[hocvan]</td></tr>
<tr><td width="680">NGUYÊN QUÁN: $a[nguyenquan]</td></tr>
<tr><td width="680">ĐỊA CHỈ: $a[diachicungu]</td></tr>
<tr><td width="200">SỐ CMND:$a[socmnd]</td><td width="200">NGÀY CẤP: $a[ngaycapcmnd]</td><td width="200">NƠI CẤP: $a[noicapcmnd]</td></tr>
<tr><td width="250">HỢP ĐỒNG LAO ĐỘNG:</td><td width="100"></td><td width="300"></td></tr>
<tr><td width="180">SỐ HĐLĐ: $a[sohdld]</td><td width="150">HỢP ĐỒNG THỬ VIỆC</td><td width="30" border="1"></td><td width="50"></td><td width="180">HỢP ĐỒNG CHÍNH THỨC:</td><td width="30" border="1"></td></tr>
<tr><td width="680">CHUYỂN CÔNG TÁC TỪ ĐƠN VỊ:---------------------ĐẾN ĐƠN VỊ:---------------------TỪ NGÀY:----/----/------</td></tr>
<tr><td width="680">LÝ DO:-------------------------------------------------------------------------------------------------------------------------------</td></tr>
<tr><td width="680">CHUYỂN CÔNG TÁC TỪ ĐƠN VỊ:---------------------ĐẾN ĐƠN VỊ:---------------------TỪ NGÀY:----/----/------</td></tr>
<tr><td width="680">LÝ DO:-------------------------------------------------------------------------------------------------------------------------------</td></tr>
<tr><td width="680">CHUYỂN CÔNG TÁC TỪ ĐƠN VỊ:---------------------ĐẾN ĐƠN VỊ:---------------------TỪ NGÀY:----/----/------</td></tr>
<tr><td width="680">LÝ DO:-------------------------------------------------------------------------------------------------------------------------------</td></tr>
<tr><td width="280">NGHỈ DÀI HẠN TỪ NGÀY:----/----/------------</td><td width="200">ĐẾN NGÀY:----/-----/------------</td></tr>
<tr><td width="680">LÝ DO:-------------------------------------------------------------------------------------------------------------------------------</td></tr>
<tr><td width="280">NGHỈ VIỆC NGÀY:----/----/------------</td><td width="400">LÝ DO NGHỈ:---------------------------------------------------------</td></tr>
<tr><td width="680">LÝ DO:-------------------------------------------------------------------------------------------------------------------------------</td></tr>
<tr><td width="680"><b><u>HỒ SƠ GỒM CÓ:</u></b></td></tr>
<tr><td width="200"><ul><li>ĐƠN XIN VIỆC:</li></ul></td><td width="30" border="1"></td><td width="120"></td><td width="220"><ul><li>GIẤY KHÁM SỨC KHỎE:</li></ul></td><td width="30" border="1"></td></tr>
<tr><td width="200"><ul><li>SƠ YẾU LÝ LỊCH:</li></ul></td><td width="30" border="1"></td><td width="120"></td><td width="220"><ul><li>CÁC NHẬN HẠNH KIỂM:</li></ul></td><td width="30" border="1"></td></tr>
<tr><td width="200"><ul><li>2 PHOTO CMND:</li></ul></td><td width="30" border="1"></td><td width="120"></td><td width="220"><ul><li>2HÌNH 3x4:</li></ul></td><td width="30" border="1"></td></tr>
<tr><td width="200"><ul><li>PHOTO HỘ KHẨU:</li></ul></td><td width="30" border="1"></td><td width="120"></td><td width="220"><ul><li>BẢN SAO KHAI SINH:</li></ul></td><td width="30" border="1"></td></tr>
<tr><td width="680"><b><u color="red">GHI CHÚ:</u></b></td></tr>
<tr><td width="680">-----------------------------------------------------------------------------------------------------------------------------------------</td></tr>
<tr><td width="680">-----------------------------------------------------------------------------------------------------------------------------------------</td></tr>
<tr><td width="680">-----------------------------------------------------------------------------------------------------------------------------------------</td></tr>
<tr><td width="680">-----------------------------------------------------------------------------------------------------------------------------------------</td></tr>
</table>
EOD;
$pdf->writeHTMLCell($w=0, $h=0, $x='', $y='', $html, $border=0, $ln=2, $fill=0, $reseth=true, $align='r', $autopadding=true);


$pdf->Output('../ns_hdld/in_hdld.pdf', 'I');
mysql_close($con);
?>