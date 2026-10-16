<?
//ini_set('memory_limit','16M');
//ini_set('max_execution_time', 300);

require_once('../_tcpdf/config/lang/eng.php');
require_once('../_tcpdf/tcpdf.php');
require_once('../_global.inc');

date_default_timezone_set('Asia/Ho_Chi_Minh');

$pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->setFontSubsetting(false);
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->SetFont('freesans','',10);
$pdf->AddPage('P', 'A4');

$dataWeek = json_decode(file_get_contents('tmp/dbWeek.json'),true);
$n = count($dataWeek[key($dataWeek)][3]); 

$i = 1;
while(current($dataWeek)){
    
    $pdf->Cell(8,5,$i,'LR',0,'C',false,'',0,true,'','T');
    $pdf->Cell(30,5,key($dataWeek),'R',0,'C');
    $pdf->Cell(60,5,$dataWeek[key($dataWeek)][0],'R');
    $pdf->Cell(30,5,$dataWeek[key($dataWeek)][1],'R');
    $pdf->Cell(10,5,$dataWeek[key($dataWeek)][2],'R');
    
    foreach($dataWeek[key($dataWeek)][3] as $o) $pdf->Cell(11,5,substr($o,0,5),'RB');
    $pdf->Cell(12,5,$dataWeek[key($dataWeek)][5],'R');
    $pdf->Cell(12,5,$dataWeek[key($dataWeek)][6],'R');
    $pdf->Cell(12,5,$dataWeek[key($dataWeek)][7],'R');
    $pdf->Cell(12,5,$dataWeek[key($dataWeek)][8],'R',0,'C');
    
    $pdf->Cell(0,5,'','',1);//dong ke tiep
    
    $pdf->Cell(8,5,'','LRB');
    $pdf->Cell(30,5,'','RB');
    $pdf->Cell(60,5,'','RB');
    $pdf->Cell(30,5,'','RB');
    $pdf->Cell(10,5,'','RB');
    foreach($dataWeek[key($dataWeek)][4] as $o) $pdf->Cell(11,5,substr($o,0,5),'RB');
    
    $pdf->Cell(12,5,'','RB');
    $pdf->Cell(12,5,'','RB');
    $pdf->Cell(12,5,'','RB');
    $pdf->Cell(12,5,'','RB');
    
    $pdf->Cell(0,5,'','',1);//dong ke tiep
    
    $i++;
    next($dataWeek);
}
$pdf->Output('tbPrint.pdf', 'I');
?>