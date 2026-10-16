<div class="clear" style="height:10px;"></div>
<div class="form_text"><b>Xử Lý Giờ Ra Vào</b><span id="iconWait" style="margin-left:10px"></span></div>
<div class="clear"></div>

<div class="grid_16" style="background-color:#f4f4f4;">
	<form id="mcc_analysis_form">
		<div style="float:left;height:40px;line-height:40px;background-color:#f4f4f4;padding-left:10px"><span>Chọn ngày xử lý:</span>
			
			<select id="loadDate" name="ngaythangnam"><option value="0">---</option>
			<?
				$q = 'SELECT ngaythangnam FROM mcc_analysis GROUP BY ngaythangnam ';
				$result = mysql_query($q);
				while($entry = mysql_fetch_array($result)){
					echo '<option>'.date('d-m-Y', strtotime($entry['ngaythangnam'])).'</option>';
				}
			?>
			</select>
			
			<select id="_n" name="_n"><option value="0">---</option></select>
			<input id="mcc_unmatched" type="button" value="Kiểm tra..." title="{ Dùng để kiểm tra những mã số thẻ không chính xác hoặc không hợp lệ }">
			<input id="mcc_analysis" type="button" value="Liệt kê...">
			
		</div>
	</form>
	
	<form id="mcc_save_form">
		<div style="float:right;height:40px;line-height:40px;background-color:#f4f4f4">|
			
			<input id="mcc_save" type="button" value="Lưu...">
			<input id="copyDate" name="ngaythangnam" type="hidden">
			<input id="copyN" name="_n" type="hidden">
			<input id="D_save" name="D_save" type="hidden"/>
			
		</div>
	</form>
</div>

<div class="clear"></div>
<div class="grid_16" style="margin-top:10px"><div id="analysis_grid"></div></div>
<div class="clear"></div>

<script>
$(document).ready(function(){

	var D = null;
	var grid = null;
	
	
	$("#loadDate").change(function(){
		if($(this).val()==0){
			if(grid){
				grid.destroy();
				$("#analysis_grid").css({"height":"0px","margin-bottom":"0px"});
			}
		}
	});
	
////////////////////////////////////////////////////////////////////////////////////////////

	$("#loadDate").change(function(){
		$.ajax({
			url:"mcc/mcc.n.php",
			type:"POST",
			data:$("#mcc_analysis_form").serialize(),
			success:function(responseText){
				////
				$("#_n").html(responseText);
				//alert(responseText);
				$("#copyDate").val($("#loadDate").val());
				$("#copyN").val($("#_n").val());
			}
		})
	})
	
	$("#_n").change(function(){
		$("#copyN").val($("#_n").val());
	});
	
	$("#mcc_unmatched").click(function(){
		$.ajax({
			url:"mcc/mcc.analysis.unmatched.php",
			type:"POST",
			data:$("#mcc_analysis_form").serialize(),
			success:function(responseText){
				$("#analysis_grid").html(responseText);
			}
		});
	});
	
	$("#mcc_analysis").click(function(){
		$.ajax({
			url:"mcc/mcc.analysis.php",
			type:"POST",
			data:$("#mcc_analysis_form").serialize(),
			success:function(responseText){
				
				////kiem tra chuoi~ tra ve` la` kieu JSON
				if(responseText.charAt(0)!="[" && responseText.charAt(responseText.length-1)!="]")
				{alert(responseText); return;}
				
				var columns = [
				{id:"I", name:"#", field:"I", cssClass:"mcc_grid1", width:40},
				{id:"N", name:"N", field:"N", cssClass:"mcc_grid1", width:120},
				{id:"F", name:"F", field:"F", cssClass:"mcc_grid1", width:180},
				{id:"G", name:"G", field:"G", cssClass:"mcc_grid1", width:110}
				<?
				for($n=1; $n<=6; $n++){
				echo ',{id:"H'.$n.'", name:"'.$n.'", field:"H'.$n.'", width:50, editor:TextCellEditor}';
				}
				?>
				];

				D = JSON.parse(responseText);

				var options = {
				editable: true,
				enableAddRow: false,
				enableCellNavigation: true,
				enableColumnReorder: false,
				asyncEditorLoading: false
				};

				$("#analysis_grid").css({"height":"500px","margin-bottom":"10px"});
				grid = new Slick.Grid($("#analysis_grid"), D, columns, options);
			}
		});
	});
	
	$("#mcc_save").click(function(){
		
		if(prompt("Bạn có chắc chắn lưu không?", "")=="Y"){
	
			$('#D_save').val(JSON.stringify(D));
			$.ajax({
				url:"mcc/mcc.save.php",
				type:"POST",
				data:$("#mcc_save_form").serialize(),
				success:function(responseText){
					alert('- Save');
					location.reload();
				}
			});
		} else alert('Dữ liệu chưa được lưu.');
	});
	
});
</script>