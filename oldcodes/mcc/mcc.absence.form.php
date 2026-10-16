<div class="grid_16"><h1 class="nsdt_T">[ XỬ LÝ VẮNG / KHÔNG QUẸT THẺ ]</h1></div>
<div class="clear"></div>

<div class="grid_16">
<form id="mcc_absence_form">
<label>Nhập ngày tháng năm : </label>
<input id="loadDate" name="loadDate" maxlength="10" style="width:100px"><input id="b_search" type="button" value="Search...">
</form>
</div>
<div class="clear"></div>

<div class="grid_16">
	<div id="absence_grid" style="height:400px"></div>
</div>
<div class="clear"></div>

<script>
$(document).ready(function(){

	$("#loadDate").datepicker({dateFormat: 'dd-mm-yy'});

	$("#b_search").click(function(){
		$.ajax({
			url:"mcc/mcc.absence.php",
			type:"POST",
			data:$("#mcc_absence_form").serialize(),
			success:function(responseText){
			
				if(responseText.charAt(0)!="[" && responseText.charAt(responseText.length-1)!="]") alert(responseText);
				
				var columns = [
				{id:"I", name:"#", field:"I", width:40},
				{id:"N", name:"N", field:"N", width:110},
				{id:"F", name:"F", field:"F", width:180},
				{id:"D", name:"D", field:"D", width:180}
				];

				D = JSON.parse(responseText);

				var options = {
				editable: false,
				enableAddRow: false,
				enableCellNavigation: true,
				enableColumnReorder: false,
				asyncEditorLoading: false
				};

				grid = new Slick.Grid($("#absence_grid"), D, columns, options);
			}
		});
	});
});
</script>