<?php

require_once ('../base/base/BaseModel.php');

class t_sbt_busbin extends BaseModel {

    /**
     * 便詳細を取得
     */ 
    public function getBinDetail($buscompany_id, $bin_no) {
        $sql = <<<SQL
            SELECT  IFNULL(busbin_detail_short_name, '') busbin_detail_short_name,
                    IFNULL(busbin_detail_name, '') busbin_detail_name,
                    except_delay_flg
            FROM    t_sbt_busbin BIN
                    LEFT JOIN t_sbt_busbin_detail BD
                    ON BD.buscompany_id = :buscompany_id
                    AND BD.busbin_detail_cd = BIN.busbin_detail_cd
                    AND BD.lang_cd = :lang_cd
            WHERE   BIN.buscompany_id = :buscompany_id
                    AND BIN.bin_no = :bin_no
SQL;
        $param = array(
            ":buscompany_id"    => $buscompany_id,
            ":bin_no"           => $bin_no,
            ":lang_cd"          => 'ja'
        );

        return $this->fetchRow($sql, $param);
    }

    /**
     * 本日運行するコース一覧を取得
     */
    public function getCourseList($buscompany_id, $lang_cd) {
        $sql = <<<SQL
            SELECT  DISTINCT
                    BIN.course_id,
                    RCL.course_name
            FROM    t_sbt_busbin BIN
                    LEFT JOIN t_sbt_route_course_lang RCL
                        ON RCL.buscompany_id = :buscompany_id
                        AND RCL.course_id = BIN.course_id
                        AND RCL.lang_cd = :lang_cd
            WHERE   BIN.buscompany_id = :buscompany_id
                    AND (ybkbn IS NULL OR ybkbn = (
                        SELECT  ybkbn
                        FROM    t_sbt_calendar
                        WHERE   buscompany_id = :buscompany_id
                                AND srvdate = CURDATE()
                    ))
            ORDER BY BIN.course_id
SQL;

        $param = array(
            ":buscompany_id"    => $buscompany_id,
            ":lang_cd"          => $lang_cd
        );

        return $this->fetchAll($sql, $param);
    }
}
