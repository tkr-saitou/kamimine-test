<!doctype html>

<html lang="ja">

<head>
    <?php 
        require_once('./data/variables.php');
        echo($common_head);
    ?>
	<link rel="stylesheet" href="css/style.css">
	<link href="css/style-s.css" rel="stylesheet" type="text/css" media="only screen and (max-width:479px)">
	<link href="css/style-m.css" rel="stylesheet" type="text/css" media="only screen and (min-width:480px) and (max-width:767px)">
	<link href="css/style-l.css" rel="stylesheet" type="text/css" media="only screen and (min-width:768px) and (max-width:979px)">
    <link type="text/css" rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/cupertino/jquery-ui.min.css" />
	<link href="css/admin.css" rel="stylesheet" type="text/css">
</head>

<body id="dashboard">

<!--container-->
<div id="container">
	<!--header-->
	<header id="header">
		<h1><img src="images/logo.png" alt="SUB TouR"></h1>

		<!-- top-menu -->
		<div id="top-menu"><div class="menu"><img src="images/menu-bar.png" alt=""></div></div>

		<div class="js-menu sb-right">
			<ul>
				<li><a href="<?php echo(USAGE_URL); ?>" target="_blank">ご利用方法</a></li>
				<li><a href="<?php echo(ROUTEMAP_URL); ?>" target="_blank">路線図</a></li>
				<li><a href="<?php echo(TIMETABLE_URL); ?>" target="_blank">バス時刻表</a></li>
			</ul> 
		</div>
		<!-- top-menu -->
	</header>

	<section id="main-menu">
		<div class="main-menu-box">
			<a href="<?php echo(ROUTEMAP_URL); ?>" target="_blank" class="route-map">路線図</a>
			<div class="dashboard">ダッシュボード</div>
			<a href="<?php echo(TIMETABLE_URL); ?>" target="_blank" class="timetable">時刻表</a>
		</div>
	</section>

	<section id="situation" class="dashboard">
		<h2 class="dashboard">本日の状況</h2>

		<div class="situation-contents">
			<article class="situation-table">
			</article>

			<article class="alert">
			</article>

			<article class="to-busloca">
				<a href="<?php echo(HOME_URL)?>index.php"><p>バスロケ<br />サービス<br />へ</p></a>
			</article>
		</div>
	</section>

	<section id="traffic-information" class="dashboard">
		<h2 class="dashboard">運行状況確認</h2>

		<div class="select-contents clearfix" id="service-term">
            <input type="text" id="date" class="date" name="date" value="">

			<select id="course_id" name="course_id" class="system"></select>

			<select id="shift_pattern_cd" name="shift_pattern_cd" class="service"></select>
		</div>

		<div id="serviceListBtn" class="indicate">表示</div>

		<div class="traffic-information-table">
		</div>
	</section>

	<section id="service-graph-area" class="graph">
        <p id="msg">条件を入力して表示ボタンを押して下さい</p>
	</section>

    <div id="search-graph-area">
	<section id="search-information" class="dashboard">
		<h2 class="dashboard">検索状況確認</h2>

		<div class="select-contents clearfix" id="service-term">
            <input type="text" id="from_date" class="date" name="from_date" value="">
            <span class="period">&nbsp;&nbsp;～&nbsp;&nbsp;</span>
            <input type="text" id="to_date" class="date" name="to_date" value="">
		</div>

		<div id="searchGraphBtn" class="indicate">表示</div>
	</section>

	<section id="search-analysis-graph-area" class="graph">
	</section>

	<section id="search-ranking-graph-area" class="graph">
        <p id="msg">期間を入力して表示ボタンを押して下さい</p>
	</section>
    </div>
<!--
	<section class="download_check dashboard clearfix">
		<section id="download">
			<h2 class="dashboard">運行情報ダウンロード</h2>

			<div class="download-date-box">
				<select name="download-date" class="download-date">
					<option value="0" selected>2016/01/06</option>
					<option value="1">2016/01/07</option>
				</select>
			</div>

			<div class="download-button"><a href="#">ダウンロード</a></div>
		</section>
	</section>
-->
</div>
<!--/container-->

<footer>
	<p class="copyright">&copy; 2015 SUBTour All Rights Reserved.</p>
</footer>
    <!-- Java Script -->
    <?php echo($common_js); ?>
	<script type="text/javascript" src="./lib/js/jquery.ui.datepicker-ja.js"></script>
    <script type="text/javascript" src="./lib/js/d3.js"></script>
	<script type="text/javascript" src="./js/admin.js"></script>

<p id="topbutton"><a href="#header"></a></p>

</body>

</html>
