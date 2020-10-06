<?php

require_once("../base/base/BaseApi.php");
require_once("../models/t_sbt_bus_current_route.php");
require_once("../models/t_sbt_busdia.php");
require_once("../models/t_sbt_busdia_actual.php");

// ### Class Definition -----------------------------------------------------------------
class subtourReceiveRoute extends BaseApi {

    /**
     * バスの路線変更処理
     * Androidの路線変更画面にて送信した際に呼ばれる
     */
    protected function main() {

        // 識別名確認
        if (strcmp($_POST["mark"], MARK) != 0) {
            $this->exitSystemErr("Mark is Invalid!(".$_POST["mark"].")");
        }
        // dataの中に下記情報が含まれている
        //   diano: 便番号(bin_no)
        //   route: コースID（course_id）
        //   area : カテゴリーコード（buscategory_cd）
        //   carid: デバイスID（device_id）
        $json = $_POST["data"];
        $json = preg_replace("/".preg_quote("\\")."/", "", $json);
        $data = json_decode($json, true);

        // 初期値としてバス停、停車順番号は固定値をセット
        $args = array(BUSCOMPANY_ID, $data["diano"]);
        $bscd = $this->db->invoke('t_sbt_busdia', 'getFirstBusStopData', $args)["busstop_id"];
        $stop_seq = 1;

        // ステータスを指定路線の始発バス停に更新
        $args = array($data["carid"], BUSCOMPANY_ID, $data["diano"], $bscd, $stop_seq);
        $this->db->invoke('t_sbt_bus_current_route', 'updateBusRoute', $args);

        // 運行実績にレコードを挿入
        $args = array(BUSCOMPANY_ID, $data["diano"], 1);
        $dia_time = $this->db->invoke('t_sbt_busdia', 'getDiaTime', $args);
        $reg_type = 1; // 路線強制変更時はreg_type=1
        $args = array(BUSCOMPANY_ID, $data["diano"], $bscd, $dia_time, date('H:i:s'), $reg_type, $stop_seq);
        $this->db->invoke('t_sbt_busdia_actual', 'insert', $args);

        $this->db->commit();

        print(0);
        return NULL;
    }
}

// ### Run Process ---------------------------------------------------------------------
$class = new subtourReceiveRoute();
$class->run();

