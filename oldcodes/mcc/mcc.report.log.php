<?
session_start();
require_once('../_global.inc');
require_once('../_nsdt/nsdt_php.php');
require_once('../_nsdt/nsdt_mysql.php');

$fromDate = nsdt_inputDate($_POST['fromDate']);
$toDate = nsdt_inputDate($_POST['toDate']);

if(!$fromDate){exit('{E}-Chưa nhập ngày bắt đầu.');}
if(!$toDate){exit('{E}-Chưa nhập ngày kết thúc.');}

$_connect = mysql_connect(_MYSQL_HOST, _MYSQL_USER, _MYSQL_PASSWORD);
mysql_select_db(_MYSQL_DATABASE, $_connect);

$q = 	'SELECT * FROM mcc_report_log
		WHERE "'.$fromDate.'" BETWEEN fromDate AND toDate
		OR "'.$toDate.'" BETWEEN fromDate AND toDate';
mysql_query($q);

if(!mysql_affected_rows($_connect)){
	$q = 'INSERT INTO mcc_report_log(fromDate,toDate,isProcessed) VALUES("'.$fromDate.'","'.$toDate.'",1)';
	mysql_query($q);
	
	//update ns_xinphep
	$q = $timxinphep('2,4',$fromDate,$toDate);
	$r = mysql_query($q);
	if(mysql_affected_rows($_connect)){
		while($e=mysql_fetch_array($r)){
			$q = 'SELECT intersectDays("'.$fromDate.'","'.$toDate.'","'.$e['ngay_batdau'].'","'.$e['ngay_ketthuc'].'")';
			$r = mysql_query($q);
			$idays = mysql_fetch_row($r);
			$songaytru = $e['songaytru'];
			$tongsongaytru = $idays[0]+$songaytru;
			$tongsongaytru = ($tongsongaytru>3) ? 3 : $tongsongaytru;
			$q = 'UPDATE ns_xinphep SET songaytru='.$tongsongaytru.' WHERE ma_nhanvien="'.$e['ma_nhanvien'].'" AND id_phepnam IN(2,4)';
			mysql_query($q);
		}
	}
	
	$q = $timxinphep('3',$fromDate,$toDate);
	$r = mysql_query($q);
	if(mysql_affected_rows($_connect)){
		while($e=mysql_fetch_array($r)){
			$q = 'UPDATE ns_xinphep SET songaytru=1 WHERE ma_nhanvien="'.$e['ma_nhanvien'].'" AND id_phepnam IN(3)';
			mysql_query($q);
		}
	}
	
	//update ns_xuly_thaisan
	$q = $pregnancySearchAll($fromDate,$toDate);
	
	echo '{I}-OK.';
} else echo '{E}-Trùng thời gian. Vui lòng chọn lại.';

mysql_close($_connect);
?>