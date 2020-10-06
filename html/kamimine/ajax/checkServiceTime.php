<?php

require_once("../base/base/BaseApi.php");
require_once("../models/t_sbt_busdia.php");
require_once("../models/t_sbt_bus_current_route.php");

// ### Class Definition -----------------------------------------------------------------
class checkServiceTime extends BaseApi {

    protected function main() {

        // 本日のダイヤの始発・最終時刻を取得
        $max = $this->db->invoke('t_sbt_busdia', 'getLastDiaTime',array(BUSCOMPANY_ID));
        $min = $this->db->invoke('t_sbt_busdia', 'getFirstDiaTime',array(BUSCOMPANY_ID));

        $now = date("H:i:s");
        // $max < $now || $now < $min
        if (Util::cmpTime($max, $now) < 0 || Util::cmpTime($now, $min) < 0) {
            // 運行時間外の場合は、運行中のバスが残っていないかをチェック
            $args = array(BUSCOMPANY_ID);
            $count = $this->db->invoke('t_sbt_bus_current_route', 'getServiceBusCount', $args);
        	if ($count == 0) {
		        $data = array("status" => 1, "min" => $min, "max" => $max);
        	} else {
		        $data = array("status" => 0);
	        }
        } else {
            // 運行時間内
	        $data = array("status" => 0);
        }

        return $data;

    }
}

// ### Run Process ---------------------------------------------------------------------
$class = new checkServiceTime();
$class->run();

