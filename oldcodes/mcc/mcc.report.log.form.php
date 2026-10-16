<div class="clear" style="height: 10px;"></div>
<div class="grid_3" style="background-color:#F4F4F4;color:#cccccc;border-radius: 0 15px 0 0; height:20px; padding-left:10px; padding-top:5px"><b>Nhật Ký Chấm Công</b></div>
<div class="grid_1" id="iconWait"></div>
<div class="clear"></div>
<div class="grid_16" style="background-color: #F4F4F4; border-radius: 0 15px 15px 15px;">
<form style="margin-top:5px; margin-bottom:5px">
    <table style="margin-left: 10px">
        <tr><td style="width: 60px;">Từ ngày</td>
        <td><input id="fromDate" maxlength="10" style="width:100px"></td></tr>
        <tr><td>Đến ngày</td>
        <td><input id="toDate" maxlength="10" style="width:100px"></td></tr>
        <tr><td></td>
        <td><input id="bnConfirm" type="button" value="Xác nhận..."></td></tr>
    </table>
</form>
</div>
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
	
	$("#bnConfirm").click(function(){
		if(prompt("Bạn có chắc chắn lưu không?", "")!="Y") return;
		
        $("#iconWait").html("<img src=\"images/loading.gif\">");
        $.post('mcc/mcc.report.log.php',{
                "fromDate":$("#fromDate").val(),
                "toDate":$("#toDate").val()},function(responseText){
            $("#iconWait").html("");
			if(errorAlert(responseText)) $("#tbShow").html(responseText);
        })
    })
})
</script>