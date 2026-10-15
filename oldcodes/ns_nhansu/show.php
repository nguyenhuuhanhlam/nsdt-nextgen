<?
if(isset($_GET['id'])) $id=$_GET['id']; else $id=null;
if(isset($_GET['ma_nhanvien'])) $ma_nhanvien=$_GET['ma_nhanvien']; else $ma_nhanvien=null;
function sex($s){
	if ($s) return 'Nam'; else return 'Nữ';
}
date_default_timezone_set('Asia/Ho_Chi_Minh');
$data = '';
$i = 0; 
$con = mysql_connect(_MYSQL_HOST, _MYSQL_USER, _MYSQL_PASSWORD);
mysql_select_db(_MYSQL_DATABASE, $con);
$q = 'SELECT * FROM ns_nhansu 
		INNER JOIN ns_donvi ON ns_donvi.id_donvi=ns_nhansu.id_donvi
		INNER JOIN ns_chucvu ON ns_chucvu.id_chucvu=ns_nhansu.id_chucvu
		INNER JOIN ns_quoctich ON ns_quoctich.id_quoctich=ns_nhansu.id_quoctich
		INNER JOIN ns_tinhthanh ON ns_nhansu.noisinh=ns_tinhthanh.id_tinhthanh
		INNER JOIN ns_tinhthanh AS nc ON ns_nhansu.noicapcmnd=nc.id_tinhthanh 
		ORDER by ns_donvi.id_donvi' ;
		
//echo $q; die;
	
$result = mysql_query($q);
while($entry = mysql_fetch_array($result,MYSQL_ASSOC)){ 
		
		$a = array(	'ma_nhanvien'=>$entry['ma_nhanvien'],
					'hoten'=>$entry['ho'].chr(32).$entry['ten'],
					'donvi'=>$entry['donvi'],
					'chucvu'=>$entry['chucvu'],
					'gioitinh'=>sex($entry['gioitinh']),
					'ngaysinh'=>date('d-m-Y',strtotime($entry['ngaysinh'])),
					'ngayvaolam'=>date('d-m-Y',strtotime($entry['ngayvaolam'])),
					'ngayhetthuviec'=>date('d-m-Y',strtotime($entry['ngayhetthuviec'])),
					'socmnd'=>$entry['socmnd'],
					'ngaycapcmnd'=>$entry['ngaycapcmnd'],
					'nguyenquan'=>$entry['nguyenquan'],
					'diachithuongtru'=>$entry['diachithuongtru'],
					'diachicungu'=>$entry['diachicungu'],
					'quoctich'=>$entry['quoctich'],
					'noisinh'=>$entry['tinhthanh'],
					'noicapcmnd'=>$entry['tinhthanh']
					);	
		$data .= 'data['.$i.'] ='.json_encode($a).';';
		$i++; 
	} 
	
//echo $data;	
mysql_close($con);
?>

<script type="text/javascript">
		var grid;
		var data = []; <? echo $data; ?>
		var storyid_donviFormatter = function(row, cell, value, columnDef, dataContext) {
		    return "<a href='?id=202&ma_nhanvien=" + dataContext["ma_nhanvien"] + "'>" + dataContext["ma_nhanvien"] + "</a>";
		};		
		var columns = [
		
			{id:"ma_nhanvien", name:"ID", field:"ma_nhanvien", width:100, resizable:false, formatter:storyid_donviFormatter},
			{id:"hoten", name:"Họ tên", field: "hoten", width:210},
			{id:"donvi", name:"Đơn vị", field:"donvi", width:100, resizable:false},
			{id:"chucvu", name:"Chức vụ", field:"chucvu", width:100, resizable:false},
			{id:"gioitinh", name:"Sex", field:"gioitinh", width:40, resizable:false},
			{id:"ngaysinh", name:"Ngày sinh", field:"ngaysinh", width:75, resizable:false},
			{id:"ngayvaolam", name:"Ngày vào", field:"ngayvaolam", width:75, resizable:false},
			{id:"ngayhetthuviec", name:"Ngày KTTV", field:"ngayhetthuviec", width:75, resizable:false},
			{id:"nguyenquan", name:"Nguyên quán", field:"nguyenquan", width:85, resizable:false},
			{id:"diachithuongtru", name:"DC TT", field:"diachithuongtru", width:175, resizable:false},
			{id:"socmnd", name:"CMND", field:"socmnd", width:175, resizable:false},
			{id:"ngaycapcmnd", name:"Ngày CMND", field:"ngaycapcmnd", width:175, resizable:false},
			{id:"quoctich", name:"Quốc tịch", field:"quoctich", width:175, resizable:false},
			{id:"noisinh", name:"Nơi sinh", field:"noisinh", width:175, resizable:false},
			{id:"noicapcmnd", name:"Nơi cap", field:"noicapcmnd", width:175, resizable:false},
			{id:"diachicungu", name:"DC CN", field:"diachicungu", width:175, resizable:false}
			
		];
			
		var options = {
			enableCellNavigation: false,
            enableColumnReorder: false
		};
        grid = new Slick.Grid($("#myGrid"), data, columns, options);
		$("#grid").show();
		
</script>