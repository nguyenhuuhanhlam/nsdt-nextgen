<?
require_once '../_PHPExcel/Classes/PHPExcel.php';
require_once '../_PHPExcel/Classes/PHPExcel/Writer/Excel2007.php';
require_once('../_nsdt/nsdt_php.php');

error_reporting(E_ALL);
$objPHPExcel = new PHPExcel();
PHPExcel_Cell::setValueBinder( new PHPExcel_Cell_AdvancedValueBinder() );
$dataMonth = json_decode(file_get_contents('tmp/dbMonth.json'),true);

/*tao 1 mang danh sach ngay thang lay tu file mcc.time */
//$dataWeek_dayname = json_decode(file_get_contents('tmp/dbWeek_dayname.json'),true);

//$n = count($dataWeek[key($dataWeek)][3]);
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
$objPHPExcel->getActiveSheet()->SetCellValue('H5','TCTG');
$objPHPExcel->getActiveSheet()->SetCellValue('I5','TTG');
$objPHPExcel->getActiveSheet()->SetCellValue('J5','THC');
$objPHPExcel->getActiveSheet()->SetCellValue('K5','TLTT');
$objPHPExcel->getActiveSheet()->SetCellValue('L5','TCN');
$objPHPExcel->getActiveSheet()->SetCellValue('M5','TLTS');
$objPHPExcel->getActiveSheet()->SetCellValue('N5','B_CN');
$objPHPExcel->getActiveSheet()->SetCellValue('O5','TCD');
$objPHPExcel->getActiveSheet()->SetCellValue('P5','TPN');
$objPHPExcel->getActiveSheet()->SetCellValue('Q5','GT');
$objPHPExcel->getActiveSheet()->SetCellValue('R5','CT');
$objPHPExcel->getActiveSheet()->SetCellValue('S5','NV');
$objPHPExcel->getActiveSheet()->SetCellValue('T5','KT');

$i=6;
$bLetter = 'A';
$stt = 1;
$k = null;
while(current($dataMonth)){
$cLetter = ord($bLetter);
	$objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,$stt++);
	$objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,key($dataMonth));
	$objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,$dataMonth[key($dataMonth)]['hoten']);
	 $objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,$dataMonth[key($dataMonth)]['dv']);
	$objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,$dataMonth[key($dataMonth)]['cc']);
	$objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,$dataMonth[key($dataMonth)]['ncccn']);
	$objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,$dataMonth[key($dataMonth)]['nchc']);
	
	$objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,$dataMonth[key($dataMonth)]['tctg']);
	$objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,$dataMonth[key($dataMonth)]['ttg']);
	$objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,$dataMonth[key($dataMonth)]['thc']);
	$objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,$dataMonth[key($dataMonth)]['tltt']);
	$objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,$dataMonth[key($dataMonth)]['tcn']);
	$objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,$dataMonth[key($dataMonth)]['tlts']);
	
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
$objWriter->save('tmp/dbMonth.xlsx');
echo '<a href="../mcc/tmp/dbMonth.xlsx">Download!</a>';
?>