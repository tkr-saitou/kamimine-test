<?php

require_once ('../base/base/BaseModel.php');

class t_sbt_shift_pattern_list extends BaseModel {

    /**
     * 分類一覧を取得
     */
    public function getBusCategoryList($buscompany_id, $shift_pattern_cd) {
        $sql = <<< SQL
            SELECT  DISTINCT
                    VDIA.buscategory_cd,
                    CAL.category_name
            FROM    t_sbt_shift_pattern_list PAL
                    LEFT JOIN v_sbt_busdia VDIA
                         ON VDIA.buscompany_id = :buscompany_id
                         AND VDIA.bin_no = PAL.bin_no
                    LEFT JOIN t_sbt_buscategory_lang CAL
                        ON CAL.buscompany_id = :buscompany_id
                        AND CAL.buscategory_cd = VDIA.buscategory_cd
                        AND CAL.lang_cd = :lang_cd
            WHERE   PAL.buscompany_id = :buscompany_id
                    AND PAL.shift_pattern_cd = :shift_pattern_cd
                    AND PAL.ybkbn = (
                        SELECT  ybkbn
                        FROM    t_sbt_calendar
                        WHERE   buscompany_id = :buscompany_id
                                AND srvdate = CURDATE()
                    )
 
            ORDER BY buscategory_cd
SQL;
        $param = array(
            ":buscompany_id"    => $buscompany_id,
            ":lang_cd"          => 'ja',
            ":shift_pattern_cd" => $shift_pattern_cd
        );

        return $this->fetchAll($sql, $param);
    }

    /**
     * 仕業パターンよりコースを取得する
     */
    public function getCourseListByShiftPattern($buscompany_id, $shift_pattern_cd, $buscategory_cd) {
        $sql = <<< SQL
            SELECT  DISTINCT 
                    VDIA.course_id, 
                    COL.course_name
            FROM    t_sbt_shift_pattern_list PAL
                    LEFT JOIN v_sbt_busdia VDIA
                         ON VDIA.buscompany_id = :buscompany_id
                         AND VDIA.bin_no = PAL.bin_no
                    LEFT JOIN t_sbt_route_course_lang COL
                         ON COL.buscompany_id = :buscompany_id
                         AND COL.course_id = VDIA.course_id
                         AND COL.lang_cd = :lang_cd
		    WHERE   PAL.buscompany_id = :buscompany_id
                    AND PAL.shift_pattern_cd = :shift_pattern_cd
                    AND PAL.ybkbn = (
                        SELECT  ybkbn
                        FROM    t_sbt_calendar
                        WHERE   buscompany_id = :buscompany_id
                                AND srvdate = CURDATE()
                    )
		            AND VDIA.buscategory_cd = :buscategory_cd
SQL;
        $param = array(
            ":buscompany_id"    => $buscompany_id,
            ":lang_cd"          => 'ja',
            ":shift_pattern_cd" => $shift_pattern_cd,
            ":buscategory_cd"   => $buscategory_cd
        );

        return $this->fetchAll($sql, $param);
    }

    /**
     * 仕業パターンの一覧を取得
     * 各便の始発時刻が付与される
     */
    public function getCourseDiaTimeByShiftPattern($buscompany_id, $shift_pattern_cd, $buscategory_cd) {
        $sql = <<< SQL
            SELECT  DISTINCT 
                    VDIA.course_id, 
                    COL.course_name, 
                    VDIA.bin_no, 
                    DATE_FORMAT(VDIA.dia_time,'%H:%i') AS dia_time,
		            BSL.busstop_name, 
                    DATE_FORMAT(VDIA.dia_time,'%H%i') AS keytime
            FROM    t_sbt_shift_pattern_list PAL
                    LEFT JOIN v_sbt_busdia VDIA
                         ON VDIA.buscompany_id = :buscompany_id
                         AND VDIA.bin_no = PAL.bin_no
                    LEFT JOIN t_sbt_route_course_lang COL
                         ON COL.buscompany_id = :buscompany_id
                         AND COL.course_id = VDIA.course_id
                         AND COL.lang_cd = :lang_cd
                    LEFT JOIN t_sbt_busstop_lang BSL
                         ON BSL.busstop_id = VDIA.busstop_id
                         AND BSL.lang_cd = :lang_cd
		    WHERE   PAL.buscompany_id = :buscompany_id
                    AND PAL.shift_pattern_cd = :shift_pattern_cd
                    AND PAL.ybkbn = (
                        SELECT  ybkbn
                        FROM    t_sbt_calendar
                        WHERE   buscompany_id = :buscompany_id
                                AND srvdate = CURDATE()
                    )
                    AND VDIA.buscategory_cd = :buscategory_cd
                    AND VDIA.stop_seq = 1 
            ORDER BY VDIA.dia_time
SQL;
        $param = array(
            ":buscompany_id"    => $buscompany_id,
            ":lang_cd"          => 'ja',
            ":shift_pattern_cd" => $shift_pattern_cd,
            ":buscategory_cd"   => $buscategory_cd
        );

        return $this->fetchAll($sql, $param);
    }

    /**
     * 仕業の詳細情報を取得
     */
    public function getShiftDetailList($buscompany_id, $shift_pattern_cd, $buscategory_cd, $course_id) {
        $sql = <<< SQL
            SELECT  DISTINCT 
                    VDIA.bin_no, 
		            BSL.busstop_name, 
                    VDIA.dia_time
            FROM    t_sbt_shift_pattern_list PAL
                    LEFT JOIN v_sbt_busdia VDIA
                         ON VDIA.buscompany_id = :buscompany_id
                         AND VDIA.bin_no = PAL.bin_no
                    LEFT JOIN t_sbt_busstop_lang BSL
                         ON BSL.busstop_id = VDIA.busstop_id
                         AND BSL.lang_cd = :lang_cd
		    WHERE   PAL.buscompany_id = :buscompany_id
                    AND PAL.shift_pattern_cd = :shift_pattern_cd
                    AND PAL.ybkbn = (
                        SELECT  ybkbn
                        FROM    t_sbt_calendar
                        WHERE   buscompany_id = :buscompany_id
                                AND srvdate = CURDATE()
                    )
                    AND VDIA.buscategory_cd = :buscategory_cd
		            AND VDIA.course_id = :course_id
                    AND VDIA.stop_seq = 1
		            AND (VDIA.ybkbn = (
                        SELECT  ybkbn 
                        FROM    t_sbt_calendar 
                        WHERE   buscompany_id = :buscompany_id
                                AND srvdate = CURDATE()
                    ) OR VDIA.ybkbn IS NULL)
            ORDER BY VDIA.dia_time
SQL;
        $param = array(
            ":buscompany_id"    => $buscompany_id,
            ":lang_cd"          => 'ja',
            ":shift_pattern_cd" => $shift_pattern_cd,
            ":buscategory_cd"   => $buscategory_cd,
            ":course_id"        => $course_id
        );

        return $this->fetchAll($sql, $param);

    }

    /**
     * 仕業パターン一覧を取得
     */
    public function getShiftList($buscompany_id, $buscategory_cd) {
        $sql = <<< SQL
            SELECT  DISTINCT 
                    PA.shift_pattern_cd, 
                    PA.shift_pattern_name 
            FROM    t_sbt_shift_pattern PA
                    LEFT JOIN t_sbt_shift_pattern_list PAL
                        ON PAL.buscompany_id = :buscompany_id
                        AND PAL.shift_pattern_cd = PA.shift_pattern_cd
                        AND PAL.ybkbn = (
                            SELECT  ybkbn
                            FROM    t_sbt_calendar
                            WHERE   buscompany_id = :buscompany_id
                                    AND srvdate = CURDATE()
                        )
                    LEFT JOIN v_sbt_busdia VDIA
                         ON VDIA.buscompany_id = :buscompany_id
                         AND VDIA.bin_no = PAL.bin_no
		    WHERE   PA.buscompany_id = :buscompany_id
                    AND VDIA.buscategory_cd = :buscategory_cd
            ORDER BY shift_pattern_name
SQL;
        $param = array(
            ":buscompany_id" => $buscompany_id, 
            ":buscategory_cd" => $buscategory_cd
        );

        return $this->fetchAll($sql, $param);
    }
    public function getShiftListByDateCourseId($buscompany_id, $buscategory_cd, $date, $course_id) {
        $date = str_replace("/", "-", $date);
        $sql = <<< SQL
            SELECT  DISTINCT
                    PA.shift_pattern_cd,
                    PA.shift_pattern_name
            FROM    t_sbt_shift_pattern PA
                    LEFT JOIN t_sbt_shift_pattern_list PAL
                        ON PAL.buscompany_id = :buscompany_id
                        AND PAL.shift_pattern_cd = PA.shift_pattern_cd
                        AND PAL.ybkbn = (
                            SELECT  ybkbn
                            FROM    t_sbt_calendar
                            WHERE   buscompany_id = :buscompany_id
                                    AND srvdate = :date
                        )
                    LEFT JOIN v_sbt_busdia VDIA
                         ON VDIA.buscompany_id = :buscompany_id
                         AND VDIA.bin_no = PAL.bin_no
                         AND VDIA.course_id = :course_id
		    WHERE   PA.buscompany_id = :buscompany_id
                    AND VDIA.buscategory_cd = :buscategory_cd
            ORDER BY shift_pattern_name
SQL;
        $param = array(
            ":buscompany_id" => $buscompany_id,
            ":buscategory_cd" => $buscategory_cd,
            ":date" => $date,
            ":course_id" => $course_id
        );

        return $this->fetchAll($sql, $param);
    }

    /**
     * 引数の仕業パターンの最終便の情報を取得する
     */
    public function getShiftLastBin($buscompany_id, $shift_pattern_cd) {
        $sql = <<< SQL
            SELECT  shift_pattern_cd,
                    bin_no
            FROM    t_sbt_shift_pattern_list 
            WHERE   buscompany_id = :buscompany_id
                    AND shift_pattern_cd = :shift_pattern_cd
                    AND ybkbn = (
                        SELECT  ybkbn
                        FROM    t_sbt_calendar
                        WHERE   buscompany_id = :buscompany_id
                                AND srvdate = CURDATE()
                    )
	                AND seq = (
                        SELECT  MAX(seq) 
                        FROM    t_sbt_shift_pattern_list
	                    WHERE   buscompany_id = :buscompany_id
                                AND shift_pattern_cd = :shift_pattern_cd
                                AND ybkbn = (
                                    SELECT  ybkbn
                                    FROM    t_sbt_calendar
                                    WHERE   buscompany_id = :buscompany_id
                                            AND srvdate = CURDATE()
                                )
                    )
SQL;
        $param = array(
            ":buscompany_id"    => $buscompany_id,
            ":shift_pattern_cd" => $shift_pattern_cd
        );

        return $this->fetchRow($sql, $param);
    }

    /**
     * 仕業コード、便番号から次の便の始発時刻を取得
     * 仕業の最終便だった場合、0を返す
     */
    public function getNextFirstDiaTime($buscompany_id, $shift_pattern_cd, $bin_no) {
        $sql = <<<SQL
            SELECT  dia_time
            FROM    t_sbt_busdia
            WHERE   buscompany_id = :buscompany_id
                    AND stop_seq = 1
                    AND bin_no = (
                        SELECT  bin_no
                        FROM    t_sbt_shift_pattern_list
                        WHERE   buscompany_id = :buscompany_id
                                AND shift_pattern_cd = :shift_pattern_cd
                                AND ybkbn = (
                                    SELECT  ybkbn
                                    FROM    t_sbt_calendar
                                    WHERE   buscompany_id = :buscompany_id
                                            AND srvdate = CURDATE()
                                )
                                AND seq = (
                                    SELECT  seq + 1
                                    FROM    t_sbt_shift_pattern_list
                                    WHERE   buscompany_id = :buscompany_id
                                            AND shift_pattern_cd = :shift_pattern_cd
                                            AND ybkbn = (
                                                SELECT  ybkbn
                                                FROM    t_sbt_calendar
                                                WHERE   buscompany_id = :buscompany_id
                                                        AND srvdate = CURDATE()
                                            )
                                            AND bin_no = :bin_no
                                )
                    )
SQL;
        $param = array(
            ":buscompany_id"    => $buscompany_id,
            ":shift_pattern_cd" => $shift_pattern_cd,
            ":bin_no"           => $bin_no
        );
        
        $diaTime = $this->fetchOne($sql, $param);

        if (!is_null($diaTime)) {
            return $diaTime;
        } else {
            // 仕業の最終便だった場合、0を返す
            return 0;
        }
    }
}

