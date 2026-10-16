<?
require_once('../_tcpdf/tcpdf.php');
require_once('../_nsdt/nsdt_php.php');
//require_once('../lib/itime.php');

$mytable = json_decode(file_get_contents('tmp/@chamcong_thang.json'),true);

//$dateRange=array(nsdt_dmyFormat($mytable['fromDate']),nsdt_dmyFormat($mytable['toDate']));

class MYPDF extends TCPDF {
	//public $dateRange;
	
	//public function getday($d){
		//return $this->dateRange=$d;
	//}

    public function Header() {
        $image_file = K_PATH_IMAGES.'logo.jpg';
        $this->Image($image_file,20, 8, 100, '', 'JPG', '', 'T', false, 150, '', false, false, 0, false, false, false);
		$this->SetFont('freesans', '', 12, '', true);
		$this->Cell(0,6,'','',1);
		$this->Cell(0,25,'BẢNG CHẤM CÔNG THÁNG','','','C');
		$this->Cell(0,6,'','',1);
		$this->SetFont('freesans', '', 10, '', true);
		//$this->Cell(0,22,'(Từ ngày '.$this->dateRange[0].' đến ngày '.$this->dateRange[1].")",'','','C');
		$this->Cell(0,21,'','',1);
		$this->Cell(7,6,"STT",'LTRB','','C');
		//$this->Cell(27,6,"Mã Nhân Viên",'LTRB','','C');
		$this->Cell(45,6,"Họ Tên",'LTRB','','C');
		$this->Cell(25,6,"Đơn vị",'LTRB','','C');
		$this->Cell(10,6,"HCC",'LTRB','','C');
		$this->Cell(10,6,"NC",'LTRB','','C');
		$this->Cell(12,6,"TTG",'LTRB','','C');
		$this->Cell(12,6,"HC",'LTRB','','C');
		$this->Cell(10,6,"LT",'LTRB','','C');
		$this->Cell(10,6,"CN",'LTRB','','C');
		$this->Cell(10,6,"B_CN",'LTRB','','C');
		$this->Cell(10,6,"CD",'LTRB','','C');
		$this->Cell(10,6,"PN",'LTRB','','C');
		$this->Cell(10,6,"GT",'LTRB','','C');

    }
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('freesans', 'I', 8);
        $this->Cell(0, 10, 'Trang '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
}
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
//$pdf->getday($dateRange);
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->SetMargins(15, 47, PDF_MARGIN_RIGHT);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->SetFont('freesans', '', 8, '', true);
$pdf->AddPage('P', 'A4');

$i = 0;

foreach($mytable as $r){

		$pdf->Cell(7,6,($i+1),'LTRB','','C');//stt    
		$pdf->Cell(45,6,$r[0],'LTRB');
		$pdf->Cell(25,6,$r[1],'LTRB');
		$pdf->Cell(10,6,$r[2],'LTRB','','C');
		$pdf->Cell(10,6,$r[3],'LTRB','','C');
		$pdf->Cell(12,6,$r[4],'LTRB','','C');
		$pdf->Cell(12,6,$r[5],'LTRB','','C');
		$pdf->Cell(10,6,$r[6],'LTRB','','C');
		$pdf->Cell(10,6,$r[7],'LTRB','','C');
		$pdf->Cell(10,6,$r[8],'LTRB','','C');
		$pdf->Cell(10,6,$r[9],'LTRB','','C');
		$pdf->Cell(10,6,$r[10],'LTRB','','C');
		$pdf->Cell(10,6,$r[11],'LTRB','','C');
		$pdf->Cell(0,6,'','',1);//new_line
		$i++;
	}
	

$pdf->Output('@chamcongthang.pdf', 'I');
?>