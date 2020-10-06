<?php

require_once ("../data/variables.php");

$reportDay = "2015-03-29";
// フォルダ名、ファイル名末尾
$dayPrefix = preg_replace("/-/", "", $reportDay);
$dir = "../data/".$dayPrefix."/";
if (!is_dir($dir)) {
	if (!mkdir($dir)) exit(1);
}
// 曜日取得
$ybkbn = date("w", strtotime($reportDay));

$sysCodes = array(
	1010001,	// 0.中央循環線1
	1010002,	// 1.中央循環線2
	1010003,	// 2.東部循環線A1
	1010004,	// 3.東部循環線A2
	1010005,	// 4.東部循環線B1
	1010006,	// 5.東部循環線B2
	1010007,	// 6.南部循環線A1
	1010008,	// 7.南部循環線A2
	1010009,	// 8.南部循環線B1
	1010010,	// 9.南部循環線B2
	1010011,	// 10.南部線(午前)
	1010012,	// 11.南部線(午後)
	1010013,	// 12.北部循環線(東)
	1010014,	// 13.北部循環線(西)
	1010015,	// 14.西部線1
	1010016,	// 15.西部線2
	1020011,	// 16.レターバス(左)
	1020012,	// 17.レターバス(右)
);


$link = connectDBD($DB);
foreach ($sysCodes as $sysCd) {
	print($sysCd.": ");
	// 便を取得
	$bin = array();
	$sql = "SELECT diano FROM t_busdia where ybkbn = ".$ybkbn." and syscd = ".$sysCd." GROUP BY diano ORDER BY diano";
	$result = mysqli_query($link, $sql);
	if (!$result) {
		eLog("Invalid Query: ".mysqli_error($link)." << ".$sql, $LogFile);
		print($sql."\n");
		exit(1);
	}
	if (mysqli_num_rows($result) == 0) {
		print("指定日ダイヤなし\n");
		continue;
	}
	while ($row = mysqli_fetch_assoc($result)) {
		$bin[] = $row["diano"];
	}

	// バス停を取得
	$busstop = array();
	$sql = "SELECT D.bscd, S.bsname FROM t_busdia AS D INNER JOIN t_busstop AS S USING (bscd)"
		."WHERE D.ybkbn = ".$ybkbn." AND D.syscd = ".$sysCd." ";
	if ($sysCd < 1020000) {
		$sql .= "AND diano = 1 ";
	} else {
		$sql .= "AND diano = 2 ";
	}
	$sql .= "ORDER BY D.junno";
	$result = mysqli_query($link, $sql);
	if (!$result) {
		eLog("Invalid Query: ".mysqli_error($link)." << ".$sql, $LogFile);
		print($sql."\n");
		exit(1);
	}
	if (mysqli_num_rows($result) == 0) {
		print("0\n");
		exit(1);
	}
	while ($row = mysqli_fetch_assoc($result)) {
		$busstop[] = array(
			"bscd" => $row["bscd"],
			"bsname" => $row["bsname"]
		);
	}
	//print_r($busstop);
	// ダイヤ時刻を取得
	$diaMatrix = array();
	$diaMatrix[0][0] = "便名";
	for ($i = 0; $i < count($bin); $i++) {
		$diaMatrix[0][$i + 1] = $bin[$i]."便";
		$jun = 0;
		for ($j = 0; $j < count($busstop); $j++) {
			$sql = "SELECT junno, DATE_FORMAT(ttime, '%H:%i') AS dTime FROM t_busdia "
				."WHERE ybkbn = ".$ybkbn." AND syscd = ".$sysCd." AND diano = ".$bin[$i]." "
				."AND bscd = ".$busstop[$j]["bscd"]." AND junno = ".($jun + 1);
			$result = mysqli_query($link, $sql);
			if (!$result) {
				eLog("Invalid Query: ".mysqli_error($link)." << ".$sql, $LogFile);
				print($sql."\n");
				exit(1);
			}
			$row = mysqli_fetch_assoc($result);
			$diaMatrix[$j + 1][0] = $busstop[$j]["bsname"];
			$diaMatrix[$j + 1][$i + 1] = $row["dTime"];
			if (mysqli_num_rows($result) > 0) $jun = $row["junno"];
		}
	}
	//print_r($diaMatrix);
/*	
	// ダイヤcsvファイル出力
	$file = "../data/".$sysCd."_diaTime.csv";
	$fp = fopen($file, "w");
	flock($fp, LOCK_EX);
	for ($i = 0; $i < count($diaMatrix); $i++) {
		$text = "";
		for ($j = 0; $j < count($diaMatrix[0]); $j++) {
			if ($j > 0) $text .= ",";
			$text .= $diaMatrix[$i][$j];
		}
		fwrite($fp, mb_convert_encoding($text."\n", "SJIS-win", "UTF-8"));
	}
	flock($fp, LOCK_UN);
	fclose($fp);
*/
	// バス停到着時刻を取得
	$timeMatrix = array();
	$timeMatrix[0][0] = "便名";
	for ($i = 0; $i < count($bin); $i++) {
		$timeMatrix[0][$i + 1] = $bin[$i]."便";
		$prevReached = "00:00:00";
		for ($j = 0; $j < count($busstop); $j++) {
			// ダイヤのないバス停は取得しない
			if ($diaMatrix[$j + 1][$i + 1] != NULL) {
				$sql = "SELECT DATE_FORMAT(MIN(gpstime), '%H:%i:%s') AS rTime FROM t_busprobe_stock "
					."WHERE DATE(gpstime) = '".$reportDay."' AND syscd = ".$sysCd." "
					."AND diano = ".$bin[$i]." AND bscd = ".$busstop[$j]["bscd"]." "
					."AND TIME(gpstime) > '".$prevReached."'";// ORDER BY gpstime";
				$result = mysqli_query($link, $sql);
				if (!$result) {
					eLog("Invalid Query: ".mysqli_error($link)." << ".$sql, $LogFile);
					print($sql."\n");
					exit(1);
				}
				if (mysqli_num_rows($result) == 0) {
					$reachTime = $diaMatrix[$j + 1][$i + 1].":00";
				} else {
					$row = mysqli_fetch_assoc($result);
					$reachTime = $row["rTime"];
				}
				$prevReached = $reachTime;
			} else {
				$reachTime = NULL;
			}
			if ($i == 0) $timeMatrix[$j + 1][0] = $busstop[$j]["bsname"];
			$timeMatrix[$j + 1][$i + 1] = $reachTime;
		}
	}
	//print_r($timeMatrix);
	
	// 路線名を取得
	$sql = "SELECT sysname FROM t_rosenlist WHERE syscd = ".$sysCd;
	$result = mysqli_query($link, $sql);
	if (!$result) {
		eLog("Invalid Query: ".mysqli_error($link)." << ".$sql, $LogFile);
		print($sql."\n");
		exit(1);
	}
	$row = mysqli_fetch_assoc($result);
	$sysName = $row["sysname"];

	// 実時間csvファイル出力
	$file = $dir.$sysName."_realTime_".$dayPrefix.".csv";
	$fp = fopen($file, "w");
	flock($fp, LOCK_EX);
	for ($i = 0; $i < count($timeMatrix); $i++) {
		$text = "";
		for ($j = 0; $j < count($timeMatrix[0]); $j++) {
			if ($j > 0) $text .= ",";
			$text .= $timeMatrix[$i][$j];
		}
		fwrite($fp, mb_convert_encoding($text."\n", "SJIS-win", "UTF-8"));
	}
	flock($fp, LOCK_UN);
	fclose($fp);

	print("complete\n");

}

mysqli_close($link);

