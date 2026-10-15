<div class="clear" style="height:10px;"></div>
<div class="form_text"><b>Danh Sách Nhân Viên</b><span id="iconWait" style="margin-left:10px"></span></div>
<div class="clear"></div>
<div class="grid_16" style="background-color:#F4F4F4;">
<form id="danhsach_nv">
	<table style="margin-left:10px;margin-top:10px">
		<tr>
			<td>
				<select id="maso_type" name="maso_type">
					<option value="1">Mã nhân viên</option>
					<option value="2">Mã số thẻ</option>
				</select>
			</td>
			<td><input type="text" id="maso" name="maso" size="12" maxlength="16"></td>
			<td>Đơn vị:</td>
			<td>
				<select id="id_donvi" name="id_donvi">
					<option value="">---</option>
					<? echo nsdt_mysql_droplist('ns_donvi', 'id_donvi', 'donvi'); ?>
				</select>
			</td>
			<td>Nghỉ hộ sản:</td>
			<td><input type="checkbox" id="nhs" name="nhs" value="1"></td>
			<td>Đang làm việc:</td>
			<td><input type="checkbox" id="dlv" name="dlv" value="1"></td>
		</tr>
		<tr>
			<td>Tên:</td>
			<td><input type="text" id="ten" name="ten" size="12"></td>
			<td>C.vụ:</td>
			<td>
				<select name="id_chucvu" id="id_chucvu">
					<option value="">---</option>
					<? echo nsdt_mysql_droplist('ns_chucvu', 'id_chucvu', 'chucvu'); ?>
				</select>
			</td>
			<td>Nghỉ dài hạn:</td>
			<td><input type="checkbox" id="ndh" name="ndh" value="1"></td>
		</tr>
		<tr>
			<td>Ngày vào từ:</td>
			<td><input type="text" id="ngay_batdau1" name="ngay_batdau1" maxlength="10" size="12"></td>
			<td>đến:</td>
			<td><input type="text" id="ngay_ketthuc1" name="ngay_ketthuc1" maxlength="10" size="12"></td>
			<td>Xin nghỉ việc:</td>
			<td><input type="checkbox" id="xnv" name="xnv" value="1"></td>
		</tr>
		<tr>
			<td>Ngày sinh từ:</td>
			<td><input type="text" id="ngay_batdau2" name="ngay_batdau2" maxlength="10" size="12"></td>
			<td>đến:</td>
			<td><input type="text" id="ngay_ketthuc2" name="ngay_ketthuc2" maxlength="10" size="12"></td>
			<td>Tự ý nghỉ việc:</td>
			<td><input type="checkbox" id="tynv" name="tynv" value="1"></td>
		</tr>
		<tr>
			<td colspan="7" align="right"><input id="search" type="button" value="Tìm kiếm..."></td>
		</tr>
	</table>
</form>
</div>
<div class="clear" style="height: 10px;"></div>
<div class="grid_16">
<div id="show_dsnv" style="height:350px; overflow:auto;"></div>
</div>
<script>
$(document).ready(function(){
	//Calender
	<? echo _CALENDARVI; ?>
    
    calendarVI = new dhtmlXCalendarObject(["ngay_batdau1","ngay_ketthuc1","ngay_batdau2","ngay_ketthuc2"]);
	calendarVI.loadUserLanguage("vi");
    calendarVI.setDateFormat("%d-%m-%Y");
    calendarVI.hideTime();
	//search
	$("#search").click(function(){
		var maso_type = $("#maso_type").val();
		var maso = $("#maso").val();
		var id_donvi = $("#id_donvi").val();
		var ten = $("#ten").val();
		var id_chucvu = $("#id_chucvu").val();
		var ngay_batdau1 = $("#ngay_batdau1").val();
		var ngay_ketthuc1 = $("#ngay_ketthuc1").val();
		var ngay_batdau2 = $("#ngay_batdau2").val();
		var ngay_ketthuc2 = $("#ngay_ketthuc2").val();
		var nhs = $("#nhs:checked").val();
		var ndh = $("#ndh:checked").val();
		var dlv = $("#dlv:checked").val();
		var xnv = $("#xnv:checked").val();
		var tynv = $("#tynv:checked").val();
		$("#show_dsnv").load("ns_nhansu/@search.php", {maso_type:maso_type, maso:maso,id_donvi:id_donvi,ten:ten,id_chucvu:id_chucvu,ngay_batdau1:ngay_batdau1,ngay_ketthuc1:ngay_ketthuc1,ngay_batdau2:ngay_batdau2,ngay_ketthuc2:ngay_ketthuc2,nhs:nhs,ndh:ndh,xnv:xnv,tynv:tynv,dlv:dlv});
	});
	//----
})
</script>