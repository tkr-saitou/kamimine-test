<?php

require_once("../base/base/BaseApi.php");
require_once("../models/t_sbt_route_course.php");

// ### Class Definition -----------------------------------------------------------------
class getCoursePointList extends BaseApi {

    protected function main() {
        $buscategory_cd = $_POST["areaCd"];
        $course_id = $_POST["routeCd"];

        // ポリライン描画用の点列一覧を取得
        $args = array(BUSCOMPANY_ID, $buscategory_cd, $course_id);
        $routePointList = $this->db->invoke('t_sbt_route_course', 'getCoursePointList', $args);
        if (count($routePointList) == 0) exit("{\"status\":0, \"route\":[]}");

        $route = array();
        $tmp = array();
        $courseCd = 0;
        $key = 0;
        do {
            $row = $routePointList[$key];
            if ($row) {
                if ($courseCd !== $row["course_id"]) {
                    if ($courseCd !== 0) {
                        $route["$courseCd"]["points"] = $tmp;
                        $tmp = array();
                    }
                    $courseCd = $row["course_id"];
                    $route["$courseCd"]["course_id"] = $row["course_name"];
                }
                $tmp[$row["seq"]] = array(
                    "lat" => $row["lat"],
                    "lng" => $row["lng"]
                );
            } else {
                $route["$courseCd"]["points"] = $tmp;
            }
            $key++;
        } while ($row);

        $data = array(
            "status"	=> 0,
            "route"	=> $route
        );

        return $data;
    }
}

// ### Run Process ---------------------------------------------------------------------
$class = new getCoursePointList();
$class->run();

