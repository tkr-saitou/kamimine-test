<?php

require_once("../base/base/BaseApi.php");
require_once("../models/t_sbt_shift_pattern_list.php");
require_once("../models/t_sbt_busdia.php");

// ### Class Definition -----------------------------------------------------------------
class subtourGetShiftEndtime extends BaseApi {

    protected function main() {

        // 識別名確認
        if (strcmp($_POST["mark"], MARK) != 0) {
            $this->exitSystemErr("Mark is Invalid!(".$_POST["mark"].")");
        }
        // 仕業コード
        $shiftCd = $_POST["shift"];

        // 仕業情報の取得
        $shiftData = $this->db->invoke('t_sbt_shift_pattern_list', 'getShiftLastBin',array(BUSCOMPANY_ID,$shiftCd));
        if (count($shiftData) == 0) {
            print(0);
        	return;
        }

        // 最終ダイヤ時刻を取得
        $args = array(BUSCOMPANY_ID, $shiftData["bin_no"]);
        $shiftEndTime = $this->db->invoke('t_sbt_busdia', 'getLastDiaTimeFromBin', $args);
        if(empty($shiftEndTime) || strlen($shiftEndTime) <> 8) {
            // 取得できなかった場合または時刻形式として不正であった場合（lengthのみで判断）
	        print(0);
        	return;
        }

        return $shiftEndTime;
    }
}

// ### Run Process ---------------------------------------------------------------------
$class = new subtourGetShiftEndtime();
$class->run();

