<?
$id_xuly_thaisan = isset($_GET['id_xuly_thaisan'])? $_GET['id_xuly_thaisan'] : null;
if ($id_xuly_thaisan){
	$q = 	'SELECT *, CONCAT(ho," ",ten) AS hoten FROM ns_xuly_thaisan
						JOIN ns_nhansu 	ON ns_xuly_thaisan.ma_nhanvien=ns_nhansu.ma_nhanvien
						JOIN ns_donvi 	ON ns_nhansu.id_donvi=ns_donvi.id_donvi
						JOIN ns_thaisan 	ON ns_thaisan.id_thaisan=ns_xuly_thaisan.id_thaisan
					WHERE id_xuly_thaisan='.$id_xuly_thaisan.'';
					
		$result = mysql_query($q);
		while ($entry=mysql_fetch_array($result,MYSQL_ASSOC)){
			$ma_nhanvien = $entry['ma_nhanvien'];
			$hoten = $entry['hoten'];
			$donvi = $entry['donvi'];
			$thaisan = $entry['thaisan'];
			$id_thaisan = $entry['id_thaisan'];
			$songaynghi = $entry['songaynghi'];
			$ngay_batdau = nsdt_dmyFormat($entry['ngay_batdau']);
			$ngay_ketthuc = nsdt_dmyFormat($entry['ngay_ketthuc']);
		}	
}
?>
<div class="clear" style="height:10px;"></div>
<div class="form_text"><b>Cập Nhật Tình Trạng Thai Sản</b><span id="iconWait" style="margin-left:10px"></span></div>
<div class="clear"></div>
<div class="grid_16" style="background-color:#F4F4F4;">
<form id="thaisan_xuly">
	<table style="margin-left:10px;margin-top:10px">
		<tr>
			<td style="width:100px">Mã nhân viên:</td>
			<td>
				<input type="hidden" id="id_xuly_thaisan" name="id_xuly_thaisan" size="12" value="<? echo $id_xuly_thaisan;?>">
				<input type="text" id="ma_nhanvien" name="ma_nhanvien" size="12" value="<? echo $ma_nhanvien;?>" readonly>
			</td>
			<td>Họ tên nhân viên:</td>
			<td><input type="text" id="hoten" name="hoten" size="30" value="<? echo $hoten;?>" readonly></td>
		<tr>
		<tr>
			<td>Đơn vị:</td>
			<td>
				<input type="hidden" id="id_donvi" name="id_donvi" value="<? echo $id_donvi;?>">
				<input type="text" id="donvi" name="donvi" size="12" value="<? echo $donvi;?>" readonly>
			</td>
			<td>Thai sản:</td>
			<td>
				<select id="id_thaisan" name="id_thaisan">
				<?
					$q='SELECT * FROM ns_thaisan';
					$result=mysql_query($q);
					while ($entry=mysql_fetch_array($result,MYSQL_ASSOC)){
					if($entry['id_thaisan']==$id_thaisan) $selected = 'selected'; else $selected = null;
					echo '<option '.$selected.' value="'.$entry['id_thaisan'].'">'.$entry['thaisan'].'</option>';
					}	
				?>
				</select>
			</td>
		</tr>
		<tr>
			<td>Từ ngày:</td>
			<td><input type="text" id="ngay_batdau" name="ngay_batdau" size="12" value="<? echo $ngay_batdau ?>"></td>
			<td>Đến ngày:</td>
			<td><input type="text" id="ngay_ketthuc" name="ngay_ketthuc" size="12" value="<? echo $ngay_ketthuc ?>"></td>
		</tr>
		</tr>
			<td><input type="button" onClick="location.href='?id=267'" value="Nhập mới..."></td>
			<td></td><td></td><td><input id="modify" type="button" value="Lưu..."></td>
			<td></td><td></td><td><input id="delete" type="button" value="Xóa..."></td>
		</tr>
	</table>
</form>	
</div>
<div class="clear" style="height: 10px;"></div>
<div class="grid_16" style="background-color: #F4F4F4; border-radius: 4px 15px 15px 15px;">
	<table style="margin-left:10px">
	<tr><td><select id="maso_type" name="maso_type">
		<option value="1">Mã nhân viên</option>
		<option value="2">Họ tên</option>
		</select></td>
	
		<td><input type="text" id="maso" name="maso" maxlength="16"></td>
		<td>Từ ngày:</td><td><input type="text" id="timngay_batdau" name="timngay_batdau" maxlength="10" size="12"></td>
		<td>Đến ngày:</td><td><input type="text" id="timngay_ketthuc" name="timngay_ketthuc" maxlength="10" size="12"></td>
		<td>Đơn vị:</td><td><select id="id_donvi_x" name ="id_donvi_x"><option value="">---</option><? echo nsdt_mysql_droplist('ns_donvi', 'id_donvi', 'donvi'); ?></select></td>
		<td><input type="button" id="search" value="Tìm kiếm..."></td>
		</tr>
	</table>
</div>
<div class="clear" style="height: 10px;"></div>
<div class="grid_16">
	<div id="myGrid" style="height:300px; overflow:auto"></div>
	</div>
<script>
$(document).ready(function(){
	//Calender
	<? echo _CALENDARVI; ?>
	
    calendarVI = new dhtmlXCalendarObject(["ngay_batdau","ngay_ketthuc","timngay_batdau","timngay_ketthuc"]);
	calendarVI.loadUserLanguage("vi");
    calendarVI.setDateFormat("%d-%m-%Y");
    calendarVI.hideTime();
	//1. Sửa
	$("#modify").click(function(){
		$.ajax({
			url:"ns_thaisan/ns_modify.php",
			type:"POST",
			data:$("#thaisan_xuly").serialize(),
			success:function(responseText){
				if(responseText=="1"){
					alert('Ghi nhận thành công');
					location.href='/?id=267';
				}
				else{
					alert(responseText);
				}
			}
		});
	});
	//2. Search-----------
	$("#search").click(function(){
		var maso_type = $("#maso_type").val();
		var maso = $("#maso").val();
		var id_donvi_x = $("#id_donvi_x").val();
		var timngay_batdau = $("#timngay_batdau").val();
		var timngay_ketthuc = $("#timngay_ketthuc").val();
		$("#myGrid").load("ns_thaisan/search.php", {maso_type:maso_type, maso:maso,timngay_batdau:timngay_batdau,timngay_ketthuc:timngay_ketthuc,id_donvi_x:id_donvi_x});
	});
	//Xóa
	$("#delete").click(function(){
	if (prompt("Bạn có chắc chắn xóa không?", "")=="YES"){
		$.ajax({
			url:"ns_thaisan/ns_thaisan_delete.php",
			type:"POST",
			data:$("#thaisan_xuly").serialize(),
			success:function(responseText){
				if(responseText=="1"){
					alert('Xóa thành công');
					location.href='/?id=267';
				}
				else{
					alert(responseText);
				}
			}
		});
	}
	});
});
</script>
