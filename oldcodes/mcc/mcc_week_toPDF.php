<?
require_once('../_tcpdf/config/lang/eng.php');
require_once('../_tcpdf/tcpdf.php');
require_once('../_global.inc');

class MYPDF extends TCPDF {
    public function Header() {
        $image_file = K_PATH_IMAGES.'logo.jpg';
        $this->Image($image_file,20, 8, 100, '', 'JPG', '', 'T', false, 150, '', false, false, 0, false, false, false);
		$this->SetFont('freesans', '', 12, '', true);
		
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
$pdf->SetMargins(20, 46, PDF_MARGIN_RIGHT);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->SetFont('freesans', '', 8, '', true);
$pdf->AddPage('L', 'A4');

$dataWeek = json_decode(file_get_contents('tmp/dbWeek.json'),true);
$n = count($dataWeek[key($dataWeek)]['timeOfficeList']); 

$i = 1;
while(current($dataWeek)){
    
    $pdf->Cell(8,5,$i,'LR',0,'C',false,'',0,true,'','T');
    $pdf->Cell(30,5,key($dataWeek),'R',0,'C');
    $pdf->Cell(60,5,$dataWeek[key($dataWeek)]['fullname'],'R');
    $pdf->Cell(30,5,$dataWeek[key($dataWeek)]['donvi'],'R');
    $pdf->Cell(10,5,$dataWeek[key($dataWeek)]['chuyencan'],'R');
    
    foreach($dataWeek[key($dataWeek)]['timeOfficeList'] as $o) $pdf->Cell(11,5,substr($o,0,5),'RB');
    $pdf->Cell(12,5,$dataWeek[key($dataWeek)]['TCTG'],'R');
    $pdf->Cell(12,5,$dataWeek[key($dataWeek)]['TCTG'],'R');
    $pdf->Cell(12,5,$dataWeek[key($dataWeek)]['TCTG'],'R');
    $pdf->Cell(12,5,$dataWeek[key($dataWeek)]['TCTG'],'R',0,'C');
    
    $pdf->Cell(0,5,'','',1);//dong ke tiep
   
    $pdf->Cell(8,5,'','LRB');
    $pdf->Cell(30,5,'','RB');
    $pdf->Cell(60,5,'','RB');
    $pdf->Cell(30,5,'','RB');
    $pdf->Cell(10,5,'','RB');
    foreach($dataWeek[key($dataWeek)]['partTimeList'] as $o) $pdf->Cell(11,5,substr($o,0,5),'RB');
    
    $pdf->Cell(12,5,'','RB');
    $pdf->Cell(12,5,'','RB');
    $pdf->Cell(12,5,'','RB');
    $pdf->Cell(12,5,'','RB');
    
    $pdf->Cell(0,5,'','',1);//dong ke tiep
    
    $i++;
    next($dataWeek);
}
$pdf->Output('mcc_week_toPDF.pdf', 'I');
?>