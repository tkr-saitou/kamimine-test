<?php
require_once ('./cgi/communitybus_common.php');
require_once ('drawMap.php');
require_once ('utils.php');
$link = connectDBD($DB);

/*
 * isset判定後、div要素を出力
 */
function setDivWithIsset($post, $index, $id, $cls1, $cls2) {
	if (isset($post[$index])) {
		echo '<div id="'.$id.'" class="'.$cls1.'">';
	} else {
		echo '<div id="'.$id.'" class="'.$cls2.'">';
	}
} 

/*
 * DBデータ取得後、selectのoption出力
 */
function setOptionAfterFetch($sql, $cd, $name, $post = 0) {
	global $link;
	$result = mysqli_query($link, $sql);
	while ($row = mysqli_fetch_assoc($result)) {
		if ($post == $row[$cd]) {
			echo '<option value="'.$row[$cd].'" selected>'.$row[$name].'</option>';
		} else {
			echo '<option value="'.$row[$cd].'">'.$row[$name].'</option>';
		}
	}
}

/*
 * エリア一覧の出力
 */ 
function setAreaList($post) {
	$sql = "SELECT * FROM t_arealist";
	if (isset($post['area'])) { 
		setOptionAfterFetch($sql, "areacd", "areaname", $post['area']);
	} else {
		setOptionAfterFetch($sql, "areacd", "areaname");
	}
}

/*
 * 路線一覧の出力
 */
function setRosenList($post) {
	$sql = "SELECT * FROM t_rosenlist WHERE areacd = ".$post['area'];
	if (isset($post['rosen'])) {
		setOptionAfterFetch($sql, "syscd", "sysname", $post['rosen']);
	} else {
		setOptionAfterFetch($sql, "syscd", "sysname");
	}
}

/*
 * バス停一覧の出力
 */
function setBsstopList($post, $ft) {
	$sql = "SELECT TRUNCATE(bscd / 10, 0) AS bscd, bsname FROM t_busstop"
		." WHERE bscd between ".($post['area'] * 100000)." AND ".(($post['area'] + 1) * 100000)
                ." AND SUBSTRING(t_busstop.ybkbn, DAYOFWEEK(CURDATE()), 1) = 1"
		." GROUP BY TRUNCATE(bscd / 10, 0)";
	if (!isset($post['fromBS']) && !isset($post['toBS'])) {
		setOptionAfterFetch($sql, "bscd", "bsname");
	} else {
		if ($ft == 0) {
			setOptionAfterFetch($sql, "bscd", "bsname", $post['fromBS']);
		} else {
			setOptionAfterFetch($sql, "bscd", "bsname", $post['toBS']);
		}
	}
}

/*
 * 地図の出力
 */
function setMap($post) {
	$center = array("100" => "32.889620,130.759160",	// 初期位置(レターバス)
			"101" => "32.866905,130.808888",	// キャロッピー号
			"102" => "32.889620,130.759160");	// レターバス
	$polyline = getRosenPolyline($post);
	$markers = getBusStopMarkers($post);
	$location = getBusLocation($post);

	$src = 'http://maps.google.com/maps/api/staticmap?'
		.'&center='.$center[$post["area"]]
		.'&zoom=12'
		.'&size=240x240'
		.'&sensor=false'
		.'&format=jpg-baseline'
		.$markers
		.$location;
		// .$polyline;   // ガラケーのレターバスは文字数オーバーのため、ポリライン無し

	echo '<img border="0" src="'.$src.'">';
	echo '<div id="image_reference"><img src="./images/map_reference_fp.png" alt="map_reference"></div><br>';
}

/*
 * 検索結果の出力
 */
function setResult($post) {
	if (isset($post["rosen"])) {
		echo '<div id="result_box">';

		$areacd = intval($post["area"]);
		$syscd = intval($post["rosen"]);
		$frombscd = intval($post["fromBS"]);
		$tobscd = intval($post["toBS"]);

		if ($syscd == 0 && $tobscd == 0) { // エリア・出発指定
		        $flg = 0;
		} else if ($syscd == 0 && $tobscd != 0) { // エリア・出発・到着指定
		        $flg = 1;
		} else if ($syscd != 0 && $tobscd != 0) { // 全指定
		        $flg = 2;
		} else { // エリア・路線・出発指定
		        $flg = 3;
		}
		$cmd = "./cgi/CalcDelayTime.exe $flg $frombscd $tobscd $areacd $syscd";
		exec($cmd, $arr, $res);
		$ret = json_decode($arr[0], TRUE)["results"];

		if (count($ret) != 0) {
			for ($i = 0; $i < count($ret); $i++) {
				$id = "result_".$i;
				$row = $ret[$i];
				echo '<div id="rosen" class="nonDisp"></div>';
				echo '<div class="box" id="search_result">'
					.'<img class="box_title" src="./images/result_title.png">'
					.'<div class="result_rosen">'
//					.'<p>検索結果 '.($i + 1).'</p>'
//					.'<span class="bus_rosen" id="'.$id.'">'.getSysname($syscd).'</span>'
					.'<span class="bus_rosen" id="'.$id.'">'.getSysname($row['syscd']).'</span>'
					.'<div class="result_detail">'
					.'<span class="from_busstop" id="'.$id.'">'.getBsname($frombscd).'</span>'
					.'<div class="detail_info">'
					.'<span class="delay" id="'.$id.'">'.$row['delay'].'分の遅れ</span>'
					.'</div>'
					.'<span class="to_busstop" id="'.$id.'">'.getBsname($tobscd).'</span>'
					.'</div>' 
					.'</div>'
					.'</div>';
			}
		} else {
				echo '<div class="box" id="search_result">'
					.'<img class="box_title" src="./images/result_title.png">'
					.'<div class="result_rosen">'
					.'<p>現在、運行していません。</p>'
					.'</div>'
					.'</div>';
		}
	} else {
		echo '<div id="result_box" class="nonDisp">';
	}
}

?>
