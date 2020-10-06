<?php

require_once("../base/base/BaseApi.php");
require_once("../models/t_sbt_busdia_actual.php");

// ### Class Definition -----------------------------------------------------------------
class getServiceTable extends BaseApi {

    protected function main() {
        $date = $_POST['date'];
        $course_id = $_POST['course_id'];
        $shift_pattern_cd = $_POST['shift_pattern_cd'];

        // 仕業一覧の取得
        $args = array(BUSCOMPANY_ID, $date, $course_id, $shift_pattern_cd);
        $table = $this->db->invoke('t_sbt_busdia_actual', 'getServiceTable', $args);
        if (count($table) == 0) exit("{\"status\":1}");

        $data = array(
	        "status"	=> 0,
	        "data"		=> $table
        );

        return $data;
    }
}

// ### Run Process ---------------------------------------------------------------------
$class = new getServiceTable();
$class->run();

