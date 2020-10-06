<!doctype html>

<html lang="ja">

<head>
    <?php 
        require_once('./data/variables.php');
        echo($common_head);
    ?>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/busstopsearch.css">
    <link href="css/busstopsearch-s.css" rel="stylesheet" type="text/css" media="only screen and (max-width:479px)">
    <link href="css/busstopsearch-m.css" rel="stylesheet" type="text/css" media="only screen and (min-width:480px) and (max-width:767px)">
    <link href="css/busstopsearch-l.css" rel="stylesheet" type="text/css" media="only screen and (min-width:768px) and (max-width:979px)">
    
</head>


<body id="top">

<!--container-->
<div id="container">
	<!--header-->
	<header id="header">
		<h1><img src="images/logo.png" alt="SUB TouR-Z"></h1>

		<!-- top-menu -->
		<div id="top-menu"><div class="menu"><img src="images/menu-bar.png" alt=""></div></div>

		<div class="js-menu sb-right">
            <?php require_once('./data/variables.php'); ?>
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
			<div class="route-map">route-map</div>
			<div class="serch">バス停を探します。</div>
			<div class="timetable">timetable</div>
		</div>
	</section>

	<section class="aiueo clearfix">
		<ul>
			<li><a href="#" class="initial">あ</a></li>
			<li><a href="#" class="initial">い</a></li>
			<li><a href="#" class="initial">う</a></li>
			<li><a href="#" class="initial">え</a></li>
			<li><a href="#" class="initial">お</a></li>
		</ul>

		<ul>
			<li><a href="#" class="initial">か</a></li>
			<li><a href="#" class="initial">き</a></li>
			<li><a href="#" class="initial">く</a></li>
			<li><a href="#" class="initial">け</a></li>
			<li><a href="#" class="initial">こ</a></li>
		</ul>

		<ul>
			<li><a href="#" class="initial">さ</a></li>
			<li><a href="#" class="initial">し</a></li>
			<li><a href="#" class="initial">す</a></li>
			<li><a href="#" class="initial">せ</a></li>
			<li><a href="#" class="initial">そ</a></li>
		</ul>

		<ul>
			<li><a href="#" class="initial">た</a></li>
			<li><a href="#" class="initial">ち</a></li>
			<li><a href="#" class="initial">つ</a></li>
			<li><a href="#" class="initial">て</a></li>
			<li><a href="#" class="initial">と</a></li>
		</ul>

		<ul>
			<li><a href="#" class="initial">な</a></li>
			<li><a href="#" class="initial">に</a></li>
			<li><a href="#" class="initial">ぬ</a></li>
			<li><a href="#" class="initial">ね</a></li>
			<li><a href="#" class="initial">の</a></li>
		</ul>

		<ul>
			<li><a href="#" class="initial">は</a></li>
			<li><a href="#" class="initial">ひ</a></li>
			<li><a href="#" class="initial">ふ</a></li>
			<li><a href="#" class="initial">へ</a></li>
			<li><a href="#" class="initial">ほ</a></li>
		</ul>

		<ul>
			<li><a href="#" class="initial">ま</a></li>
			<li><a href="#" class="initial">み</a></li>
			<li><a href="#" class="initial">む</a></li>
			<li><a href="#" class="initial">め</a></li>
			<li><a href="#" class="initial">も</a></li>
		</ul>

		<ul class="last">
			<li><a href="#" class="initial">や</a></li>
			<li><a href="#" class="initial">ゆ</a></li>
			<li><a href="#" class="initial">よ</a></li>
			<li><a href="#" class="initial">わ</a></li>
		</ul>
	</section>

	<section class="result">
		<div class="result-title"></div>

		<ol class="table clearfix"></ol>

		<div class="button"><a href="#" id="backbutton">戻る</a></div>
	</section>
</div>
<!--/container-->

<footer>
	<nav>
		<ul class="clearfix">
            <li><a href="<?php echo(USAGE_URL); ?>" target="_blank">ご利用方法</a></li>
            <li><a href="<?php echo(ROUTEMAP_URL); ?>" target="_blank">路線図</a></li>
            <li><a href="<?php echo(TIMETABLE_URL); ?>" target="_blank">バス時刻表</a></li>
		</ul>
	</nav>

	<p class="copyright">&copy; 2015 SUBTour All Rights Reserved.</p>
</footer>

<p id="topbutton"><a href="#header"></a></p>

    <!-- Java Script -->
    <?php echo($common_js); ?>
    <script type="text/javascript" src="./js/busstopsearch.js"></script>

</body> 
</html>
