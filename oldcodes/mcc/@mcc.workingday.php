<?
session_start();
require_once('../_global.inc');
require_once('../_nsdt/nsdt_php.php');

$day = nsdt_inputDate($_POST['day']);
$closingTime = $_POST['closingTime'];
$holiday = isset($_POST['holiday']) ? (int)$_POST['holiday'] : 0;
$disable = isset($_POST['disable']) ? (int)$_POST['disable'] : 0;

if($day){
	$_connect = mysql_connect(_MYSQL_HOST, _MYSQL_USER, _MYSQL_PASSWORD);
	mysql_select_db(_MYSQL_DATABASE, $_connect);
	
	$q = 'SELECT day FROM ___mcc_working_day WHERE day="'.$day.'"';
	mysql_query($q);
	
	if(mysql_affected_rows($_connect)){
		
		$qc = $closingTime ? 'closingTime="'.$closingTime.'",' : '';
		
		$q =	'UPDATE ___mcc_working_day
				SET '.$qc.' holiday='.$holiday.',disable='.$disable.'
				WHERE day="'.$day.'"';
		if(mysql_query($q)) echo '{I}OK'; else echo '{E}Loi';
		
	}else{
	
		if(!$closingTime) {exit('{E} Chưa nhập giờ đóng chuyền.');}
		
	
			$q = 	'INSERT INTO ___mcc_working_day(day,closingTime,holiday,disable)
					VALUES("'.$day.'","'.$closingTime.'",'.$holiday.','.$disable.')';
			
		if	(mysql_query($q)){
		echo "{I}OK";
		}
		else{
		echo "{E}Lỗi";
		}
		
		
	}
	mysql_close($_connect);
	
} else {exit('{E}Chưa nhập ngày tháng');}
?>