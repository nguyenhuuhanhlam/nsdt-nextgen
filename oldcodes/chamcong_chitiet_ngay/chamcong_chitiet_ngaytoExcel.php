<?
require_once '../_PHPExcel/Classes/PHPExcel.php';
require_once '../_PHPExcel/Classes/PHPExcel/Writer/Excel2007.php';
require_once('../_nsdt/nsdt_php.php');

error_reporting(E_ALL);
$objPHPExcel = new PHPExcel();

$dataWeek = json_decode(file_get_contents('chamcong_chitiet_ngay.json'),true);

//print_r($dataWeek);die;

$xlEdgeLeft = 7;
$xlEdgeTop = 8;
$xlEdgeBottom = 9;
$xlEdgeRight = 10;
/*tao 1 mang danh sach ngay thang lay tu file mcc.time */
$dataWeek_dayname = json_decode(file_get_contents('dtList.json'),true);

$objPHPExcel->setActiveSheetIndex(0);
$objPHPExcel->getActiveSheet()->SetCellValue('A1','Cty TNHH May Xuất Nhập Khẩu Đức Thành');
$objPHPExcel->getActiveSheet()->SetCellValue('A2','18 Trần Hưng Đạo- P.Mỹ Thới-TPLX-An Giang');
$objPHPExcel->getActiveSheet()->SetCellValue('F3','BẢNG CHẤM CÔNG TUẦN');

$objPHPExcel->getActiveSheet()->SetCellValue('A5','STT');
$objPHPExcel->getActiveSheet()->SetCellValue('B5','MNV');
$objPHPExcel->getActiveSheet()->SetCellValue('C5','HỌ TÊN');
$objPHPExcel->getActiveSheet()->SetCellValue('D5','DON VI');
$row = 5;
$col = 4;
foreach($dataWeek_dayname as $day){
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++,$row,$day);
}
//$objPHPExcel->getActiveSheet()->SetCellValue($i++.'5','TC');

$row = 6;
$col = 0;
$i = 1;
while(current($dataWeek)){
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++,$row,$i);
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++,$row,$dataWeek[key($dataWeek)]['ma_nhanvien']);
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++,$row,$dataWeek[key($dataWeek)]['hoten']);
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++,$row,$dataWeek[key($dataWeek)]['donvi']);
foreach ($dataWeek_dayname as $ngay){
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col++,$row,$dataWeek[key($dataWeek)][$ngay]);
	}

	
    next($dataWeek);
	$i++;
	$row++;
	$col = 0;
}





$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
$objWriter->save('chamcong_chitiet_ngay.xlsx');
echo '<a href="chamcong_chitiet_ngay.xlsx">Download!</a>';
?>