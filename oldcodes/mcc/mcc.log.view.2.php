<?
require_once('../_global.inc');
require_once('../_nsdt/nsdt_php.php');
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
				SET
				t.h1=(@H1),
				t.h2=(@H2),
				t.h3=(@H3),
				t.h4=(@H4),
				t.h5=(@H5),
				t.h6=(@H6)';
				
				if(!mysql_query($q)){exit('khong load duoc file');}
				
			}else{echo '*file du lieu khong tim thay';die;}
	
	$q = 	'SELECT ns_nhansu.ma_nhanvien,CONCAT(ho," ",ten) AS fullname, id_donvi,donvi
			FROM ns_nhansu
			LEFT JOIN t ON empcode=ma_nhanvien
			JOIN ns_donvi USING(id_donvi)
			WHERE empcode IS NULL
			AND ma_the IS NOT NULL
			AND ngayvaolam <= "'.$loadDate.'"
			AND ns_nhansu.ma_nhanvien NOT IN (SELECT ns_xinphep.ma_nhanvien
			FROM ns_xinphep
			WHERE ("'.$loadDate.'" BETWEEN ngay_batdau AND ngay_ketthuc AND songaynghi>0))
			ORDER BY id_donvi';

	$r = mysql_query($q);
	
	$htmlTable =
		'<style scoped>
		 .default th {background-color:#f4faff; border:solid 1px #A8D4FF;}
		 tr.odd {background-color:#EEEEEE;}
		 tr.header{height:20px}
		 .default td {border:solid 1px #CCCCCC; vertical-align:middle; text-align:center; height:20px}
		 td.x {padding-left:5px; padding-right:5px; text-align:center}
		 td.y {padding-left:5px; text-align:left}
		 td.z {width:43px; text-align:left}
		 </style>';
	$htmlTable .=
		'<table class="default" width="100%"><tr class="header">
		<th class="x">#</th>
		<th class="x">M</th>
		<th>F</th>
		<th>DV</th>
		</tr>';
	$i = 0;	
	while($e=mysql_fetch_array($r,MYSQL_ASSOC)){
		$htmlTable .=
			'<tr>
			<td>'.++$i.'</td>
			<td class="x">'.$e['ma_nhanvien'].'</td>
			<td class="y">'.$e['fullname'].'</td>
			<td class="x">'.$e['donvi'].'</td>
			</tr>';
	}
	$htmlTable .= '</table>';
	
	print($htmlTable); 
}
?>