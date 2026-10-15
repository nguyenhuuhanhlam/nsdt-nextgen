<?
date_default_timezone_set('Asia/Ho_Chi_Minh');
$con = mysql_connect(_MYSQL_HOST, _MYSQL_USER, _MYSQL_PASSWORD);
mysql_select_db(_MYSQL_DATABASE, $con);

$q = 'SELECT *,CONCAT(ho," ",ten) AS hoten FROM ns_xuly_thaisan
		INNER JOIN ns_nhansu 	ON ns_xuly_thaisan.ma_nhanvien=ns_nhansu.ma_nhanvien
		INNER JOIN ns_donvi 	ON ns_xuly_thaisan.id_donvi = ns_donvi.id_donvi
		INNER JOIN ns_thaisan 	ON ns_xuly_thaisan.id_thaisan = ns_thaisan.id_thaisan';
	
$result = mysql_query($q);
while($entry = mysql_fetch_array($result,MYSQL_ASSOC)){ 
		
		$a = array('<a href="?id=268&id_xuly_thaisan='.$entry['id_xuly_thaisan'].'">'.$entry['ma_nhanvien'].'</a>',$entry['hoten'],$entry['donvi'],$entry['thaisan'],$entry['ngay_batdau'],$entry['ngay_ketthuc'],$entry['songaytru']);
		$data[] = $a;
	}
//echo $data;	
mysql_close($con);
file_put_contents('../ns_thaisan/report_thaisan.json',json_encode($data));
echo '<style scoped>
			             .default th {background-color:#f4faff; border:solid 1px #A8D4FF;}
                         tr.odd {background-color:#EEEEEE;}
                         tr.header{height:20px}
                         .default td {border:solid 1px #CCCCCC; vertical-align:middle; text-align:center;}
                         td.empFullname {padding-left:10px; text-align:left}
                         </style>';    
        echo    '<input type="button" value="toPDF" onclick="window.open(\'ns_thaisan/toPDF.php\',\'_blank\')">
                <table class="default" width="100%">';
        
        echo    '<tr class="header">
                <th>STT</th>
                <th>MÃ NHÂN VIÊN</th>
                <th>HỌ & TÊN</th>
                <th>ĐƠN VỊ</th><th>THAI SẢN</th><th>NGÀY BĐ</th><th>NGÀY KT</th><th>NGÀY KT</th></tr>';        
        $i=0;
        foreach($data as $r){
            
            $odd = (int)($i%2) ? '' : 'class="odd"';
            echo '<tr '.$odd.'>';
            echo '<td>'.($i+1).'</td>'; //STT
            echo '<td>'.$r[0].'</td>'; //MANV
            echo '<td style="text-align:left;">'.$r[1].'</td>'; //HOTEN
			echo '<td style="text-align:left;">'.$r[2].'</td>'; //DONVI
            echo '<td style="text-align:left;">'.$r[3].'</td>'; //TS
            echo '<td>'.nsdt_dmyFormat($r[4]).'</td>';
            echo '<td>'.nsdt_dmyFormat($r[5]).'</td>';
			 echo '<td>'.$r[6].'</td>'; //MANV
            echo '</tr>';
            
            $i++;
        }
        echo '</table>';
?>