<?
require_once '../_PHPExcel/Classes/PHPExcel.php';
require_once '../_PHPExcel/Classes/PHPExcel/Writer/Excel2007.php';
date_default_timezone_set('Asia/Ho_Chi_Minh');

error_reporting(E_ALL);
$objPHPExcel = new PHPExcel();

$dataWeek = json_decode(file_get_contents('tmp/dbWeek.json'),true);
$n = count($dataWeek[key($dataWeek)][3]);

$objPHPExcel->setActiveSheetIndex(0);
$objPHPExcel->getActiveSheet()->SetCellValue('A1','Cty TNHH May Xuất Nhập Khẩu Đức Thành');
$objPHPExcel->getActiveSheet()->SetCellValue('A2','18 Trần Hưng Đạo- P.Mỹ Thới-TPLX-An Giang');
$objPHPExcel->getActiveSheet()->SetCellValue('F3','BẢNG CHẤM CÔNG TUẦN');
$objPHPExcel->getActiveSheet()->SetCellValue('B5','MNV');
$objPHPExcel->getActiveSheet()->SetCellValue('C5','HỌ TÊN');
$objPHPExcel->getActiveSheet()->SetCellValue('D5','CC');
$i=6;
$bLetter = 'A';

//$objPHPExcel->getActiveSheet()->SetCellValue($bLetter.$i,'TIEU DE 1');

while(current($dataWeek)){
    
    $cLetter = ord($bLetter)+1;
    
    $objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,key($dataWeek));
    $objPHPExcel->getActiveSheet()->mergeCells(chr($cLetter-1).$i.':'.chr($cLetter-1).($i+1));
      
    $objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,$dataWeek[key($dataWeek)][0]);
    $objPHPExcel->getActiveSheet()->mergeCells(chr($cLetter-1).$i.':'.chr($cLetter-1).($i+1));
    
    $objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,$dataWeek[key($dataWeek)][1]); 
    $objPHPExcel->getActiveSheet()->mergeCells(chr($cLetter-1).$i.':'.chr($cLetter-1).($i+1));
    
    $objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,$dataWeek[key($dataWeek)][2]);
    $objPHPExcel->getActiveSheet()->mergeCells(chr($cLetter-1).$i.':'.chr($cLetter-1).($i+1));

    foreach($dataWeek[key($dataWeek)][3] as $o)
        $objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,$o);
    
    foreach($dataWeek[key($dataWeek)][4] as $p)
        $objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++-$n).($i+1),$p);
    
    $objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++-$n).$i,$dataWeek[key($dataWeek)][5]);
    $objPHPExcel->getActiveSheet()->mergeCells(chr($cLetter-1-$n).$i.':'.chr($cLetter-1-$n).($i+1));
    
    $objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++-$n).$i,$dataWeek[key($dataWeek)][6]);
    $objPHPExcel->getActiveSheet()->mergeCells(chr($cLetter-1-$n).$i.':'.chr($cLetter-1-$n).($i+1));
    
    $objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++-$n).$i,$dataWeek[key($dataWeek)][7]);
    $objPHPExcel->getActiveSheet()->mergeCells(chr($cLetter-1-$n).$i.':'.chr($cLetter-1-$n).($i+1));
    
	$objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++-$n).$i,$dataWeek[key($dataWeek)][11]);
    $objPHPExcel->getActiveSheet()->mergeCells(chr($cLetter-1-$n).$i.':'.chr($cLetter-1-$n).($i+1));
	
    $objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++-$n).$i,$dataWeek[key($dataWeek)][8]);
    $objPHPExcel->getActiveSheet()->mergeCells(chr($cLetter-1-$n).$i.':'.chr($cLetter-1-$n).($i+1));
    
    $i+=2;
    next($dataWeek);
}

$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
$objWriter->save('tmp/dbWeek.xlsx');
echo '<a href="../mcc/tmp/dbWeek.xlsx">Download!</a>';
?>