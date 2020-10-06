<?php

require_once("../base/base/BaseApi.php");
require_once("../models/t_sbt_search_history.php");

// ### Class Definition -----------------------------------------------------------------
class getSearchHistory extends BaseApi {

    protected function main() {
        $ret = array("status" => 0, "data" => array());
        $from = $_POST["from"];
        $to = $_POST["to"];

        // 分析対象データを取得
        $args = array(BUSCOMPANY_ID, $from, $to, $EXCEPT_IP_ADDRESS);
        $history = $this->db->invoke('t_sbt_search_history', 'getSearchHistory', $args);

        // 積み上げ面グラフを作成するためのデータ整形
        $devicePattern = array('Android', 'Mac', 'U', 'Windows', 'iPad', 'iPhone');
        $data = array();
        $tmpDate = 0;
        $preDevice = NULL;
        foreach ($history as $row) {
            // 全デバイスデータの0初期化
            if (strcmp($tmpDate, $row["date"]) != 0) {
                $tmpDate = $row["date"];
                $this->completionDevice($data, $devicePattern, $row["date"]);
            }
            // 検索履歴データに更新
            if (!is_null($row["device"])) {
                $data[$row["device"]][$row["date"]] = array(
                    "date"  => $row["date"],
                    "count" => $row["count"]
                );
            }
        }

        // 積み上げるためのデータ整形
        $maxCount = 0;
        foreach ($devicePattern as $key => $device) {
            if ($key != 0) {
                foreach ($data[$device] as $date => $row) {
                    $preDevice = $devicePattern[$key - 1];
                    $data[$device][$date]["count"] += $data[$preDevice][$date]["count"];
                    // 最大件数の取得
                    if ($maxCount < $data[$device][$date]["count"]) {
                        $maxCount = $data[$device][$date]["count"];
                    }
                }
            }
        }

        $ret["data"] = $data;

        // D3スケール用データの作成
        $ret["minXScale"] = $from;
        $ret["maxXScale"] = $to;
        $ret["minYScale"] = 0;
        $ret["maxYScale"] = $maxCount;
        $ret["devicePattern"] = array_reverse($devicePattern);
        $ret["color"] = array('#ff7f7f', '#7f7fff', '#ffbf7f', '#7fff7f', '#ff7fff', '#ffff7f');

        return $ret;
    }

    private function completionDevice(&$data, $devicePattern, $date) {
        foreach ($devicePattern as $device) {
            $data[$device][$date] = array(
                "date"  => $date,
                "count" => 0
            );
        }
    }
}

// ### Run Process ---------------------------------------------------------------------
$class = new getSearchHistory();
$class->run();

