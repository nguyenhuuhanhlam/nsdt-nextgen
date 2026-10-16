<?
    $getDay = isset($_GET['day']) ? $_GET['day'] : null;
    $getClosingTime = isset($_GET['closingTime']) ? $_GET['closingTime'] : null;
    $getHoliday = isset($_GET['holiday']) ? (int)$_GET['holiday'] : null;
    $getDisable = isset($_GET['disable']) ? (int)$_GET['disable'] : null;
?>
<style scoped>
.x{float:left;position:relative;border:none;padding:0 0 0 0;margin:0 0 0 0}
.n{width:22px;height:22px;line-height:22px;background-color:#ddd;color:#fff;font-size:20px}
.t td{height:22px}
</style>
<div class="clear" style="height: 10px;"></div>
<div class="form_text"><b>Sửa Giờ Đóng Chuyền</b><span id="iconWait" style="margin-left:10px"></span></div>
<div class="clear"></div>

<div class="grid_16" style="background-color:#f4f4f4;height:145px">
	
	<div class="x n" style="left:30px;top:10px;"><center><b>1</b></center></div>
	<div class="x" style="left:40px;top:10px;">
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
	
	<div class="x n" style="left:70px;top:10px;"><center><b>*</b></center></div>
	<div class="x" style="left:80px;top:10px;">
		<table class="t">
			<tr>
			<td>Nhập ngày tháng:</td>
			<td><input id="reDay" type="text" maxlength="10" style="width:100px"></td></tr>
			
			<tr>
			<td>Nhập giờ đóng chuyền:</td>
			<td><input id="reClosingTime" type="text" maxlength="8" style="width:100px"></td></tr>
		</table>	
	</div>
	
	<div class="x" style="left:80px;top:10px;">
		<div><select class="y" id="unitList" multiple="multiple" size="6" style="width:150px;margin:0 0 0 0"><? echo nsdt_mysql_droplist('ns_donvi','id_donvi','donvi'); ?></select></div>
		<div><input type="button" id="bnReUpdate" value="Cập nhật..." style="width:150px"></div>
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
    
    calendarVI = new dhtmlXCalendarObject(["day","reDay","dateSearch"]);
	calendarVI.loadUserLanguage("vi");
    calendarVI.setDateFormat("%d-%m-%Y");
    calendarVI.hideTime();

    $("#bnUpdate").click(function(){
        $.post("mcc/mcc.workingday.php",{
				"_frm_":0,
                "day":$("#day").val(),
                "closingTime":$("#closingTime").val(),
                "holiday":$("#holiday:checked").val(),
                "disable":$("#disable:checked").val()},function(responseText){
            alert(responseText);
            location.href="http://ducthanhkl.com.vn:8081/?id=160";
        })
    })
	
	$("#bnReUpdate").click(function(){
        $.post("mcc/mcc.workingday.php",{
				"_frm_":1,
                "reDay":$("#reDay").val(),
                "reClosingTime":$("#reClosingTime").val(),
				"unitList":$("#unitList").val()
				},function(responseText){
            alert(responseText);
            //location.href="http://ducthanhti.com.vn:8081/?id=160";
        })
    })
	
	$("#unitList").change(function(){
		$.post("mcc/mcc.workingday.search.php",{"_frm_":1,"reDay":$("#reDay").val(),"unitList":$("#unitList").val()},function(responseText){
			
			var jdata = $.parseJSON(responseText);
			if(jdata['multi']==1){
				$("#reClosingTime").val(jdata['data']);
				$("#tbView").html("");
			}else{
				var s='<style scoped>\
				.default th {background-color:#f4faff; border:solid 1px #A8D4FF;}\
				 tr.odd {background-color:#EEEEEE;}\
				 tr.header{height:20px}\
                .default td {border:solid 1px #CCCCCC; vertical-align:middle; text-align:center;}\
                td.empFullname {padding-left:10px; text-align:left}\
                </style>';
				s+="<table class='default'><tr class='header'><th>Giờ Đóng Chuyền</th><th>Đơn vị</th></tr>";
				for(i=0;i<jdata["data"].length;i++){
					s += "<tr><td>"+jdata["data"][i][1]+"</td><td>"+jdata["data"][i][0]+"</td></tr>";
				}
				s += "</table>";
				
				$("#tbView").html(s);
			}
        })
	})
	
	$("#bnSearch").click(function(){
        $.post("mcc/mcc.workingday.search.php",{"_frm_":2,"dateSearch":$("#dateSearch").val()},function(responseText){
			$("#tbView").css({"height":"400px","border":"2px dashed #666","margin-bottom":"10px"});
			$("#tbView").html(responseText);
        })
    })
	
	$("#bnSearchMonthList").click(function(){
        $.post("mcc/mcc.workingday.search.php",{"_frm_":3,"month":$("#month").val()},function(responseText){
			if(errorAlert(responseText)){
				//$("#tbView").css({"height":"30px"});
				$("#tbView").css({"height":"400px","border":"2px dashed #666","margin-bottom":"10px"});
				$("#tbView").html(responseText);
			}
        })
    })
})
</script>