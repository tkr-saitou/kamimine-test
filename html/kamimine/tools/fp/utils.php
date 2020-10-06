<?php
//require_once ('./cgi/communitybus_common.php');
//$link = connectDBD($DB);


/*
 * DBデータ取得後、selectのoption出力
 *
function createList($sql) {
	global $link;
	$result = mysqli_query($link, $sql);
	while ($row = mysqli_fetch_assoc($result)) {
		$list[] = $row;
	}
	return $list;
}

/*
 * エリア一覧の出力
 * 
function getAreaList() {
	$sql = "SELECT * FROM t_arealist";
	return createList($sql);
}

/*
 * 路線一覧の出力
 *
function getRosenList($post) {
	$sql = "SELECT * FROM t_rosenlist WHERE areacd = ".$post['area'];
	return createList($sql);
}

/*
 * バス停一覧の出力
 *
function getBsstopList($post) {
	$sql = "SELECT TRUNCATE(bscd / 10, 0) AS bscd, bsname FROM t_busstop"
		." WHERE bscd between ".($post['area'] * 100000)." AND ".(($post['area'] + 1) * 100000)
		." GROUP BY TRUNCATE(bscd / 10, 0)";
	return createList($sql);
}
*/
/*
 * 路線名の取得
 */
function getSysname($syscd) {
	global $link;
	$sql = "SELECT sysname FROM t_rosenlist WHERE syscd = ".$syscd;
	$result = mysqli_query($link, $sql);
	$row = mysqli_fetch_assoc($result);
	return $row['sysname'];		
}

/*
 * バス停名の取得
 */
function getBsname($bscd) {
	global $link;
	$sql = "SELECT bsname FROM t_busstop WHERE TRUNCATE(bscd / 10, 0) = ".$bscd;
	$result = mysqli_query($link, $sql);
	$row = mysqli_fetch_assoc($result);
	return $row['bsname'];
}

?>
