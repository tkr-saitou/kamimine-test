<?php

require_once ('../base/base/BaseModel.php');

class t_sbt_busprobe extends BaseModel {

    /**
     * プローブ情報の登録
     */
    public function insert($data) {
        $now = date("Y-m-d H:i:s", time());
        $sql = <<< SQL
            INSERT IGNORE INTO t_sbt_busprobe 
            VALUES (
                :device_id,
                :gps_time,
                :lat,
                :lng,
                :velocity,
                :angle,
                :buscompany_id,
                :buscategory_cd,
                :course_id,
                :bin_no,
                :busstop_id,
                NULL,
                :reg_time,
                NULL,
                NULL,
                :upd_time,
                NULL
            )
SQL;
        $param = array(
            ":device_id"        => $data["device_id"],
            ":gps_time"         => $data["gpstime"],
            ":lat"              => $data["lat"],
            ":lng"              => $data["lng"],
            ":velocity"         => $data["vel"],
            ":angle"            => $data["ang"],
            ":buscompany_id"    => $data["buscompany_id"],
            ":buscategory_cd"   => $data["buscategory_cd"],
            ":course_id"        => $data["course_id"],
            ":bin_no"           => $data["bin_no"],
            ":busstop_id"       => $data["busstop_id"],
            ":reg_time"         => $now,
            ":upd_time"         => $now
        );

        $this->query($sql, $param);
    }

    /**
     * バスの車両IDと最新のGPS更新時間を取得(当日のみ)
     */
    public function getLatestGPSTime($deviceList) {
        $sql = "
            SELECT  device_id,
                    MAX(gps_time) AS gps_time 
            FROM    t_sbt_busprobe
	        WHERE   gps_time > (NOW() - INTERVAL 600 SECOND) 
                    AND lat != 0 
                    AND lng != 0
        ";
        $inList = "";
        foreach ($deviceList as $row) {
            $inList .= "'".$row["device_id"]."',";            
        }
        $sql .= "   AND device_id IN (".substr($inList, 0, -1).")
            GROUP BY device_id
        ";
        return $this->fetchAll($sql);
    }

    /**
     * バスの車両IDと最新のGPS更新時間を取得(当日のみ)
     * 【デモ用】
     */
    public function getLatestGPSTimeForDemo($time) {
        $sql = <<< SQL
            SELECT  device_id,
                    MAX(gps_time) AS gps_time 
            FROM    t_sbt_busprobe
	        WHERE   SUBSTRING(DATE_FORMAT(gps_time, '%H%i%s'), 4) = :time
            GROUP BY device_id
SQL;
        $param = array(":time" => $time);

        return $this->fetchAll($sql, $param);
    }

    /**
     * プローブ情報取得
     */
    public function getProbeData($device_id, $gps_time) {
        $sql = <<< SQL
            SELECT  pro.device_id, 
                    pro.gps_time AS `latesttime`,
                    pro.buscompany_id,
                    pro.buscategory_cd,
                    bin.route_id,
                    bin.course_id,
                    pro.bin_no, 
                    pro.lat, 
                    pro.lng, 
                    pro.velocity, 
                    pro.angle 
            FROM    t_sbt_busprobe pro
            INNER JOIN t_sbt_busbin bin 
              ON pro.buscompany_id = bin.buscompany_id
             AND pro.bin_no = bin.bin_no
		    WHERE pro.device_id = :device_id 
              AND pro.gps_time = :gps_time
SQL;
        $param = array(":device_id" => $device_id, ":gps_time" => $gps_time);
        $probeData = $this->fetchRow($sql, $param);

        // angle(方位)が取得できていない場合、過去最後に取得できた方位を取得する
        // angle = 0 && velocity(速度) = 0 の場合のみ、angleが取得できていないと判定する
        // 北を向いて走行している場合に対応するため
        if ($probeData["angle"] == 0 && $probeData["velocity"] == 0) {
            // NOT (angle = 0 AND velocity = 0) -> angle != 0 OR velocity != 0
            $sql = <<<SQL
                SELECT  angle
                FROM    t_sbt_busprobe
                WHERE   device_id = :device_id
                        AND gps_time = (
                            SELECT  MAX(gps_time)
                            FROM    t_sbt_busprobe
                            WHERE   angle != 0 OR velocity != 0
                        )
SQL;
            $param = array(":device_id" => $device_id);

            $row = $this->fetchRow($sql, $param);
            if (!is_null($row)) {
                $probeData["angle"] = $row["angle"];
            }
            // 過去一度も角度が取得できていない場合は0のまま
        }

        return $probeData;
    }

    /**
     * 更新時刻とバス停を取得
     */
    public function getLatestBusLocation($device_id, $buscompany_id) {
        $sql = <<< SQL
            SELECT  BP.busstop_id,
                    BSL.busstop_name,
                    BP.buscategory_cd,
                    BCL.category_name,
                    BIN.route_id,
                    BIN.course_id,
                    COL.course_name,
                    DATE_FORMAT(BP.gps_time, '%H:%i') AS gps_time
            FROM    t_sbt_busprobe BP
                    LEFT JOIN t_sbt_busstop_lang BSL
                        ON BSL.busstop_id = BP.busstop_id
                        AND BSL.lang_cd = :lang_cd
                    LEFT JOIN t_sbt_buscategory_lang BCL
                        ON BCL.buscompany_id = :buscompany_id
                        AND BCL.buscategory_cd = BP.buscategory_cd
                        AND BCL.lang_cd = :lang_cd
                    LEFT JOIN t_sbt_busbin BIN
                        ON BIN.buscompany_id = :buscompany_id
                        AND BIN.bin_no = BP.bin_no
                    LEFT JOIN t_sbt_route_course_lang COL
                        ON COL.buscompany_id = :buscompany_id
                        AND COL.course_id = BIN.course_id
                        AND COL.lang_cd = :lang_cd
	        WHERE   device_id = :device_id
                    AND gps_time = (
                        SELECT  MAX(gps_time) 
                        FROM    t_sbt_busprobe
	                    WHERE   device_id = :device_id 
                                AND gps_time > CURRENT_DATE()
                                AND busstop_id != 0
                    )
SQL;
        $param = array(
            ":buscompany_id"    => $buscompany_id,
            ":device_id"        => $device_id,
            ":lang_cd"          => 'ja'
        );

        return $this->fetchRow($sql, $param);
    }

    /**
     * 更新時刻とバス停を取得
     * 【デモ用】
     */
    public function getLatestBusLocationForDemo($device_id, $buscompany_id, $start_time, $end_time) {
        $sql = <<< SQL
            SELECT  BP.busstop_id,
                    BSL.busstop_name,
                    BP.buscategory_cd,
                    BCL.category_name,
                    BIN.route_id,
                    BIN.course_id,
                    COL.course_name,
                    DATE_FORMAT(BP.gps_time, '%H:%i') AS gps_time
            FROM    t_sbt_busprobe BP
                    LEFT JOIN t_sbt_busstop_lang BSL
                        ON BSL.busstop_id = BP.busstop_id
                        AND BSL.lang_cd = :lang_cd
                    LEFT JOIN t_sbt_buscategory_lang BCL
                        ON BCL.buscompany_id = :buscompany_id
                        AND BCL.buscategory_cd = BP.buscategory_cd
                        AND BCL.lang_cd = :lang_cd
                    LEFT JOIN t_sbt_busbin BIN
                        ON BIN.buscompany_id = :buscompany_id
                        AND BIN.bin_no = BP.bin_no
                    LEFT JOIN t_sbt_route_course_lang COL
                        ON COL.buscompany_id = :buscompany_id
                        AND COL.route_id = BIN.route_id
                        AND COL.course_id = BIN.course_id
                        AND COL.lang_cd = :lang_cd
	        WHERE   device_id = :device_id
                    AND gps_time = (
                        SELECT  MAX(gps_time) 
                        FROM    t_sbt_busprobe
	                    WHERE   device_id = :device_id 
                                AND SUBSTRING(DATE_FORMAT(gps_time, '%H%i%s'), 4) BETWEEN :start_time AND :end_time
                                AND busstop_id != 0
                    )
SQL;
        $param = array(
            ":buscompany_id"    => $buscompany_id,
            ":lang_cd"          => 'ja',
            ":device_id"        => $device_id,
            ":start_time"       => $start_time,
            ":end_time"         => $end_time
        );

        return $this->fetchRow($sql, $param);
    }

}
