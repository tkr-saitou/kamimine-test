<?php

require_once("../base/base/BaseApi.php");
require_once("../models/t_sbt_busstop.php");

// ### Class Definition -----------------------------------------------------------------
class getBusStopId8DigitList extends BaseApi {

    protected function main() {
        $buscategory_cd = $_POST["buscategory_cd"];
        $course_id = $_POST["course_id"];
        $departure = $_POST["fromBS"];
        $arrival = $_POST["toBS"];
        $orientation = $_POST["orientation"];

        // バス停取得
        $args = array(BUSCOMPANY_ID, $buscategory_cd, $course_id, $departure, $arrival, $orientation);
        $this->logger->writeDebug("################koooo##############");
        $this->logger->writeDebug($args);
        $this->logger->writeDebug("################oooo##############");
        $busStopSelectList = $this->db->invoke('t_sbt_busstop', 'getBusStopId8DigitList', $args);
        if (count($busStopSelectList) == 0) exit("{\"status\":0, \"busstop\":[]}");

        $data = array(
	        "status"	=> 0,
	        "busstop"	=> $busStopSelectList
        );

        return $data;
    }
}

// ### Run Process ---------------------------------------------------------------------
$class = new getBusStopId8DigitList();
$class->run();

