<?
session_start();
require_once('../_global.inc');
require_once('../_nsdt/nsdt_php.php');
require_once('../_nsdt/_time.class.php');

$loadDate = nsdt_inputDate($_POST['loadDate']);

if($loadDate){
	$_connect = mysql_connect(_MYSQL_HOST, _MYSQL_USER, _MYSQL_PASSWORD);
	mysql_select_db(_MYSQL_DATABASE, $_connect);
	$q = 'DROP TEMPORARY TABLE IF EXISTS t';
	if(!mysql_query($q)){ mysql_close($_connect); exit('ER1'); }
	$q = 'CREATE TEMPORARY TABLE t (empcode CHAR(16),h1 TIME,h2 TIME,h3 TIME,h4 TIME,h5 TIME,h6 TIME)';
	if(!mysql_query($q)){ mysql_close($_connect); exit('ER2'); }
	
	$f = _MCC_LOG_FOLDER.'/'.$loadDate.'.txt';
	if(file_exists($f)){
				$q = 'LOAD DATA INFILE "'.$f.'" INTO TABLE t
				FIELDS TERMINATED BY " " LINES TERMINATED BY "\r\n"
				(empcode, @h1,@h2,@h3,@h4,@h5,@h6)
				SET	t.h1=@H1,t.h2=@H2,t.h3=@H3,t.h4=@H4,t.h5=@H5,t.h6=@H6';
				//echo $q; die;
				if(!mysql_query($q)){exit('khong load duoc file');}
				
			}else{echo '{E}File dữ liệu không tìm thấy';die;}
	
	$q = 	'SELECT empcode,CONCAT(ho," ",ten) AS fullname,donvi,h1,h2,h3,h4,h5,h6
			FROM t
			JOIN ns_nhansu ON t.empcode=ns_nhansu.ma_nhanvien
			JOIN ns_donvi USING(id_donvi) ORDER BY ns_donvi.donvi';
			// echo $q;
			// die;
	$r=mysql_query($q);
	
	$htmlTable =
		'<style scoped>
		 .default th {background-color:#f4faff; border:solid 1px #A8D4FF;}
		 tr.odd {background-color:#EEEEEE;}
		 tr.header{height:20px}
		 .default td {border:solid 1px #CCCCCC; vertical-align:middle; text-align:center; height:20px}
		 td.x {padding-left:5px; padding-right:5px; text-align:center}
		 td.y {padding-left:5px; text-align:left}
		 td.z {width:43px; text-align:center}
		 </style>';
	$htmlTable .=
		'<table class="default" width="100%"><tr class="header">
		<th class="x">M</th>
		<th>F</th><th>DV</th>
		<th>H1</th><th>H2</th><th>H3</th><th>H4</th><th>H5</th><th>H6</th></tr>';
	
	$T = new _Time();
	while($e=mysql_fetch_array($r,MYSQL_ASSOC)){
		$htmlTable .=
			'<tr>
			<td class="x">'.$e['empcode'].'</td>
			<td class="y">'.$e['fullname'].'</td>
			<td class="y">'.$e['donvi'].'</td>
			<td class="z">'.$T->hmNullShow($e['h1']).'</td>
			<td class="z">'.$T->hmNullShow($e['h2']).'</td>
			<td class="z">'.$T->hmNullShow($e['h3']).'</td>
			<td class="z">'.$T->hmNullShow($e['h4']).'</td>
			<td class="z">'.$T->hmNullShow($e['h5']).'</td>
			<td class="z">'.$T->hmNullShow($e['h6']).'</td></tr>';
	}
	
	$htmlTable .= '</table>';
	
	print($htmlTable); 
	
	mysql_close($_connect);
	
}else {exit('{E}Chưa nhập ngày tháng.');} 
?>