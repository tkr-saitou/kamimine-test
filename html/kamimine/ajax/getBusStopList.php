<?php

require_once("../base/base/BaseApi.php");
require_once("../models/t_sbt_busstop.php");

// ### Class Definition -----------------------------------------------------------------
class getBusStopList extends BaseApi {

    protected function main() {
        $buscategory_cd = $_POST["areaCd"];
        $course_id = $_POST["routeCd"];

        $args = array(BUSCOMPANY_ID, $buscategory_cd, $course_id);
        $busStopList = $this->db->invoke('t_sbt_busstop', 'getBusStopList', $args);
        if (count($busStopList) == 0) exit("{\"status\":0, \"busstop\":[]}");

        $data = array(
	        "status"	=> 0,
	        "busstop"	=> $busStopList
        );
        // if ($busstop_id != 0) {
        //     $data = t_sbt_busdia::findAllByDepartureOnRosen($buscompany_id, $busstop_id, $course_id);
        // }
$this->logger->writeDebug($busStopList);
        return $data;
    }
}

// ### Run Process ---------------------------------------------------------------------
$class = new getBusStopList();
$class->run();

