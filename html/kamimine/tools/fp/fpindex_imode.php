<!DOCTYPE html PUBLIC"-//i-mode group (ja)//DTD XHTML i-XHTML(Locale/Ver.=ja/2.3) 1.0//EN" "i-xhtml_4ja_10.dtd">
<?php
	require_once('./fp/htmlGenerator.php');
/*
 * 検索結果の出力
 */
function setResult_imode($post) {
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
                                echo '<div algin="center">'
                                        .'<img src="./images/i/result_title.gif" alt="">'
                                        .'<div>'
//                                      .'<p>検索結果 '.($i + 1).'</p>'
                                        .getSysname($row['syscd'])
                                        .'<div>'
                                        .'　'.getBsname($frombscd)
                                        .'<div>'
                                        .'　↓ '.$row['delay'].'分の遅れ'
                                        .'</div>'
                                        .' 　'.getBsname($tobscd)
                                        .'</div>'
                                        .'</div>'
                                        .'</div><br>';
                        }
                } else {
                                echo '<div algin="center">'
                                        .'<img src="./images/i/result_title.gif" alt="">'
                                        .'<div>'
                                        .'<p>現在、運行していません。</p>'
                                        .'</div>'
                                        .'</div>';
                }
        } else {
                echo '<div>';
        }
}
/*
 * 地図の出力
 */
function setMap_imode($post) {
        $center = array("100" => "32.889620,130.759160",        // 初期位置(レターバス)
                        "101" => "32.866905,130.808888",        // キャロッピー号
                        "102" => "32.889620,130.759160");       // レターバス
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
        echo '<div id="image_reference"><img src="./images/i/map_reference_fp.gif" alt="map_reference"></div><br>';
}
?>
<html>
	<head>
		<title>バスロケ いまココ</title>
	</head>
	<body>
		<div>
			<div align="center">
				<div><img src="./images/i/title.gif" width=200 height=45 alt="title"></div>
				<div><img src="./images/i/character.gif"  width=140 height=80 alt="character"></div>
				<!-- div><font size="1">レターバス、キャロッピー号の運行状況をリアルタイムで調べることができます</font></div -->
			</div>
			<div>
				<marquee>
<?php
$telop = file_get_contents('/home/imacoco/telop.txt');
echo htmlentities(mb_convert_encoding($telop, "UTF-8", "UTF-8,SJIS"));
?>
				</marquee>
			</div>
		</div>
		<div>
<?php
	if (intval($_POST["fromBS"]) != 0) {
                echo "			<div>";
		setMap_imode($_POST);
		echo "			</div><br>";
	}
?>
			<div>
<?php
	setResult_imode($_POST);
?>
			</div>
			<form method="POST" action="fpindex_imode.php">
			<div align="center">
					<img src="./images/i/search_title.gif" alt="">
					<div>
<?php
	if (intval($_POST["area"]) == 0) {
?>
						<div>
							<div>バスを選択<font color="red" size="1">（必須）</font></div>
							<select name="area">
<?php
	setAreaList($_POST);
?>
							</select>
						</div>
<?php
	} else {
?>
						<div>
							<input type="hidden" name="area" value="<?php echo $_POST["area"]; ?>">
                                                </div>
						<div>
							<div>路線を選択<font color="black" size="1">（任意）</font></div>
							<select name="rosen">
								<option value=""></option>
<?php
	setRosenList($_POST);
?>
							</select>
						</div>
						<div>
							<div>出発バス停を選択<font color="red" size="1">（必須）</font></div>
							<select name="fromBS">
<?php
	setBsstopList($_POST, 0);
?>

							</select>
						</div>
						<div>
							<div>到着バス停を選択<font color="black" size="1">（任意）</font></div>
							<select name="toBS">
								<option value=""></option>
<?php
	setBsstopList($_POST, 1);
?>


							</select>
						</div>
<?php
	}
?>
					</div>
					<input type="submit" value="検索">
				</div>
				</form><br>
				<div align="left">
					<img src="./images/i/menu_title.gif" alt="">
					<table>
						<tr><td><a href="fpnotice_imode.php"><img src="./images/i/icon_info.gif" alt="info" width="32" height="32"><font size="1">このサイトについて</font></a><br></td></tr>
						<tr><td><a href="manual.pdf" target="_blank"><img src="./images/i/icon_manual.gif" alt="manual" width="32" height="32"><font size="1">ご利用方法</font></a><br></td></tr>
						<tr><td><a href="mailto:kikaku@city.koshi.lg.jp?subject=<?php echo urlencode(mb_convert_encoding('いまココについての意見・感想', 'Shift-JIS', 'auto')); ?>"><img src="./images/i/icon_mail.gif" alt="mail" width="32" height="32"><font size="1">ご意見・ご感想</font></a><br></td></tr>
						<tr><td><a href="http://www.city.koshi.lg.jp/kiji/pubm/list.aspx?c_id=315&mst=0&redi=ON"><img src="./images/i/icon_timetable.gif" alt="time" width="32" height="32"><font size="1">レターバス時刻表</font></a><br></td></tr>
						<tr><td><a href="http://www.town.kikuyo.lg.jp/mobile/"><img src="./images/i/icon_timetable.gif" alt="time" width="32" height="32"><font size="1">キャロッピー号時刻表</font></a><br></td></tr>
					</table>
				</div>
			</div>
		</div><br>
		<div>
			<div>Copyrights 2014 KCS All Rights Reserved.</div>
		</div>
	</body>
</html>
