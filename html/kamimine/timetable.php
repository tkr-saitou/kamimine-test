<!doctype html>

<html lang="ja">

<head>
    <?php 
        require_once('./data/variables.php');
        echo($common_head);
    ?>
    <link rel="stylesheet" href="css/timetable.css">
    <link href="css/timetable-s.css" rel="stylesheet" type="text/css" media="only screen and (max-width:479px)">
    <link href="css/timetable-m.css" rel="stylesheet" type="text/css" media="only screen and (min-width:480px) and (max-width:767px)">
    <link href="css/timetable-l.css" rel="stylesheet" type="text/css" media="only screen and (min-width:768px) and (max-width:979px)">
    
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

	<section id="timetable-menu">
		<div class="timetable-menu-box">
            <a href="<?php echo(HOME_URL); ?>" target="_blank" class="main">Home</a>
			<div class="timetable">時刻表</div>
            <a href="<?php echo(ROUTEMAP_URL); ?>" target="_blank" class="route-map">路線図</a>
		</div>
	</section>

	<section id="timetable-content">
		<p class="bus-stop"></p>

        <select class="select-arrival">
            <option value="0">あいうえお 行</option>
            <option value="0">かきくけこさしすせそ 行</option>
            <option value="0">たちつてと 行</option>
        </select>

		<ul class="days clearfix">
		</ul>

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
    <script type="text/javascript" src="./js/timetable.js"></script>

</body> 
</html>
