<style>
.x{float:left;border:none;padding:0 0 0 0;margin:0 0 0 0}
.n{width:22px;height:22px;line-height:22px;background-color:#ddd;color:#fff;font-size:20px}
.y{padding:0 0 0 0; margin: 0 0 0 0}
</style>
<div class="clear" style="height: 10px;"></div>
<div class="form_text"><b>Đọc File Dữ Liệu</b><span id="iconWait" style="margin-left:10px"></span></div>
<div class="clear"></div>

<div class="grid_16" style="background-color:#f4f4f4; height:80px">	
	<div class="x n" style="position:relative;left:30px;top:10px;"><center><b>1</b></center></div>
	<div class="x" style="position:relative; left:40px; top:10px;">
		<div class="x" style="width:70px">Ngày đọc:</div>
		<div class="x" style="width:100px;"><input class="y" id="loadDate" type="text" maxlength="10" style="width:100px;"></div>
		<div style="clear:both"></div>
		<div class="x" style="position:relative; left:70px;  padding-top:5px"><input class="y" id="bnLoad" type="button" value="Đọc..." style="width:100px"></div>
	</div>
</div>

<div class="clear"></div>
<div class="grid_16" id="tbShow" style="margin-top:10px"></div>
<div class="clear"></div>

<script>
$(document).ready(function(){
    
    <? echo _CALENDARVI; ?>
    
    calendarVI = new dhtmlXCalendarObject(["loadDate"]);
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
    
    $("#bnLoad").click(function(){
        $("#iconWait").html("<img src=\"images/loading.gif\">");
        $.post('mcc/@mcc3.php',{
                "_frm_":1,
                "loadDate":$("#loadDate").val()},function(responseText){
            $("#iconWait").html("");
            if(errorAlert(responseText)) $("#tbShow").html(responseText);
        })
    })
})
</script>