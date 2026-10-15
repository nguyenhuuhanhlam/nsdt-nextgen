<div class="grid_10"><h1 class="nsdt_T" align="center">CẬP NHẬT DANH MỤC THAI SẢN </h1></div>
<div class="clear"></div>
<!---------->
<form id="dm_thaisan_nhap">
<div class="grid_2">Tên thai sản:</div>
<div class="grid_2"><input type="text" id="thaisan" name="thaisan"></div>
<div class="clear"></div>
<!---------->
<div class="grid_5" align="right"><input type="button" id="save" value="Lưu..."></div>
<div class="clear"></div>
<!---------->
</form>
<div class="grid_16">
	<div id="myGrid" style="width:280px; height:270px; overflow:auto;"><? include('ns_thaisan/dm_show.php'); ?></div>
</div>
<div class="clear"></div>

<script type="text/javascript">
$(document).ready(function(){
	// Save
	$("#save").click(function(){
		$.ajax({
			url:"ns_thaisan/dm_save.php",
			type:"POST",
			data:$("#dm_thaisan_nhap").serialize(),
			success:function(responseText){
				if(responseText=="1"){
					alert('Ghi nhận thành công');
					location.reload();
				}
				else{
					alert(responseText);
				}
			}
		});
	});
	//----
});
</script>