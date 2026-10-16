<?
session_start();
require_once('../_global.inc');
require_once('../_nsdt/nsdt_php.php');

$date = nsdt_inputDate($_POST['date']);

if(!$date){ exit('{E}Chưa nhập ngày tháng.'); }

$f_raw = _MCC_DATA_FOLDER . '/' . date('Ymd', strtotime($date)) . '.txt';
$f_fake = _TIMERECORD_FOLDER . '/' . date('Ymd', strtotime($date)) . '.txt';

if(file_exists($f_raw)){
	
	$conn = mysql_connect(_MYSQL_HOST, $_SESSION['@username'], $_SESSION['@password']);
	mysql_select_db(_MYSQL_DATABASE, $conn);
	
	$q ='SELECT closingTime FROM ___mcc_working_day WHERE day ="'.$date.'"';
		$r = mysql_query($q);
		$e = mysql_fetch_array($r,MYSQL_ASSOC);
		if (!$e['closingTime']){
			mysql_close($conn);
			exit ('{E} Chưa có giờ đóng chuyền');}
	$query_string = 'DELETE FROM _timerecord';
	mysql_query($query_string);
	
	$query_string = 'LOAD DATA INFILE "'.$f_raw.'" INTO TABLE _timerecord 
		FIELDS TERMINATED BY "\t" LINES TERMINATED BY "\r\n"
		(Event,Date,Time,IDCode,Shift,MachineNumber)
		SET _timerecord.Time=fixRawTime(Time,"'.$e['closingTime'].'")';
	
	//echo $query_string; die;
	
	if(mysql_query($query_string)){
		////
		if(file_exists($f_fake)) unlink($f_fake);
		
		$query_string = 'SELECT * INTO OUTFILE "'.$f_fake.'" FIELDS TERMINATED BY "\t" LINES TERMINATED BY "\r\n" FROM _timerecord';
		if(mysql_query($query_string)){
			echo '{I}OK';
		} else exit('{E}Lỗi truy vấn khi xuất file!');
	} else exit('{E}Lỗi truy vấn khi load file!');
	
} else exit('{E}File dữ liệu không tìm thấy.');
?>