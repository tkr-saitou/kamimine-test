<?php

require_once("../base/base/BaseApi.php");
require_once("../models/t_sbt_search_history.php");

// ### Class Definition -----------------------------------------------------------------
class getSearchRanking extends BaseApi {

    protected function main() {
        $ret = array("status" => 0, "data" => array());
        $from = $_POST["from"];
        $to = $_POST["to"];

        // 分析対象データを取得
        $args = array(BUSCOMPANY_ID, $from, $to);
        $ranking = $this->db->invoke('t_sbt_search_history', 'getSearchRanking', $args);
$this->logger->writeDebug($ranking);
        // ランキング下位をまとめる処理
        $data = array();
        foreach ($ranking as $rank => $row) {
            if ($rank < RANK_MAX) {
                $data[] = array(
                    "busstop_id"    => $row["busstop_id"],
                    "busstop_name"  => $row["busstop_name"],
                    "count"         => $row["count"]
                );
            } else {
                if (!isset($data[RANK_MAX])) {
                    $data[RANK_MAX] = array(
                        "busstop_id"    => "others",
                        "busstop_name"  => "その他",
                        "count"         => $row["count"]
                    );
                } else {
                    $data[RANK_MAX]["count"] += $row["count"];
                }
            }
        }
        $ret["data"] = $data;

        // D3スケール用データの作成
        $ret["minXScale"] = 0;
        $ret["maxXScale"] = $data[0]["count"];
        if ($data[0]["count"] < $data[count($data) - 1]["count"]) {
            $ret["maxXScale"] = $data[count($data) - 1]["count"];
        }
        $ret["minYScale"] = 0;
        $ret["maxYScale"] = count($data);

        return $ret;
    }

}

// ### Run Process ---------------------------------------------------------------------
$class = new getSearchRanking();
$class->run();

