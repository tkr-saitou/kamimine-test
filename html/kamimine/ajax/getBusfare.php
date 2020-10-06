<?php

require_once("../base/base/BaseApi.php");
require_once("../models/t_sbt_busdia.php");
require_once("../models/t_sbt_busstop.php");
require_once("../models/t_sbt_busdia_actual.php");

// ### Class Definition -----------------------------------------------------------------
class getBusfare extends BaseApi {

	protected function main() {
        //バス会社IDの取得
        $buscompany_id = $_POST["buscompany_id"];
        //今日の曜日区分を取得
        $today = date("Y-m-d");
        $data["day"] = $this->db->invoke('t_sbt_busdia', 'getDays', array($buscompany_id, $today));
        //バス停データの取得
        $busstop_id = $_POST["busstop_id"];
        $bin_no = $_POST["bin_no"];
//        $ybkbn = $data["day"]["ybkbn"];
        $lang_cd = "ja";
//        $data["dia"] = $this->db->invoke('t_sbt_busdia', 'getBindia', array(BUSCOMPANY_ID, $busstop_id, $bin_no, $ybkbn, $lang_cd));
        $data["dia"] = $this->db->invoke('t_sbt_busdia', 'getBindia', array(BUSCOMPANY_ID, $busstop_id, $bin_no, $lang_cd));
        //バス停名を取得
        $data["name"] = $this->db->invoke('t_sbt_busstop', 'getBusstopName', array($busstop_id, $lang_cd)); 
        //バス停の位置情報の取得
        $place = $this->db->invoke('t_sbt_busdia_actual', 'getDiaCondition', array($today, $buscompany_id, $bin_no));
        if(empty($place)){
            //バスが出発していないとき
            $data['flg'] = 0;
        }else{
            $data['counter'] = 0;
            foreach($data['dia'] as $i => $dia) {
                //自分の検索するバス停に対する処理
                if(empty($place[$i])){
                    $data['flg'] = 1;
                    $data['counter'] = $data['counter'] + 1;
                }
                if($dia['busstop_id'] == $busstop_id){
                    //出発しているとき
                    if($data['flg'] == 0){
                        $data['flg'] = 2;
                        $data['now'] = $i + 1;
                        break;
                    //～駅前を出発しているとき
                    }else if($data['flg'] == 1){
                        $data['now'] = $i + 1;
                        break;
                    }
                }
            }
        }
		return $data;
	}
}

// ### Run Process ---------------------------------------------------------------------
$class = new getBusfare();
$class->run();
