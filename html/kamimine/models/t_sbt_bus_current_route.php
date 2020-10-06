<?php

require_once ('../base/base/BaseModel.php');

class t_sbt_bus_current_route extends BaseModel {

    /**
     * ステータスの初期更新
     */
    public function updateBusStatus($device_id, $buscompany_id) { 
        $sql = <<< SQL
            REPLACE INTO t_sbt_bus_current_route 
            VALUES(
                :device_id, 
                :buscompany_id, 
                0, 0, 0, NULL, NOW(), NULL, NULL, NOW(), NULL
            )
SQL;
        $param = array(
            ":device_id"        => $device_id,
            ":buscompany_id"    => $buscompany_id
        );

        $this->query($sql, $param);
    }

    /**
     * ステータスの更新
     */
    public function updateBusRoute($device_id, $buscompany_id, $bin_no, $busstop_id, $stop_seq) {
        $sql = <<< SQL
            REPLACE INTO t_sbt_bus_current_route 
            VALUES(
                :device_id, 
                :buscompany_id, 
                :bin_no,
                :stop_seq,
                :busstop_id, 
                NULL, NOW(), NULL,NULL,NOW(),NULL
            )
SQL;
        $param = array(
            ":device_id"     => $device_id,
            ":buscompany_id" => $buscompany_id,   
            ":bin_no"        => $bin_no,
            ":stop_seq"      => $stop_seq,
            ":busstop_id"    => $busstop_id
        );

        $this->query($sql, $param);
    }

    /**
     * 運行しているバスの台数を取得 
     */
    public function getServiceBusCount($buscompany_id) {
        $sql = <<< SQL
            SELECT  COUNT(*) AS count 
            FROM    t_sbt_bus_current_route 
            WHERE   buscompany_id = :buscompany_id
                    AND DATE(reg_time) = CURDATE() 
                    AND bin_no != 0
SQL;
        $param = array(":buscompany_id" => $buscompany_id);

        return $this->fetchOne($sql, $param);
    }

    /**
     * 運行しているバスのデバイスIDの一覧を取得 
     */
    public function getServiceBusList($buscompany_id) {
        $sql = <<<SQL
            SELECT  device_id
            FROM    t_sbt_bus_current_route 
            WHERE   buscompany_id = :buscompany_id
                    AND DATE(reg_time) = CURDATE() 
                    AND bin_no != 0
SQL;
        $param = array(":buscompany_id" => $buscompany_id);

        return $this->fetchAll($sql, $param);
    }

    /**
     * バスのステータスを取得
     */
    public function getBusStatus($device_id, $buscompany_id) {
        $sql = <<< SQL
            SELECT  CUR.device_id, 
                    VDIA.buscategory_cd, 
                    VDIA.course_id, 
                    CUR.bin_no, 
                    CUR.busstop_id, 
                    CUR.stop_seq 
            FROM    t_sbt_bus_current_route CUR
                    INNER JOIN v_sbt_busdia VDIA
                         ON VDIA.buscompany_id = :buscompany_id
                         AND VDIA.bin_no = CUR.bin_no
		    WHERE   device_id = :device_id
SQL;
        $param = array(
            ":device_id"        => $device_id,
            ":buscompany_id"    => $buscompany_id
        );

        return $this->fetchRow($sql, $param);
    }
}
