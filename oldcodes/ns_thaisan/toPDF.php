<?
require_once('../_tcpdf/tcpdf.php');
require_once('../_nsdt/nsdt_php.php');
date_default_timezone_set('Asia/Ho_Chi_Minh');
$mytable = json_decode(file_get_contents('../ns_thaisan/report_thaisan.json'),true);

class MYPDF extends TCPDF {
	//public $dateRange;
	
	public function getday($d){
		//return $this->dateRange=$d;
	}

    public function Header() {
        $image_file = K_PATH_IMAGES.'logo.jpg';
        $this->Image($image_file,10, 8, 100, '', 'JPG', '', 'T', false, 150, '', false, false, 0, false, false, false);
		$this->SetFont('freesans', '', 12, '', true);
		$this->Cell(0,6,'','',1);
		$this->Cell(0,25,'DANH SÁCH THAI SẢN','','','C');
		$this->Cell(0,6,'','',1);
		$this->SetFont('freesans', '', 10, '', true);
		$this->Cell(0,22,'(Từ ngày..............đến ngày..............)','','','C');
		$this->Cell(0,20,'','',1);
		$this->Cell(7,6,"STT",'LTRB','','C');
		$this->Cell(27,6,"Mã Nhân Viên",'LTRB','','C');
		$this->Cell(50,6,"Họ Tên",'LTRB','','C');
		$this->Cell(30,6,"Đơn Vị",'LTRB','','C');
		$this->Cell(32,6,"Thai Sản",'LTRB','','C');
		$this->Cell(20,6,"Ngày BĐ",'LTRB','','C');
		$this->Cell(20,6,"Ngày KT",'LTRB','','C');
    }
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('freesans', 'I', 8);
        $this->Cell(0, 10, 'Trang '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
}
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->SetMargins(15, 46, PDF_MARGIN_RIGHT);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->SetFont('freesans', '', 10, '', true);
$pdf->AddPage('P', 'A4');
$i=0;
foreach($mytable as $r){
	 $pdf->Cell(7,6,($i+1),'LTRB');
    $pdf->Cell(27,6,strip_tags($r[0]),'LTRB','','C');
    $pdf->Cell(50,6,$r[1],'LTRB');
    $pdf->Cell(30,6,$r[2],'LTRB');
    $pdf->Cell(32,6,$r[3],'LTRB');
    $pdf->Cell(20,6,nsdt_dmyFormat($r[4]),'LTRB');
    $pdf->Cell(20,6,nsdt_dmyFormat($r[5]),'LTRB');
    $pdf->Cell(0,6,'','',1);//new_line
	$i++;
}

$pdf->Output('report_thaisan.pdf', 'I');
?>