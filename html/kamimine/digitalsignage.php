<!doctype html>

<html lang="ja">

<head>
    <?php 
        require_once('./data/variables.php');
        echo($common_head);
    ?>
	<link rel="stylesheet" href="css/signage.css">
</head>

<body id="top">

<!--
<section id="news">
	    <marquee>
            <span id="telop"></span>
		</marquee>
</section>
-->

<!--container-->
<div id="container">
	<input type="hidden" id="course_id" name="course_id" value="05">
	<input type="hidden" id="fromBS" name="fromBS" value="<?php echo($_REQUEST["fromBS"]) ?>">
	<input type="hidden" id="toBS" name="toBS" value="0">

	<section id="map">
        <div id="map_canvas"></div>
		<div class="reference">
			<p class="usually">通常運行</p>
			<p class="five-minutes">5分程度の遅れ</p>
			<p class="ten-minutes">10分以上の遅れ</p>
		</div>
	</section>

	<section id="result" class="clearfix">
		<div class="situation" id="situation"></div>
		<div class="present-time clearfix">
			<p>
			<span>現在時刻</span><br>
			<label id="current_time"></label>
			</p>
		</div>
		<div class="timetable" id="timetable"></div>
	</section>

</div>
<!--/container-->

<!-- Java Script -->
	<script>var signage_enabled = true;</script>
    <?php echo($common_js); ?>
	<script type="text/javascript" src="./js/drawBusMap.js"></script>
	<script type="text/javascript" src="./js/signage.js"></script>

</body>

</html>

