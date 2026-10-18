<style>
.x{float:left;border:none;padding:0 0 0 0;margin:0 0 0 0}
.n{width:22px;height:22px;line-height:22px;background-color:#ddd;color:#fff;font-size:20px}
.y{padding:0 0 0 0; margin: 0 0 0 0}
</style>
<div class="clear" style="height: 10px;"></div>
<div class="form_text"><b>Ngày Công Trong Tháng</b><span id="iconWait" style="margin-left:10px"></span></div>
<div class="clear"></div>

<div class="grid_16" style="background-color:#f4f4f4; height:140px">	
	<div class="x n" style="position:relative;left:30px;top:10px;"><center><b>1</b></center></div>
	<div class="x" style="position:relative; left:40px; top:10px;">
		<div class="x" style="width:70px">Từ ngày:</div>
		<div class="x" style="width:100px;"><input class="y" id="fromDate" type="text" maxlength="10" style="width:100px;"></div>
		<div style="clear:both"></div>
		<div class="x" style="width:70px; padding-top:5px">Đến ngày:</div>
		<div class="x" style="width:100px; padding-top:5px"><input class="y" id="toDate" type="text" maxlength="10" style="width:100px;"></div>
		<div style="clear:both"></div>
		<div class="x" style="position:relative; left:70px;  padding-top:5px"><input class="y" id="bnLoad" type="button" value="Đọc..." style="width:100px"></div>
	</div>
	
	<div class="x n" style="position:relative;left:70px;top:10px;"><center><b>2</b></center></div>
	<div class="x" style="position:relative; left:80px; top:10px;">
		<div class="x" style="left:0px; top:0px;">Mã nhân viên:</div>
		<div class="x" style="left:0px; top:0px; padding-left:15px"><input class="y" id="ma_nhanvien"  size="14"></div>	
		<div style="clear:both"></div>
		<div class="x" style="left:0px; top:0px;">Chọn đơn vị:</div>
		<div class="x" style="left:0px; top:0px; padding-left:20px">
			<select class="y" id="unitList" multiple="multiple" size="5"><? echo nsdt_mysql_droplist('ns_donvi','id_donvi','donvi'); ?></select>
		</div>
		<div style="clear:both"></div>
		<div class="x" style="padding-left:100px"><input id="bnShow" type="button" value="Xem bảng..."></div>
	</div>
	
	<div class="x n" style="position:relative;left:100px;top:10px;"><center><b>3</b></center></div>
	<div class="x" style="position:relative; left:110px; top:10px;">
		<input id="toPDF" type="button" value="In PDF" onclick="window.open('chamcong_chitiet_ngay/chamcong_chitiet_ngaytoPDF.php','_blank')" disabled>
		<div style="clear:both"></div>
		<input id="toExcel" type="button" value="Xuất Excel" onclick="window.open('chamcong_chitiet_ngay/chamcong_chitiet_ngaytoExcel.php','_blank')" disabled>
	</div>
	
</div>

<div class="clear"></div>
<div class="grid_16" id="tbShow" style="margin-top:10px;overflow:auto;"></div>
<div class="clear"></div>

<script>
$(document).ready(function(){
    
    <? echo _CALENDARVI; ?>
    
    calendarVI = new dhtmlXCalendarObject(["fromDate","toDate"]);
	calendarVI.loadUserLanguage("vi");
    calendarVI.setDateFormat("%d-%m-%Y");
    calendarVI.hideTime();
	
	function errorAlert(e){
		if(e.substring(0,3)=="{E}"||e.substring(0,3)=="{I}"){
			alert(e.substring(4,e.length));
			return false;
		}
		return true;
	}
	//Load File
	$("#bnLoad").click(function(){
        $("#iconWait").html("<img src=\"images/loading.gif\">");
        $.post('chamcong_chitiet_ngay/chamcong_chitiet_ngay.php',{
                "_frm_":1,
                "fromDate":$("#fromDate").val(),
                "toDate":$("#toDate").val(),
                "unitList":$("#unitList").val()},function(responseText){
            $("#iconWait").html("");
            if(errorAlert(responseText)) $("#tbShow").html(responseText);
        })
    })
	//Show Bang
	$("#bnShow").click(function(){
        $("#iconWait").html("<img src=\"images/loading.gif\">");
        $.post('chamcong_chitiet_ngay/chamcong_chitiet_ngay.php',{
                "_frm_":0,
                "fromDate":$("#fromDate").val(),
                "toDate":$("#toDate").val(),
				"ma_nhanvien":$("#ma_nhanvien").val(),
                "unitList":$("#unitList").val()},function(responseText){
            $("#iconWait").html("");
            if(errorAlert(responseText)){
				$("#tbShow").css({"margin-bottom":"10px"});
				$("#tbShow").html(responseText);
				$("#toPDF").prop("disabled",false);
				$("#toExcel").prop("disabled",false);
			}
        })
    })
	$("#ma_nhanvien").click(function(){
		$("#unitList option").prop("selected", false);
	})
});
</script>