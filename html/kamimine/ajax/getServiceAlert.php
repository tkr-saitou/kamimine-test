<?php

require_once("../base/base/BaseApi.php");
require_once("../models/t_sbt_busdia_actual.php");

// ### Class Definition -----------------------------------------------------------------
class getServiceAlert extends BaseApi {

    protected function main() {

        // 警告一覧を取得
        $args = array(BUSCOMPANY_ID, date('Y-m-d H:i:s'));
        $alertShiftList = $this->db->invoke('t_sbt_busdia_actual', 'getAlertShiftList', $args);
        if (count($alertShiftList) == 0) exit("{\"status\":0}");
        $data = array(
            "status"	=> 1,
            "data"	    => $alertShiftList
        );

        return $data;
    }
}

// ### Run Process ---------------------------------------------------------------------
$class = new getServiceAlert();
$class->run();

