<?php

$BugFile = "./bugFile.txt";
$LogFile = "./subtour.log";

if ($_SERVER["REQUEST_METHOD"] != "POST") {
	// POSTでない場合
	$str = "REQUEST_METHOD is not POST!";
	eLog($str, $LogFile);
	print(1);
} else {
	$bug = $_POST["bug"];
	printBug($bug, $BugFile);
	print(0);
}

// バグ出力
function printBug($str, $file) {
	$timeStamp = date("Y-m-d H:i:s");
	$fp = fopen($file, "a");
	flock($fp, LOCK_EX);
	fwrite($fp, "/==================================================================/\n");
	fwrite($fp, $timeStamp."\n\n".$str."\n");
	flock($fp, LOCK_UN);
	fclose($fp);
}

// エラーログ出力
function eLog($str, $file) {
	$timeStamp = date("Y-m-d H:i:s");
	$fp = fopen($file, "a");
	flock($fp, LOCK_EX);
	fwrite($fp, $timeStamp." ----- ".$str."\n");
	flock($fp, LOCK_UN);
	fclose($fp);
}
