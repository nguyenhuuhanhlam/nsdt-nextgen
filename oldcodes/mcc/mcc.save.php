<?
require_once('../_global.inc');
	
$jsonData = $_POST['D_save'];
$ngaythangnam = $_POST['ngaythangnam'];
$_n = (int)$_POST['_n'];
$sql_ngaythangnam = date('Y-m-d', strtotime($ngaythangnam));
$entry = json_decode($jsonData, true);

$fp = fopen('../mcc_log/'.$sql_ngaythangnam.'.txt', 'a+'); 

foreach($entry as $r){
	$h = array();
	$s = null;
	
	$s .= trim($r['N']).chr(32);
	for($i=1; $i<=8; $i++){
		if(isset($r['H'.$i])){
			if(trim($r['H'.$i])!='')
			array_push($h, trim($r['H'.$i]));
		}
	}
	$s .= implode(chr(32), $h);
	fputs($fp, $s."\r\n");
}

fclose($fp);

////xoa thong tin da xu ly trong analysis
$_connect = mysql_connect(_MYSQL_HOST, _MYSQL_USER, _MYSQL_PASSWORD);
mysql_select_db(_MYSQL_DATABASE, $_connect);
$len_giotrongngay = $_n * 8 + $_n-1;
$q = 	'DELETE FROM mcc_analysis
		WHERE ngaythangnam="'.$sql_ngaythangnam.'" AND LENGTH(gio_trongngay)='.$len_giotrongngay;
mysql_query($q);

?>