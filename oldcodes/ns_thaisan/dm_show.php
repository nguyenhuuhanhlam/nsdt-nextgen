<?
date_default_timezone_set('Asia/Ho_Chi_Minh');
$con = mysql_connect(_MYSQL_HOST, _MYSQL_USER, _MYSQL_PASSWORD);
mysql_select_db(_MYSQL_DATABASE, $con);
mysql_query("SET NAMES 'UTF8'");

$q = 'SELECT * FROM ns_thaisan ORDER BY id_thaisan';
$data =array();
$result = mysql_query($q);
while($entry = mysql_fetch_array($result)){ 	
		$a = array($entry['id_thaisan'],'<a href="?id=266&id_thaisan='.$entry['id_thaisan'].'">'.$entry['thaisan'].'</a>');
		$data[] = $a;
	} 
mysql_close($con);
echo '<style scoped>
	 .default th {background-color:#f4faff; border:solid 1px #A8D4FF;vertical-align:center;}
	 tr.odd {background-color:#EEEEEE;}
	 tr.header{height:25px;vertical-align:center;}
	 .default td {border:solid 1px #CCCCCC; vertical-align:middle; text-align:center; height:20px;padding-left:5px;}
	 td.empFullname {padding-left:10px; text-align:left}
	 </style>';    
        echo    '<table class="default" width="100%">';
        
        echo    '<tr class="header"><th>STT</th><th>Thai Sản</th></tr>';        
        $i=0;
        foreach($data as $r){
            
            $odd = (int)($i%2) ? '' : 'class="odd"';
            
            echo '<tr '.$odd.'>';
			echo '<td>'.$r[0].'</td>';
            echo '<td style="text-align:left;">'.$r[1].'</td>';
            echo '</tr>';
        }
        echo '</table>';
?>
