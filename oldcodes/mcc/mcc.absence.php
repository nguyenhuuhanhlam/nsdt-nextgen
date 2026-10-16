<?
require_once('../_global.inc');

$loadDate = $_POST['loadDate'];
$fname = date('Ymd', strtotime($loadDate));
$f = _MCC_DATA_FOLDER.'/'.$fname.'.txt';

if(file_exists($f)){
	$_connect = mysql_connect(_MYSQL_HOST, _MYSQL_USER, _MYSQL_PASSWORD);
	mysql_select_db(_MYSQL_DATABASE, $_connect);
	
	mysql_query('DELETE FROM mcc');
	mysql_query('DELETE FROM mcc_absence');
	
	$q = 	'LOAD DATA INFILE "'.$f.'" INTO TABLE mcc 
		FIELDS TERMINATED BY "\t" LINES TERMINATED BY "\r\n"
		(@C1, @ngaythangnam, gio, @ma_the, @C5, @C6)
		SET mcc.ngaythangnam = STR_TO_DATE(@ngaythangnam, "%d-%m-%Y"),
		mcc.gio = LEFT(gio, 5),
		mcc.ma_the = TRIM(LEADING "0" FROM TRIM(LEADING "\'" FROM @ma_the))';
	mysql_query($q);
	
	$q = 	'INSERT INTO mcc_absence(ma_the, ngaythangnam, gio_trongngay)
		SELECT mcc.ma_the, mcc.ngaythangnam, GROUP_CONCAT(DISTINCT gio ORDER BY gio ASC SEPARATOR " ") AS gio_trongngay
		FROM mcc GROUP BY mcc.ma_the';
	mysql_query($q);
	
	$q = 'SELECT * FROM ns_donvi';
	$result = mysql_query($q);
	
	$q = 'SELECT mcc_absence.ma_the, ns_nhansu.ma_the, ho, ten,donvi, ma_nhanvien, ns_nhansu.id_donvi
	FROM ns_nhansu
	LEFT JOIN mcc_absence ON ns_nhansu.ma_the=mcc_absence.ma_the
	INNER JOIN ns_donvi ON ns_nhansu.id_donvi=ns_donvi.id_donvi
	WHERE mcc_absence.ma_the IS NULL AND ns_nhansu.ma_the IS NOT NULL ORDER BY ns_nhansu.id_donvi';
	
	$result = mysql_query($q);

	$D = array();
	$i=0;
	while($entry = mysql_fetch_array($result)){
		$i++;
		
		$a = array(	'I'=>$i,
					'N'=>$entry['ma_nhanvien'],
					'F'=>$entry['ho'].' '.$entry['ten'],
					'D'=>$entry['donvi']);
		
		array_push($D, $a);
	}
	
	echo json_encode($D);

	
} else echo '{E}- Chưa chọn ngày tháng';
?>