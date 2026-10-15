<div class="clear" style="height:10px;"></div>
<div class="form_text"><b>Cập Nhật Tình Trạng Thai Sản</b><span id="iconWait" style="margin-left:10px"></span></div>
<div class="clear"></div>
<div class="grid_16" style="background-color:#F4F4F4;">

<form id="thaisan_nhap">
	<table style="margin-left:10px;margin-top:10px">
		<tr>
			<td style="width:100px">Mã nhân viên:</td>
				<td><input type="text" id="ma_nhanvien" name="ma_nhanvien" size="12" maxlength="16"></td>
			<td>Họ tên nhân viên:</td>
				<td><input type="text" id="hoten" name="hoten" size="30" maxlength="80"></td></tr>
		<tr>
		<tr>
			<td>Đơn vị:</td>
				<td><input type="hidden" id="id_donvi" name="id_donvi" size="12" maxlength="25">
				<input type="text" id="donvi" name="donvi" size="12" maxlength="25"></td>
			<td>Thai sản:</td>
				<td><select id="id_thaisan" name="id_thaisan">
				<option value="">---</option>
				<? echo nsdt_mysql_droplist('ns_thaisan', 'id_thaisan', 'thaisan'); ?></select></td>
		</tr>
		<tr>
			<td>Từ ngày:</td><td><input type="text" id="ngay_batdau" name="ngay_batdau" size="12"></td>
			<td>Đến ngày:</td><td><input type="text" id="ngay_ketthuc" name="ngay_ketthuc" size="12"></td>
			<td><input id="save" type="button" value="Lưu..."></td>
		</tr>
	</table>
</form>	
</div>
<div class="clear" style="height: 10px;"></div>
<div class="grid_16" style="background-color: #F4F4F4; border-radius: 4px 15px 15px 15px;">
	<table style="margin-left:10px">
	<tr><td><select id="maso_type" name="maso_type">
		<option value="">---</option>
		<option value="1">Mã nhân viên</option>
		<option value="2">Họ tên</option>
		</select></td>
	
		<td><input type="text" id="maso" name="maso" maxlength="16" size="10"></td>
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
<script type="text/javascript">
$(document).ready(function(){
	//Calender
	<? echo _CALENDARVI; ?>
    
    calendarVI = new dhtmlXCalendarObject(["ngay_batdau","ngay_ketthuc","timngay_batdau","timngay_ketthuc"]);
	calendarVI.loadUserLanguage("vi");
    calendarVI.setDateFormat("%d-%m-%Y");
    calendarVI.hideTime();
	//1. Save
	$("#save").click(function(){
		$.ajax({
			url:"ns_thaisan/ns_save.php",
			type:"POST",
			data:$("#thaisan_nhap").serialize(),
			success:function(responseText){
				if(responseText=="1"){
					alert('Ghi nhận thành công');
					location.reload();
				}
				else{
					alert(responseText);
				}
			}
		});
	});
	//-----------
	$("#ma_nhanvien").change(function(){
		var ma_nhanvien = $("#ma_nhanvien").val();	
		$.ajax({
			url: "ns_thaisan/ma_nhanvien.php",
			type: "POST",
			cache: false,
			data: "ma_nhanvien=" + ma_nhanvien,
			success: function(responseText){
				var getData = $.parseJSON(responseText);
				//input dữ liệu lấy về từ server vào textbox
				$('#hoten').val(getData.hoten);
				$('#ma_nhanvien').val(getData.ma_nhanvien);
				$('#id_donvi').val(getData.id_donvi);
				$('#donvi').val(getData.donvi);
			},
			error: function (){
				alert('---');
			}
		});
	});
	//tìm id nhân viên, họ tên
	$("#search").click(function(){
		var maso_type = $("#maso_type").val();
		var maso = $("#maso").val();
		var id_donvi_x = $("#id_donvi_x").val();
		var timngay_batdau = $("#timngay_batdau").val();
		var timngay_ketthuc = $("#timngay_ketthuc").val();
		$("#myGrid").load("ns_thaisan/search.php", {maso_type:maso_type, maso:maso,timngay_batdau:timngay_batdau,timngay_ketthuc:timngay_ketthuc,id_donvi_x:id_donvi_x});
	});
	//------
});		 				
</script>