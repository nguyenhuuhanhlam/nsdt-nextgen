<?
	require_once('../_global.inc');
	
	$ngaythangnam = $_POST['ngaythangnam'];
	$sql_ngaythangnam = date('Y-m-d', strtotime($ngaythangnam));
	
	$_connect = mysql_connect(_MYSQL_HOST, _MYSQL_USER, _MYSQL_PASSWORD);
	mysql_select_db(_MYSQL_DATABASE, $_connect);
	
	if($ngaythangnam!=0){
		$q = 'SELECT LENGTH(gio_trongngay) AS n FROM ___mcc_analysis WHERE ngaythangnam="'.$sql_ngaythangnam.'" GROUP BY n';
		$result = mysql_query($q);
		
		$s = null;
		while($entry = mysql_fetch_array($result)){
			$n = (int)($entry['n'] / 8);
			$s .= '<option>'.$n.'</option>'; 
		}
		
		echo $s;
	} else echo '<option value="0">---</option>';
	
?>