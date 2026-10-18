<?
	function ngaysinh($d){
		$d = explode('-',$d);
		return $d[2].'-'.$d[1].'-'.$d[0];
	}
	
	function timedot($t){
		$t = explode(':', $t);
		$m = (int)($t[1])/60;
		if(!$m) return $t[0].'.'.'00'; 
		
		$m = explode('.',$m);
		return $t[0] . '.' . substr($m[1],0,2);
	}
	
	function kieuphep($k){
		switch($k){
			case 1:
				return 'KP';
			break;
			case 2:
				return 'CP';
			break;
			case 3:
				return 'CD';
			break;
			case 4:
				return 'PN';
			break;
			case 5:
				return 'NK';
			break;
			default:
				return 0;
			break;
		}
	}
	
	function dateInZero($d){
		$sd = explode('-',$d);
		if($sd[1]=='00') return $sd[2];
		else return $d;
	}
	
	function dateRange( $first, $last, $step = '+1 day', $format = 'Y-m-d' ) {
		$dates = array();
		$current = strtotime( $first );
		$last = strtotime( $last );

		while( $current <= $last ) {

			$dates[] = date( $format, $current );
			$current = strtotime( $step, $current );
		}

		return $dates;
	}
	
	function nsdt_dmFormat($d){
		$d = explode('-',$d);
		return $d[2].'/'.$d[1];
	}
    
    function nsdt_dmyFormat($d){
		if(!$d) return false;
        $d = explode('-',$d);
		return $d[2].'-'.$d[1].'-'.$d[0];
	}
	
    function nsdt_JSONReport($errList,$errInd){
        return '[{"REP":"'.$errList[$errInd].'"}]';
    }
    
	function nsdt_dateGt($d1,$d2){
		$d1 = strtotime($d1);
		$d2 = strtotime($d2);
		
		return ($d1>$d2) ? true : false;
	}
	
	/*OK.in_used*/
	function nsdt_inputDate($s){
		if($s){
			$ymd = explode('-', $s);
			if(count($ymd)<3 || count($ymd)>3) return false;
			if($ymd[0]>1000){
				$y = $ymd[0];
				$d = $ymd[2];
			}else{
				$y = $ymd[2];
				$d = $ymd[0];
			}
			$m = $ymd[1];
			if(checkdate($m,$d,$y)) return $y.'-'.$m.'-'.$d;
		}
		return false;
	}
	
	////mcc_log
	/*
		@|1|2|3|4|5
		@|2|5|
		@-> |0|2|0|0|5
	*/
	function nsdt_array_EqPos($d1,$d2){
		for($i=0;$i<count($d1);$i++){
			$d1[$i] = isset($d2[0]) ? ($d1[$i]==$d2[0] ? $d1[$i] : 0) : 0;
			if($d1[$i]) array_shift($d2);
		}
		return $d1;
	}
	/*
		@|-|2|-|-|5|
		@|3|4|
		@-> |-|3|-|-|4|
	*/
	function nsdt_array_xPos($a,$b){
		$r = array();
		foreach($a as $a){
			if($a){array_push($r, $b[0]); array_shift($b);}
			else array_push($r,0);
		}
		return $r;
	}
	//ham pro :))
	function pregnancyOfficeTime(&$t,$d,$a){
		for($i=0;($a[0]+$i)<=$d[count($d)-1]&&($a[0]+$i)<=$a[1];$i++){
            $T=new _Time();
			$k = array_search(($a[0]+$i),$d);
            if($k!==false) $t[$k]=($a[2]==1) ? $t[$k] : ($T->hmGe($t[$k],'08:00') ? '07:00' : $t[$k]);
		}
	}
	
	function pregnancyXTime(&$t,$d,$a,&$n){
		for($i=0;($a[0]+$i)<=$d[count($d)-1]&&($a[0]+$i)<=$a[1];$i++){
            $T=new _Time();
			$k = array_search(($a[0]+$i),$d);
			
            if($k!==false){
				if($a[2]==1){
					$t[$k]=$t[$k];
				}else{
					if($T->hmGe($t[$k],'08:00')) $t[$k]='07:00'; else $t[$k]=$t[$k];
					++$n;
				}
			}
		}
	}
	
	function pregnancyReCalculation($empdaylist,$holidaylist,&$timeofficelist,&$parttimelist,$pregdata,&$n){
	
		$T = new _Time();
	
		for($i=0;$i<count($empdaylist);$i++){
			if($empdaylist[$i]){
				//if($pregdata[0]<=$empdaylist[$i]&&$empdaylist[$i]<=$pregdata[1]){
					switch($pregdata[2]){
						case 1:
							$parttimelist[$i]=0;
						break;
						case 2:
						case 3:
							if(!@in_array($empdaylist[$i],$holidaylist)){
								if($T->hmGe($timeofficelist[$i],'04:00')){
									if($T->hmGe($timeofficelist[$i],'07:00')) $timeofficelist[$i] = '07:00';
									++$n;
								}
								
							}
							$parttimelist[$i]=0;
						break;
					}	
				//}
			}
		}
	}
	
	//dung lam dung
	function droplistSelected($id,$s,$v=null){
		$select = '<select id="'.$id.'" name="'.$id.'"><option>---</option>';
		
		foreach($s as $s){
			if($s==$v) $select .= '<option value="'.$s.'" selected>'.$s.'</option>';
			else $select .= '<option value="'.$s.'">'.$s.'</option>';
		}
		$select .= '</select>';
		
		return $select;
	}
	
	function right($value, $count){
		return substr($value, ($count*-1));
	}
 
	function left($string, $count){
		return substr($string, 0, $count);
	}
	
	function sum_the_time($time1, $time2) {
		//if(!$time2) $time2 = '00:00:00';
		
		$times = array($time1, $time2);
		$seconds = 0;
		foreach ($times as $time)
		{
		list($hour,$minute,$second) = explode(':', $time);
		$seconds += $hour*3600;
		$seconds += $minute*60;
		$seconds += $second;
		}
		$hours = floor($seconds/3600);
		$seconds -= $hours*3600;
		$minutes  = floor($seconds/60);
		$seconds -= $minutes*60;
		
		//return $hours.':'.$minutes.':'.$seconds;
		return 
		sprintf('%02d:%02d:%02d',
		$hours,
		$minutes,$seconds);
	}
	function sum_the_time_h_s($time1, $time2) {
		
		$times = array($time1, $time2);
		$seconds = 0;
		foreach ($times as $time)
		{
		list($hour,$minute,$second) = explode(':', $time);
		$seconds += $hour*3600;
		$seconds += $minute*60;
		$seconds += $second;
		}
		$hours = floor($seconds/3600);
		$seconds -= $hours*3600;
		$minutes  = floor($seconds/60);
		$seconds -= $minutes*60;
		
		//return $hours.':'.$minutes.':'.$seconds;
		return 
		sprintf('%02d:%02d',
		$hours,
		$minutes);
	}

	
?>