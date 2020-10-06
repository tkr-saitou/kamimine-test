<?php

require_once("../base/base/BaseApi.php");
require_once("../models/t_sbt_shift_pattern_list.php");

// ### Class Definition -----------------------------------------------------------------
class getShiftList extends BaseApi {

    protected function main() {
        $date = $_POST['date'];
        $course_id = $_POST['course_id'];

        // 仕業一覧の取得
        if (empty($date) || empty($course_id)) {
            $args = array(BUSCOMPANY_ID, BUSCATEGORY_CD);
            $shiftList = $this->db->invoke('t_sbt_shift_pattern_list', 'getShiftList', $args);
        } else {
            $args = array(BUSCOMPANY_ID, BUSCATEGORY_CD, $date, $course_id);
            $shiftList = $this->db->invoke('t_sbt_shift_pattern_list', 'getShiftListByDateCourseId', $args);
        }
        if (count($shiftList) == 0) exit("{\"status\":0}");

        $data = array(
	        "status"	=> 0,
	        "data"		=> $shiftList
        );

        return $data;
    }
}

// ### Run Process ---------------------------------------------------------------------
$class = new getShiftList();
$class->run();

