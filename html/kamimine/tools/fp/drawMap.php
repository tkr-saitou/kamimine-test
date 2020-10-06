<?php

define("HOME_URL", "http://160.16.126.45/");

/*
 * 路線ポリラインの色を取得
 */
function getRosenColor($syscd) {
	$color = "0x000000";
	switch (intval($syscd)) {
	case 1020011: // レターバス左回り
		$color = "0xFF0000";
		break;
	case 1020012: // レターバス右回り
		$color = "0x0000FF";
		break;
	}
	return $color;
}

/*
 * 路線ポリラインの取得
 */
function getRosenPolyline($post) {
	global $link;
	$areacd = $post['area'];
	$syscd = $post['rosen'];
	$sql = "SELECT syscd, sysname, no, lat, lng FROM t_rosen JOIN t_rosenlist USING(areacd, syscd)"
		." WHERE syscd = ".$syscd
		." ORDER BY syscd, no";
	$result = mysqli_query($link, $sql);

	$polyline = "&path=color:".getRosenColor($syscd);
	while ($row = mysqli_fetch_assoc($result)) {
		$polyline .= "|".$row["lat"].",".$row["lng"];
	}
	return $polyline;
}

/*
 * バス停のアイコンを取得
 */
function getBusStopIcon($bscd, $post) {
	$icon = HOME_URL."images/bs_b.png";
	if ($bscd == $post['fromBS'] || $bscd == $post['toBS']) {
		$icon = HOME_URL."images/bs_g.png";
	} else if ($bscd < 10200000) {
		$icon = HOME_URL."images/bs_o.png";
	}
	return $icon;
}

/*
 * バス停の色を取得
 * (文字数削減版用)
 */
function getBusStopColor($bscd, $post) {
	$color = "";
	if ($bscd == intval($post['fromBS']) * 10 || $bscd == intval($post['toBS']) * 10) {
		$color = "color:blue|";
	}
	return $color;
}

/*
 * バス停の取得
 */
function getBusStopMarkers($post) {
	global $link;
	$syscd = $post['rosen'];
	$sql = "SELECT bscd, bsname, lat, lng FROM t_busdia JOIN t_busstop USING(bscd)"
		." WHERE syscd = ".$syscd
		." AND (TRUNCATE(bscd / 10, 0) = ".$post['fromBS']
		." OR TRUNCATE(bscd / 10, 0) = ".$post['toBS'].")"
		." GROUP BY bscd";
	$result = mysqli_query($link, $sql);

	$markers = "";
	while ($row = mysqli_fetch_assoc($result)) {
		$icon = getBusStopIcon($row['bscd'], $post);
		$markers .= "&markers=icon:".$icon."|".$row["lat"].",".$row["lng"];
		// 文字数削減版(バス停をアイコンにしない)
		//$color = getBusStopColor($row['bscd'], $post);
		//$markers .= "&markers=".$color.$row["lat"].",".$row["lng"];
	}
	return $markers;
}

/*
 * バスのアイコンを取得
 */
function getBusIcon($bus) {
	$icon = "";
	if ($bus["lat"] == 0 && $bus["lng"] == 0) return;
	switch (intval($bus["areacd"])) {
	case 101:
		$icon = HOME_URL."images/bus_3.png";
		break;
	case 102:
		if ($bus["syscd"] == 1020011) {
			$icon = HOME_URL."images/bus_2.png";
		} else {
			$icon = HOME_URL."images/bus_1.png";
		}
		break;
	default:
		$icon = HOME_URL."images/bus_2.png";
		break;
	}
	return $icon;
}

/*
 * 最新のバス位置情報取得
 */
function getBusLocation($post) {
	global $link;
	$syscd = $post['rosen'];
	$today = "2015-01-08 17:32:00";//date('Y-m-d', time());　//テスト用！！！！！
	$sql = "SELECT carid, MAX(gpstime) AS `latesttime` FROM t_busprobe"
		." WHERE gpstime > '".$today."' AND syscd = ".$syscd
		." GROUP BY carid";
	$result = mysqli_query($link, $sql);

	// 本日運行している各バスに対して最新の位置・路線・遅れ時間を取得
	$markers = "";
	while ($row = mysqli_fetch_assoc($result)) {
		// 位置と路線取得
		$sql2 = "SELECT carid, lat, lng, ang, vel, areacd, syscd, diano FROM t_busprobe "
			."WHERE carid = '".$row["carid"]."' AND gpstime = '".$row["latesttime"]."'";
		$result2 = mysqli_query($link, $sql2);
		if (!$result2) {
			eLog("Invalid Query: ".mysqli_error($link)." << ".$sql, $LogFile);
			exit("{\"status\":-1}");
		}
		$busList[] = mysqli_fetch_assoc($result2);
		// 遅れ時間取得 未
	}

	foreach ($busList as $bus) {
		$icon = getBusIcon($bus);
		$markers .= "&markers=icon:".$icon."|".$bus["lat"].",".$bus["lng"];
	}
	return $markers;
}

?>
