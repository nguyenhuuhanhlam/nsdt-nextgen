<?
require_once '../_PHPExcel/Classes/PHPExcel.php';
require_once '../_PHPExcel/Classes/PHPExcel/Writer/Excel2007.php';
require_once('../_nsdt/nsdt_php.php');

error_reporting(E_ALL);
$objPHPExcel = new PHPExcel();

$dataWeek = json_decode(file_get_contents('tmp/dbWeek.json'),true);
$xlEdgeLeft = 7;
$xlEdgeTop = 8;
$xlEdgeBottom = 9;
$xlEdgeRight = 10;
/*tao 1 mang danh sach ngay thang lay tu file mcc.time */
$dataWeek_dayname = json_decode(file_get_contents('tmp/dbWeek_dayname.json'),true);

$n = count($dataWeek[key($dataWeek)]['timeOfficeList']);
$objPHPExcel->setActiveSheetIndex(0);
$objPHPExcel->getActiveSheet()->SetCellValue('B1','Cty TNHH May Xuất Nhập Khẩu Đức Thành');
$objPHPExcel->getActiveSheet()->SetCellValue('B2','18 Trần Hưng Đạo- P.Mỹ Thới-TPLX-An Giang');
$objPHPExcel->getActiveSheet()->SetCellValue('F3','BẢNG CHẤM CÔNG TUẦN');
/*border*/
//$objPHPExcel->getActiveSheet()->getStyle('E13')->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

$objPHPExcel->getActiveSheet()->SetCellValue('B5','STT');
$objPHPExcel->getActiveSheet()->SetCellValue('C5','MNV');
$objPHPExcel->getActiveSheet()->SetCellValue('D5','HỌ TÊN');
$objPHPExcel->getActiveSheet()->SetCellValue('E5','DON VI');

/*dat chieu rong cot*/
$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(35);
$objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(30);
$objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(15);

/* ghi ra danh sach ngay thang bat dau tu cell F5 */
$i = 'F';
foreach($dataWeek_dayname as $day){
	$objPHPExcel->getActiveSheet()->SetCellValue($i++.'5',nsdt_dmFormat($day));
}

$objPHPExcel->getActiveSheet()->SetCellValue($i++.'5','CC');
$objPHPExcel->getActiveSheet()->SetCellValue($i++.'5','NCN');
$objPHPExcel->getActiveSheet()->SetCellValue($i++.'5','NHC');
$objPHPExcel->getActiveSheet()->SetCellValue($i++.'5','TCTG');
$objPHPExcel->getActiveSheet()->SetCellValue($i++.'5','TTG');
$objPHPExcel->getActiveSheet()->SetCellValue($i++.'5','THC');
$objPHPExcel->getActiveSheet()->SetCellValue($i++.'5','TLT');
$objPHPExcel->getActiveSheet()->SetCellValue($i++.'5','LTT');
$objPHPExcel->getActiveSheet()->SetCellValue($i++.'5','CN');
$objPHPExcel->getActiveSheet()->SetCellValue($i++.'5','LTS');
$objPHPExcel->getActiveSheet()->SetCellValue($i++.'5','B_CN');
//$objPHPExcel->getActiveSheet()->SetCellValue($i++.'5','TLT');

$i=6;
$bLetter = 'A';

$stt = 1;
while(current($dataWeek)){
    
	$cLetter = ord($bLetter)+1;
	//so thu tu
	$objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,$stt++);
	$objPHPExcel->getActiveSheet()->mergeCells(chr($cLetter-1).$i.':'.chr($cLetter-1).($i+1));
    //ma nhan vien
    $objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,key($dataWeek));
    $objPHPExcel->getActiveSheet()->mergeCells(chr($cLetter-1).$i.':'.chr($cLetter-1).($i+1));
     //ho ten 
    $objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,$dataWeek[key($dataWeek)]['fullname']);
    $objPHPExcel->getActiveSheet()->mergeCells(chr($cLetter-1).$i.':'.chr($cLetter-1).($i+1));
    //don vi
    $objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,$dataWeek[key($dataWeek)]['donvi']); 
    $objPHPExcel->getActiveSheet()->mergeCells(chr($cLetter-1).$i.':'.chr($cLetter-1).($i+1));

    foreach($dataWeek[key($dataWeek)]['timeOfficeList'] as $o)

        $objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++).$i,$o);
    
    foreach($dataWeek[key($dataWeek)]['partTimeList'] as $p)

        $objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++-$n).($i+1),$p);
	///chuyen can
	$objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++-$n).$i,$dataWeek[key($dataWeek)]['chuyencan']);
    $objPHPExcel->getActiveSheet()->mergeCells(chr($cLetter-1-$n).$i.':'.chr($cLetter-1-$n).($i+1));
    //tong gio
    $objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++-$n).$i,$dataWeek[key($dataWeek)]['NCN']);
    $objPHPExcel->getActiveSheet()->mergeCells(chr($cLetter-1-$n).$i.':'.chr($cLetter-1-$n).($i+1));
    //gio hanh chanh
    $objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++-$n).$i,$dataWeek[key($dataWeek)]['NHC']);
    $objPHPExcel->getActiveSheet()->mergeCells(chr($cLetter-1-$n).$i.':'.chr($cLetter-1-$n).($i+1));
    //tong cac thoi gian
    $objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++-$n).$i,$dataWeek[key($dataWeek)]['TCTG']);
    $objPHPExcel->getActiveSheet()->mergeCells(chr($cLetter-1-$n).$i.':'.chr($cLetter-1-$n).($i+1));
    //tong thoi gian
	$objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++-$n).$i,$dataWeek[key($dataWeek)]['TTG']);
    $objPHPExcel->getActiveSheet()->mergeCells(chr($cLetter-1-$n).$i.':'.chr($cLetter-1-$n).($i+1));
	// tong hanh chanh
    $objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++-$n).$i,$dataWeek[key($dataWeek)]['THC']);
    $objPHPExcel->getActiveSheet()->mergeCells(chr($cLetter-1-$n).$i.':'.chr($cLetter-1-$n).($i+1));
	// tong lam them
    $objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++-$n).$i,$dataWeek[key($dataWeek)]['TLT']);
    $objPHPExcel->getActiveSheet()->mergeCells(chr($cLetter-1-$n).$i.':'.chr($cLetter-1-$n).($i+1));
	//lam them truoc
    $objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++-$n).$i,$dataWeek[key($dataWeek)]['LTT']);
    $objPHPExcel->getActiveSheet()->mergeCells(chr($cLetter-1-$n).$i.':'.chr($cLetter-1-$n).($i+1));
	// tong chu nhat
    $objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++-$n).$i,$dataWeek[key($dataWeek)]['CN']);
    $objPHPExcel->getActiveSheet()->mergeCells(chr($cLetter-1-$n).$i.':'.chr($cLetter-1-$n).($i+1));
	// tong lam them sau
    $objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++-$n).$i,$dataWeek[key($dataWeek)]['LTS']);
    $objPHPExcel->getActiveSheet()->mergeCells(chr($cLetter-1-$n).$i.':'.chr($cLetter-1-$n).($i+1));
	// tong bau con nho
    $objPHPExcel->getActiveSheet()->SetCellValue(chr($cLetter++-$n).$i,$dataWeek[key($dataWeek)]['BCN']);
    $objPHPExcel->getActiveSheet()->mergeCells(chr($cLetter-1-$n).$i.':'.chr($cLetter-1-$n).($i+1));
    $i+=2;
    next($dataWeek);
}

$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
$objWriter->save('tmp/dbWeek.xlsx');
echo '<a href="../mcc/tmp/dbWeek.xlsx">Download!</a>';
?>