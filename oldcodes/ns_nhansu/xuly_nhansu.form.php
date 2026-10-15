<?
$ma_nhanvien = isset($_GET['ma_nhanvien'])?  $_GET['ma_nhanvien'] : null;
if ($ma_nhanvien){
	$q = 'SELECT *, B.tinhthanh AS N, ns_tinhthanh.tinhthanh AS M FROM ns_nhansu 
	INNER JOIN ns_donvi 			ON ns_donvi.id_donvi		= ns_nhansu.id_donvi
	INNER JOIN ns_chucvu 			ON ns_chucvu.id_chucvu		= ns_nhansu.id_chucvu
	INNER JOIN ns_congviec 			ON ns_congviec.id_congviec	= ns_nhansu.id_congviec
	INNER JOIN ns_honnhan 			ON ns_honnhan.id_honnhan	= ns_nhansu.id_honnhan
	INNER JOIN ns_dantoc 			ON ns_dantoc.id_dantoc		= ns_nhansu.id_dantoc
	INNER JOIN ns_hocvan 			ON ns_hocvan.id_hocvan		= ns_nhansu.id_hocvan
	INNER JOIN ns_quoctich 			ON ns_quoctich.id_quoctich	= ns_nhansu.id_quoctich
	INNER JOIN ns_tinhthanh 		ON ns_tinhthanh.id_tinhthanh= ns_nhansu.noisinh
	INNER JOIN ns_tinhthanh AS B 	ON B.id_tinhthanh			= ns_nhansu.noicapcmnd
	WHERE ns_nhansu.ma_nhanvien="'.trim($ma_nhanvien).'"';
		$result = mysql_query($q);
		while ($entry=mysql_fetch_array($result,MYSQL_ASSOC)){	
			$ma_nhanvien = $entry['ma_nhanvien'];
			$ma_the = $entry['ma_the'];
			$ho = $entry['ho'];
			$ten = $entry['ten'];
			$socmnd = $entry['socmnd'];
			$ngaysinh = $entry['ngaysinh'];		
			$gioitinh = $entry['gioitinh'];
			$id_quoctich = $entry['id_quoctich'];
			$quoctich = $entry['quoctich'];
			$id_dantoc = $entry['id_dantoc'];
			$dantoc = $entry['dantoc'];
			$ngaycapcmnd = $entry['ngaycapcmnd'];
			$noicapcmnd = $entry['N'];
			$noisinh = $entry['M'];
			$id_hocvan = $entry['id_hocvan'];
			$hocvan = $entry['hocvan'];
			$id_honnhan = $entry['id_honnhan'];
			$honnhan = $entry['honnhan'];
			$nguyenquan = $entry['nguyenquan'];
			$diachithuongtru = $entry['diachithuongtru'];
			$diachicungu = $entry['diachicungu'];
			$id_donvi = $entry['id_donvi'];
			$donvi = $entry['donvi'];
			$id_chucvu = $entry['id_chucvu'];
			$chucvu = $entry['chucvu'];
			$id_congviec = $entry['id_congviec'];
			$congviec = $entry['congviec'];
			$ngayvaolam = $entry['ngayvaolam'];
			$ngayhetthuviec = $entry['ngayhetthuviec'];
			$hesoluong = $entry['hesoluong'];
			$btkt = $entry['btkt'];
			}
}
?>
<div class="clear" style="height:10px;"></div>
<div class="form_text"><b>Thay Đổi Thông Tin Nhân Viên</b><span id="iconWait" style="margin-left:10px"></span></div>
<div class="clear"></div>
<div class="grid_16" style="background-color:#F4F4F4;">
<form id="nhansu_xuly">
	<table style="margin-left:10px;margin-top:10px">
		<tr>
			<td>Mã nhân viên:</td>
			<td><input type="text" id="ma_nhanvien" name="ma_nhanvien" size="18" maxlength="14" value="<? echo $ma_nhanvien;?>" readonly></td>
		</tr>
		<tr>
			<td style="color:red;">Mã số thẻ:</td>
			<td>
				<input type="hidden" id="ma_the" name="ma_the" value="<? echo $ma_the; ?>">
				<input type="text" id="ma_the_sua" name="ma_the_sua" value="<? echo $ma_the; ?>" size="18" maxlength="16" readonly>
			</td>
		</tr>
		<tr>
			<td style="color:red;">Họ +</td>
			<td colspan="7"><input type="text" id="ho" name="ho" size="35" maxlength="80" value="<? echo $ho; ?>"></td>
		</tr>
		<tr>
			<td style="color:red;">Tên nhân viên:</td>
			<td><input type="text" id="ten" name="ten" size="18" maxlength="12" value="<? echo $ten; ?>"></td>
		</tr>
		<tr>
			<td style="color:red;">Ngày sinh:</td>
			<td><input id="ngaysinh" name="ngaysinh" size="18" value="<? echo nsdt_dmyFormat($ngaysinh);?>"></td>
			<td style="text-align:right; color:red;">Nơi sinh:</td>
			<td>
				<select id="noisinh" name="noisinh">
					<option value="">---</option>
					<?
							$q = 'SELECT * FROM ns_tinhthanh';
							$result=mysql_query($q);
							while ($entry = mysql_fetch_array($result,MYSQL_ASSOC)){
							if($entry['tinhthanh']==$noisinh) $selected = 'selected'; else $selected = null;
									echo '<option '.$selected.' value="'.$entry['id_tinhthanh'].'">'.$entry['tinhthanh'].'</option>';
							}
						?>
				</select>
			</td>
		</tr>
		<tr>
			<td style="color:red;">Giới tính:</td>
			<td><input id="gioitinh" name="gioitinh" size="18" value="<? echo $gioitinh;?>"></td>
			<td style="color:red; text-align:right;">Dân tộc:</td>
			<td>
				<select id="id_dantoc" name="id_dantoc">
					<option value="">---</option>
					<?
						$q = 'SELECT * FROM ns_dantoc';
						$result = mysql_query($q);
						while ($entry = mysql_fetch_array($result,MYSQL_ASSOC)){
								if($entry['id_dantoc']==$id_dantoc) $selected = 'selected'; else $selected = null;
								echo '<option '.$selected.' value="'.$entry['id_dantoc'].'">'.$entry['dantoc'].'</option>';
						}		
					?>
				</select>
			</td>
			</tr>
			<tr>
			<td style="color:red;">Quốc tịch:</td>
			<td>
				<select id="id_quoctich" name="id_quoctich">
					<option value="">---</option>
					<?
						$q = 'SELECT * FROM ns_quoctich';
						$result = mysql_query($q);
						while ($entry = mysql_fetch_array($result,MYSQL_ASSOC)){
							if($entry['id_quoctich']==$id_quoctich) $selected = 'selected'; else $selected = null;
							echo '<option '.$selected.' value="'.$entry['id_quoctich'].'">'.$entry['quoctich'].'</option>';
						}
					?>
				</select>
			</td>
			<td style="text-align:right; color:red;">Nguyên quán:</td>
			<td colspan="7"><input type="text" id="nguyenquan" name="nguyenquan" size="80" maxlength="255" value="<? echo $nguyenquan; ?>"></td>
		</tr>
		<tr>
			<td style="color:red;">Số CMND:</td>
			<td><input type="text" id="socmnd" name="socmnd" size="18" maxlength="12" value="<? echo $socmnd;?>"></td>
			<td style="text-align:right; color:red;">Địa chỉ thường trú:</td>
			<td colspan="7"><input type="text" id="diachithuongtru" name="diachithuongtru" size="80" maxlength="255" value="<? echo $diachithuongtru; ?>"></td>
		</tr>
		<tr>
			<td style="color:red;">Ngày cấp CMND:</td>
			<td><input id="ngaycapcmnd" name="ngaycapcmnd" size="18" value="<? echo nsdt_dmyFormat($ngaycapcmnd); ?>"></td>
			<td style="text-align:right; color:red;">Địa chỉ cư ngụ:</td>
			<td colspan="7"><input type="text" id="diachicungu" name="diachicungu" size="80" maxlength="255" value="<? echo $diachicungu; ?>"></td>
		</tr>
		<tr>
			<td style="color:red;">Nơi cấp CMND:</td>
			<td>
				<select id="noicapcmnd" name="noicapcmnd">
					<option value="">---</option>
					<?
						$q='SELECT * FROM ns_tinhthanh';
						$result=mysql_query($q);
						while ($entry = mysql_fetch_array($result,MYSQL_ASSOC)){
						if($entry['tinhthanh']==$noicapcmnd) $selected = 'selected'; else $selected = null;
								echo '<option '.$selected.' value="'.$entry['id_tinhthanh'].'">'.$entry['tinhthanh'].'</option>';
						}
					?>
				</select>
			</td>
			<td style="text-align:right; color:red;">Hôn nhân:</td>
			<td>
				<select id="id_honnhan" name="id_honnhan">
					<option value="">---</option>
					<? 
						$q='SELECT * FROM ns_honnhan';
						$result=mysql_query($q);
						while ($entry=mysql_fetch_array($result,MYSQL_ASSOC)){
							if($entry['id_honnhan']==$id_honnhan) $selected = 'selected'; else $selected = null;
								echo '<option '.$selected.' value="'.$entry['id_honnhan'].'">'.$entry['honnhan'].'</option>';
						}		
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td style="color:red;">Học vấn:</td>
			<td>
				<select id="id_hocvan" name="id_hocvan">
					<option value="">---</option>
					<?
						$q='SELECT * FROM ns_hocvan';
						$result=mysql_query($q);
						while ($entry=mysql_fetch_array($result,MYSQL_ASSOC)){
							if($entry['id_hocvan']==$id_hocvan) $selected = 'selected'; else $selected = null;
								echo '<option '.$selected.' value="'.$entry['id_hocvan'].'">'.$entry['hocvan'].'</option>';
						}	
					?>
				</select>
			</td>
			<td style="text-align:right;">Đơn vị:</td>
			<td>
				<select id="id_donvi" name="id_donvi" disabled>
					<option value="">---</option>
					<?
						$q = 'SELECT * FROM ns_donvi';
						$result = mysql_query($q);
						while($entry = mysql_fetch_array($result,MYSQL_ASSOC)){
							if($entry['id_donvi']==$id_donvi) $selected = 'selected'; else $selected = null;
								echo '<option '.$selected.' value="'.$entry['id_donvi'].'">'.$entry['donvi'].'</option>';
						}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td>Ngày vào làm:</td>
			<td><input id="ngayvaolam" name="ngayvaolam" size="18" value="<? echo nsdt_dmyFormat($ngayvaolam); ?>"></td>
			<td style="text-align:right;">Chức vụ:</td>
			<td>
				<select id="id_chucvu" name="id_chucvu" disabled>
					<option value="">---</option>
					<?
						$q = 'SELECT * FROM ns_chucvu';
						$result = mysql_query($q);
						while($entry = mysql_fetch_array($result,MYSQL_ASSOC)){
							if($entry['id_chucvu']==$id_chucvu) $selected = 'selected'; else $selected = null;
								echo '<option '.$selected.' value="'.$entry['id_chucvu'].'">'.$entry['chucvu'].'</option>';
						}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td style="color:red;">Ngày KTTV:</td>
			<td><input id="ngayhetthuviec" name="ngayhetthuviec" size="18" value="<? echo nsdt_dmyFormat($ngayhetthuviec); ?>"></td>
			<td style="text-align:right; color:red;">Công việc:</td>
			<td>
				<select id="id_congviec" name="id_congviec">
					<option value="">---</option>
					<?
						$q = 'SELECT * FROM ns_congviec';
						$result = mysql_query($q);
						while($entry = mysql_fetch_array($result,MYSQL_ASSOC)){
							if($entry['id_congviec']==$id_congviec) $selected = 'selected'; else $selected = null;
								echo '<option '.$selected.' value="'.$entry['id_congviec'].'">'.$entry['congviec'].'</option>';
						}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td>Hệ số lương:</td>
			<td><input id="hesoluong" name="hesoluong" size="10" value="<? echo $hesoluong;?>" readonly></td>
			<td style="text-align:right;">BT/KT:</td>
			<td><input id="btkt" name="btkt" size="3" value="<? echo $btkt;?>"></td>
		</tr>
		<tr>
			<td></td><td></td><td></td><td></td>
			<td style="text-align:left;"><input id="modify" type="button" value="Lưu..."></td>
		</tr>
	</table>
</form>
</div>

<script type="text/javascript">
$(document).ready(function(){
	//Calender
	<? echo _CALENDARVI; ?>
    
    calendarVI = new dhtmlXCalendarObject(["ngaysinh","ngaycapcmnd","ngayvaolam","ngayhetthuviec"]);
	calendarVI.loadUserLanguage("vi");
    calendarVI.setDateFormat("%d-%m-%Y");
    calendarVI.hideTime();
	//modify
	$("#modify").click(function(){
		$.ajax({
			url:"ns_nhansu/modify.php",
			type:"POST",
			data:$("#nhansu_xuly").serialize(),
			success:function(responseText){
			if(responseText=="1"){
				alert('Ghi nhận thành công');
				location.href='/?id=201';
				}
				else{
					alert(responseText);
				}
			}
		});
	});
});
</script>