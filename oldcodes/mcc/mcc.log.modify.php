<?
	session_start();
	require_once('../_global.inc');
	require_once('../_nsdt/nsdt_php.php');
	
    $REP = array(
                    1=>'Chưa nhập ngày tháng.',
                    2=>'Không tìm thấy file dữ liệu cho ngày này.',
                    3=>'Mã số nhân viên không tìm thấy.',
                    4=>'Không tìm thấy mã số nhân viên này.',
                    5=>'Mã nhân viên không có trong danh sách',
                    6=>'Trùng mã nhân viên'
                );

    /*INITIALIZATION
    */
	$loadDate = nsdt_inputDate($_POST['loadDate']);
	$f = _MCC_LOG_FOLDER.'/'.$loadDate.'.txt';
	$ma_nhanvien = $_POST['ma_nhanvien'];
	$switch = (int)$_POST['m_switch'];
	
	$h = array();
	
	if($switch){
		if($_POST['h1']) array_push($h, $_POST['h1']);
		if($_POST['h2']) array_push($h, $_POST['h2']);
		if($_POST['h3']) array_push($h, $_POST['h3']);
		if($_POST['h4']) array_push($h, $_POST['h4']);
		if($_POST['h5']) array_push($h, $_POST['h5']);
		if($_POST['h6']) array_push($h, $_POST['h6']);
	}
    
    /*VALIDATION
    */
    if(!$loadDate){echo nsdt_JSONReport($REP,1);die;}
    if(!$ma_nhanvien){echo nsdt_JSONReport($REP,3);die;}
    
    /*RUN
    */
    	
	if(file_exists($f)){
		$data = file($f);
		$numlines = count($data);

        switch($switch){
            case 0:
            case 1:
            //sua gio
        		for($i=0; $i<$numlines; $i++){
        			if($ma_nhanvien == substr($data[$i],0,14)){
        				$D = explode(chr(32), $data[$i]);
        				
        				if(count($h)){
        					///neu co post h cap nhat file
        					$line = $ma_nhanvien.chr(32).implode(' ', $h).PHP_EOL;
        					$data[$i]=$line;
        					file_put_contents($f, $data);
        					echo 'Đã cập nhật.';
        				}else echo json_encode($D);
                        
                        $notfound = 0;
                        die;
                        
        			}else $notfound = 1;
        		}
                if($notfound) echo nsdt_JSONReport($REP,4);
            break;
            
            //them gio
            case 2:
           	    $_connect = mysql_connect(_MYSQL_HOST, $_SESSION['@username'], $_SESSION['@password']);
	            mysql_select_db(_MYSQL_DATABASE, $_connect);
                $q='select ma_nhanvien from ns_nhansu where ma_nhanvien="'.$ma_nhanvien.'"';
                mysql_query($q);
                if(mysql_affected_rows($_connect)){
                    for($i=0; $i<$numlines; $i++){
                        if($ma_nhanvien == substr($data[$i],0,14)){echo nsdt_JSONReport($REP,6); mysql_close($_connect); die;}
                    }
                    
                    $line = $ma_nhanvien.chr(32).implode(chr(32),$h).PHP_EOL;
                    $fh = fopen($f,'a');
                    fwrite($fh,$line);
                    fclose($fh);
                    echo 'Cập nhật thành công.';
                }else echo nsdt_JSONReport($REP,5);
                
            break;
        }
        
	} else echo nsdt_JSONReport($REP,2);
?>