<?
$id_thaisan =isset($_GET['id_thaisan'])? $_GET['id_thaisan'] : null;
if ($id_thaisan){
	$q = 'SELECT * FROM ns_thaisan WHERE id_thaisan="'.$id_thaisan.'"';
		$result = mysql_query($q);
		$entry = mysql_fetch_array($result);
		$id_thaisan = $entry['id_thaisan'];
		$thaisan = $entry['thaisan'];
}
?>
<div class="grid_10"><h1 class="nsdt_T" align="center">CẬP NHẬT DANH MỤC THAI SẢN </h1></div>
<div class="clear"></div>
<!---------->
<form id="dm_thaisan_xuly">
<div class="grid_2">Tên thai sản:</div>
<div class="grid_2">
	<input type="text" id="id_thaisan" name="id_thaisan" value="<? echo $id_thaisan;?>" hidden>
	<input type="text" id="thaisan" name="thaisan" value="<? echo $thaisan;?>">
</div>
<div class="clear"></div>
<!---------->
<div class="grid_1">
	<input type="button"  onClick="location.href='?id=265'" value="Nhập mới..."></div>
<div class="grid_4" align="right">
	<input type="button" id="modify" value="Lưu..."></div>
<div class="clear"></div>
<!---------->
</form>
<div class="grid_16">
	<div id="myGrid" style="width:280px; height:270px; overflow:auto;"><? include('ns_thaisan/dm_show.php'); ?></div>
</div>
<div class="clear"></div>

<script type="text/javascript">
$(document).ready(function(){
	//-----
	$("#modify").click(function(){
		$.ajax({
			url:"ns_thaisan/dm_modify.php",
			type:"POST",
			data:$("#dm_thaisan_xuly").serialize(),
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
	//-----
});
</script>
