<?php

require_once("../base/base/BaseApi.php");
require_once("../models/t_sbt_busprobe.php");
require_once("../models/t_sbt_busdia.php");
require_once("../models/t_sbt_busdia_actual.php");
require_once("../models/t_sbt_busbin.php");
require_once("../models/t_sbt_bus_current_route.php");

// ### Class Definition -----------------------------------------------------------------
class getBusLocation extends BaseApi {

    protected function main() {
        $today = date('Y-m-d', time());
        $areacd = $_POST["areaCd"];
        $routecd = $_POST["routeCd"];

        // バスの車両IDと最新のGPS更新時間を取得(当日のみ) 
        if(DEMO) {
            //*** デモモード ***//
            $now = date("His");
            $time = substr($now, -3); // TODO 175443(17時54分43秒) のうち443を取得しているが、何の意図で？
            $args = array($time);
            $latestGPSTime = $this->db->invoke('t_sbt_busprobe', 'getLatestGPSTimeForDemo', $args);
        } else {
            //*** 本番モード ***//
            // 運行中のデバイスIDの一覧を取得
            $args = array(BUSCOMPANY_ID);
            $deviceList = $this->db->invoke('t_sbt_bus_current_route', 'getServiceBusList', $args);
            if (count($deviceList) == 0) exit("{\"status\":0, \"bus\":[]}");
            // 運行中のデバイスごとのプローブ最新時刻を取得
            $args = array($deviceList);
            $latestGPSTime = $this->db->invoke('t_sbt_busprobe', 'getLatestGPSTime', $args);
        }
        if (count($latestGPSTime) == 0) exit("{\"status\":0, \"bus\":[]}");

        // DBよりプローブ情報取得
        $probeList = array();
        foreach ($latestGPSTime as $row) {
            $args = array($row['device_id'], $row['gps_time']);
            $probeList[] = $this->db->invoke('t_sbt_busprobe', 'getProbeData', $args);
        }

        // 本日運行している各バスに対して最新の位置・路線・遅れ時間を取得
        $busLocationList = array();
        $delay = array();
        foreach ($probeList as $row) {
	        if ($row["buscategory_cd"] == 0) continue;
	        if ($areacd != 0 && $areacd != $row["buscategory_cd"]) continue;
	        if ($routecd != 0 && $routecd != $row["course_id"]) continue;

            if(DEMO) {
                $row["bin_no"] = $row["bin_no"] + (substr($now, 0, 2) - 9) * 6 + ceil(substr($now, 2, 2) / 10);
            }

        	// 最終バス停のひとつ前のバス停を取得
            $args = array(BUSCOMPANY_ID, $row['bin_no']);
            //$lastbscd = $this->db->invoke('t_sbt_busdia', 'getBusstopBeforeLast', $args);
            $lastbscd = $this->db->invoke('t_sbt_busdia', 'getLastBusstop', $args);
	
	        // 遅れ時間取得
	        $bscd = $lastbscd['busstop_id'];
            $sys1 = $row['course_id'];
            $area1 = $row['buscategory_cd'];
            $delay['delay'] = 0;
            $cmd = "../cgi/CalcDelayTime.exe 3 $bscd 0 $area1 $sys1";
//$this->logger->writeDebug("*********** command ***********");
//$this->logger->writeDebug($cmd);
            $arr = null;
            $res = 0;
            $tmp = array();
            exec($cmd, $arr, $res);
            if ($res > 0) {
                $tmp = json_decode($arr[0], true);
//$this->logger->writeDebug("*********** result CalcDelay Time ***********");
//$this->logger->writeDebug($tmp);
                // 遅れ判定除外フラグを確認し、除外の場合は0のまま
                $args = array(BUSCOMPANY_ID, $tmp["results"][0]["diano"]);
                $except_delay_flg = $this->db->invoke('t_sbt_busbin', 'getBinDetail', $args)["except_delay_flg"];
                if ($except_delay_flg != 1) {
                    $delay['delay'] = $tmp["results"][0]["delay"];
                }
            }

            // バスアイコンの吹き出し用データ取得
            if(DEMO) {
                $now = date("His");
                $start_time = "000";
                $end_time = substr($now, -3);
                // 更新時刻とバス停取得
                $args = array($device_id, BUSCOMPANY_ID, $start_time, $end_time);
                $balloon = $this->db->invoke('t_sbt_busprobe', 'getLatestBusLocationForDemo', $args);
                if (count($balloon) == 0) exit("{\"status\":1}");
                $balloon["gps_time"] = substr($now, 0, 2).":".substr($now, 2, 1).substr($balloon["gps_time"], -1);
            } else {
                // 更新時刻とバス停取得
/*
                // プローブから取得
                $args = array($row['device_id'], BUSCOMPANY_ID);
                $balloon = $this->db->invoke('t_sbt_busprobe', 'getLatestBusLocation', $args);
*/
                // 運行実績から取得
                $args = array(BUSCOMPANY_ID, $row["bin_no"], date('Y-m-d'));
                $balloon = $this->db->invoke('t_sbt_busdia_actual', 'getLatestBusLocation', $args);
                if (count($balloon) == 0) exit("{\"status\":1}");
            }
            $text["text"] = $balloon["category_name"]." ".$balloon["course_name"]."は、"
                           .$balloon["real_time"]."に".$balloon["busstop_name"]."付近にいました。";

	        $busLocationList[] = array_merge($row, $delay, $text);
        }
//$this->logger->writeDebug("*********** busLocationList ***********");
//$this->logger->writeDebug($busLocationList);

        $data = array(
	        "status"	=> 0,
	        "bus"		=> $busLocationList
        );

        return $data;
    }
}

// ### Run Process ---------------------------------------------------------------------
$class = new getBusLocation();
$class->run();

