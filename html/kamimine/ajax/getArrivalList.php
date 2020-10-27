<?php

require_once("../base/base/BaseApi.php");
require_once("../models/t_sbt_busstop.php");

// ### Class Definition -----------------------------------------------------------------
class getArrivalList extends BaseApi {

    protected function main() {
        // var_dump("aaaaa");
        $busstop_id = $_POST["busstopId"];
        $course_id = $_POST["courseId"];

        // バス停一覧取得
        $args = array(BUSCOMPANY_ID, $busstop_id, $course_id);
        $busStopList = $this->db->invoke('t_sbt_busstop', 'findAllByDepartureOnRosen', $args);
        if (count($busStopList) == 0) exit("{\"status\":0, \"busstop\":[]}");

        $data = array(
	        "status"	=> 0,
	        "busstop"	=> $busStopList
        );
        return $data;
    }
}

// ### Run Process ---------------------------------------------------------------------
$class = new getArrivalList();
$class->run();