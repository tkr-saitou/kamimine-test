<!doctype html>

<html lang="ja">

<head>
    <?php 
        require_once('./data/variables.php');
        echo($common_head);
    ?>
	<link rel="stylesheet" href="css/style.css">
	<link href="css/style-s2.css" rel="stylesheet" type="text/css" media="only screen and (max-width:479px)">
	<link href="css/style-m.css" rel="stylesheet" type="text/css" media="only screen and (min-width:480px) and (max-width:767px)">
	<link href="css/style-l.css" rel="stylesheet" type="text/css" media="only screen and (min-width:768px) and (max-width:979px)">
</head>

<body id="top">

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
			<div class="main">バスの運行状況が<br />リアルタイムにわかります。</div>
			<a href="<?php echo(TIMETABLE_URL); ?>" target="_blank" class="timetable">時刻表</a>
		</div>
	</section>

	<section id="news">
	    <marquee>
            <span id="telop"></span>
		</marquee>
	</section>

	<section id="map">
        <div id="map_canvas" style="width:100%; height:100%"></div>
		<div class="reference clearfix">
			<p class="usually">通常運行</p>
			<p class="five-minutes">5分程度の遅れ</p>
			<p class="ten-minutes">10分以上の遅れ</p>
		</div>
	</section>

	<section id="serch">
<!--
        <input type="hidden" id="buscategory_cd" name="buscategory_cd" value="001">
-->
		<ul class="navi clearfix">
			<!-- <li>バス<span>をえらぶ</span></li> -->
			<li>
                <select id="course_id" name="course_id"></select>
                <div class="category category01" id="courseText">路線<span>をえらぶ</span></div>
            </li>
			<li>
                <select id="fromBS" name="fromBS"></select>
                <div class="category category02" id="fromBSText">出発<span>するバス停をえらぶ</span></div>
            </li>
			<li>
                <select id="toBS" name="toBS"></select>
                <div class="category category03" id="toBSText">到着<span>するバス停をえらぶ</span></div>
            </li>
			<!-- <li class="landmark"><span>出発地付近の</span>ランドマーク<span>でえらぶ</span></li> -->
			<!-- <li class="landmark"><span>到着地付近の</span>ランドマーク<span>でえらぶ</span></li> -->
		</ul>

		<div  class="button" id="searchBtn">検索</div>
	</section>

	<section id="result" class="clearfix">
		<div class="present-time clearfix">
			<p>
            <span>現在時刻</span><br>
            <label id="current_time"></label>
            </p>
			<div class="refresh">自動<span id="status">更新</span></div>
		</div>

		<div class="timetable" id="timetable"></div>

		<div class="situation" id="situation"></div>
	</section>
</div>
<!--/container-->

<footer class="clearfix">
    <div class="footer-box clearfix">
        <div class="footer-left">
            <nav>
                <ul class="clearfix">
					<li><a href="<?php echo(USAGE_URL); ?>" target="_blank">ご利用方法</a></li>
					<li><a href="<?php echo(ROUTEMAP_URL); ?>" target="_blank">路線図</a></li>
					<li><a href="<?php echo(TIMETABLE_URL); ?>" target="_blank">バス時刻表</a></li>
                </ul>
            </nav>

            <p class="copyright">&copy; 2015 SUBTouR-Z All Rights Reserved.</p>
        </div>
        <?php if (OPINION) echo($opinion_html); ?>
    </div>

</footer>

<p id="topbutton"><a href="#header"></a></p>
    <!-- Java Script -->
    <?php echo($common_js); ?>
	<script type="text/javascript" src="./js/drawBusMap.js"></script>
	<script type="text/javascript" src="./js/index.js"></script>

</body>

</html>

