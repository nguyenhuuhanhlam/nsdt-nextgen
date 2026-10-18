<?
require_once('../_tcpdf/tcpdf.php');
require_once('../_nsdt/nsdt_php.php');
$mytable = json_decode(file_get_contents('../chamcong_chitiet_ngay/chamcong_chitiet_ngay.json'),true);

$dateRange=array(nsdt_dmyFormat($mytable['fromDate']),nsdt_dmyFormat($mytable['toDate']));

class MYPDF extends TCPDF {
	public $dateRange;
	
	public function getday($d){
		return $this->dateRange=$d;
	}

    public function Header() {
        $image_file = K_PATH_IMAGES.'logo.jpg';
        $this->Image($image_file,20, 8, 100, '', 'JPG', '', 'T', false, 150, '', false, false, 0, false, false, false);
		$this->SetFont('freesans', '', 12, '', true);
		$this->Cell(0,6,'','',1);
		$this->Cell(0,25,'CHẤM CÔNG CHI TIẾT THÁNG','','','C');
		$this->Cell(0,6,'','',1);
		$this->SetFont('freesans', '', 10, '', true);
		$this->Cell(0,22,'(Từ ngày '.$this->dateRange[0].' đến ngày '.$this->dateRange[1].")",'','','C');
		$this->Cell(0,21,'','',1);
		$this->Cell(7,6,"STT",'LTRB','','C');
		//$this->Cell(27,6,"Mã Nhân Viên",'LTRB','','C');
		$this->Cell(45,6,"Họ Tên",'LTRB','','C');
		$this->Cell(25,6,"Đơn vị",'LTRB','','C');
		$this->Cell(17,6,"01",'LTRB','','C');
		$this->Cell(13,6,"02",'LTRB','','C');
		$this->Cell(13,6,"H2",'LTRB','','C');
		$this->Cell(13,6,"H3",'LTRB','','C');
		$this->Cell(13,6,"H4",'LTRB','','C');
		$this->Cell(13,6,"H5",'LTRB','','C');
		$this->Cell(13,6,"H6",'LTRB','','C');
    }
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('freesans', 'I', 8);
        $this->Cell(0, 10, 'Trang '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
}
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->getday($dateRange);
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->SetMargins(18, 47, PDF_MARGIN_RIGHT);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->SetFont('freesans', '', 8, '', true);
$pdf->AddPage('L', 'A4');

$i = 0;
$k = null;
$n = 1;
foreach($mytable['data'] as $r){

	if(!$k) $k = $r[0];
	if($k==$r[0]){
		
		} else {			
			$k = $r[0];
			$pdf->Cell(0,6,'','',1);//new_line
			/// Cần thì mở chổ này
// $html = <<<EOD
// <table width="100%">
// <tr><td></td><td></td><td>Đức Thành, ngày .....tháng ..... năm.....</td></tr>
// <tr><td align="center">CHỦ TỊCH CÔNG ĐOÀN</td><td></td><td align="center">NGƯỜI SỬ DỤNG LAO ĐỘNG</td></tr>
// <tr><td align="center"><img src="../images/sauoanh.jpg" width="160" height="80"></td><td></td><td></td></tr>
// <tr><td align="center">VŨ VIẾT OANH</td><td></td><td align="center">HỒ MINH TRIẾT</td></tr>
// </table>
// EOD;
// $pdf->writeHTMLCell($w=0, $h=0, $x='', $y='', $html, $border=0, $ln=1, $fill=0, $reseth=true, $align='r', $autopadding=true);
			$pdf->AddPage('P', 'A4');
			$i = 0;
			}
	$pdf->Cell(7,6,($i+1),'LTRB','','C');//stt
    //$pdf->Cell(27,6,strip_tags($r[0]),'LTRB','','C');//ma_nhanvien
    $pdf->Cell(45,6,$r[1],'LTRB');
    $pdf->Cell(25,6,$r[2],'LTRB');
    $pdf->Cell(17,6,$r[3],'LTRB');
    $pdf->Cell(13,6,$r[4],'LTRB');
    $pdf->Cell(13,6,$r[5],'LTRB');
	$pdf->Cell(13,6,$r[6],'LTRB');
	$pdf->Cell(13,6,$r[7],'LTRB');
	$pdf->Cell(13,6,$r[8],'LTRB');
	$pdf->Cell(13,6,$r[9],'LTRB');
    $pdf->Cell(0,6,'','',1);//new_line
	$i++;
}
$pdf->Output('chitiet_quetthe_tho', 'I');
?>