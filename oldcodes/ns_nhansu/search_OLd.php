<?
require_once('../_global.inc');
require_once('../_nsdt/nsdt_php.php');

$maso_type = (int)$_POST['maso_type'];
$maso = trim($_POST['maso']);
$ten = $_POST['ten'];
$id_donvi = $_POST['id_donvi'];
$id_chucvu = $_POST['id_chucvu'];
$ngay_batdau1 = nsdt_inputDate($_POST['ngay_batdau1']);
$ngay_ketthuc1 = nsdt_inputDate($_POST['ngay_ketthuc1']);
$ngay_batdau2 = nsdt_inputDate($_POST['ngay_batdau2']);
$ngay_ketthuc2 = nsdt_inputDate($_POST['ngay_ketthuc2']);
$nhs =$_POST['nhs'];
$ndh = $_POST['ndh'];
$xnv = $_POST['xnv'];
$tynv = $_POST['tynv'];
$con = mysql_connect(_MYSQL_HOST, _MYSQL_USER, _MYSQL_PASSWORD);
mysql_select_db(_MYSQL_DATABASE, $con);

	$q = 'SELECT ns_nhansu.ma_nhanvien,ma_the,ho,ten,gioitinh,ngaysinh,ns.tinhthanh AS noisinh,nguyenquan,diachithuongtru,diachicungu,quoctich,dantoc,socmnd,ngaycapcmnd,nc.tinhthanh AS noicapcmnd,honnhan,hocvan,donvi,chucvu,congviec,ngayvaolam,ngayhetthuviec,hesoluong,loaihopdong,ngay_nghiviec
				FROM ns_nhansu
				JOIN ns_tinhthanh AS ns ON ns.id_tinhthanh = ns_nhansu.noisinh
				JOIN ns_quoctich ON ns_quoctich.id_quoctich = ns_nhansu.id_quoctich
				JOIN ns_tinhthanh AS nc ON ns_nhansu.noicapcmnd=nc.id_tinhthanh
				JOIN ns_hdld ON ns_hdld.id_hdld = ns_nhansu.id_hdld
				JOIN ns_dantoc ON ns_dantoc.id_dantoc = ns_nhansu.id_dantoc
				JOIN ns_honnhan ON ns_honnhan.id_honnhan = ns_nhansu.id_honnhan
				JOIN ns_hocvan ON ns_hocvan.id_hocvan = ns_nhansu.id_hocvan
				JOIN ns_donvi ON ns_donvi.id_donvi = ns_nhansu.id_donvi
				JOIN ns_chucvu ON ns_chucvu.id_chucvu = ns_nhansu.id_chucvu
				JOIN ns_congviec ON ns_congviec.id_congviec = ns_nhansu.id_congviec';
$a = array();
if($nhs==1) array_push($a, 'ns_nhansu.id_nghidaihan="1"');
if($ndh==1) array_push($a, 'ns_nhansu.id_nghidaihan="2"');
if($xnv==1) array_push($a, 'ns_nhansu.id_nghidaihan="3"');
if($tynv==1) array_push($a, 'ns_nhansu.id_nghidaihan="4"');
if($ten) array_push($a, 'lower(ten) REGEXP lower("^'.$ten.'$")');
if($id_donvi) array_push($a, 'ns_nhansu.id_donvi='.$id_donvi);
if($id_chucvu) array_push($a, 'ns_nhansu.id_chucvu='.$id_chucvu);

if ($ngay_ketthuc1 and $ngay_batdau1){
	array_push($a, '(("'.$ngay_batdau1.'" <= ngayvaolam) OR ("'.$ngay_ketthuc1.'" = ngayhetthuviec)) AND (("'.$ngay_ketthuc1.'" > ngayvaolam) OR ("'.$ngay_ketthuc1.'" = ngayvaolam))');}
if ($ngay_batdau1 and $ngay_ketthuc1==null){
	array_push($a, '(ngayvaolam>="'.$ngay_batdau1.'")');}
	
if ($ngay_ketthuc2 and $ngay_batdau2){
	array_push($a, '(("'.$ngay_batdau2.'" <= ngaysinh) OR ("'.$ngay_ketthuc2.'" = ngaysinh)) AND (("'.$ngay_ketthuc2.'" > ngaysinh) OR ("'.$ngay_ketthuc2.'" = ngaysinh))');}
if ($ngay_batdau2 and $ngay_ketthuc2==null){
	array_push($a, '(ngaysinh>="'.$ngay_batdau2.'")');}
if($maso_type==1){
	if ($maso){
		array_push($a, 'ns_nhansu.ma_nhanvien LIKE "%'.$maso.'"');}}		

else
		array_push($a, 'ma_the LIKE "%'.$maso.'"');

for($i=0; $i<count($a); $i++){
	if(!$i) $q .= ' WHERE '.$a[$i]; else $q .= ' AND '.$a[$i];
}
$q .=' ORDER BY ns_nhansu.id_donvi,ns_nhansu.id_chucvu';
//echo $q;die;

$data = array();
$r = mysql_query($q);
while($e = mysql_fetch_array($r,MYSQL_ASSOC)){
	
	$a = array('<a href="?id=202&ma_nhanvien='.$e['ma_nhanvien'].'">'.$e['ma_nhanvien'].'</a>',$e['ma_the'],$e['ho'],$e['ten'],$e['gioitinh'],dateInZero(nsdt_dmyFormat($e['ngaysinh'])),$e['noisinh'],$e['nguyenquan'],$e['diachithuongtru'],$e['diachicungu'],$e['quoctich'],$e['dantoc'],$e['hocvan'],$e['honnhan'],$e['socmnd'],nsdt_dmyFormat($e['ngaycapcmnd']),$e['noicapcmnd'],$e['donvi'],$e['chucvu'],$e['congviec'],nsdt_dmyFormat($e['ngayvaolam']),nsdt_dmyFormat($e['ngayhetthuviec']),$e['hesoluong'],$e['loaihopdong'],nsdt_dmyFormat($e['ngay_nghiviec']));
	$data[]= $a;				
} 
mysql_close($con);
file_put_contents('../ns_nhansu/danhsach_nhanvien.json',json_encode($data));
		echo '<style scoped>
			             .default th {background-color:#f4faff; border:solid 1px #A8D4FF; padding-top:3px;}
                         tr.odd {background-color:#EEEEEE;}
                         tr.header{height:20px}
                         .default td {border:solid 1px #CCCCCC; vertical-align:middle; text-align:center; height:25px;padding-left:5px;}
                         td.empFullname {padding-left:5px; text-align:left}
                         </style>';    
        echo    '<input type="button" value="toExcel" onclick="window.open(\'ns_nhansu/danhsach_toExcel.php\',\'_blank\')">
                <table id="default" class="default" width="1600px">';
        
        echo    '<tr class="header">
                <th>STT</th>
                <th>MÃ NHÂN VIÊN</th><th>MÃ THẺ</th><th>HỌ & TÊN</th><th>ĐƠN VỊ</th><th>CHỨC VỤ</th>
				<th>GT</th><th>NGÀY SINH</th><th>NGÀY VÀO</th><th>NGÀY KTTV</th><th>ĐCCN</th><th>HSL</th><th>HĐLĐ</th><th>NNV</th></tr>';
        $i=0;
		
        foreach($data as $r){
            $odd = (int)($i%2) ? '' : 'class="odd"';
            echo '<tr '.$odd.'>';
            echo '<td>'.($i+1).'</td>'; //STT
            echo '<td style="text-align:left">'.$r[0].'</td>'; //MANV
            echo '<td style="text-align:left">'.$r[1].'</td>'; //mathe
			echo '<td style="text-align:left">'.$r[2].' '. $r[3].'</td>'; //hoten
            echo '<td>'.$r[17].'</td>'; //donvi
            echo '<td>'.$r[18].'</td>';//chucvu
            echo '<td>'.$r[4].'</td>';//gioitinh
			echo '<td>'.$r[5].'</td>';//ngaysinh
			echo '<td>'.$r[20].'</td>';//ngayvao
			echo '<td>'.$r[21].'</td>';//ngaykttv
			echo '<td style="text-align:left">'.$r[9].'</td>';//dccn
			echo '<td>'.$r[22].'</td>';//HSL
			echo '<td>'.$r[23].'</td>';//HDLD
			echo '<td>'.$r[24].'</td>';//dccn
            echo '</tr>';
            $i++;
        }
        echo '</table>';
		
?>