<?php

require_once("../base/base/BaseApi.php");
require_once("../models/t_sbt_busdia_actual.php");
require_once("../models/t_sbt_busdia.php");
require_once("../models/t_sbt_busstop.php");

// ### Class Definition -----------------------------------------------------------------
class getServiceAnalysisData extends BaseApi {

    protected function main() {
        $ret = array("status" => 0, "data" => array());
        $date = $_POST["date"];
        $course_id = $_POST["course_id"];
        $bin_no = $_POST["bin_no"];

        // 分析対象データを取得
        $args = array($date, BUSCOMPANY_ID, $course_id, $bin_no);
        $analysisData = $this->db->invoke('t_sbt_busdia_actual', 'getServiceAnalysisData', $args);
        if (count($analysisData) == 0) { // 分析対象データが存在しない場合
            $ret["status"] = 1;
            return $ret;
        }

        // D3の描画に必要なデータを整える
        $result = array("data" => $analysisData);
        $paddingMinutes = 5;                // X軸の余白時間(単位：分)
        $maxIdx = count($analysisData) - 1; // 分析データの最大インデックス

        // Yスケール用の軸の両極
        $result["minYScale"] = $date." ".$analysisData[0]["dia_time"];
        $result["maxYScale"] = $date." ".$analysisData[$maxIdx]["dia_time"];

        // Xスケール用の軸の両極
//        // X軸の最小値は入力データの最小値－paddingMinutes分
//        if ($analysisData[0]["real_time"] < $analysisData[0]["dia_time"]) {
//            // 最初の便の始発バス停においてダイヤ時刻より運行実績が早い時は運行データを使用
//            $result["minXScale"] = date("Y/m/d H:i:s", strtotime($date." ".$analysisData[0]["real_time"]."-".$paddingMinutes." minute"));
//        } else {
//            $result["minXScale"] = date("Y/m/d H:i:s", strtotime($date." ".$analysisData[0]["dia_time"]."-".$paddingMinutes." minute"));
//        }
        // X軸の最小値はダイヤの最小値－paddingMinutes分
        $result["minXScale"] = date("Y/m/d H:i:s", strtotime($date." ".$analysisData[0]["dia_time"]."-".$paddingMinutes." minute"));
        // X軸の最大値は入力データの最大値＋paddingMinutes分
        if ($analysisData[$maxIdx]["dia_time"] < $analysisData[$maxIdx]["real_time"]) {
            // 最後の便の終着バス停においてダイヤ時刻より運行実績が遅い時は運行データを使用
            $result["maxXScale"] = date("Y/m/d H:i:s", strtotime($date." ".$analysisData[$maxIdx]["real_time"]."+".$paddingMinutes." minute"));
        } else {
            $result["maxXScale"] = date("Y/m/d H:i:s", strtotime($date." ".$analysisData[$maxIdx]["dia_time"]."+".$paddingMinutes." minute"));
        }

        // 実績の記録種別に3が含まれる場合、警告を表示する
        $result["warning"] = "";
        if ($analysisData[$maxIdx]["reg_type"] == 3) {
            $result["warning"] = $analysisData[$maxIdx - 1]["busstop_name"]." 以降は、バスの位置情報が正しく取得できていない箇所が含まれている可能性があります。";
        }

        $ret = array(
	        "status"	=> 0,
	        "data"		=> $result
        );

        return $ret;
    }
}

// ### Run Process ---------------------------------------------------------------------
$class = new getServiceAnalysisData();
$class->run();

