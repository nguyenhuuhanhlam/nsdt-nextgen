<?
require_once('../_global.inc');
require_once('../_nsdt/nsdt_php.php');
$maso_type = $_POST['maso_type'];
$maso = $_POST['maso'];
$id_donvi_x = $_POST['id_donvi_x'];
$timngay_batdau = nsdt_inputDate($_POST['timngay_batdau']);
$timngay_ketthuc = nsdt_inputDate($_POST['timngay_ketthuc']);

$con = mysql_connect(_MYSQL_HOST, _MYSQL_USER, _MYSQL_PASSWORD);
mysql_select_db(_MYSQL_DATABASE, $con);

if ((!$maso_type) and (!$maso) and (!$timngay_batdau) and (!$timngay_ketthuc) and (!$id_donvi_x)){
	echo "Vui lòng nhập từ khóa để tìm";die;}
else{
if (($maso_type) and (!$maso)){
	echo "Vui lòng nhập 1 từ khóa để tìm";die;}
else{
$q = 'SELECT *,CONCAT (ho," ",ten) AS hoten FROM ns_xuly_thaisan 
		JOIN ns_nhansu using (ma_nhanvien)
		JOIN ns_donvi ON ns_donvi.id_donvi=ns_nhansu.id_donvi
		JOIN ns_thaisan using(id_thaisan)';
$a = array();
switch($maso_type){
    case 1:
        if($maso) array_push($a, 'ns_xuly_thaisan.ma_nhanvien LIKE "%'.$maso.'"');
    break;
    
    case 2:
        array_push($a, 'lower(ns_nhansu.ten) REGEXP lower("^'.$maso.'")');
    break;
}
if (($timngay_ketthuc) AND ($timngay_batdau)){
	array_push($a, '(("'.$timngay_batdau.'" <= ngay_ketthuc OR "'.$timngay_ketthuc.'" = ngay_ketthuc) AND ("'.$timngay_ketthuc.'" > ngay_batdau OR "'.$timngay_ketthuc.'" = ngay_batdau))');}

if (($timngay_batdau) AND ($timngay_ketthuc ==null)){
	array_push($a, '(ngay_batdau>="'.$timngay_batdau.'")');}

if ($id_donvi_x){
array_push($a, 'ns_nhansu.id_donvi = '.$id_donvi_x.'');
}

for($i=0; $i<count($a); $i++){
	if(!$i){
		$q .= ' WHERE '.$a[$i];}
	else{
		$q .= ' AND '.$a[$i];}
}}}
$q .=' ORDER BY ns_nhansu.id_donvi';

//echo $q; die;

$data = array();
	$result = mysql_query($q);
		while ($entry=mysql_fetch_array($result))
		 {
		  $a = array('<a href="?id=268&id_xuly_thaisan='.$entry['id_xuly_thaisan'].'">'.$entry['ma_nhanvien'].'</a>',$entry['hoten'],$entry['donvi'],$entry['thaisan'],$entry['ngay_batdau'],$entry['ngay_ketthuc'],$entry['songaynghi']);
		$data[] = $a;
		 }
mysql_close($con);
file_put_contents('../ns_thaisan/report_thaisan.json',json_encode($data));
echo '<style scoped>
			             .default th {background-color:#f4faff; border:solid 1px #A8D4FF;}
                         tr.odd {background-color:#EEEEEE;}
                         tr.header{height:20px}
                         .default td {border:solid 1px #CCCCCC; vertical-align:middle; text-align:center;}
                         td.empFullname {padding-left:10px; text-align:left}
                         </style>';    
        echo    '<input type="button" value="In..." onclick="window.open(\'ns_thaisan/toPDF.php\',\'_blank\')">
                <table class="default" width="100%">';
        
        echo    '<tr class="header">
                <th>STT</th>
                <th>MÃ NHÂN VIÊN</th>
                <th>HỌ & TÊN</th>
                <th>ĐƠN VỊ</th><th>THAI SẢN</th><th>NGÀY BĐ</th><th>NGÀY KT</th><th>SNN</th></tr>';        
        $i=0;
        foreach($data as $r){
            $odd = (int)($i%2) ? '' : 'class="odd"';
            echo '<tr '.$odd.'>';
            echo '<td>'.($i+1).'</td>'; //STT
            echo '<td>'.$r[0].'</td>'; //MANV
            echo '<td style="text-align:left; padding-left:5px;"">'.$r[1].'</td>'; //HOTEN
			echo '<td>'.$r[2].'</td>'; //DONVI
            echo '<td>'.$r[3].'</td>'; //thaisan
            echo '<td>'.nsdt_dmyFormat($r[4]).'</td>';
            echo '<td>'.nsdt_dmyFormat($r[5]).'</td>';
			echo '<td>'.$r[6].'</td>'; //so ngay nghi
            echo '</tr>';
            $i++;
        }
        echo '</table>';
?>