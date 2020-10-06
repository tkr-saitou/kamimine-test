<!DOCTYPE html PUBLIC"-//i-mode group (ja)//DTD XHTML i-XHTML(Locale/Ver.=ja/2.3) 1.0//EN" "i-xhtml_4ja_10.dtd">
<?php
	require_once('./fp/htmlGenerator.php');
?>
<html>
	<head>
		<title>いまココ</title>
		<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1,user-scalable=no" />
		<meta name="keywords" content="いまココ,バスロケ,コミニュケーションバス,ロケーション,熊本,くまもと,合志市,菊陽町,レターバス,キャロッピー">
		<script type="text/javascript" src="./js/GoogleAnalytics.js"></script>
		<script type="text/javascript" src="//maps.googleapis.com/maps/api/js?sensor=false"></script>
		<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js"></script>
		<link rel="stylesheet" type="text/css" href="./css/feature.css">
	</head>
	<body>
		<div id="header">
			<div id="header_top"></div>
			<div id="header_contents">
				<div id="header_title"><img src="./images/title_s.png" alt="title"></div><br>
				<div id="header_character"><img src="./images/character_s.png" alt="character"></div>
				<div id="header_text">レターバス、キャロッピー号の運行状況を<br>リアルタイムで調べることができます</div>
			</div>
			<div id="header_bottom">
				<div id="header_info">
					<marquee>
<?php
$telop = file_get_contents('/home/imacoco/telop.txt');
echo htmlentities(mb_convert_encoding($telop, "UTF-8", "UTF-8,SJIS"));
?>
					</marquee>
				</div>
			</div>
		</div>
		<div id="contents">
<?php
	setDivWithIsset($_POST, "rosen", "map", "", "nonDisp");
	setMap($_POST);
?>
			</div>
			<div id="menu">
<?php
	setResult($_POST);
?>
			</div>
			<form method="POST" action="fpindex.php">
			<div class="box" id="search_box">
					<img class="box_title" src="./images/search_title.png">
					<div id="search_options">
<?php
	if (intval($_POST["area"]) == 0) {  // areaがnonDispだと次画面に伝わらないので、hiddenを追加
		setDivWithIsset($_POST, "area", "area", "nonDisp", "");
?>
							<div class="custom_select_title">バスを選択<span class="custom_select_title_hissu">（必須）</span></div>
							<select class="selector" id="area" name="area">
<?php
	setAreaList($_POST);
?>
							</select>
						</div>
<?php
	} else {
?>
						<div class="dummy_area">
							<input type="hidden" name="area" value="<?php echo $_POST["area"]; ?>">
                                                </div>
<?php
	}
?>
<?php
	setDivWithIsset($_POST, "area", "rosen", "", "nonDisp");
?>
							<div class="custom_select_title">路線を選択<span class="custom_select_title_ninni">（任意）</span></div>
							<select class="selector" id="rosen" name="rosen">
								<option value=""></option>
<?php
	setRosenList($_POST);
?>
						</select>
						</div>
<?php
	setDivWithIsset($_POST, "area", "fromBS", "", "nonDisp");
?>
							<div class="custom_select_title">出発バス停を選択<span class="custom_select_title_hissu">（必須）</span></div>
							<select class="selector" id="fromBS" name="fromBS">
<?php
	setBsstopList($_POST, 0);
?>

							</select>
						</div>
<?php
	setDivWithIsset($_POST, "area", "toBS", "", "nonDisp");
?>
							<div class="custom_select_title">到着バス停を選択<span class="custom_select_title_ninni">（任意）</span></div>
							<select class="selector" id="toBS" name="toBS">
								<option value=""></option>
<?php
	setBsstopList($_POST, 1);
?>


							</select>
						</div>
<!--
						<input type="image" id="btn_search" src="./images/btn_search.png">
-->
						<input type="submit" id="btn_search" value="検索">
					</div>
				</div>
				</form>
				<div class="box" id="menu_box">
					<img class="box_title" src="./images/menu_title.png">
					<ul id="menu_list">
						<li><a href="fpnotice.php"><img src="./images/icon_info.png" alt="info" width="32" height="32">このサイトについて　</a></li>
						<li><a href="manual.pdf" target="_blank"><img src="./images/icon_manual.png" alt="manual" width="32" height="32">ご利用方法　　　　　</a></li>
						<li><a href="mailto:kikaku@city.koshi.lg.jp?subject=いまココについての意見・感想"><img src="./images/icon_mail.png" alt="mail" width="32" height="32">ご意見・ご感想　　　</a></li>
						<li><a href="http://www.city.koshi.lg.jp/kiji/pubm/list.aspx?c_id=315&mst=0&redi=ON"><img src="./images/icon_timetable.png" alt="time" width="32" height="32">レターバス時刻表　　</a></li>
						<li><a href="http://www.town.kikuyo.lg.jp/mobile/"><img src="./images/icon_timetable.png" alt="time" width="32" height="32">キャロッピー号時刻表</a></li>
					</ul>
				</div>
			</div>
		</div>
		<div id="footer">
			<div id="copyrights">Copyrights 2014 KCS All Rights Reserved.</div>
		</div>
	</body>
</html>
