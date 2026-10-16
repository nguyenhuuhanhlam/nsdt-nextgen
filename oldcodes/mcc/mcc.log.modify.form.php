<div class="clear" style="height:10px;"></div>
<div class="form_text"><b>Sửa Đổi Giờ Ra Vào</b><span id="iconWait" style="margin-left:10px"></span></div>
<div class="clear"></div>

<div class="grid_16" style="background-color:#f4f4f4;margin-bottom:10px">
	<form id="mcc_log_modify_form" style="margin-top:5px;margin-bottom:5px">
		<table style="margin-left: 10px;">
			<tr><td style="width: 100px;">Nhập ngày tháng:</td>
			<td><input id="loadDate" type="text" name="loadDate" maxlength="10" style="width:100px">
			<input id="view" type="button" value="Xem..." title="{ Dùng để kiểm tra lại những cặp giờ đã chỉnh sửa }" style="width:60px;"></td>
			</tr>
			
			<tr><td>Mã nhân viên:</td>
			<td><input id="ma_nhanvien" type="text" name="ma_nhanvien" maxlength="14" style="width:100px">
			<input id="search" type="button" value="Tìm..." style="width:60px"></td>
			</tr>
			
			<tr><td></td><td><input id="m_switch" name="m_switch" value="0" hidden>
			<input id="h1" type="text" name="h1" maxlength="5" style="width:45px">
			<input id="h2" type="text" name="h2" maxlength="5" style="width:45px">
			<input id="h3" type="text" name="h3" maxlength="5" style="width:45px">
			<input id="h4" type="text" name="h4" maxlength="5" style="width:45px">
			<input id="h5" type="text" name="h5" maxlength="5" style="width:45px">
			<input id="h6" type="text" name="h6" maxlength="5" style="width:45px">
			<input id="modify" type="button" value="Cập nhật...">
			<input id="add" type="button" value="Thêm..." disabled title="{ Sử dụng thêm giờ cho nhân viên quên quẹt thẻ }">
			</td></tr>
		</table>
	</form>
</div>

<div class="clear"></div>
<div class="grid_16" id="tbView" style="overflow:auto"></div>
<div class="clear"></div>
<div class="grid_6" id="tbView_2" style="overflow:auto"></div>
<div class="clear"></div>
<script>
$(document).ready(function(){
    
    <? echo _CALENDARVI; ?>
    
    calendarVI = new dhtmlXCalendarObject("loadDate");
	calendarVI.loadUserLanguage("vi");
    calendarVI.setDateFormat("%d-%m-%Y");
    calendarVI.hideTime();
    
    $("#ma_nhanvien").keyup(function(){
        if($("#ma_nhanvien").val().length==4){
            $.post("mcc/mcc.log.search.php",{"ma_nhanvien":$("#ma_nhanvien").val()},function(responseText){
                $("#ma_nhanvien").val(responseText);
            })
        }
    })
    
	$("#modify").click(function(){
		$("#m_switch").val("1");
		
		$.ajax({
			url:"mcc/mcc.log.modify.php",
			type:"POST",
			data:$("#mcc_log_modify_form").serialize(),
			success:function(responseText){
		          alert(responseText);
			}
		})
	})
	
	$("#search").click(function(){
	
		$("#m_switch").val("0");
	
		$.ajax({
			url:"mcc/mcc.log.modify.php",
			type:"POST",
			data:$("#mcc_log_modify_form").serialize(),
			success:function(responseText){
				
                var jsonData = JSON.parse(responseText);
                if(!jsonData[0]['REP']){
					$('#add').prop('disabled', true);
				
                    $("#h1").val(jsonData[1]);
    				$("#h2").val(jsonData[2]);
    				$("#h3").val(jsonData[3]);
    				$("#h4").val(jsonData[4]);
    				$("#h5").val(jsonData[5]);
    				$("#h6").val(jsonData[6]);
                } else {
					alert(jsonData[0]['REP']);
					
					//clear gio
					$("#h1").val('');
					$("#h2").val('');
					$("#h3").val('');
					$("#h4").val('');
					$("#h5").val('');
					$("#h6").val('');
					
					$('#modify').prop('disabled', true);
					$('#add').prop('disabled', false);
				}
			}
		})
	})
 
    $("#add").click(function(){
        $("#m_switch").val("2");
        
    	$.ajax({
    		url:"mcc/mcc.log.modify.php",
    		type:"POST",
    		data:$("#mcc_log_modify_form").serialize(),
    		success:function(responseText){
    		      alert(responseText);
            }
	   })
    })
	
	$("#view").click(function(){
		$("#iconWait").html("<img src=\"images/loading.gif\">");
		$.post("mcc/mcc.log.view.php",{"loadDate":$("#loadDate").val()},function(responseText){
			$("#iconWait").html("");
			
			$("#tbView").css({"height":"300px","border":"2px dashed #666"});
			$("#tbView").html(responseText);
			$("#tbView_2").html("<img src=\"images/loading.gif\">");
			$.post("mcc/mcc.log.view.2.php",{"loadDate":$("#loadDate").val()},function(responseText){
				$("#tbView_2").css({"height":"200px","width":"400px"});
				$("#tbView_2").html(responseText);
			})
		})
	})
})
</script>
