<?php

require_once("../base/base/BaseApi.php");
require_once("../models/t_sbt_busstop.php");

class getDepartureList extends BaseApi {

  protected function main() {
      // var_dump("aaaaa");
      $busstop_id = $_POST["busstopId"];
      $course_id = $_POST["courseId"];

      // バス停一覧取得
      $args = array(BUSCOMPANY_ID, $busstop_id, $course_id);
      $busStopList = $this->db->invoke('t_sbt_busstop', 'findAllByArrivalOnRosen', $args);
      if (count($busStopList) == 0) exit("{\"status\":0, \"busstop\":[]}");

      $data = array(
        "status"	=> 0,
        "busstop"	=> $busStopList
      );
      return $data;
  }
}

// ### Run Process ---------------------------------------------------------------------
$class = new getDepartureList();
$class->run();
// $rosen = getInt('rosen', 0);
// $arrival = getInt('arrival', 0);

// $link = connectDB($DB);

// if ($rosen == 0 && $arrival == 0) {
//     $data = array_map(function ($e) {
//         return $e['cd'];
//     }, BusstopUtil::findAll($link));
// } elseif ($rosen == 0) {
//     $data = BusstopUtil::findAllByBusstop($link, $arrival);
// } else {
//     $data = BusstopUtil::findAllByArrivalOnRosen($link, $arrival, $rosen);
// }

// mysqli_close($link);

// echo json_encode(array('status' => 1, 'data' => $data));
