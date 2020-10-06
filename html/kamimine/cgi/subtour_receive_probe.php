<?php

require_once("../base/base/BaseApi.php");
require_once("../models/t_sbt_buscategory.php");
require_once("../models/t_sbt_busdia.php");
require_once("../models/t_sbt_busprobe.php");
require_once("../models/t_sbt_busdia_actual.php");
require_once("../models/t_sbt_bus_current_route.php");
require_once("../models/t_sbt_shift_pattern_list.php");

// ### Class Definition -----------------------------------------------------------------
class subtourReceiveProbe extends BaseApi {

    protected function main() {
        // 識別名確認
        if (strcmp($_POST["mark"], MARK) != 0) { 
            $this->exitSystemErr("Mark is Invalid!(".$_POST["mark"].")");
        }

        $carid = $_POST["carid"];
        $shiftcd = $_POST["shiftcd"];
        $json = $_POST["data"];
        $json = preg_replace("/".preg_quote("\\")."/", "", $json);
        $dataArr = json_decode($json, true);
        if (!is_array($dataArr)) {
            $this->exitSystemErr("JSON data is not Array!");
        }
        $isUnkou = 0;

        // 現在のステータスを取得
        $this->get_status($areacd, $routecd, $diano, $bscd, $junno, $link, $carid);

        // ステータスに残っているゴミデータのクリア
        $this->clearOldCurrentRoute($shiftcd, $carid, $diano);

        // 現在のステータスを取得(ゴミデータが入っていた場合の対応)
        $this->get_status($areacd, $routecd, $diano, $bscd, $junno, $link, $carid);

        // プローブ情報のINSERT
        for ($i = 0; $i < count($dataArr); $i++) {
            $data = $dataArr[$i];
            $data["device_id"] = $carid;
            $data["buscompany_id"] = BUSCOMPANY_ID;
            $data["buscategory_cd"] = $areacd;
            $data["course_id"] = $routecd;
            $data["bin_no"] = $diano;
            $data["busstop_id"] = 0;
            $args = array($data);
            $this->db->invoke('t_sbt_busprobe', 'insert', $args);
        }
        $this->db->commit();

        if ($areacd == 0 && $routecd == 0 && $diano == 0) { // 路線判定結果なしor回送中
            // 現在時刻付近のダイアの始発バス停に対して到着or待機中判定
            $cmd = "./DecideRosen.exe $carid $junno $shiftcd";
            exec($cmd, $arr, $res);
//            $this->logger->writeDebug($cmd);
//            $this->logger->writeDebug($arr);
//            $this->logger->writeDebug($res);
            if($res != 0 && $res != 2 ){
                $this->exitSystemErr("DecideRosen Error:<< ".$arr[0].",".$arr[1].",".$arr[2].",".$arr[3].",".$res);
            }
        } else { // 路線走行中
            // 走行路線中の現在の最終通過バス停以降のバス停に対して到着判定
            $cmd = "./PassingBusStop.exe $carid $areacd $routecd $diano $junno $bscd";
            exec($cmd, $arr, $res);
//            $this->logger->writeDebug($cmd);
//            $this->logger->writeDebug($arr);
//            $this->logger->writeDebug($res);
            if ($res == 1) { // Error
                $this->exitSystemErr("PassingBusStop Error:<< ".$arr[0].":".$arr[1].",".$res);
            }

            // 運行実績を記録した場合は$arr[0]=1となる
            if (isset($arr[0]) && strcmp($arr[0], "1") == 0) {
$this->logger->writeDebug("@@@@@@@@@@@@@@@@@@@ 補完作業開始 @@@@@@@@@@@@@@@@@@@@");
                // 指定便のダイヤ情報を取得する(停車順に格納)
                $args = array(BUSCOMPANY_ID, $diano);
                $diaInfo = $this->db->invoke('t_sbt_busdia', 'getDiaInfo', $args);
                // 運行情報を補完する
                $args = array(BUSCOMPANY_ID, date('Y-m-d'), $diano, $diaInfo);
                $this->db->resetConnection();
                $this->db->invoke('t_sbt_busdia_actual', 'completionActual', $args);
                $this->db->commit();
$this->logger->writeDebug("@@@@@@@@@@@@@@@@@@@ 補完完了 @@@@@@@@@@@@@@@@@@@@@@");
            }

            // 終着バス停ならば再度路線判定を実施
            if ($res == 2) {
                unset($cmd, $arr, $res);
                $cmd = "./DecideRosen.exe $carid $junno $shiftcd";
                exec($cmd, $arr, $res);
                if($res != 0 && $res != 2 ){
                    $this->exitSystemErr("DecideRosen Error:<< ".$arr[0].",".$arr[1].",".$arr[2].",".$arr[3].",".$res);
                }
            }
        }

        //ステータス情報再取得
        $this->get_status($areacd, $routecd, $diano, $bscd, $junno, $link, $carid);

        if ($junno == 0){
            $isUnkou = 0;
        } else {
            $isUnkou = 1;
        }

        // 判定ロジックより得た路線情報をJSONデータに
        // 分類名を取得
        $args = array(BUSCOMPANY_ID, $areacd);
        $areaName = $this->db->invoke('t_sbt_buscategory', 'getCategoryName', $args);
        // 路線名
        $args = array(BUSCOMPANY_ID, $areacd, $routecd);
        $routeName = $this->db->invoke('t_sbt_busdia', 'getCourseName', $args);
        // 付帯情報
        $args = array(BUSCOMPANY_ID, $diano);
        $firstBusStopData = $this->db->invoke('t_sbt_busdia', 'getFirstBusStopData', $args);
        if (mysqli_num_rows($firstBusStopData) == 0) $other = "-";
        else $other = $firstBusStopData["busstop_name"]."(".$firstBusStopData["dia_time"].")";

        $jsonArr = array(
            "status"	=> $isUnkou, // 1:路線走行中、2:回送中
            "route"	    => array(
                "area"		=> $areaName, // エリア名もしくはバス名(レターバスかキャロッピー号)
                "route"		=> $routeName, // 路線名
                "diaNo"		=> $diano, // 便
                "other"		=> $other // 補足情報(始発バス停(始発時刻)や次のバス停等)
            )
        );

        return $jsonArr;
    }

    /**
     * バスのステータスを取得
     */
    private function get_status(&$areacd, &$routecd, &$diano, &$bscd, &$junno, $link, $carid){
        // バスのステータスを取得
        $args = array($carid, BUSCOMPANY_ID);
        $busStatus = $this->db->invoke('t_sbt_bus_current_route', 'getBusStatus', $args);
        if (empty($busStatus)) {
            $areacd = 0;
            $routecd = 0;
            $diano = 0;
            $bscd = 0;
            $junno = 0;
            $args = array($carid, BUSCOMPANY_ID);
            $this->db->invoke('t_sbt_bus_current_route', 'updateBusStatus', $args);
        } else {
            $areacd = $busStatus["buscategory_cd"];
            $routecd = $busStatus["course_id"];
            $diano = $busStatus["bin_no"];
            $bscd = $busStatus["busstop_id"];
            $junno = $busStatus["stop_seq"];
        }
    } 

    /**
     * バスのステータスのゴミデータのクリア
     * 終点まで行かなかったバスへの対策
     */
    private function clearOldCurrentRoute($shift_pattern_cd, $device_id, $bin_no) {
        // 仕業コード、便番号から次の便の始発時刻を取得
        $args = array(BUSCOMPANY_ID, $shift_pattern_cd, $bin_no);
        $firstDiaTime = $this->db->invoke('t_sbt_shift_pattern_list', 'getNextFirstDiaTime', $args);

        if ($firstDiaTime != 0) { // 仕業の最終便以外
            // 次の便の始発時刻2分前を過ぎていたら
            if (Util::cmpTime(date('H:i', strtotime('+2 minute')), $firstDiaTime) >= 0) {
                // 最後にバス停を通過した時刻からBORDER_ELAPSE_TIME以上経過しているか
                $args = array(BUSCOMPANY_ID, $bin_no, date('Y-m-d'), BORDER_ELAPSE_TIME);
                if ($this->db->invoke('t_sbt_busdia_actual', 'checkBorderElapseTime', $args)) {
$this->logger->writeDebug("@@@@@@@@@@@@@@@@@@@ クリア開始 @@@@@@@@@@@@@@@@@@@@");
                    // ステータスをクリア
                    $args = array($device_id, BUSCOMPANY_ID);
                    $this->db->invoke('t_sbt_bus_current_route', 'updateBusStatus', $args);
                    // 指定便のダイヤ情報を取得する(停車順に格納)
                    $args = array(BUSCOMPANY_ID, $bin_no);
                    $diaInfo = $this->db->invoke('t_sbt_busdia', 'getDiaInfo', $args);
                    // 運行実績に最終バス停を記録(補完はしない)
                    $args = array(BUSCOMPANY_ID, $bin_no, $diaInfo[count($diaInfo)-1]["busstop_id"], $diaInfo[count($diaInfo)-1]["dia_time"], date('H:i:s'), 3, $diaInfo[count($diaInfo)-1]["stop_seq"]);
                    $this->db->invoke('t_sbt_busdia_actual', 'insert', $args);
                    $this->db->commit();
                    $this->db->resetConnection();
$this->logger->writeDebug("@@@@@@@@@@@@@@@@@@@ クリア完了 @@@@@@@@@@@@@@@@@@@@");
                }
            }
        } else { // 仕業の最終便
            // 現段階では考慮なし(2016/1/1)
        }
    }
}

// ### Run Process ---------------------------------------------------------------------
$class = new subtourReceiveProbe();
$class->run();

