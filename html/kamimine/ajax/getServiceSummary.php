<?php

require_once("../base/base/BaseApi.php");
require_once("../models/t_sbt_busbin.php");
require_once("../models/t_sbt_busdia_actual.php");

// ### Class Definition -----------------------------------------------------------------
class getServiceSummary extends BaseApi {

    protected function main() {

        // 本日のコース一覧の取得
        $args = array(BUSCOMPANY_ID, 'ja');
        $courseList = $this->db->invoke('t_sbt_busbin', 'getCourseList', $args);

        // 運行状況の取得
        $ServiceTable = array();
        $ServiceStatus = array();
        foreach ($courseList as $row) {
            $args = array(BUSCOMPANY_ID, date('Y-m-d'), $row['course_id'], 0);
            $ServiceTable[$row['course_id']] = $this->db->invoke('t_sbt_busdia_actual', 'getServiceTable', $args);
            // 運行状況集計用配列の初期化
            $ServiceStatus[$row['course_id']] = array(
                'plan'          => 0, // 運行予定
                'mid'           => 0, // 運行中
                'comp'          => 0, // 運行完了
                'unknown'       => 0, // GPS情報が取得できていない
                'course_name'   => $row['course_name']
            );
        }
        if (count($ServiceTable) == 0) exit("{\"status\":1}");

        // 運行状況を集計
        foreach ($ServiceTable as $courseId => $courseSum) {
            // 運行状況集計
            foreach ($courseSum as $row) {
                if (is_null($row['busstop_id'])) {
                    if ($row['is_plan']) {
                        // 運行予定
                        $ServiceStatus[$courseId]['plan']++;
                    } else {
                        // GPS情報が取得できていない
                        $ServiceStatus[$courseId]['unknown']++;
                    }
                } else if (strcmp($row['busstop_id'], $row['to_busstop_id']) == 0) {
                    // 運行完了
                    $ServiceStatus[$courseId]['comp']++;
                } else {
                    // 運行中
                    $ServiceStatus[$courseId]['mid']++;
                }
            }
        }

        $data = array(
	        "status"	=> 0,
	        "data"		=> $ServiceStatus
        );

        return $data;
    }
}

// ### Run Process ---------------------------------------------------------------------
$class = new getServiceSummary();
$class->run();

