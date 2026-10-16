<?
    $getDay = isset($_GET['day']) ? $_GET['day'] : null;
    $getClosingTime = isset($_GET['closingTime']) ? $_GET['closingTime'] : null;
    $getHoliday = isset($_GET['holiday']) ? (int)$_GET['holiday'] : null;
    $getDisable = isset($_GET['disable']) ? (int)$_GET['disable'] : null;
?>
<style scoped>
.x{float:left;position:relative;border:none;padding:0 0 0 0;margin:0 0 0 0}
.n{width:30px;height:30px;line-height:30px;background-color:#ddd;color:#fff;font-size:20px}
.t td{height:22px}
</style>
<div class="clear" style="height: 10px;"></div>
<div class="form_text_fake"><b>Sửa Giờ Đóng Chuyền</b><span id="iconWait" style="margin-left:10px"></span></div>
<div class="clear"></div>

<div class="grid_16" style="background-color:#f4f4f4;height:145px">
	
	<div class="x" style="left:10px;top:10px;">
		<table class="t">
			<tr>
			<td>Nhập ngày tháng:</td>
			<td><input id="day" type="text" maxlength="10" style="width:100px;"></td></tr>
			
			<tr>
			<td>Nhập giờ đóng chuyền:</td>
			<td><input id="closingTime" type="text" maxlength="8" style="width:100px;"></td></tr>
			
			<tr><td></td>
			<td><input type="checkbox" id="holiday" value="1" style="margin:0 5 0 0">Holiday</td></tr>
			
			<tr><td></td>
			<td><input type="checkbox" id="disable" value="1" style="margin:0 5 0 0">Disable</td></tr>
			
			<tr><td></td><td><input type="button" id="bnUpdate" value="Cập nhật..." style="width:100px"></td></tr>
		</table>
	</div>
</div>

<div class="clear" style="height:5px;"></div>
<div class="grid_16" style="background-color:#f4f4f4;height:40px;">
	<table style="margin-left:10px;margin-top:6px">
	<tr>
		<td>Chọn ngày kiểm tra:</td><td>
		<input id="dateSearch" maxlength="10" style="width:100px"></td>
		<td><input type="button" id="bnSearch" value="Liệt kê..."></td>
		<td><input type="button" id="bnSearchMonthList" value="Liệt kê trong tháng..."></td>
		<td><select id="month"><option value="0">---</option><? for($i=1;$i<=12;$i++) echo '<option value="'.$i.'">'.$i.'</option>'; ?></select></td>
	</tr>
	</table>
</div>
<div class="clear" style="height:10px;"></div>
<div class="grid_16" id="tbView" style="overflow:auto;width:936"></div>
<script>
$(document).ready(function(){
    
    <? echo _CALENDARVI; ?>
    
    calendarVI = new dhtmlXCalendarObject(["day","dateSearch"]);
	calendarVI.loadUserLanguage("vi");
    calendarVI.setDateFormat("%d-%m-%Y");
    calendarVI.hideTime();
    
	function errorAlert(e){
		if(e.substring(0,3)=="{E}"||e.substring(0,3)=="{I}"){
			alert(e.substring(3,e.length));
			return false;
		}
		return true;
	}
	
    $("#bnUpdate").click(function(){
        $.post("mcc/@mcc.workingday.php",{
				"_frm_":0,
                "day":$("#day").val(),
                "closingTime":$("#closingTime").val(),
                "holiday":$("#holiday:checked").val(),
                "disable":$("#disable:checked").val()},function(responseText){
            errorAlert(responseText);
            location.href="http://ducthanhkl.com.vn:8081/?id=160";
        })
    })
	
	$("#bnSearch").click(function(){
        $.post("mcc/@mcc.workingday.search.php",{"_frm_":2,"dateSearch":$("#dateSearch").val()},function(responseText){
			$("#tbView").css({"height":"50px","border":"2px dashed #666","margin-bottom":"10px"});
			$("#tbView").html(responseText);
        })
    })
	
	$("#bnSearchMonthList").click(function(){
        $.post("mcc/@mcc.workingday.search.php",{"_frm_":3,"month":$("#month").val()},function(responseText){
			if(errorAlert(responseText)){
				//$("#tbView").css({"height":"30px"});
				$("#tbView").css({"height":"50px","border":"2px dashed #666","margin-bottom":"10px"});
				$("#tbView").html(responseText);
			}
        })
    })
})
</script>