<?php

require_once("../base/base/BaseApi.php");
require_once("../models/t_sbt_busdia.php");
require_once("../models/t_sbt_busdia_actual.php");

// ### Class Definition -----------------------------------------------------------------
class getBuscurrent extends BaseApi {
	protected function main() {
		//日付、コースの取得
		$date = $_POST["date"];
		$course_id = $_POST["course_id"];
		$bin_no = $_POST["bin_no"];

        //今日の曜日区分を取得
        $data["day"] = $this->db->invoke('t_sbt_busdia', 'getDays', array(BUSCOMPANY_ID, $date));
		//現在時刻の取得
		$current_time = date("g:i");
		//便ごとのダイヤを取得
		$binList = $this->db->invoke('t_sbt_busdia', 'getBinList', array(BUSCOMPANY_ID, $course_id, $data['day']['ybkbn'])); //modelがybkbnない状態になっている
		foreach($binList as $i => $bin){
			$data[$i] = $this->db->invoke('t_sbt_busdia_actual', 'getBindiaactual', array(BUSCOMPANY_ID, $date, $data['day']['ybkbn'], $bin['bin_no'])); //作る
			//便ごとの遅れ情報を取得
			foreach($data[$i] as $j -> $dia){
				$dif_hour = data[$i][$j]['hour'] - data[$i][$j]['actual_hour'];
				$dif_min = data[$i][$j]['minute'] - data[$i][$j]['actual_minute'];
				if($dif_hour >= 0 && $dif_min >= 0){
					//遅れがなく、GPS情報がとれているとき
					$data[$i][$j]['flg'] = 0;
				}else if($dif_hour < 0 || $dif_min < 0){
					//遅れがあり、GPS情報がとれているとき
					$data[$i][$j]['flg'] = 1;
					$data[$i][$j]['delay'] = $dif_hour . ":" . $dif_min;
				}else{
					//遅れに関わらず、GPS情報がとれていないとき
					$data[$i][$j]['flg'] = 2;
				}
			}		
		}

		//データを返す
		return $data;
	}
}

// ### Run Process ---------------------------------------------------------------------
$class = new getBuscurrent();
$class->run();	
/*　　・下記機能を、グラフを出している画面（仮称：管理ダッシュボート）に追加したい。
　　　①XX:XX現在、運行状況は下記の通りです。
　　　　　1便　遅れなし　XX:XXにYYバス停を通過
　　　　　2便　GPS情報が取得できていません。ご確認ください。
　　　　　3便　5分程度の遅れ　XX:XXにYYバス停を通過
　　　②日付と仕業パターンを指定したら、下記情報を出す
　　　　　日付－仕業パターン－便－バス停－ダイヤ時刻－実際の通過時刻
　　　　　※実際の時刻は（名称変更した）t_sbt_busdia_actualから取得。

　　　　　※上記をやりたい背景として、きちんと運用できていなかったり、
　　　　　　運用状況がよく見えなかったりすると、
　　　　　　システムに対する顧客満足度が下がってしまう、という点が
　　　　　　あります。実際に頸南バスでうまく行ってない部分があった。
*/
