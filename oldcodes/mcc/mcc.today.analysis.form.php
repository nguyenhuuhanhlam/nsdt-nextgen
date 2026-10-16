<?
	$today = date('Ymd');
	$f = _MCC_DATA_FOLDER.'/'.$today.'.txt';

	if(file_exists($f)){
		
		mysql_query('DELETE FROM mcc');
		mysql_query('DELETE FROM mcc_today_analysis');
		
		$q = 	'LOAD DATA INFILE "'.$f.'" INTO TABLE mcc 
			FIELDS TERMINATED BY "\t" LINES TERMINATED BY "\r\n"
			(@C1, @ngaythangnam, gio, @ma_the, @C5, @C6)
			SET mcc.ngaythangnam = STR_TO_DATE(@ngaythangnam, "%d-%m-%Y"),
			mcc.gio = LEFT(gio, 5),
			mcc.ma_the = TRIM(LEADING "0" FROM TRIM(LEADING "\'" FROM @ma_the))';
		mysql_query($q);
		
		$q = 	'INSERT INTO mcc_today_analysis(ma_the, ngaythangnam, gio_trongngay)
			SELECT mcc.ma_the, mcc.ngaythangnam, GROUP_CONCAT(DISTINCT gio ORDER BY gio ASC SEPARATOR " ") AS gio_trongngay
			FROM mcc GROUP BY mcc.ma_the';
		mysql_query($q);

	} else { echo '- File dữ liệu không tìm thấy.'; die; }
	
	$q = 'SELECT COUNT(*) FROM mcc_today_analysis';
	$result = mysql_query($q);
	$card_total = mysql_fetch_row($result);
	
	$q = 'SELECT COUNT(*) FROM ns_nhansu WHERE ma_the IS NOT NULL';
	$result = mysql_query($q);
	$emp_total = mysql_fetch_row($result);
	
	$q = 'SELECT ns_nhansu.ma_nhanvien, ns_nhansu.ho, ns_nhansu.ten, ns_nhansu.id_donvi, ns_donvi.donvi, LEFT(gio_trongngay, 8) AS H1 FROM mcc_today_analysis
		JOIN ns_nhansu ON mcc_today_analysis.ma_the=ns_nhansu.ma_the
		INNER JOIN ns_donvi ON ns_nhansu.id_donvi=ns_donvi.id_donvi
		WHERE LEFT(gio_trongngay, 8)>"07:05:00" ORDER BY ns_nhansu.id_donvi';
	$result = mysql_query($q);
	
	$D = array();
	$i=0;
	while($entry = mysql_fetch_array($result)){
		$i++;
		$a = array(	'I'=>$i,
					'N'=>$entry['ma_nhanvien'],
					'F'=>$entry['ho'].' '.$entry['ten'],
					'G'=>$entry['donvi'],
					'H'=>$entry['H1']);
		array_push($D, $a);
	}
?>

<div class="grid_16"><h1 class="nsdt_T">[ BÁO CÁO NGÀY ]</h1></div>
<div class="clear"></div>

<div class="grid_16"><table><tr><td>Tổng số nhân sự có mã thẻ:</td><td><? echo $emp_total[0]; ?></td></tr></table></div>
<div class="clear"></div>

<div class="grid_16">Tổng số nhân sự có quét thẻ tại thời điểm này là:<? echo $card_total[0]; ?></div>
<div class="clear"></div>

<div class="grid_16">Danh sách nhân viên đi trễ</div>
<div class="clear"></div>

<div class="grid_16">
	<div id="late_grid" style="height:500px"></div>
</div>
<div class="clear"></div>

<div class="grid_16">
<form id="ditre_print_form">
	<input id="ditre_print" type="button" value="Print...">
</form>
</div>
<div class="clear"></div>

<script>
		$("#ditre_print").click(function(){
		$.post('mcc/pdf.php', function(){ window.open("http://10.128.128.40:8081/_temp/late_pdf.pdf"); /**/});
	});
	
	var grid;
	var columns = [
	{id:"I", name:"#", field:"I", width:40},
	{id:"N", name:"N", field:"N", width:110},
	{id:"F", name:"F", field:"F", width:180},
	{id:"G", name:"G", field:"G", width:110},
	{id:"H", name:"H", field:"H", width:60}
	];
	
	var options = {
	editable: false,
	enableAddRow: false,
	enableCellNavigation: true,
	enableColumnReorder: false,
	asyncEditorLoading: false
	};
	
	var D = JSON.parse(<? echo "'".json_encode($D)."'"; ?>);
	grid = new Slick.Grid($("#late_grid"), D, columns, options);
	
</script>