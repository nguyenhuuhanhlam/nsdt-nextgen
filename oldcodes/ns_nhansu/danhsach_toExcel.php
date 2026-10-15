<?
require_once '../_PHPExcel/Classes/PHPExcel.php';
require_once '../_PHPExcel/Classes/PHPExcel/Writer/Excel2007.php';
require_once('../_nsdt/nsdt_php.php');
require_once('../lib/itime.php');
error_reporting(E_ALL);
$objPHPExcel = new PHPExcel();
PHPExcel_Cell::setValueBinder( new PHPExcel_Cell_AdvancedValueBinder() );

$mytable = json_decode(file_get_contents('danhsach_nhanvien.json'),true);

/*tao 1 mang danh sach ngay thang lay tu file mcc.time */
//$dataWeek_dayname = json_decode(file_get_contents('tmp/dbWeek_dayname.json'),true);

//$n = count($dataWeek[key($dataWeek)][3]);
$objPHPExcel->setActiveSheetIndex(0);
$objPHPExcel->getActiveSheet()->SetCellValue('A1','Cty TNHH May Xuất Nhập Khẩu Đức Thành');
$objPHPExcel->getActiveSheet()->SetCellValue('A2','18 Trần Hưng Đạo- P.Mỹ Thới-TPLX-An Giang');
$objPHPExcel->getActiveSheet()->SetCellValue('F3','BẢNG CHẤM CÔNG TUẦN');

$objPHPExcel->getActiveSheet()->SetCellValue('A5','STT');
$objPHPExcel->getActiveSheet()->SetCellValue('B5','MNV');
$objPHPExcel->getActiveSheet()->SetCellValue('C5','MÃ THẺ');
$objPHPExcel->getActiveSheet()->SetCellValue('D5','HỌ');
$objPHPExcel->getActiveSheet()->SetCellValue('E5','TÊN');
$objPHPExcel->getActiveSheet()->SetCellValue('F5','GIỚI TÍNH');
$objPHPExcel->getActiveSheet()->SetCellValue('G5','NGÀY SINH');
$objPHPExcel->getActiveSheet()->SetCellValue('H5','NƠI SINH');
$objPHPExcel->getActiveSheet()->SetCellValue('I5','NGUYÊN QUÁN');
$objPHPExcel->getActiveSheet()->SetCellValue('J5','ĐCTT');
$objPHPExcel->getActiveSheet()->SetCellValue('K5','ĐCCN');
$objPHPExcel->getActiveSheet()->SetCellValue('L5','QUỐC TỊCH');
$objPHPExcel->getActiveSheet()->SetCellValue('M5','DÂN TỘC');
$objPHPExcel->getActiveSheet()->SetCellValue('N5','HỌC VẤN');
$objPHPExcel->getActiveSheet()->SetCellValue('O5','HÔN NHÂN');
$objPHPExcel->getActiveSheet()->SetCellValue('P5','SỐ CMND');
$objPHPExcel->getActiveSheet()->SetCellValue('Q5','NGÀY CẤP');
$objPHPExcel->getActiveSheet()->SetCellValue('R5','NƠI CẤP');
$objPHPExcel->getActiveSheet()->SetCellValue('S5','ĐƠN VỊ');
$objPHPExcel->getActiveSheet()->SetCellValue('T5','CHỨC VỤ');
$objPHPExcel->getActiveSheet()->SetCellValue('U5','CÔNG VIỆC');
$objPHPExcel->getActiveSheet()->SetCellValue('V5','NGÀY VL');
$objPHPExcel->getActiveSheet()->SetCellValue('W5','NGÀY KTTV');
$objPHPExcel->getActiveSheet()->SetCellValue('X5','HSL');
$objPHPExcel->getActiveSheet()->SetCellValue('Y5','HĐLĐ');
$objPHPExcel->getActiveSheet()->SetCellValue('X5','NNV');

$i=6;
$stt = 1;

foreach($mytable as $r){
 
		$objPHPExcel->getActiveSheet()->SetCellValue("A".$i,$stt++);
		$objPHPExcel->getActiveSheet()->SetCellValue("B".$i,strip_tags($r[0]));
		$objPHPExcel->getActiveSheet()->SetCellValue("C".$i,$r[1]);
		$objPHPExcel->getActiveSheet()->SetCellValue("D".$i,$r[2]);
		$objPHPExcel->getActiveSheet()->SetCellValue("E".$i,$r[3]);
		$objPHPExcel->getActiveSheet()->SetCellValue("F".$i,$r[4]);
		$objPHPExcel->getActiveSheet()->SetCellValue("G".$i,$r[5]);
		$objPHPExcel->getActiveSheet()->SetCellValue("H".$i,$r[6]);
		$objPHPExcel->getActiveSheet()->SetCellValue("I".$i,$r[7]);	
		$objPHPExcel->getActiveSheet()->SetCellValue("J".$i,$r[8]);
	
		$objPHPExcel->getActiveSheet()->SetCellValue("K".$i,$r[9]);
		$objPHPExcel->getActiveSheet()->SetCellValue("L".$i,$r[10]);
		$objPHPExcel->getActiveSheet()->SetCellValue("M".$i,$r[11]);
		$objPHPExcel->getActiveSheet()->SetCellValue("N".$i,$r[12]);
		$objPHPExcel->getActiveSheet()->SetCellValue("O".$i,$r[13]);
		$objPHPExcel->getActiveSheet()->SetCellValue("P".$i,$r[14]);
		$objPHPExcel->getActiveSheet()->SetCellValue("Q".$i,$r[15]);
		$objPHPExcel->getActiveSheet()->SetCellValue("R".$i,$r[16]);	
		$objPHPExcel->getActiveSheet()->SetCellValue("S".$i,$r[17]);
		$objPHPExcel->getActiveSheet()->SetCellValue("T".$i,$r[18]);
		$objPHPExcel->getActiveSheet()->SetCellValue("U".$i,$r[19]);
		$objPHPExcel->getActiveSheet()->SetCellValue("V".$i,$r[20]);
		$objPHPExcel->getActiveSheet()->SetCellValue("W".$i,nsdt_dmyFormat($r[21]));
		$objPHPExcel->getActiveSheet()->SetCellValue("X".$i,$r[22]);
		$objPHPExcel->getActiveSheet()->SetCellValue("Y".$i,$r[23]);
		$objPHPExcel->getActiveSheet()->SetCellValue("Z".$i,$r[24]);
		//$objPHPExcel->getActiveSheet()->SetCellValue("I".$i,$r[16]);	
		//$objPHPExcel->getActiveSheet()->SetCellValue("J".$i,$r[17]);			
		$i++;
	}
	
$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
$objWriter->save('danhsach_nhanvien.xlsx');
echo '<a href="../ns_nhansu/danhsach_nhanvien.xlsx">Download!</a>';
?>