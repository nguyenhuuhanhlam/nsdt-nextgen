<?
	function nsdt_mysql_count($t, $f, $c=null){
		$c = $c ? chr(32).$c : null;
		$q = 'SELECT COUNT('.$f.') FROM '.$t.$c;
		$r = mysql_query($q);
		$e = mysql_fetch_row($r);
		return $e[0];
	}
	
	function nsdt_mysql_droplist($t, $v, $s){
		$o = null;
		$q = 'SELECT * FROM '.$t.' ORDER BY '.$v;
		$r = mysql_query($q);
		while ($e=mysql_fetch_array($r)){
				$o .= '<option value="'.$e[$v].'">'.$e[$s].'</option>';
		}
		return $o;
	}
	
	/*FUNCTION
	*/
	$pregnancySearch = create_function(
	'$kUnit,$d1,$d2',
	'return "SELECT ma_nhanvien,id_donvi,
	GROUP_CONCAT(DAYOFYEAR(ngay_batdau),\"|\",DAYOFYEAR(ngay_ketthuc),\"|\",id_thaisan ORDER BY ngay_batdau) AS pregnancyData
	FROM ns_xuly_thaisan
	WHERE id_donvi IN($kUnit)
	AND (\"$d1\" <= ngay_ketthuc OR \"$d2\" = ngay_ketthuc)
	AND (\"$d2\" > ngay_batdau OR \"$d2\" = ngay_batdau)
	GROUP BY ma_nhanvien";');
	
	$pregnancySearchAll = create_function(
	'$d1,$d2',
	'return "SELECT ma_nhanvien,id_donvi,
	GROUP_CONCAT(DAYOFYEAR(ngay_batdau),\"|\",DAYOFYEAR(ngay_ketthuc),\"|\",id_thaisan ORDER BY ngay_batdau) AS pregnancyData
	FROM ns_xuly_thaisan
	WHERE (\"$d1\" <= ngay_ketthuc OR \"$d2\" = ngay_ketthuc)
	AND (\"$d2\" > ngay_batdau OR \"$d2\" = ngay_batdau)
	GROUP BY ma_nhanvien";');
	
	$pregnancyUpdate = create_function(
	'$kP,$m,$d1,$d2',
	'return "UPDATE mcc_log SET K=$kP
	WHERE ma_nhanvien=\"$m\"
	AND ngaythangnam BETWEEN \"$d1\" AND \"$d2\"";'
	);
	
	$diligentSearch = create_function('$kUnit,$d1,$d2','return "SELECT * FROM ns_chuyencan
	WHERE id_donvi IN($kUnit) AND ngay_tru BETWEEN \"$d1\" AND \"$d2\" ";');
	
	$timxinphep = create_function(
	'$kxinphep,$d1,$d2',
	'return "SELECT *,SUM(songaynghi) AS songaynghi
	FROM ns_xinphep
	WHERE id_phepnam IN($kxinphep)
	AND (\"$d1\" <= ngay_ketthuc OR \"$d2\" = ngay_ketthuc)
	AND (\"$d2\" > ngay_batdau OR \"$d2\" = ngay_batdau)
	GROUP BY ma_nhanvien";');
?>