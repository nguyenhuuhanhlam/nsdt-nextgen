<?
require_once('../_global.inc');
require_once('../_nsdt/nsdt_php.php');

switch($_POST['_frm_']){
	case 0:
		$day = nsdt_inputDate($_POST['day']);
		$closingTime = $_POST['closingTime'];
		$holiday = isset($_POST['holiday']) ? (int)$_POST['holiday'] : 0;
		$disable = isset($_POST['disable']) ? (int)$_POST['disable'] : 0;
		
		if($day){
			$_connect = mysql_connect(_MYSQL_HOST, _MYSQL_USER, _MYSQL_PASSWORD);
			mysql_select_db(_MYSQL_DATABASE, $_connect);
			
			$q = 'SELECT day FROM mcc_working_day WHERE day="'.$day.'" LIMIT 1';
			mysql_query($q);
			
			if(mysql_affected_rows($_connect)){
				
				$qc = $closingTime ? 'closingTime="'.$closingTime.'",' : '';
				
				$q =	'UPDATE mcc_working_day
						SET '.$qc.' holiday='.$holiday.',disable='.$disable.'
						WHERE day="'.$day.'"';
				mysql_query($q);
				
			}else{
			
				if(!$closingTime) {exit('{E} Chưa nhập giờ đóng chuyền.');}
				
				$q = 'SELECT * FROM ns_donvi';
				$r = mysql_query($q);
				while($e=mysql_fetch_array($r,MYSQL_ASSOC)){
					$q = 	'INSERT INTO mcc_working_day(day,closingTime,holiday,disable,id_donvi)
							VALUES("'.$day.'","'.$closingTime.'",'.$holiday.','.$disable.','.$e['id_donvi'].')';
					mysql_query($q);	
				}
				
			}
			
			mysql_close($_connect);
			
			exit('{I} OK');
			
		} else {exit('{E} Chưa nhập ngày tháng');}
		
	break;
	
	case 1:
		$reDay = nsdt_inputDate($_POST['reDay']);
		$reClosingTime = $_POST['reClosingTime'];
		$unitList = $_POST['unitList']!='null' ? $_POST['unitList'] : null;
		
		if(!$reDay){echo '{E}-Chưa nhập ngày tháng';die;}
		if(!$reClosingTime){echo '{E}-Chưa nhập giờ đóng chuyền';die;}
		
		$_connect = mysql_connect(_MYSQL_HOST, _MYSQL_USER, _MYSQL_PASSWORD);
		mysql_select_db(_MYSQL_DATABASE, $_connect);
		$q = 'SELECT day FROM mcc_working_day WHERE day="'.$reDay.'" LIMIT 1';
		mysql_query($q);
		if(mysql_affected_rows($_connect)){
			foreach($unitList as $u){
				$q = 'UPDATE mcc_working_day SET closingTime="'.$reClosingTime.'" WHERE day="'.$reDay.'" AND id_donvi='.$u;
				mysql_query($q);
			}
		}else {
			echo '{E}-Không có ngày này';
		}
		mysql_close($_connect); 
	break;
}
?>