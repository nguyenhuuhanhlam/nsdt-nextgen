<style scoped>
.x{float:left;position:relative;border:none;padding:0 0 0 0;margin:0 0 0 0}
.n{width:22px;height:22px;line-height:22px;background-color:#ddd;color:#fff;font-size:20px}
.t td{height:22px}
</style>

<div class="clear" style="height:10px;"></div>
<div class="form_text"><b>Báo Cáo Giờ Công Tháng</b><span id="iconWait" style="margin-left:10px"></span></div>
<div class="clear"></div>

<div class="grid_16" style="background-color:#f4f4f4;height:130px">
	<div class="x n" style="left:30px;top:10px;"><center><b>1</b></center></div>
	<div class="x" style="left:40px;top:10px;">
		<table class="t">
			<tr>
			<td style="width:70px">Từ ngày:</td>
			<td><input id="fromDate" type="text" maxlength="10" style="width:100px"></td></tr>
			
			<tr>
			<td>Đến ngày:</td>
			<td><input id="toDate" type="text" maxlength="10" style="width:100px"></td></tr>
			
			<tr>
			<td></td>
			<td><input id="bnLoad" type="button" value="Đọc..." style="width:100px"></td></tr>
		</table>
	</div>
	
	<div class="x n" style="left:60px;top:10px;"><center><b>2</b></center></div>
	<div class="x" style="left:70px;top:10px;">
		<table class="t">
			<tr>
			<td style="vertical-align:top;width:75px">Chọn đơn vị:</td>
			<td><select id="unitList" size="5" style="width:150px;margin:0 0 0 0" multiple="multiple" size="6"><? echo nsdt_mysql_droplist('ns_donvi','id_donvi','donvi'); ?></select></td></tr>
			
			<tr>
			<td></td>
			<td><input id="bnShow" type="button" value="Xem bảng..." style="width:150px"></td></tr>
		</table>
	</div>
	
	<div class="x n" style="left:100px;top:10px;"><center><b>3</b></center></div>
	<div class="x" style="left:110px;top:10px;">
		<input type="button" value="In ra PDF" style="margin:0 0 0 0;" onclick="window.open('mcc/toPDF.php','_blank')">
		<input type="button" value="Lưu ra EXCEL" style="margin:0 0 0 0;" onclick="window.open('mcc/toEXCEL.month.php','_blank')">
	</div>
</div>

<div class="clear"></div>
<div class="grid_16" style="height:10px"></div>
<div class="clear"></div>

<div class="grid_16" id="tbShow"></div>
<div class="clear"></div>

<script>
$(document).ready(function(){
    
    <? echo _CALENDARVI; ?>
    
    calendarVI = new dhtmlXCalendarObject(["fromDate","toDate"]);
	calendarVI.loadUserLanguage("vi");
    calendarVI.setDateFormat("%d-%m-%Y");
    calendarVI.hideTime();
	
    $("#bnLoad").click(function(){
        $("#iconWait").html("<img src=\"images/loading.gif\">");
        $.post('mcc/mcc.time.month.php',{
                "_frm_":1,
                "fromDate":$("#fromDate").val(),
                "toDate":$("#toDate").val(),
                "unitList":$("#unitList").val()},function(responseText){
            $("#iconWait").html("");
			if(errorAlert(responseText)) $("#tbShow").html(responseText);
        })
    })
    
    $("#bnShow").click(function(){
        $("#iconWait").html("<img src=\"images/loading.gif\">");
        $.post('mcc/mcc.time.month.php',{
                "_frm_":0,
                "fromDate":$("#fromDate").val(),
                "toDate":$("#toDate").val(),
                "unitList":$("#unitList").val()},function(responseText){
            $("#iconWait").html("");
            $("#tbShow").html(responseText);
        })
    })
})
</script>