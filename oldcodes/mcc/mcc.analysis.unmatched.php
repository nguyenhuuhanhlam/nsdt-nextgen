<?
	require_once('../_global.inc');
	
	$ngaythangnam = $_POST['ngaythangnam'];
	if($ngaythangnam){
		$sql_ngaythangnam = date('Y-m-d', strtotime($ngaythangnam));
		
		$_connect = mysql_connect(_MYSQL_HOST, _MYSQL_USER, _MYSQL_PASSWORD);
		mysql_select_db(_MYSQL_DATABASE, $_connect);
		
		$q = 'SELECT mcc_analysis.ma_the AS m, ns_nhansu.ma_the FROM mcc_analysis
		LEFT JOIN ns_nhansu ON mcc_analysis.ma_the = ns_nhansu.ma_the
		WHERE ns_nhansu.ma_the IS NULL AND ngaythangnam="'.$sql_ngaythangnam.'"';
		//echo $q; die;
		$result = mysql_query($q);
		
		$unmatched = array();
		while($entry = mysql_fetch_array($result)){
			array_push($unmatched, $entry['m']);
		}
		
		echo '<table style="border:solid 1px gray">';
		foreach($unmatched as $u) echo '<tr><td style="border:solid 1px gray">'.$u.'</td></tr>';
		echo '</table>';
	} else { exit ('{E} - Chưa chọn ngày tháng.');}
	
	
?>