<?
require_once('../_global.inc');
require_once('../_nsdt/nsdt_php.php');

$_frm_ = $_POST['_frm_'];

$_connect = mysql_connect(_MYSQL_HOST, _MYSQL_USER, _MYSQL_PASSWORD);
mysql_select_db(_MYSQL_DATABASE, $_connect);

switch($_frm_){
	case 1:
		$unitList = isset($_POST['unitList']) ? implode(',',$_POST['unitList']) : null;
		$reDay = isset($_POST['reDay']) ? nsdt_inputDate($_POST['reDay']) : null;
		
		if($reDay){
			if(count(explode(',',$unitList))>1){
				$q = 	'SELECT * FROM mcc_working_day
						JOIN ns_donvi ON ns_donvi.id_donvi=mcc_working_day.id_donvi
						WHERE mcc_working_day.id_donvi IN('.$unitList.') AND day="'.$reDay.'"';
				$r=mysql_query($q);
				while($e=mysql_fetch_array($r,MYSQL_ASSOC)){
					$a[] = array($e['donvi'],$e['closingTime']); 
				}
				print('{"multi":2,"data":'.json_encode($a).'}');
			}else{	
				$q = 'SELECT * FROM mcc_working_day WHERE id_donvi IN("'.$unitList.'") AND day="'.$reDay.'"';
				$r=mysql_query($q);
				$e = mysql_fetch_array($r,MYSQL_ASSOC);
				print('{"multi":1,"data":["'.$e['closingTime'].'"]}');
			}
		}
	break;
	
	case 2:
		$dateSearch = isset($_POST['dateSearch']) ? nsdt_inputDate($_POST['dateSearch']) : null;
		
		if($dateSearch){
			$q =	'SELECT * FROM mcc_working_day
					JOIN ns_donvi ON ns_donvi.id_donvi=mcc_working_day.id_donvi
					WHERE day="'.$dateSearch.'" ORDER BY mcc_working_day.id_donvi';
			$r=mysql_query($q);

			echo '<style scoped>
				 .default th {background-color:#f4faff; border:solid 1px #A8D4FF;}
				 tr.odd {background-color:#EEEEEE;}
				 tr.header{height:20px}
				 .default td {border:solid 1px #CCCCCC; vertical-align:middle; text-align:center; height:20px}
				 td.unit{text-align:left; padding-left:10px}
				 </style>';
			
			echo '<table class="default">';
			echo '<tr class="header"><th style="width:100px">D</th><th style="width:80px">T</th><th style="width:130px">DV</th><th style="width:20px">H</th><th style="width:20px">D</th></tr>';
			$i=0;
			while($e=mysql_fetch_array($r,MYSQL_ASSOC)){
				$odd = (int)($i%2) ? '' : 'class="odd"'; 
				echo '<tr '.$odd.'>
						<td>'.$e['day'].'</td>
						<td>'.$e['closingTime'].'</td>
						<td class="unit">'.$e['donvi'].'</td>
						<td>'.$e['holiday'].'</td>
						<td>'.$e['disable'].'</td></tr>';
				$i++;
			}
			echo '</table>';
		}
	break;
	
	case 3:
		$month = (int)($_POST['month']) ? $_POST['month'] : null;
		if(!$month) exit('{E}-Chưa nhập tháng');
		$q = 'SELECT day,holiday,disable FROM mcc_working_day WHERE MONTH(day)='.$month.' GROUP BY day ORDER BY day';
		$r = mysql_query($q);
		
		echo '	<style scoped>
				.default, .holiday {float:left; margin-left:5px; margin-top:5px;
				background-color:#eeeeee;
				border-radius: 5px 5px 5px 5px}
				.default:hover, .holiday:hover{background-color:#ddffdd;cursor:hand}
				.holiday {background-color:#ffeeee;}
				</style>';
		echo 	'<script>
					function x(s){
						$.post("mcc/mcc.workingday.search.php",{"_frm_":2,"dateSearch":s},function(responseText){
							$("#tbView").html(responseText);
						})
					}
				</script>';
		
		while($e = mysql_fetch_array($r,MYSQL_ASSOC)){
			$class = (int)($e['holiday']) ? 'holiday' : 'default';
			echo '<div class="'.$class.'" onclick="x(this.innerHTML)">'.nsdt_dmyFormat($e['day']).'</div>';
		}
	break;
}
mysql_close($_connect);
?>