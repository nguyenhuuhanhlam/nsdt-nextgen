<?
require_once '../_PHPExcel/Classes/PHPExcel.php';
require_once '../_PHPExcel/Classes/PHPExcel/Writer/Excel2007.php';
require_once('../_nsdt/nsdt_php.php');

error_reporting(E_ALL);
$objPHPExcel = new PHPExcel();
PHPExcel_Cell::setValueBinder( new PHPExcel_Cell_AdvancedValueBinder() );
$dataMonth = json_decode(file_get_contents('tmp/@chamcong_thang.json'),true);

$objPHPExcel->setActiveSheetIndex(0);
$objPHPExcel->getActiveSheet()->SetCellValue('A1','Cty TNHH May Xuất Nhập Khẩu Đức Thành');
$objPHPExcel->getActiveSheet()->SetCellValue('A2','18 Trần Hưng Đạo- P.Mỹ Thới-TPLX-An Giang');
$objPHPExcel->getActiveSheet()->SetCellValue('F3','BẢNG CHẤM CÔNG THÁNG');

$objPHPExcel->getActiveSheet()->SetCellValue('A5','Stt');
$objPHPExcel->getActiveSheet()->SetCellValue('B5','MNV');
$objPHPExcel->getActiveSheet()->SetCellValue('C5','Họ tên');
$objPHPExcel->getActiveSheet()->SetCellValue('D5','Đơn vị');
$objPHPExcel->getActiveSheet()->SetCellValue('E5','HCC');
$objPHPExcel->getActiveSheet()->SetCellValue('F5','NCCCN');
$objPHPExcel->getActiveSheet()->SetCellValue('G5','NCHC');
$objPHPExcel->getActiveSheet()->SetCellValue('H5','TTGCCN');
$objPHPExcel->getActiveSheet()->SetCellValue('I5','THCCCN');
$objPHPExcel->getActiveSheet()->SetCellValue('J5','TLTCCN');
$objPHPExcel->getActiveSheet()->SetCellValue('K5','TTG');
$objPHPExcel->getActiveSheet()->SetCellValue('L5','HC');
$objPHPExcel->getActiveSheet()->SetCellValue('M5','LT');
$objPHPExcel->getActiveSheet()->SetCellValue('N5','CN');
$objPHPExcel->getActiveSheet()->SetCellValue('O5','B_CN');
$objPHPExcel->getActiveSheet()->SetCellValue('P5','CD');
$objPHPExcel->getActiveSheet()->SetCellValue('Q5','PN');
$objPHPExcel->getActiveSheet()->SetCellValue('R5','GT');
$objPHPExcel->getActiveSheet()->SetCellValue('S5','CT');
$objPHPExcel->getActiveSheet()->SetCellValue('T5','NV');
$objPHPExcel->getActiveSheet()->SetCellValue('U5','KT');


/*dat chieu rong cot*/
$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(5);
$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(35);
$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension("G")->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension("H")->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension("I")->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension("J")->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension("K")->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension("L")->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension("M")->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension("N")->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension("O")->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension("P")->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension("Q")->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension("R")->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension("S")->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension("T")->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension("U")->setWidth(10);
$i=6;
$bLetter = 'A';
$stt = 1;

while(current($dataMonth)){
$cLetter = ord($bLetter);
 //so thu tu
	$objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,$stt++);
	$objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,key($dataMonth));
	$objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,$dataMonth[key($dataMonth)]['hoten']);
	 $objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,$dataMonth[key($dataMonth)]['dv']);
	$objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,$dataMonth[key($dataMonth)]['cc']);
	$objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,$dataMonth[key($dataMonth)]['ncccn']);
	
	$objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,$dataMonth[key($dataMonth)]['nchc']);
	$objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,$dataMonth[key($dataMonth)]['ttgccn']);
	$objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,$dataMonth[key($dataMonth)]['thcccn']);
	$objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,$dataMonth[key($dataMonth)]['tltccn']);
	$objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,$dataMonth[key($dataMonth)]['ttg']);
	$objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,$dataMonth[key($dataMonth)]['thc']);
	$objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,$dataMonth[key($dataMonth)]['tlt']);
	
	$objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,$dataMonth[key($dataMonth)]['tcn']);
	$objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,$dataMonth[key($dataMonth)]['bcn']);
	$objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,$dataMonth[key($dataMonth)]['tcd']);
	$objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,$dataMonth[key($dataMonth)]['tpn']);
	$objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,$dataMonth[key($dataMonth)]['gt']);
	
	$objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,$dataMonth[key($dataMonth)]['ct']);
	$objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,$dataMonth[key($dataMonth)]['nv']);
	$objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,$dataMonth[key($dataMonth)]['kt']);
	$i++;
    next($dataMonth);
 }


$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
$objWriter->save('tmp/@chamcong_thang.xlsx');
echo '<a href="../mcc/tmp/@chamcong_thang.xlsx">Download!</a>';
?>