<?php

require_once("../base/base/BaseApi.php");
require_once("../models/t_sbt_route_course.php");

// ### Class Definition -----------------------------------------------------------------
class getCourseList extends BaseApi {

    protected function main() {

        // コース一覧の取得
        $courseList = $this->db->invoke('t_sbt_route_course', 'getCourseList', array(BUSCOMPANY_ID, BUSCATEGORY_CD));
        if (count($courseList) == 0) exit("{\"status\":0}");

        $data = array(
	        "status"	=> 0,
	        "route"		=> $courseList
        );

        return $data;
    }
}

// ### Run Process ---------------------------------------------------------------------
$class = new getCourseList();
$class->run();

