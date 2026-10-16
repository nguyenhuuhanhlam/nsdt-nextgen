<div class="clear" style="height:10px;"></div>
<div class="form_text"><b>Đọc Dữ Liệu Máy Chấm Công</b><span id="iconWait" style="margin-left:10px"></span></div>
<div class="clear"></div>

<div class="grid_16" style="background-color:#f4f4f4;margin-bottom:10px">
	<form id="frmLoad">
		<div style="height:40px;line-height:40px;background-color:#f4f4f4">
			<div style="float:left;padding-left:10px">Chọn ngày tháng:<input id="loadDate" type="text" maxlength="10" style="width:100px;margin-left:10px">
			<input id="bnLoad" type="button" value="Đọc...">
			<input id="bnReLoad" type="button" value="Đọc lại..." title="{ Lưu ý khi sử dụng chức năng này sẽ phải nhập lại các cặp giờ }">
			</div>
		</div>
	</form>
</div>
<script>
$(document).ready(function(){
	
    <? echo _CALENDARVI; ?>
    
    calendarVI = new dhtmlXCalendarObject("loadDate");
	calendarVI.loadUserLanguage("vi");
    calendarVI.setDateFormat("%d-%m-%Y");
    calendarVI.hideTime();
    
    $("#bnLoad").click(function(){
       $.post('mcc/mcc.load.php',{"loadDate":$("#loadDate").val(),"inReLoad":0},function(responseText){
            errorAlert(responseText);
       })
    })
    
    $("#bnReLoad").click(function(){
       $.post('mcc/mcc.load.php',{"loadDate":$("#loadDate").val(),"inReLoad":1},function(responseText){
            errorAlert(responseText);
       })
    })
})
</script>