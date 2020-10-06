<?php

require_once (APP.'base/base/BaseModel.php');

class t_sbt_busdia extends BaseModel {

    /**
     * 指定便、停車順のダイヤ時刻を取得
     */
    public function getDiaTime($buscompany_id, $bin_no, $stop_seq) {
        $sql = <<<SQL
            SELECT  dia_time
            FROM    t_sbt_busdia
            WHERE   buscompany_id = :buscompany_id
                    AND bin_no = :bin_no
                    AND stop_seq = :stop_seq
SQL;
        $param = array(
            ":buscompany_id"    => $buscompany_id,
            ":bin_no"           => $bin_no,
            ":stop_seq"         => $stop_seq
        );

        return $this->fetchOne($sql, $param);
    }

    /**
     * 指定便、8桁バス停IDのダイヤ時刻を取得
     */
    public function getDiaTimefromBsCd8($buscompany_id, $bin_no, $busstop_id8) {
        $sql = <<<SQL
            SELECT  dia_time
            FROM    t_sbt_busdia
            WHERE   buscompany_id = :buscompany_id
                    AND bin_no = :bin_no
                    AND LEFT(busstop_id, 8) = :busstop_id8
SQL;
        $param = array(
            ":buscompany_id"    => $buscompany_id,
            ":bin_no"           => $bin_no,
            ":busstop_id8"       => $busstop_id8
        );

        return $this->fetchOne($sql, $param);
    }

    /**
     * 指定便のダイヤ情報を取得
     */
    public function getDiaInfo($buscompany_id, $bin_no) {
        $sql = <<<SQL
            SELECT  *
            FROM    t_sbt_busdia
            WHERE   buscompany_id = :buscompany_id
                    AND bin_no = :bin_no
            ORDER BY stop_seq
SQL;
        $param = array(
            ":buscompany_id"    => $buscompany_id,
            ":bin_no"           => $bin_no
        );

        return $this->fetchAll($sql, $param);
    }

    /**
     * 指定便の最終バス停を取得 
     */
    public function getLastBusstop($buscompany_id, $bin_no) {
        $sql = <<< SQL
            SELECT  SUBSTRING(busstop_id, 1, CHAR_LENGTH(busstop_id) - 2) busstop_id 
            FROM    v_sbt_busdia AS X
		    WHERE   buscompany_id = :buscompany_id
                    AND bin_no = :bin_no 
		            AND first_last_flg = 'L'
SQL;
        $param = array(":buscompany_id" => $buscompany_id, ":bin_no" => $bin_no);

        return $this->fetchRow($sql, $param);
    }

    /**
     * 本日のダイヤの最終時刻を取得
     */
    public function getLastDiaTime($buscompany_id) {
        $sql = <<< SQL
            SELECT  MAX(dia_time) AS max 
            FROM    v_sbt_busdia 
            WHERE   buscompany_id = :buscompany_id
                    AND (
                        ybkbn = (
                            SELECT  ybkbn 
                            FROM    t_sbt_calendar 
                            WHERE   buscompany_id = :buscompany_id
                                    AND srvdate = CURDATE()
                        ) 
                        OR ybkbn IS NULL
                    )
SQL;
        $param = array(":buscompany_id" => $buscompany_id);

        return $this->fetchOne($sql, $param);
    }

    /**
     * 本日のダイヤの始発時刻を取得
     */
    public function getFirstDiaTime($buscompany_id) {
        $sql = <<< SQL
            SELECT  MIN(dia_time) AS min 
            FROM    v_sbt_busdia 
            WHERE   buscompany_id = :buscompany_id
                    AND (
                        ybkbn = (
                            SELECT  ybkbn 
                            FROM    t_sbt_calendar 
                            WHERE   buscompany_id = :buscompany_id
                                    AND srvdate = CURDATE()
                        )
                        OR ybkbn IS NULL
                    )
SQL;
        $param = array(":buscompany_id" => $buscompany_id);

        return $this->fetchOne($sql, $param);
    }

    /**
     * 指定便の最終出発時刻を取得
     */
    public function getLastDiaTimeFromBin($buscompany_id, $bin_no) {
        $sql = <<< SQL
            SELECT  MAX(dia_time) AS shift_end_time 
            FROM    t_sbt_busdia
            WHERE   buscompany_id = :buscompany_id
                    AND bin_no = :bin_no
SQL;
        $param = array(
            ":buscompany_id"    => $buscompany_id,
            ":bin_no"           => $bin_no
        );

        return $this->fetchOne($sql, $param);
    }

    /**
     * コース名を取得
     */
    public function getCourseName($buscompany_id, $buscategory_cd, $course_id) {
        $sql = <<< SQL
            SELECT  DISTINCT
                    course_name 
            FROM    v_sbt_busdia VDIA
                    LEFT JOIN t_sbt_route_course_lang COL
                         ON COL.buscompany_id = :buscompany_id
                        AND COL.course_id     = VDIA.course_id
                        AND COL.lang_cd       = :lang_cd
            WHERE   VDIA.buscompany_id = :buscompany_id
                    AND VDIA.buscategory_cd = :buscategory_cd 
                    AND VDIA.course_id = :course_id
SQL;
        $param = array(
            ":buscompany_id"    => $buscompany_id,
            ":buscategory_cd"   => $buscategory_cd,
            ":course_id"        => $course_id,
            ":lang_cd"          => 'ja'
        );

        return $this->fetchOne($sql, $param);
    }

    /**
     * 指定便の始発バス停の情報を取得
     */
    public function getFirstBusStopData($buscompany_id, $bin_no) {
        $sql = <<< SQL
            SELECT  VDIA.busstop_id,
                    busstop_name, 
                    DATE_FORMAT(dia_time, '%H:%i') as dia_time 
            FROM    v_sbt_busdia VDIA
                    LEFT JOIN t_sbt_busstop_lang BSL
                         ON BSL.busstop_id = VDIA.busstop_id
                         AND BSL.lang_cd = :lang_cd
	        WHERE   VDIA.buscompany_id = :buscompany_id
    	            AND bin_no = :bin_no
                    AND stop_seq = 1
SQL;
        $param = array(
            ":buscompany_id"    => $buscompany_id,
            ":bin_no"           => $bin_no,
            ":lang_cd"          => 'ja'
        );

        return $this->fetchRow($sql, $param);
    }

    /**
     * 指定便の終着バス停が指定バス停と一致しているかどうかの確認
     */
    public function checkLastBusstop($buscompany_id, $bin_no, $busstop_id8) {
        $sql = <<<SQL
            SELECT  SUBSTRING(busstop_id, 1, CHAR_LENGTH(busstop_id) - 2) busstop_id8
            FROM    t_sbt_busdia
            WHERE   buscompany_id = :buscompany_id
                    AND bin_no = :bin_no
                    AND first_last_flg = 'L'
SQL;
        $param = array(
            ":buscompany_id"    => $buscompany_id,
            ":bin_no"           => $bin_no
        );

        if (strcmp($busstop_id8, $this->fetchOne($sql, $param)) == 0) {
            return True;
        } else {
            return False;
        }
    }
    /**
    * バス停に対応する時刻表を取得
    */
    public function getTimetable($busstop_id, $lang_cd) {
        $sql = <<< SQL
        SELECT dia.buscompany_id
                ,dia.buscategory_cd
                ,dia.route_id
                ,lang.route_name
                ,dia.course_id
                ,route.course_name
                ,route.route_direction
                ,dia.direction_no
                ,dia.ybkbn
                ,dia.busstop_id
                ,dia_time
                ,HOUR(dia_time) hour
                ,MINUTE(dia_time) minute
                ,dia.first_last_flg
        FROM v_sbt_busdia dia
        LEFT JOIN t_sbt_route_course_lang route
          ON  route.course_id = dia.course_id
          AND route.lang_cd = :lang_cd
        LEFT JOIN t_sbt_route_lang lang
          ON lang.buscompany_id = dia.buscompany_id
          AND lang.route_id = dia.route_id
        WHERE busstop_id = :busstop_id
              AND first_last_flg <> "L"
        ORDER BY dia.buscompany_id, dia.buscategory_cd, dia.route_id, dia.dia_time
SQL;
        $param = array(":busstop_id" => $busstop_id, ":lang_cd" => $lang_cd);
        return $this->fetchAll($sql, $param);
        }

    /**
     * 会社を指定して曜日区分を取得
     */
    public function getYbkbn($buscompany_id){
        $sql = <<< SQL
            SELECT  ybkbn
                   ,ybkbn_name
                   ,special_flg
            FROM t_sbt_ybkbn
            WHERE buscompany_id = :buscompany_id
              AND (special_flg IS NULL OR special_flg <> 1)
SQL;
        $param = array(":buscompany_id" => $buscompany_id);
        return $this->fetchAll($sql, $param);
    }

    /**
     * 会社,日付を指定して,今日の曜日区分を取得
     */
    public function getDays($buscompany_id, $srvdate){
        $sql = <<< SQL
            SELECT  buscompany_id
                   ,srvdate
                   ,ybkbn
            FROM t_sbt_calendar
            WHERE buscompany_id = :buscompany_id
              AND srvdate = :srvdate
SQL;
        $param = array(":buscompany_id" => $buscompany_id, ":srvdate" => $srvdate);
        return $this->fetchRow($sql, $param);
    }

    /**
     * 便に対応する時刻表及び運賃の取得
     */
    public function getBindia($buscompany_id, $busstop_id, $bin_no, $lang_cd){
        $sql = <<< SQL
        SELECT dia.buscompany_id
              ,dia.route_id
              ,dia.course_id
              ,dia.bin_no
              ,dia.ybkbn
              ,dia.stop_seq
              ,dia.busstop_id
              ,lang.busstop_name
              ,dia.dia_time
              ,HOUR(dia.dia_time) hour
              ,MINUTE(dia.dia_time) minute
              ,dia.first_last_flg
              ,fare.fare
              ,fare.IC_fare
        FROM v_sbt_busdia dia
        LEFT JOIN t_sbt_busstop_lang lang
          ON lang.busstop_id = dia.busstop_id
          AND lang.lang_cd = :lang_cd
        LEFT JOIN t_sbt_fare fare
          ON fare.buscompany_id = dia.buscompany_id
          AND fare.route_id = dia.route_id
          AND fare.origin_busstop_id = :busstop_id
          AND fare.destination_busstop_id = dia.busstop_id
        WHERE dia.buscompany_id = :buscompany_id
          AND dia.bin_no = :bin_no
        ORDER BY dia_time
SQL;
        $param = array(
            ":buscompany_id"    => $buscompany_id,
            ":busstop_id"       => $busstop_id, 
            ":bin_no"           => $bin_no, 
            ":lang_cd"          => $lang_cd
        );
        return $this->fetchAll($sql, $param);
    }

    /**
     * 便一覧の取得
     */
    public function getBinList($buscompany_id, $course_id) {
        $sql = <<<SQL
            SELECT  bin_no,
                    dia_time
            FROM    v_sbt_busdia
            WHERE   buscompany_id = :buscompany_id
                    AND first_last_flg = 'F'
SQL;
        $param = array(":buscompany_id" => $buscompany_id);
        if ($course_id != 0) {
            $sql .= " AND course_id = :course_id";
            $param[":course_id"] = $course_id;
        }
        $sql .= " ORDER BY dia_time";

        return $this->fetchAll($sql, $param);
    }

    /**
     * 指定コースで最長の便番号を取得
     */
    public function getMaxCourseBinNo($buscompany_id, $course_id) {
        $sql = <<<SQL
            SELECT  bin_no
            FROM    t_sbt_busdia
            WHERE   buscompany_id = :buscompany_id
                    AND stop_seq = (
                        SELECT  MAX(stop_seq)
                        FROM    v_sbt_busdia
                        WHERE   buscompany_id = :buscompany_id
                                AND course_id = :course_id
                    )
SQL;
        $param = array(
            ":buscompany_id"    => $buscompany_id,
            ":course_id"        => $course_id
        );

        return $this->fetchOne($sql, $param);
    }
}
