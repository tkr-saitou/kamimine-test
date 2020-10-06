<?php

require_once("../base/base/BaseApi.php");
require_once("../models/t_sbt_busdia.php");
require_once("../models/t_sbt_busstop.php");

// ### Class Definition -----------------------------------------------------------------
class getBusTimetable extends BaseApi {

	protected function main() {
		$busstop_id = $_POST["busstop_id"];
        $lang_cd = "ja";
		$data["busdia"] = $this->db->invoke('t_sbt_busdia', 'getTimetable', array($busstop_id, $lang_cd));
        $this->logger->writeDebug($data);
        $data["name"] = $this->db->invoke('t_sbt_busstop', 'getBusstopName', array($busstop_id, $lang_cd));
        $data["buscompany_id"] = $data["busdia"][0]["buscompany_id"];
        $data["ybkbn"] = $this->db->invoke('t_sbt_busdia', 'getYbkbn', array($data["buscompany_id"]));       
        //曜日区分ごとにデータを分ける
        foreach($data['ybkbn'] as $i => $row) {
            $table[$row['ybkbn']] = array();
        }
        $count = 0;
        //各ダイヤを指定の曜日に格納
        foreach($data['busdia'] as $j => $dia) {
            if(is_null($dia['ybkbn'])){
                //全てのダイヤに格納
                foreach($data['ybkbn'] as $k => $ybkbn){
                    //array_push($table[$ybkbn['ybkbn'][$count], $dia);
                    $count++;
                    $table[$ybkbn['ybkbn']] += array( $count => $dia);
                }
            }else {
                //該当するダイヤに格納
                $count++;
                $table[$dia['ybkbn']] += array( $count => $dia );
            }
        }
        $data["busdia"] = $table;

        $today = date("Y-m-d");
        $data["day"] = $this->db->invoke('t_sbt_busdia', 'getDays', array($data["buscompany_id"], $today));
		return $data;
	}
}


// ### Run Process ---------------------------------------------------------------------
$class = new getBusTimetable();
$class->run();
