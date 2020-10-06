<?php

require_once("../base/base/BaseApi.php");
require_once("../models/t_sbt_busstop.php");
require_once("../models/t_sbt_busdia.php");
require_once("../models/t_sbt_busdia_actual.php");
require_once("../models/t_sbt_search_history.php");
require_once("../models/t_sbt_route_course.php");
require_once("../models/t_sbt_busbin.php");

// ### Class Definition -----------------------------------------------------------------
class searchBus extends BaseApi {

    protected function main() {
        $return = array("status" => 0, "ret" => array(), "course_list" => array());
        $buscategory_cd = $_POST["buscategory_cd"];
        $course_id = $_POST["course_id"];
        $signage_enable = (array_key_exists('signage_enable', $_POST) && $_POST['signage_enable'] == 1);

        // 分類が未指定
        if ($buscategory_cd == 0) $this->exitSystemErr("分類未指定エラー");

        // 出発バス停
        if ($_POST["fromBsCd"] != 0) { // バス停から検索
            if ($_POST["fromBsCd"] == CURRENT_POS) { // 現在地
                $args = array($_POST["currentLat"], $_POST["currentLng"]);
                $busstopList = $this->db->invoke('t_sbt_busstop', 'getNearBusstopList', $args);
                $course_id = 0; // コース指定は無視する
                if (empty($busstopList)) { // 現在地付近にバス停がない場合
                    $return["errMsg"] = "現在地付近にバス停がありません";
                    $return["status"] = 1;
                    return $return;
                } 
            } else {
                $busstopList = array(0 => array("fromBsCd" => $_POST["fromBsCd"]));
            }
        } else if ($_POST["fromLmCd"] != 0) { // 主要施設から検索
            $busstopList = array(0 => array("fromBsCd" => $_POST["fromLmCd"]));
        } else { // 出発地が未指定(JSのチェックをすり抜けた場合)
            $this->exitSystemErr("出発地未指定エラー");
        }

        // 遅れ判定用フラグ
        // 到着バス停が未指定の場合は終着バス停をセットするようになったため、
        // フラグは下記の2パターンのみ
        // 1: 出発・到着のみ指定
        // 2: 全指定(路線・出発・到着)
        $flg = 2;
        
        // 到着バス停未指定フラグ
        $toBsUnsetFlg = 0;

        // 到着バス停
        if ($_POST["toBsCd"] != 0) {
            foreach ($busstopList as $key => $busstop) {
                $busstopList[$key]["toBsCd"] = array(0 => 
                    array(
                        "busstop_id"    => $_POST["toBsCd"],
                        "course_id"     => 0
                    ));
            }
            if ($course_id == 0) $flg = 1;
        } else if ($_POST["toLmCd"] != 0) {
            foreach ($busstopList as $key => $busstop) {
                $busstopList[$key]["toBsCd"] = array(0 => 
                    array(
                        "busstop_id"    => $_POST["toLmCd"],
                        "course_id"     => 0
                    ));
            }
            if ($course_id == 0) $flg = 1;
        } else {
            $toBsUnsetFlg = 1;
            // 到着バス停が指定されなかった場合は終着バス停を取得する
            foreach ($busstopList as $key => $busstop) {
                $args = array(BUSCOMPANY_ID, $busstop["fromBsCd"], $course_id);
                $busstopList[$key]["toBsCd"] = $this->db->invoke('t_sbt_busstop', 'getLastBusstopList', $args);
            }
        }

        $resultSum = array("n" => 0, "results" => array());
        foreach ((array)$busstopList as $key => $busstop) {
            $frombscd = $busstop["fromBsCd"];
            foreach ((array)$busstop["toBsCd"] as $tobsInfo) {
                $tobscd = $tobsInfo["busstop_id"];
                if ($course_id == 0) { // コース未指定の場合
                    $courseId = $tobsInfo["course_id"];
                } else { // コース指定の場合
                    $courseId = $course_id;
                }
                $cmd = "../cgi/CalcDelayTime.exe $flg $frombscd $tobscd $buscategory_cd $courseId";
                $arr = NULL;
                $res = 0;
                exec($cmd, $arr, $res);
//                $this->logger->writeDebug($cmd);
//                $this->logger->writeDebug($arr);
//                $this->logger->writeDebug($res);

                if ($res > 0) { // 正常終了(res: 検索結果件数)
                    //
                    // $arr[0]: 検索結果(JSON形式)
                    //
                    $result = json_decode($arr[0], true);
                    $resultSum["n"] += $result["n"];
                    $resultSum["results"] = array_merge($resultSum["results"], $result["results"]);
                } else { // EXE内のエラー
                    //
                    // $arr[0]: エラーコード(0:検索結果0件, 1:業務エラー, 2:システムエラー)
                    // $arr[1]: エラーメッセージ
                    // $arr[2]: SQL(Query結果が0件でエラーになった場合のみ存在)
                    //
                    if ($arr[0] == 0) { // 検索結果0件
                        // 検索履歴保存のためreturnしない
                        continue;
                    } else if ($arr[0] == 1) { // 業務エラー
                        $return["status"] = 1;
                        $return["errMsg"] = $arr[1];
                        return $return;
                    } else if ($arr[0] == 2) { // システムエラー
                        $return["status"] = 1;
                        $this->exitSystemErr($arr[1]);
                    }
                }
            }
        }

        // 到着バス停が未指定の場合、検索結果の到着バス停が終点ではないレコードを排除する
        if ($toBsUnsetFlg == 1) {
            $tmpResults = array();
            foreach ($resultSum["results"] as $row) {
                $args = array(BUSCOMPANY_ID, $row["diano"], $row["bscd_to"]);
                if ($this->db->invoke("t_sbt_busdia", "checkLastBusstop", $args)) {
                    $tmpResults[] = $row;
                }
            }
            $resultSum["results"] = $tmpResults;
        }

        if (empty($resultSum["results"])) {
            // ここに入るのは以下の2パターン
            // 1. 全ての検索結果が０件だった時
            // 2. 出発バス停のみ指定時に、運行が終了していて終着バス停が取得できなかった時
            $return["errMsg"] = "検索結果は０件です。";
            $return["status"] = 1;
        } else {
            // 系統名、便詳細、色、ダイヤ時刻を追加
            foreach ($resultSum["results"] as $key => $result) {
                $course_id = $result["syscd"];
                $args = array(BUSCOMPANY_ID, $course_id);
                $courseInfo = $this->db->invoke("t_sbt_route_course", "getCourseInfo", $args);
                // 系統名
                $resultSum["results"][$key]["route_name"] = $courseInfo["route_name"];
                // 便詳細
                $args = array(BUSCOMPANY_ID, $result["diano"]);
                $binDetail = $this->db->invoke("t_sbt_busbin", "getBinDetail", $args);
                $resultSum["results"][$key]["busbin_detail_name"] = $binDetail["busbin_detail_name"];
                $resultSum["results"][$key]["except_delay_flg"] = $binDetail["except_delay_flg"];
                // 色
                $resultSum["results"][$key]["route_color"] = $courseInfo["route_color"];
                // ダイヤ時刻(バス停IDだと同コースに同バス停IDが複数あるとNGなのでシーケンス番号を使う)
                if (isset($result["stop_seq_from"])) {
                    $args = array(BUSCOMPANY_ID, $result["diano"], $result["stop_seq_from"]);
                    $resultSum["results"][$key]["from_dia_time"] = $this->db->invoke("t_sbt_busdia", "getDiaTime", $args);
                } else {
                    $args = array(BUSCOMPANY_ID, $result["diano"], $result["bscd_from"]);
                    $resultSum["results"][$key]["from_dia_time"] = $this->db->invoke("t_sbt_busdia", "getDiaTimefromBsCd8", $args);
                }
                if (isset($result["stop_seq_to"])) {
                    $args = array(BUSCOMPANY_ID, $result["diano"], $result["stop_seq_to"]);
                    $resultSum["results"][$key]["to_dia_time"] = $this->db->invoke("t_sbt_busdia", "getDiaTime", $args);
                } else {
                    $args = array(BUSCOMPANY_ID, $result["diano"], $result["bscd_to"]);
                    $resultSum["results"][$key]["to_dia_time"] = $this->db->invoke("t_sbt_busdia", "getDiaTimefromBsCd8", $args);
                }
                if (!in_array($course_id, $return["course_list"])) $return["course_list"][] = $course_id;
            }
            // 出発時刻順でソート
            $sortedResultSum = array();
            while (!empty($resultSum["results"])) {
                $minTime = "23:59:59";
                $minKey = 0;
                foreach ($resultSum["results"] as $key => $result) {
                    if (Util::cmpTime($result["from"], $minTime) == -1) { // $result->from < $tmpTime
                        $minTime = $result["from"];
                        $minKey = $key;
                    }
                }
                $sortedResultSum[] = $resultSum["results"][$minKey];
                unset($resultSum["results"][$minKey]);
            }
            $resultSum["results"] = $sortedResultSum;

            //company_id,busstop_idを付ける
            $company_id = BUSCOMPANY_ID;
            $busstop_id = $this->db->invoke('t_sbt_busstop', 'getBusstopID', array(BUSCOMPANY_ID, $resultSum["results"][0]['bscd_from'],$resultSum["results"][0]['syscd']));

            //会社IDとバス停IDを格納
            foreach ($resultSum["results"] as $key => $result) {
                $resultSum["results"][$key]["buscompany_id"] = $company_id;
                $resultSum["results"][$key]["busstop_id"] = $busstop_id;
            }

            //運行情報を格納
            $today = date("Y-m-d");
//            $ybkbn = $this->db->invoke('t_sbt_busdia', 'getDays', array($company_id, $today));
            foreach ($resultSum["results"] as $key => $result) {
//                $data['dia'] = $this->db->invoke('t_sbt_busdia', 'getBindia', array($result['busstop_id'], $result['diano'], $ybkbn['ybkbn'], "ja"));
                $data['dia'] = $this->db->invoke('t_sbt_busdia', 'getBindia', array(BUSCOMPANY_ID, $result['busstop_id'], $result['diano'], "ja"));
                $place = $this->db->invoke('t_sbt_busdia_actual', 'getDiaCondition', array($today, $result['buscompany_id'], $result['diano']));
                if(empty($place)){
                    //バスが出発していないとき
                    $resultSum['results'][$key]['flg'] = 0;
                }else{
                    $resultSum['results'][$key]['counter'] = 0;
                    foreach($data['dia'] as $i => $dia) {
                        //自分の検索するバス停に対する処理
                        if(empty($place[$i])){
                            $resultSum['results'][$key]['flg'] = 1;
                            $resultSum['results'][$key]['counter'] = $resultSum['results'][$key]['counter'] + 1;
                        }
                        //バス停IDだと同コースに同バス停IDが複数あるとNGなのでシーケンス番号を使う
                        if (isset($result["stop_seq_from"])) {
                            $hit = ($dia['stop_seq'] == $result['stop_seq_from']);
                        } else {
                            $hit = ($dia['busstop_id'] == $busstop_id);
                        }
                        if($hit) {
                            //出発しているとき
                            if($resultSum['results'][$key]['flg'] == 0){
                                $resultSum['results'][$key]['flg'] = 2;
                                break 1;
                            //～駅前を出発しているとき
                            }else if($resultSum['results'][$key]['flg'] == 1){
                                break 1;
                            }
                        }
                    }
                }
            }
        }
        $return["ret"] = $resultSum;

        // デジタルサイネージからのアクセスの場合は検索履歴に保存しない
        if (!$signage_enable) {
            // 検索履歴保存
            // 検索条件に指定された条件のみを記録する(補完情報は含まない)
            $this->insertSearchHistory($_POST["course_id"], $_POST["fromBsCd"], $_POST["toBsCd"], $resultSum["n"]);
        }

        $this->db->commit();
        return $return;
    }

    /**
     * 検索履歴テーブルへINSERT
     */
    private function insertSearchHistory($course_id, $frombscd, $tobscd, $cnt) {
        // 0 -> NULL
        if ($course_id == 0) $course_id = NULL;
        if ($frombscd == 0) $frombscd = NULL;
        if ($tobscd == 0) $tobscd = NULL;

        // デバイス, ブラウザ
        $device_browser = Util::getDeviceBrowserName($_SERVER["HTTP_USER_AGENT"]);

        // 空チェック
        if ($course_id == 0) $course_id = NULL; 
        if ($tobscd == 0) $tobscd = NULL; 

        $args = array(
            date('Y-m-d G:i:s'), // 検索年月日時分秒
            $_SERVER["REMOTE_ADDR"], // IPアドレス
            $device_browser['device'],
            $device_browser['browser'],
            get_class($this),
            BUSCOMPANY_ID,
            $course_id,
            $frombscd,
            $tobscd,
            $cnt // 検索件数
        );

        // INSERT実行
        $this->db->invoke('t_sbt_search_history', 'recordSearchHistory', $args);

    }

}

// ### Run Process ---------------------------------------------------------------------
$class = new searchBus();
$class->run();
