<?
session_start();
require_once('../_global.inc');
require_once('../_nsdt/nsdt_php.php');

$inReLoad = (int)$_POST['inReLoad'];
$loadDate = nsdt_inputDate($_POST['loadDate']);
$sql_loadDate = date('Y-m-d', strtotime($loadDate));
$fname = date('Ymd', strtotime($loadDate));
$f = _TIMERECORD_FOLDER.'/'.$fname.'.txt';
$f_log = _MCC_LOG_FOLDER_FAKE.'/'.$sql_loadDate.'.txt';

if($sql_loadDate==date('Y-m-d')){ exit('{E}Không thể đọc ngày hiện hành.'); }
if(!$loadDate){ exit('{E}Chưa nhập ngày tháng.'); }

if(file_exists($f)){
	$_connect = mysql_connect(_MYSQL_HOST, $_SESSION['@username'], $_SESSION['@password']);
	mysql_select_db(_MYSQL_DATABASE, $_connect);
	
	if($inReLoad){
		$q = 'DELETE FROM ___mcc_f WHERE fname="'.$fname.'"';
		mysql_query($q);
		$q = 'DELETE FROM ___mcc_analysis WHERE ngaythangnam="'.$sql_loadDate.'"';
		mysql_query($q);
		if(file_exists($f_log)) unlink($f_log);
	}
	
	$q = 'SELECT * FROM ___mcc_f WHERE fname="'.$fname.'"';
	mysql_query($q);
	if(!mysql_affected_rows($_connect)){
		////(1).reset bảng!	
		mysql_query('DELETE FROM mcc');
		////(2).đọc dữ liệu vào bảng mcc!	
		
		// NEW
		$q = 'LOAD DATA INFILE "'.$f.'" INTO TABLE mcc 
		FIELDS TERMINATED BY "\t" LINES TERMINATED BY "\r\n"
		(@C1, @ngaythangnam, gio, @ma_the, @C5, @C6)
		SET mcc.ngaythangnam = STR_TO_DATE(@ngaythangnam, "%Y/%m/%d"),
		mcc.gio = LEFT(gio, 5),
		mcc.ma_the = TRIM(LEADING "0" FROM TRIM(LEADING "\'" FROM @ma_the))';

		// OLD
		// $q = 	'LOAD DATA INFILE "'.$f.'" INTO TABLE mcc 
		// FIELDS TERMINATED BY "\t" LINES TERMINATED BY "\r\n"
		// (@C1, @ngaythangnam, gio, @ma_the, @C5, @C6)
		// SET mcc.ngaythangnam = STR_TO_DATE(@ngaythangnam, "%d-%m-%Y"),
		// mcc.gio = LEFT(gio, 5),
		// mcc.ma_the = TRIM(LEADING "0" FROM TRIM(LEADING "\'" FROM @ma_the))';

		if(mysql_query($q)){
			$q = 'SELECT ngaythangnam FROM mcc GROUP BY ngaythangnam';
			$result = mysql_query($q);
			if(mysql_affected_rows($_connect)==1){
				///
				$entry = mysql_fetch_array($result);
				if($sql_loadDate==$entry['ngaythangnam']){
					////
					$q = 'INSERT INTO ___mcc_f(fname) VALUES("'.$fname.'")';
					
					if(mysql_query($q)){
						////(3).phân tích dữ liệu từ bảng mcc vào bảng mcc_analysis!		
						$q = 	'INSERT INTO ___mcc_analysis(ma_the, ngaythangnam, gio_trongngay)
						SELECT mcc.ma_the, mcc.ngaythangnam, GROUP_CONCAT(DISTINCT gio ORDER BY gio ASC SEPARATOR " ") AS gio_trongngay
						FROM mcc GROUP BY mcc.ma_the';
						mysql_query($q);
						echo '{I}Đọc dữ liệu thành công.';
					}
					////
				} else { exit('{E}-File ngày tháng và dữ liệu ngày tháng không đồng bộ.');}
			} else { exit('{E}-File dữ liệu không hợp lệ.');}
		} else { exit('{E}File dữ liệu lỗi.');}
	} else { exit('{E}File dữ liệu này đã được đọc.');}
} else { exit('{E}File dữ liệu không tìm thấy.');}
?>