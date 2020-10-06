<?php

require_once("../base/base/BaseApi.php");
require_once("../models/t_sbt_news_telop.php");

// ### Class Definition -----------------------------------------------------------------
class getNewsTelop extends BaseApi {

    protected function main() {

        // 分類一覧取得 
        $newsTelop = $this->db->invoke('t_sbt_news_telop', 'getCurrentNewsTelop');
        if (empty($newsTelop)) {
            $newsTelop = DEFAULTTELOP;            
        }

        $data = array(
            "status"    => 0,
            "telop"	    => $newsTelop
        );

        return $data;
    }
}

// ### Run Process ---------------------------------------------------------------------
$class = new getNewsTelop();
$class->run();

