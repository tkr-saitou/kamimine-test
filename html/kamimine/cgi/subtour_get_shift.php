<?php

require_once("../base/base/BaseApi.php");
require_once("../models/t_sbt_buscategory.php");
require_once("../models/t_sbt_shift_pattern_list.php");

// ### Class Definition -----------------------------------------------------------------
class subtourGetShift extends BaseApi {

    /**
     * 仕業パターン選択に必要な各種情報を取得して返却する。
     * Android端末の仕業選択画面の表示時に呼ばれる。
     */
    protected function main() {

        // 識別名確認
        if (strcmp($_POST["mark"], MARK) != 0) {
            $this->exitSystemErr("Mark is Invalid!(".$_POST["mark"].")");
        }
        // エリアコード
        $areaCd = $_POST["area"];
        // 仕業コード
        $shiftCd = $_POST["shift"];
        // 路線コード(路線＋上下)
        $routeCd = $_POST["route"];

        // 引数に応じてDB検索
        $data = array();
        if ($areaCd == -1) { // カテゴリ取得（画面上の「バス名」）
            $args = array(BUSCOMPANY_ID);
            $list = $this->db->invoke('t_sbt_buscategory', 'getCategoryList', $args);
            foreach ($list as $row) {
                $data[$row["buscategory_cd"]] = $row["category_name"];
            }
        } else if ($shiftCd == -1) { // 仕業パターンリスト取得（「路線名」）
            $args = array(BUSCOMPANY_ID, $areaCd);
            $list = $this->db->invoke('t_sbt_shift_pattern_list', 'getShiftList', $args);
            foreach ($list as $row) {
                $data[$row["shift_pattern_cd"]] = $row["shift_pattern_name"];
            }
        } else if ($routeCd == -1) { // 仕業パターンよりコースを取得 
            $args = array(BUSCOMPANY_ID, $shiftCd, $areaCd);
            $list = $this->db->invoke('t_sbt_shift_pattern_list', 'getCourseListByShiftPattern', $args);
            foreach ($list as $row) {
                $data[$row["course_id"]] = $row["course_name"];
            }
        } else if ($routeCd == 0) { // 仕業パターンより始発ダイヤ付のコース一覧を取得
            $args = array(BUSCOMPANY_ID, $shiftCd, $areaCd);
            $list = $this->db->invoke('t_sbt_shift_pattern_list', 'getCourseDiaTimeByShiftPattern', $args);
            foreach ($list as $row) {
                $data[$row["keytime"]] = $row["course_id"].",".$row["course_name"].",".$row["bin_no"].",".$row["dia_time"].",".$row["busstop_name"];
            }
        } else { // 詳細情報取得
            $args = array(BUSCOMPANY_ID, $shiftCd, $areaCd, $routeCd);
            $list = $this->db->invoke('t_sbt_shift_pattern_list', 'getShiftDetailList', $args);
            foreach ($list as $row) {
                $hmTime = substr($row["dia_time"], 0, -3);
                $data[$row["bin_no"]] = $row["busstop_name"]."(".$hmTime.")";
            }
        }

        $jsonArr = array(
        	"status"	=> 1,
	        "data"		=> $data
        );

        return $jsonArr;
    }
}

// ### Run Process ---------------------------------------------------------------------
$class = new subtourGetShift();
$class->run();

