<?php

require_once("../base/base/BaseApi.php");
require_once("../models/t_sbt_opinion.php");

// ### Class Definition -----------------------------------------------------------------
class insertOpinion extends BaseApi {

    protected function main() {
        $opinion = $_POST["opinion"];

        // デバイス, ブラウザ
        $device_browser = Util::getDeviceBrowserName($_SERVER["HTTP_USER_AGENT"]);

        // ご意見情報の登録
        $args = array(
            date('Y-m-d G:i:s'),        // 検索年月日時分秒
            $_SERVER["REMOTE_ADDR"],    // IPアドレス
            $device_browser['device'],
            $device_browser['browser'],
            $opinion
        );
        $this->logger->writeDebug($args);
        $this->db->invoke('t_sbt_opinion', 'insert', $args);
        $this->db->commit();
        
        $data = array(
            "status"    => 0,
        );

        return $data;
    }
}

// ### Run Process ---------------------------------------------------------------------
$class = new insertOpinion();
$class->run();

