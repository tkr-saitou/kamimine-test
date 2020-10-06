<?php
function is_mobile() {
	$ua = array(
		'DoCoMo',
		'KDDI',
		'DDIPOKET',
		'UP.Browser',
		'J-PHONE',
		'Vodafone',
		'SoftBank',
	);
	foreach ($ua as $val) {
		$str = "/".$val."/i";
		if (preg_match($str, $_SERVER['HTTP_USER_AGENT'])){
			return true;
		}
	}
	return false;
}
function is_imode_v10() {
	if( preg_match("/^DoCoMo\/1.0/", $_SERVER['HTTP_USER_AGENT']) ){
		return true;
	}
	elseif( preg_match("/^DoCoMo\/2.0[^\(]+\(c100;/", $_SERVER['HTTP_USER_AGENT']) ){
		return true;
	}
	return false;
}
if(is_imode_v10()) {
    header("Location: http://bl-imacoco.com/fpindex_imode.php");
}
elseif(is_mobile()) {
    header("Location: http://bl-imacoco.com/fpindex.php");
}
?>
