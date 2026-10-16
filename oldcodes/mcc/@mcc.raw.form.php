<style scoped>
.t td{height:22px}
label{margin-right:5px}
</style>

<div class="clear" style="height: 10px;"></div>
<div class="form_text"><b>MCC Raw</b><span id="iconWait" style="margin-left:10px"></span></div>
<div class="clear"></div>
<div class="grid_16" style="background-color:#f4f4f4;height:45px;margin-bottom:10px">
	<div style="margin-top:10px;margin-left:10px">
		<table class="t">
			<tr>
				<td><label>Date:</label></td>
				<td><input id="date" type="text" maxlength="10" style="width:100px;"></td>
				<td><input id="save" type="button" value="Save"></td>
			</tr>
		</table>
	</div>
</div>
<script>
$(document).ready(function(){
	
    <? echo _CALENDARVI; ?>

    calendarVI = new dhtmlXCalendarObject("date");
	calendarVI.loadUserLanguage("vi");
    calendarVI.setDateFormat("%d-%m-%Y");
    calendarVI.hideTime();
    
    $("#save").click(function(){
       $.post('mcc/@mcc.raw.php',{"date":$("#date").val()},function(responseText){
            //errorAlert(responseText);
			alert(responseText);
       })
    });
})
</script>