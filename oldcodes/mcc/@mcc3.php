<?
session_start();
require_once('../_global.inc');
require_once('../_nsdt/nsdt_php.php');

$loadDate = nsdt_inputDate($_POST['loadDate']);

if(!$loadDate){ exit('{E}Chưa nhập ngày tháng.');}


$f = _MCC_LOG_FOLDER . '/' . date('Y-m-d', strtotime($loadDate)) . '.txt';
$f3_log = _MCC3_LOG_FOLDER . '/' . date('Y-m-d', strtotime($loadDate)) . '.txt';

	$conn = mysql_connect(_MYSQL_HOST, $_SESSION['@username'], $_SESSION['@password']);
	mysql_select_db(_MYSQL_DATABASE, $conn);
	//lấy giờ đóng chuyền
		$q ='SELECT closingTime FROM ___mcc_working_day WHERE day ="'.$loadDate.'"';
			$r = mysql_query($q);
			$e = mysql_fetch_array($r,MYSQL_ASSOC);
		if (!$e['closingTime']){
			mysql_close($conn);
			exit ('{E} Chưa có giờ đóng chuyền cho ngày này');}
	//delete bảng mcc3_log để tạo mới		
		//mysql_query('DELETE FROM mcc_log_all');
		mysql_query('DELETE FROM mcc3_log');
	// load file từ thư mục mcc_log vao bang mcc3_log
	
	$q = 'LOAD DATA INFILE "'.$f.'" INTO TABLE mcc3_log
			FIELDS TERMINATED BY " " LINES TERMINATED BY "\r\n"
			(ma_nhanvien,@H1,@H2,@H3,@H4,@H5,@H6)
			SET
			mcc3_log.kieuthaisan =tim_thaisan(mcc3_log.ma_nhanvien,"'.$loadDate.'"),
			mcc3_log.h1= if (@H1<"07:15:00", TIMEDIFF("07:15:00",SEC_TO_TIME(FLOOR(RAND()*9*60))),@H1),
			mcc3_log.h2=@H2,
			mcc3_log.h3=@H3,
			mcc3_log.h4=@H4,
			mcc3_log.h5=@H5,
			mcc3_log.h6=@H6,
			mcc3_log.ngaythangnam="'.$loadDate.'"';
				mysql_query($q);
				//echo $q;die;
	//update vao bang mcc3_log cho kieuthaisan=1 /*Can thi mo lai cho nay*/
		// $q = 'UPDATE mcc3_log
				// SET h2=if(h2>"16:00:00",ADDTIME("16:00:00",SEC_TO_TIME(FLOOR(RAND()*8*60))),h2),
				// h3=if(h2="16:00:00",null,h3),
				// h4=if(h3 is null,null,if(h4>"16:00:00",ADDTIME("16:00:00",SEC_TO_TIME(FLOOR(RAND()*8*60))),h4)),
				// h5=if(h4 is null,null,if(h5>"16:00:00","16:00:00",h5)),
				// h6=if(h5 is null,null,if(h6>"16:00:00",ADDTIME("16:00:00",SEC_TO_TIME(FLOOR(RAND()*8*60))),h6))
				// WHERE kieuthaisan=1';
			// mysql_query($q);
	//update vào bang mcc3_log cho kieu thai san =1, giong nhu nguoi binh thuong
		$q = 'UPDATE mcc3_log JOIN ___mcc_working_day ON day=ngaythangnam
				set h2=if(h2>closingTime,ADDTIME(closingTime,SEC_TO_TIME(FLOOR(RAND()*9*60))),h2),
				h3=if(h2=closingTime,null,h3),
				h4=if(h3 is null,null,if(h4>closingTime,ADDTIME(closingTime,SEC_TO_TIME(FLOOR(RAND()*9*60))),h4)),
				h5=if(h4 is null,null,if(h5>closingTime,closingTime,h5)),
				h6=if(h5 is null,null,if(h6>closingTime,ADDTIME(closingTime,SEC_TO_TIME(FLOOR(RAND()*9*60))),h6))
				where kieuthaisan=1';
				mysql_query($q);
	//update vao bang mcc3_log cho kieuthaisan=2,3
		$q = 'UPDATE mcc3_log
				SET h2=if(h2>"15:00:00",ADDTIME("15:00:00",SEC_TO_TIME(FLOOR(RAND()*7*60))),h2),
				h3=if((h2="15:00:00" || h3>"15:00:00"),null,h3),
				h4=if(h3 is null,null,if(h4>"15:00:00",ADDTIME("15:00:00",SEC_TO_TIME(FLOOR(RAND()*7*60))),h4)),
				h5=if(h4 is null,null,if(h5>"15:00:00","15:00:00",h5)),
				h6=if(h5 is null,null,if(h6>"15:00:00",ADDTIME("15:00:00",SEC_TO_TIME(FLOOR(RAND()*7*60))),h6))
				WHERE kieuthaisan in (2,3)';
			mysql_query($q);
	// update nguoi binh thuong
		$q = 'UPDATE mcc3_log JOIN ___mcc_working_day ON day=ngaythangnam
				set h2=if(h2>closingTime,ADDTIME(closingTime,SEC_TO_TIME(FLOOR(RAND()*9*60))),h2),
				h3=if(h2=closingTime,null,h3),
				h4=if(h3 is null,null,if(h4>closingTime,ADDTIME(closingTime,SEC_TO_TIME(FLOOR(RAND()*9*60))),h4)),
				h5=if(h4 is null,null,if(h5>closingTime,closingTime,h5)),
				h6=if(h5 is null,null,if(h6>closingTime,ADDTIME(closingTime,SEC_TO_TIME(FLOOR(RAND()*9*60))),h6))
				where kieuthaisan is null';
		mysql_query($q);

		if (file_exists($f3_log)) @unlink ($f3_log);{
			$q ='SELECT holiday FROM ___mcc_working_day WHERE day ="'.$loadDate.'"';
			$r = mysql_query($q);
			$e = mysql_fetch_array($r,MYSQL_ASSOC);
			///tim ngay nghi
			if (!$e['holiday']){
				$q ='SELECT * INTO OUTFILE "'.$f3_log.'" FIELDS TERMINATED BY " " LINES TERMINATED BY "\r\n" FROM mcc3_log';
				// echo $q;
				// die;
				if (mysql_query($q))
				mysql_query('DELETE FROM mcc3_log');
				exit ('{E} Load File thành công');
			}
			else{
				$q ='SELECT * INTO OUTFILE "'.$f3_log.'" FIELDS TERMINATED BY " " LINES TERMINATED BY "\r\n" FROM mcc3_log
				WHERE kieuthaisan is null';
				if (mysql_query($q))
				mysql_query('DELETE FROM mcc3_log');
				exit ('{E} Load File thành công');
				mysql_close($conn);
			}
		}
			
	mysql_close($conn);		
?>