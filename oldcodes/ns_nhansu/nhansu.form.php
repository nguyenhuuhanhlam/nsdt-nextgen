<div class="clear" style="height: 10px;"></div>
<div class="form_text"><b>Nhập Nhân Sự Mới</b><span id="iconWait" style="margin-left:10px"></span></div>
<div class="clear"></div>

<div class="grid_16" style="background-color:#f4f4f4;">

<form id="nhansu_nhap">
	<table style="margin-left:10px;margin-top:10px">
		<tr>
			<td style="color:red;">Mã nhân viên:</td>
			<td><input type="text" id="ma_nhanvien" name="ma_nhanvien" size="18" maxlength="14"></td>
			<td><p id="mnvReport"></p></td>
		</tr>
		<tr>
			<td>Mã số thẻ:</td>
			<td><input type="text" id="ma_the" name="ma_the" size="18" maxlength="14"></td>
			<td><p id="infReport"></p></td>
		</tr>
		<tr>
			<td style="color:red;">Họ +</td>
			<td colspan="7"><input type="text" id="ho" name="ho" size="35" maxlength="80"></td>
		</tr>
		<tr>
			<td style="color:red;">Tên:</td>
			<td><input type="text" id="ten" name="ten" size="18" maxlength="12"></td>
		</tr>
		<tr>
			<td style="color:red;">Ngày sinh:</td>
			<td><input id="ngaysinh" name="ngaysinh" size="18" maxlength="10"></td>
		</tr>
		<tr>
			<td style="color:red;">Giới tính:</td>
			<td>
				<select name="gioitinh" id="gioitinh">
					<option value="Nữ">Nữ</option>
					<option value="Nam">Nam</option>
				</select>
			</td>
			<td style="color:red;" align="right">Nơi sinh</td>
			<td>
				<select id="noisinh" name="noisinh">
					<option value="">---</option>
					<? echo nsdt_mysql_droplist('ns_tinhthanh', 'id_tinhthanh', 'tinhthanh'); ?>
				</select>
			</td>
		</tr>
		<tr>
			<td style="color:red;">Quốc tịch:</td>
			<td>
				<select id="id_quoctich" name="id_quoctich">
					<option value="">---</option>
					<? echo nsdt_mysql_droplist('ns_quoctich', 'id_quoctich', 'quoctich'); ?>
				</select>
			</td>
			<td style="color:red;" align="right">Dân tộc:</td>
			<td>
				<select id="id_dantoc" name="id_dantoc">
					<option value="">---</option>
					<? echo nsdt_mysql_droplist('ns_dantoc', 'id_dantoc', 'dantoc'); ?>
				</select>
			</td>
		</tr>
		<tr>
			<td style="color:red;">Số CMND:</td>
			<td><input type="text" id="socmnd" name="socmnd" size="18" maxlength="12"></td>
			<td style="color:red;text-align:right;">Nguyên quán</td>
			<td colspan="5"><input type="text" id="nguyenquan" name="nguyenquan" size="82" maxlength="255"></td>
		</tr>
		<tr>
			<td style="color:red;">Ngày cấp CMND:</td>
			<td><input id="ngaycapcmnd" name="ngaycapcmnd" size="18" maxlength="10"></td>
			<td style="color:red;text-align:right;">Địa chỉ thường trú</td>
			<td colspan="1">
				<input type="text" id="diachithuongtru1" name="diachithuongtru1" size="20" maxlength="255">
				<input type="text" id="diachithuongtru2" name="diachithuongtru2" size="14" maxlength="255" placeholder="Phường">
				<input type="text" id="diachithuongtru3" name="diachithuongtru3" size="14" maxlength="255" placeholder="Quận Huyện TP">
				<input type="text" id="diachithuongtru4" name="diachithuongtru4" size="14" maxlength="255" placeholder="Tỉnh Thành">
			</td>
		</tr>
		<tr>
			<td style="color:red;">Nơi cấp CMND:</td>
			<td>
				<select id="noicapcmnd" name="noicapcmnd">
					<option value="">---</option>
					<? echo nsdt_mysql_droplist('ns_tinhthanh', 'id_tinhthanh', 'tinhthanh'); ?>
				</select>
			</td>
			<td style="color:red;text-align:right;">Địa chỉ cư ngụ:</td>
			<td colspan="1">
				<input type="text" id="diachicungu1" name="diachicungu1" size="20" maxlength="255">
				<input type="text" id="diachicungu2" name="diachicungu2" size="14" maxlength="255" placeholder="Phường">
				<input type="text" id="diachicungu3" name="diachicungu3" size="14" maxlength="255" placeholder="Quận Huyện TP">
				<input type="text" id="diachicungu4" name="diachicungu4" size="14" maxlength="255" placeholder="Tỉnh Thành">
			</td>
		</tr>
		<tr>
			<td style="color:red;">Học vấn:</td>
			<td>
				<select id="id_hocvan" name="id_hocvan">
					<option value="">---</option>
					<? echo nsdt_mysql_droplist('ns_hocvan', 'id_hocvan', 'hocvan'); ?>
				</select>
			</td>
			<td style="color:red;text-align:right;">Hôn nhân:</td>
			<td>
				<select id="id_honnhan" name="id_honnhan">
					<option value="">---</option>
					<? echo nsdt_mysql_droplist('ns_honnhan', 'id_honnhan', 'honnhan'); ?>
				</select>
			</td>
		</tr>
		<tr>
			<td style="color:red;">Ngày vào làm:</td>
			<td><input id="ngayvaolam" name="ngayvaolam" size="18" maxlength="10"></td>
			<td style="color:red;text-align:right;">Đơn vị:</td>
			<td>
				<select id="id_donvi" name="id_donvi">
					<option value="">---</option>
					<? echo nsdt_mysql_droplist('ns_donvi', 'id_donvi', 'donvi'); ?>
				</select>
			</td>
		</tr>
		<tr>
			<td style="color:red;">Ngày KTTV:</td>
			<td><input id="ngayhetthuviec" name="ngayhetthuviec" size="18" maxlength="10"></td>
			<td style="color:red;text-align:right;">Chức vụ:</td>
			<td>
				<select id="id_chucvu" name="id_chucvu">
					<option value="">---</option>
					<? echo nsdt_mysql_droplist('ns_chucvu', 'id_chucvu', 'chucvu'); ?>
				</select>
			</td>
		</tr>
		<tr>
			<td style="color:red;">Hệ số lương:</td>
			<td><input id="hesoluong" name="hesoluong" value="1.124" size="18"></td>
			<td style="color:red;text-align:right;">Công việc:</td>
			<td>
				<select id="id_congviec" name="id_congviec">
					<option value="">---</option>
					<? echo nsdt_mysql_droplist('ns_congviec', 'id_congviec', 'congviec'); ?>
				</select>
			</td>
		</tr>
		<tr>
			<td style="color:red;">Loại HĐLĐ:</td>
			<td>
				<select id="id_hdld" name="id_hdld">
					<option value="">---</option>
					<? echo nsdt_mysql_droplist('ns_hdld', 'id_hdld', 'loaihopdong'); ?>
				</select>
			</td>
			<td style="color:red;text-align:right;">BT/KT:</td>
			<td><input id="hesoluong" name="btkt" value="0" size="3"></td>
		</tr>
		<tr>
			<td></td><td></td><td></td>
			<td align="right"><input id="submit" type="button" value="Lưu..."></td>
		</tr>
	</table>
</form>	
</div>

<!--<div id="baoloi"></div>-->
<script>
$(document).ready(function(){
    
	<? echo _CALENDARVI; ?>
    
    calendarVI = new dhtmlXCalendarObject(["ngaysinh","ngaycapcmnd","ngayvaolam","ngayhetthuviec"]);
	calendarVI.loadUserLanguage("vi");
    calendarVI.setDateFormat("%d-%m-%Y");
    calendarVI.hideTime();
	// save
	$("#submit").click(function(){
		$.ajax({
			url:"ns_nhansu/save.php",
			type:"POST",
			data:$("#nhansu_nhap").serialize(),
			success:function(responseText){
				if(responseText=="1"){
					alert('Ghi nhận thành công');
					// $.ajax({
						// url:"ns_nhansu/miniPDF.php",
						// type:"POST",
						// data:$("#nhansu_nhap").serialize(),
						// success:function(){
							
							// window.open('ns_nhansu/in___.pdf','_blank');
						// }
						
					// });
					location.reload();
				}
				else{
					//$("#baoloi").html(responseText);
					alert(responseText);
				}
			}
		});
	});
	// kiem tra ma nhanvien nhap vao
    $("#ma_nhanvien").focusout(function(){
        $.ajax({
			url:"ns_nhansu/error_ma_nhanvien.php",
			type:"POST",
			data:{"ma_nhanvien":$("#ma_nhanvien").val()},
			success:function(responseText){
				$("#mnvReport").html(responseText);
			}
		})
    })
	// kiem tra ma the nhap vao
    $("#ma_the").focusout(function(){
        $.ajax({
			url:"ns_nhansu/error_ma_the.php",
			type:"POST",
			data:{"ma_the":$("#ma_the").val()},
			success:function(responseText){
				$("#infReport").html(responseText);
			}
		})
    })
	//-------
});
</script>