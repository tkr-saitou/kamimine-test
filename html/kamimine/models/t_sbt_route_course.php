<?php

require_once ('../base/base/BaseModel.php');

class t_sbt_route_course extends BaseModel {

    /**
     * ポリライン描画用の点列一覧を取得
     */
    public function getCoursePointList($buscompany_id, $buscategory_cd, $course_id) {
        $sql = <<< SQL
            SELECT  DISTINCT
                    ROP.course_id, 
                    COL.course_name, 
                    ROP.seq, 
                    ROP.lat, 
                    ROP.lng 
            FROM    t_sbt_route_course_point ROP
                    LEFT JOIN t_sbt_route_course_lang COL 
                        ON COL.buscompany_id = :buscompany_id
                       AND COL.course_id     = ROP.course_id
                       AND COL.lang_cd       = :lang_cd
                    LEFT JOIN v_sbt_busdia VDIA
                        ON VDIA.buscompany_id = :buscompany_id
                        AND VDIA.course_id    = ROP.course_id
            WHERE   (VDIA.ybkbn = (
                        SELECT  ybkbn 
                        FROM    t_sbt_calendar 
                        WHERE   buscompany_id = :buscompany_id
                                AND srvdate   = CURDATE()
                    )
                    OR VDIA.ybkbn IS NULL)
SQL;
        $param = array(":buscompany_id" => $buscompany_id, ":lang_cd" => 'ja');
        if ($buscategory_cd != 0) {
            $sql .= " AND VDIA.buscategory_cd = :buscategory_cd";
            $param[":buscategory_cd"] = $buscategory_cd;
        }
        if ($course_id != 0) {
            $sql .= " AND ROP.course_id = :course_id";
            $param[":course_id"] = $course_id;
        }
        $sql .= " ORDER BY ROP.course_id, ROP.seq";

        return $this->fetchAll($sql, $param);
    }

    /**
     * コース一覧を取得
     */
    public function getCourseList($buscompany_id, $buscategory_cd) {
        $sql = <<< SQL
            SELECT  DISTINCT
                    CO.course_id,
                    COL.course_name
            FROM    t_sbt_route_course CO
                    LEFT JOIN t_sbt_route_course_lang COL
                        ON COL.buscompany_id = :buscompany_id
                       AND COL.course_id     = CO.course_id
                       AND COL.lang_cd       = :lang_cd
                    LEFT JOIN t_sbt_route RO
                        ON RO.buscompany_id = :buscompany_id
                       AND RO.route_id      = CO.route_id 
            WHERE   1 = 1
SQL;
        $param = array(":buscompany_id" => $buscompany_id, ":lang_cd" => 'ja');
        if ($buscategory_cd != 0) {
            $sql .= " AND RO.buscategory_cd = :buscategory_cd";
            $param[":buscategory_cd"] = $buscategory_cd;
        }
        $sql .= " ORDER BY CO.course_id";

        return $this->fetchAll($sql, $param);
    }

    /**
     * コース名、色の取得
     */
    public function getCourseInfo($buscompany_id, $course_id) {
        $sql = <<<SQL
            SELECT  ROL.route_name,
                    RO.route_color
            FROM    t_sbt_route_course CO
                    LEFT JOIN t_sbt_route RO
                        ON RO.buscompany_id = :buscompany_id
                        AND RO.route_id = CO.route_id
                    LEFT JOIN t_sbt_route_lang ROL
                        ON ROL.buscompany_id = :buscompany_id
                        AND ROL.route_id = CO.route_id
                        AND ROL.lang_cd = :lang_cd 
            WHERE   CO.buscompany_id = :buscompany_id
                    AND CO.course_id = :course_id
SQL;
        $param = array(
            ":buscompany_id"    => $buscompany_id,
            ":course_id"        => $course_id,
            ":lang_cd"          => 'ja'
        );

        return $this->fetchRow($sql, $param);
    }
}
