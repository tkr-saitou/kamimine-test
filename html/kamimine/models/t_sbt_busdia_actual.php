<?php

require_once (APP.'base/base/BaseModel.php');

class t_sbt_busdia_actual extends BaseModel {

    /**
     * 運行情報の挿入
     * 路線強制変更時に使用
     * 重複する可能性があるためREPLACEする
     * reg_type
     *   1: 路線強制変更時
     *   2: 実績補完時
     *   3: ゴミデータクリア時
     */
    public function insert($buscompany_id, $bin_no, $busstop_id, $dia_time, $real_time, $reg_type, $stop_seq) {
        $sql = <<<SQL
            REPLACE INTO t_sbt_busdia_actual
            VALUES(
                CURDATE(),
                :buscompany_id,
                :bin_no,
                :stop_seq,
                :busstop_id,
                :dia_time,
                :real_time,
                :reg_type,
                "ADMIN",
                NOW(),
                NULL,
                "ADMIN",
                NOW(),
                NULL
            )
SQL;
        $param = array(
            ":buscompany_id"    => $buscompany_id,
            ":bin_no"           => $bin_no,
            ":stop_seq"         => $stop_seq,
            ":busstop_id"       => $busstop_id,
            ":dia_time"         => $dia_time,
            ":real_time"        => $real_time,
            ":reg_type"         => $reg_type
        );

        $this->query($sql, $param);
    }


    /**
     * 運行分析用情報を取得
     */
    public function getServiceAnalysisData($date, $buscompany_id, $course_id, $bin_no) {
        if ($bin_no == 0) {
            // 未使用(複数便表示用)
            // 選択されたコースに紐づく便を全て表示
            $sql = <<<SQL
                SELECT  BA.bin_no,
                        BSL.busstop_name,
                        BA.dia_time,
                        BA.real_time,
                        BA.reg_type
                FROM    t_sbt_busdia_actual BA
                        LEFT JOIN t_sbt_busstop_lang BSL
                            ON BSL.busstop_id = BA.busstop_id
                            AND BSL.lang_cd = :lang_cd
                WHERE   BA.date = :date
                        AND BA.buscompany_id = :buscompany_id
                        AND BA.bin_no IN (
                            SELECT  bin_no
                            FROM    t_sbt_busbin
                            WHERE   buscompany_id = :buscompany_id
                                    AND course_id = :course_id
                        )
                ORDER BY BA.dia_time
SQL;
            $param = array(
                ":date"             => $date,
                ":buscompany_id"    => $buscompany_id, 
                ":course_id"        => $course_id,
                ":lang_cd"          => 'ja'
            );
        } else {
            // 選択されたコースに紐づく便を全て表示
            $sql = <<<SQL
                SELECT  BA.bin_no,
                        BSL.busstop_name,
                        BA.dia_time,
                        BA.real_time,
                        BA.reg_type
                FROM    t_sbt_busdia_actual BA
                        LEFT JOIN t_sbt_busstop_lang BSL
                            ON BSL.busstop_id = BA.busstop_id
                            AND BSL.lang_cd = :lang_cd
                WHERE   BA.date = :date
                        AND BA.buscompany_id = :buscompany_id
                        AND BA.bin_no = :bin_no
                ORDER BY BA.real_time
SQL;
            $param = array(
                ":date"             => $date,
                ":buscompany_id"    => $buscompany_id, 
                ":bin_no"           => $bin_no,
                ":lang_cd"          => 'ja'
            );
        }
        return $this->fetchAll($sql, $param);
    }

    /**
     * ダイヤの運行状況を取得
     */
    public function getDiaCondition($date, $buscompany_id, $bin_no) {
        $sql = <<< SQL
            SELECT ba.date
                  ,ba.buscompany_id
                  ,ba.bin_no
                  ,ba.stop_seq
                  ,ba.busstop_id
                  ,lang.busstop_name
                  ,ba.dia_time
                  ,ba.real_time
            FROM t_sbt_busdia_actual ba
            LEFT JOIN t_sbt_busstop_lang lang
              ON lang.busstop_id = ba.busstop_id
              AND lang_cd = "ja"
            WHERE ba.date = :date
              AND ba.buscompany_id = :buscompany_id
              AND ba.bin_no = :bin_no
SQL;
        $param = array(":date" => $date, ":buscompany_id" => $buscompany_id, ":bin_no" => $bin_no);
        return $this->fetchAll($sql, $param);
    }

    /**
     * 最後にバス停を通過した時刻から指定時間以上経過しているかを判定
     */
    public function checkBorderElapseTime($buscompany_id, $bin_no, $date, $borderTime) {
        $sql = <<<SQL
            SELECT  MAX(real_time)
            FROM    t_sbt_busdia_actual
            WHERE   date = :date
                    AND buscompany_id = :buscompany_id
                    AND bin_no = :bin_no
SQL;
        $param = array(
            ":buscompany_id"    => $buscompany_id,
            ":bin_no"           => $bin_no,
            ":date"             => $date
        );
        $maxRealTime = $this->fetchOne($sql, $param);

        if (Util::cmpTime(date('H:i', strtotime('-'.$borderTime.' minute')), $maxRealTime) >= 0) {
            return True;
        } else {
            return False;
        }
    }

    /**
     * 便に対応するダイアと実際の時間を取得
     */
    public function getBindiaactual($buscompany_id, $date, $ybkbn, $bin_no) {
        $sql = <<<SQL
            SELECT dia.buscompany_id
                  ,dia.route_id
                  ,dia.course_id
                  ,dia.bin_no
                  ,dia.ybkbn
                  ,dia.busstop_id
                  ,dia.dia_time
                  ,HOUR(dia.dia_time) hour
                  ,MINUTE(dia.dia_time) minute
                  ,ba.real_time
                  ,HOUR(ba.real_time) actual_hour
                  ,MINUTE(ba.real_time) actual_minute
                  ,dia.first_last_flg
            FROM v_sbt_busdia dia
              LEFT JOIN t_sbt_busdia_actual ba
            ON ba.buscompany_id = dia.buscompany_id
            AND ba.bin_no = dia.bin_no
            AND ba.busstop_id = dia.busstop_id
            AND ba.date = "2015-12-24"
            WHERE buscompany_id = "KCS"
              AND dia.ybkbn = "1"
              AND dia.bin_no = 13
SQL;
        $param = array(
            ":buscompany_id"    => $buscompany_id,
            ":date"             => $date,
            ":ybkbn"             => $ybkbn,
            ":bin_no"           => $bin_no,
        );
        return $this->fetchAll($sql, $param);
    }

    /**
     * 運行情報を補完する
     */
    public function completionActual($buscompany_id, $date, $bin_no, $diaInfo) {
        // 指定便の運行実績一覧を取得する
        $sql = <<<SQL
            SELECT  BA.date,
                    BA.buscompany_id,
                    BA.bin_no,
                    BA.busstop_id,
                    BA.stop_seq,
                    BA.dia_time,
                    BA.real_time
            FROM    t_sbt_busdia_actual BA
                    INNER JOIN t_sbt_busdia DIA 
                        USING(buscompany_id, bin_no, stop_seq, busstop_id)
            WHERE   buscompany_id = :buscompany_id
                    AND date = :date
                    AND bin_no = :bin_no
SQL;
        $param = array(
            ":buscompany_id"    => $buscompany_id,
            ":date"             => $date,
            ":bin_no"           => $bin_no
        );
        $actual = $this->fetchAll($sql, $param);

        // 運行情報補完用の到着推定時刻を算出
        $estimationTime = $this->estimateActualTime($actual, $diaInfo);

        // 運行情報を補完する
        // 最後から二番目に記録したバス停～最後に記録したバス停
        // 欠落がなければ入らない
        $reg_type = 2; // 補完情報はreg_type=2
        $orgSeq = $actual[count($actual) - 2]["stop_seq"];
        $destSeq = $actual[count($actual) - 1]["stop_seq"];
        for ($i = $orgSeq + 1; $i < $destSeq; $i++) {
            $this->insert(
                $buscompany_id,
                $bin_no,
                $diaInfo[$i - 1]["busstop_id"],
                $diaInfo[$i - 1]["dia_time"],
                $estimationTime[$i],
                $reg_type,
                $diaInfo[$i - 1]["stop_seq"]
            );
        }
    }

    /**
     * ダイヤ時刻の比率から到着時刻を推定する
     * 戻り値は停車順をキーとした推定値の配列
     *
     *              始点　　　　　　　　　　 終点
     *              ↓                         ↓
     *  dia_time:   10:00   10:02   10:06   10:12
     *  real_time:  10:01    ___     ___    10:14
     *              ↑                         ↑
     *  最後から二番目に記録した運行実績     最後に記録した運行実績
     */
    private function estimateActualTime($actual, $diaInfo) {
        $acCnt = count($actual);
        // 最後から二番目に記録した運行情報
        $orgAc = $actual[$acCnt - 2];
        // 最後に記録した運行情報
        $destAc = $actual[$acCnt - 1];

        // 始点と終点の停車順 
        $orgSeq = $orgAc["stop_seq"];
        $destSeq = $destAc["stop_seq"];

        // ダイヤの始点と終点の時刻(UEからの秒数)
        $orgDiaTime = idate('U', strtotime($diaInfo[$orgSeq - 1]["dia_time"]));
        $destDiaTime = idate('U', strtotime($diaInfo[$destSeq - 1]["dia_time"]));

        // ダイヤの始点と終点の差分(秒)
        $diffDiaSec = $destDiaTime - $orgDiaTime;

        // ダイヤの始点～終点間の点の配置比率の算出(0～1)
        $ratio = array();
        for ($i = $orgSeq; $i <= $destSeq; $i++) {
            $tmpDiaTime = idate('U', strtotime($diaInfo[$i - 1]["dia_time"]));
            $ratio[$i] = ($tmpDiaTime - $orgDiaTime) / $diffDiaSec;
        }

        // 運行実績の始点と終点の時刻(UEからの秒数)
        $orgRealTime = idate('U', strtotime($orgAc["real_time"]));
        $destRealTime = idate('U', strtotime($destAc["real_time"]));

        // 運行実績の始点と終点の差分(秒)
        $diffRealSec = $destRealTime - $orgRealTime;

        // ダイヤの点の配置比率から運行実績の始点～終点間の点の位置(時刻)を推定
        $estimationTime = array();
        for ($i = $orgSeq + 1; $i < $destSeq; $i++) {
            $offset = intval($diffRealSec * $ratio[$i]);
            $estimationTime[$i] = date("H:i:s", strtotime("+".$offset." second", $orgRealTime));
        }

        return $estimationTime;
    }

     /**
      * 最後に到着したバス停と時刻を取得
      */
     public function getLatestBusLocation($buscompany_id, $bin_no, $date) {
         $sql = <<<SQL
             SELECT  BA.busstop_id,
                     BSL.busstop_name,
                     BCL.category_name,
                     COL.course_name,
                     DATE_FORMAT(BA.real_time, '%H:%i') AS real_time
             FROM    t_sbt_busdia_actual BA
                     LEFT JOIN t_sbt_busstop_lang BSL
                         ON BSL.busstop_id = BA.busstop_id
                         AND BSL.lang_cd = :lang_cd
                     LEFT JOIN v_sbt_busdia VDIA
                         ON VDIA.buscompany_id = :buscompany_id
                         AND VDIA.bin_no = :bin_no
                     LEFT JOIN t_sbt_buscategory_lang BCL
                         ON BCL.buscompany_id = :buscompany_id
                         AND BCL.buscategory_cd = VDIA.buscategory_cd
                         AND BCL.lang_cd = :lang_cd
                     LEFT JOIN t_sbt_route_course_lang COL
                         ON COL.buscompany_id = :buscompany_id
                         AND COL.course_id = VDIA.course_id
                         AND COL.lang_cd = :lang_cd
             WHERE   BA.buscompany_id = :buscompany_id
                     AND BA.bin_no = :bin_no
                     AND BA.date = :date
                     AND BA.real_time = (
                         SELECT  MAX(real_time)
                         FROM    t_sbt_busdia_actual
                         WHERE   buscompany_id = :buscompany_id
                                 AND bin_no = :bin_no
                                 AND date = :date
                     )
SQL;

         $param = array(
             ":buscompany_id" => $buscompany_id,
             ":bin_no"        => $bin_no,
             ":date"          => $date,
             ":lang_cd"       => 'ja'
         );

         return $this->fetchRow($sql, $param);
     }

    /**
     * 指定日付、コース、仕業の運行状況を取得
     */
    public function getServiceTable($buscompany_id, $date, $course_id, $shift_pattern_cd) {
        $sql = "
            SELECT  SP.shift_pattern_name,
                    VDIA1.bin_no,
                    VDIA1.course_id,
                    RCL.course_name,
                    BSL1.busstop_name from_busstop_name,
                    LEFT(VDIA1.dia_time, 5) from_dia_time,
                    BSL2.busstop_name to_busstop_name,
                    VDIA2.busstop_id to_busstop_id,
                    LEFT(VDIA2.dia_time, 5) to_dia_time,
                    BA.busstop_id,
                    BA.reg_type,
                    BSL3.busstop_name,
                    IF(CONCAT(:date, ' ', VDIA1.dia_time) > NOW(), 1, 0) is_plan,
                    BSL4.busstop_name pre_busstop_name
            FROM    v_sbt_busdia VDIA1
                    LEFT JOIN t_sbt_route_course_lang RCL
                        ON RCL.buscompany_id = :buscompany_id
                        AND RCL.course_id = VDIA1.course_id
                        AND RCL.lang_cd = :lang_cd
                    LEFT JOIN t_sbt_busstop_lang BSL1
                        ON BSL1.busstop_id = VDIA1.busstop_id
                        AND BSL1.lang_cd = :lang_cd
                    LEFT JOIN v_sbt_busdia VDIA2
                        ON VDIA2.buscompany_id = :buscompany_id
                        AND VDIA2.bin_no = VDIA1.bin_no
                        AND VDIA2.first_last_flg = 'L'
                    LEFT JOIN t_sbt_busstop_lang BSL2
                        ON BSL2.busstop_id = VDIA2.busstop_id
                        AND BSL2.lang_cd = :lang_cd
                    LEFT JOIN t_sbt_busdia_actual BA
                        ON BA.buscompany_id = :buscompany_id
                        AND BA.date = :date
                        AND BA.bin_no = VDIA1.bin_no
                        AND BA.dia_time = (
                            SELECT  MAX(dia_time)
                            FROM    t_sbt_busdia_actual
                            WHERE   buscompany_id = :buscompany_id
                                    AND date = :date
                                    AND bin_no = VDIA1.bin_no
                        )
                    LEFT JOIN t_sbt_busstop_lang BSL3
                        ON BSL3.busstop_id = BA.busstop_id
                        AND BSL3.lang_cd = :lang_cd
                    LEFT JOIN t_sbt_busdia_actual BA2
                        ON BA2.buscompany_id = :buscompany_id
                        AND BA2.date = :date
                        AND BA2.bin_no = VDIA1.bin_no
                        AND BA2.dia_time = (
                            SELECT  MAX(dia_time)
                            FROM    t_sbt_busdia_actual
                            WHERE   buscompany_id = :buscompany_id
                                    AND date = :date
                                    AND bin_no = VDIA1.bin_no
                                    AND dia_time < BA.dia_time
                        )
                    LEFT JOIN t_sbt_busstop_lang BSL4
                        ON BSL4.busstop_id = BA2.busstop_id
                        AND BSL4.lang_cd = :lang_cd

        ";
        // 検索条件に仕業コードが含まれる場合
        if (strcmp($shift_pattern_cd, "0") != 0) {
            $sql .= "
                    LEFT JOIN t_sbt_shift_pattern SP
                        ON SP.buscompany_id = :buscompany_id
                        AND shift_pattern_cd = :shift_pattern_cd
            ";
        } else { // 検索条件がコースIDのみの場合
            $sql .= "
                    LEFT JOIN t_sbt_shift_pattern_list SPL
                        ON SPL.buscompany_id = :buscompany_id
                        AND SPL.ybkbn = (
                            SELECT  ybkbn
                            FROM    t_sbt_calendar
                            WHERE   buscompany_id = :buscompany_id
                                    AND srvdate = :date
                        )
                        AND SPL.bin_no = VDIA1.bin_no
                    LEFT JOIN t_sbt_shift_pattern SP
                        ON SP.buscompany_id = :buscompany_id
                        AND SP.shift_pattern_cd = SPL.shift_pattern_cd
            ";
        }
        $sql .= "
            WHERE   VDIA1.buscompany_id = :buscompany_id
                    AND (VDIA1.ybkbn = (
                        SELECT  ybkbn
                        FROM    t_sbt_calendar
                        WHERE   buscompany_id = :buscompany_id
                                AND srvdate = :date
                    ) OR VDIA1.ybkbn IS NULL)
                    AND VDIA1.first_last_flg = 'F'
        ";
        // 検索条件にコースIDが含まれる場合
        if (strcmp($course_id, "0") != 0) {
            $sql .= "
                    AND VDIA1.course_id = :course_id
            ";
        }
        // 検索条件に仕業コードが含まれる場合
        if (strcmp($shift_pattern_cd, "0") != 0) {
            $sql .= "
                    AND VDIA1.bin_no IN (
                        SELECT  bin_no
                        FROM    t_sbt_shift_pattern_list
                        WHERE   buscompany_id = :buscompany_id
                                AND shift_pattern_cd = :shift_pattern_cd
                                AND ybkbn = (
                                    SELECT  ybkbn
                                    FROM    t_sbt_calendar
                                    WHERE   buscompany_id = :buscompany_id
                                            AND srvdate = :date
                                )
                    )
            ";
        }
        $sql .= " ORDER BY VDIA1.dia_time";

        $param = array(
            ":buscompany_id"    => $buscompany_id,
            ":date"             => $date,
            ":course_id"        => $course_id,
            ":shift_pattern_cd" => $shift_pattern_cd,
            ":lang_cd"          => 'ja'
        );

        return $this->fetchAll($sql, $param);
    }

    /**
     * 運行状況の警告一覧取得
     *
     * 仕業が存在していて、始発バス停の出発時刻を過ぎているにも関わらず、
     * 運行実績テーブルにデータが存在しない仕業一覧を取得
     */
    public function getAlertShiftList($buscompany_id, $now) {
        $sql =<<<SQL
            SELECT  DISTINCT SP.shift_pattern_name
            FROM    t_sbt_shift_pattern_list SPL
                    LEFT JOIN t_sbt_shift_pattern SP
                        ON SP.buscompany_id = :buscompany_id
                        AND SP.shift_pattern_cd = SPL.shift_pattern_cd
                    INNER JOIN t_sbt_busdia DIA
                        ON DIA.buscompany_id = :buscompany_id
                        AND DIA.bin_no = SPL.bin_no
                        AND DIA.first_last_flg = 'F'
                        AND DIA.dia_time < TIME(:now)
            WHERE   SPL.buscompany_id = :buscompany_id
                    AND SPL.ybkbn = (
                        SELECT  ybkbn
                        FROM    t_sbt_calendar
                        WHERE   buscompany_id = :buscompany_id
                                AND srvdate = DATE(:now)
                    )
                    AND SPL.bin_no NOT IN (
                        SELECT  bin_no
                        FROM    t_sbt_busdia_actual
                        WHERE   buscompany_id = :buscompany_id
                                AND date = DATE(:now)
                    )
SQL;
        $param = array(
            ":buscompany_id"    => $buscompany_id,
            ":now"              => $now
        );

        return $this->fetchAll($sql, $param);
    }
}
