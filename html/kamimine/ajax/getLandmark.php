<?php

require_once("../base/base/BaseApi.php");
require_once("../models/t_sbt_landmark.php");

// ### Class Definition -----------------------------------------------------------------
class getLandmark extends BaseApi {

    protected function main() {
        $buscategory_cd = $_POST["areaCd"];
        $course_id = $_POST["routeCd"];

        // 主要施設一覧を取得
        $args = array(BUSCOMPANY_ID, $buscategory_cd, $course_id);
        $landmarkList = $this->db->invoke('t_sbt_landmark', 'getLandmarkList', $args);
        if (count($landmarkList) == 0) exit("{\"status\":0, \"landmark\":[]}");

        $data = array(
            "status"	=> 0,
            "landmark"	=> $landmarkList
        );

        return $data;
    }
}

// ### Run Process ---------------------------------------------------------------------
$class = new getLandmark();
$class->run();

