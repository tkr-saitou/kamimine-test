<?php

require_once ('../base/base/BaseModel.php');
require_once ('../base/cllib/Util.php');

class t_sbt_busstop extends BaseModel {

    /**
     * バス停一覧を取得
     */
    public function getBusStopList($buscompany_id, $buscategory_cd, $course_id) {
        $sql = <<< SQL
            SELECT  DISTINCT
                    BS.busstop_id,
                    BSL.busstop_name,
                    BS.lat,
                    BS.lng
            FROM    t_sbt_busstop BS
                    LEFT JOIN t_sbt_busstop_lang BSL
                        ON BSL.busstop_id = BS.busstop_id
                        AND BSL.lang_cd = :lang_cd
                    LEFT JOIN v_sbt_busdia VDIA
                        ON VDIA.buscompany_id = :buscompany_id
                        AND VDIA.busstop_id = BS.busstop_id
                    LEFT JOIN v_sbt_busstop_category VBSC
                        ON VBSC.buscompany_id = :buscompany_id
                        AND VBSC.busstop_id = BS.busstop_id
            WHERE   1 = 1
SQL;
        $param = array(":buscompany_id" => $buscompany_id, ":lang_cd" => 'ja');
        if ($buscategory_cd != 0) {
            $sql .= " AND VBSC.buscategory_cd = :buscategory_cd";
            $param[":buscategory_cd"] = $buscategory_cd;
        }
        if ($course_id != 0) {
            $sql .= " AND VDIA.course_id = :course_id";
            $param[":course_id"] = $course_id;
        }
        $sql .= " ORDER BY VDIA.stop_seq"; 

        return $this->fetchAll($sql, $param);
    }

    /**
     * バス停選択肢一覧を取得
     * 末尾2桁を除いた8桁の
     * バス停コードの末尾の２桁は同一名バス停の連番
     */
    public function getBusStopId8DigitList($buscompany_id, $buscategory_cd, $course_id) {
        if ($course_id == 0) { // コースID未指定時。ダイヤはJOINしない
            $sql = <<< SQL
                SELECT  DISTINCT
                        SUBSTRING(BS.busstop_id, 1, CHAR_LENGTH(BS.busstop_id) - 2) busstop_id8, 
                        BSL.busstop_name 
                FROM    t_sbt_busstop BS
                        LEFT JOIN t_sbt_busstop_lang BSL
                            ON BSL.busstop_id = BS.busstop_id
                            AND BSL.lang_cd    = :lang_cd
                ORDER BY BSL.busstop_kana, BSL.busstop_name
SQL;
            $param = array(":lang_cd" => "ja");
        } else { // コースID指定時
            // コース内のバス停をすべて回る便(stop_seqが最大の便)のうち、時刻表が最も早い便のバス停を停車順に取得
            $sql = <<< SQL
                SELECT  SUBSTRING(BS.busstop_id, 1, CHAR_LENGTH(BS.busstop_id) - 2) busstop_id8, 
                        BSL.busstop_name 
                FROM    t_sbt_busstop BS
                        LEFT JOIN t_sbt_busstop_lang BSL
                            ON BSL.busstop_id = BS.busstop_id
                            AND BSL.lang_cd    = :lang_cd
                        LEFT JOIN v_sbt_busdia VDIA
                            ON VDIA.buscompany_id = :buscompany_id
                            AND VDIA.busstop_id    = BS.busstop_id
                        LEFT JOIN v_sbt_busstop_category VBSC
                            ON VBSC.buscompany_id = :buscompany_id
                            AND VBSC.busstop_id    = BS.busstop_id
                WHERE   VDIA.bin_no = (
                            SELECT  MIN(bin_no) 
                            FROM    v_sbt_busdia 
                            WHERE   buscompany_id = :buscompany_id 
                                    AND course_id = :course_id 
                                    AND stop_seq = (
                                        SELECT  MAX(stop_seq) 
                                        FROM    v_sbt_busdia 
                                        WHERE   buscompany_id = :buscompany_id
                                                AND course_id = :course_id
                                    )
                        )
SQL;
            $param = array(
                ":buscompany_id"    => $buscompany_id, 
                ":lang_cd"          => 'ja', 
                ":course_id"        => $course_id
            );
            if ($buscategory_cd != 0) {
                $sql .= " AND VBSC.buscategory_cd = :buscategory_cd";
                $param[":buscategory_cd"] = $buscategory_cd;
            }
            $sql .= " ORDER BY VDIA.stop_seq, SUBSTRING(BS.busstop_id, 1, CHAR_LENGTH(BS.busstop_id) - 2) ";
        }
        
        return $this->fetchAll($sql, $param);
/*
        $rs = $this->fetchAll($sql, $param);

        // 重複を排除(ダイヤの停車順にソートするためのORDER BYとDISTINCTを併用できないため)
        $busStopId8DigitList = array();
        foreach ($rs as $row) {
            $flg = True;
            foreach ($busStopId8DigitList as $item) {
                if ($row["busstop_id8"] == $item["busstop_id8"]) {
                    $flg = False;
                    break;
                }
            }
            if ($flg) $busStopId8DigitList[] = $row; 
        }
        return $busStopId8DigitList;
*/
    }

    /**
     * 現在地から半径100m以内のバス停一覧を取得
     */
    public function getNearBusstopList($currentLat, $currentLng) {
        // 全バス停を取得
        $sql = "SELECT SUBSTRING(busstop_id, 1, CHAR_LENGTH(busstop_id) - 2) busstop_id, lat, lng FROM t_sbt_busstop";
        $busstopList = $this->fetchAll($sql);

        // 半径100m以内のバス停を選別
        $nearBusstopList = array();
        foreach ($busstopList as $busstop) {
            $distance = Util::calcDistance($currentLat, $currentLng, $busstop["lat"], $busstop["lng"]);
            if (!is_null($distance) && $distance <= SEARCH_RANGE) {
                $nearBusstopList[] = array("fromBsCd" => $busstop["busstop_id"]);
            }
        }

        return $nearBusstopList;
    }

    /**
     * 指定バス停が所属する路線の終着バス停を取得する
     * 終着バス停の到着時刻が現在時刻を過ぎている場合は取得対象外
     */
    public function getLastBusstopList($buscompany_id, $busstop_id, $course_id) {
        if ($course_id == 0) { // コース未指定の場合
            $sql = <<<SQL
                select  di1.buscompany_id, 
                        di1.course_id, 
                        di1.busstop_id origin_busstop_id,
                        SUBSTRING(di2.busstop_id, 1, CHAR_LENGTH(di2.busstop_id) - 2) busstop_id,
                        count(*) cnt
                from    v_sbt_busdia di1
                        inner join v_sbt_busdia di2
                        on di1.buscompany_id = di2.buscompany_id
                            and di1.course_id     = di2.course_id
                            and di1.bin_no        = di2.bin_no
                            and di1.stop_seq      < di2.stop_seq
                            and di2.first_last_flg = 'L'
                where di1.buscompany_id  = :buscompany_id
                        and SUBSTRING(di1.busstop_id, 1, CHAR_LENGTH(di1.busstop_id) - 2) = :busstop_id
                        and di1.first_last_flg <> 'L'
                group by di1.buscompany_id,di1.course_id,di1.busstop_id,di2.busstop_id
SQL;
            $param = array(
                ":buscompany_id"    => $buscompany_id, 
                ":busstop_id"   => $busstop_id
            );
        } else {
            // コース指定の場合
            $sql = <<<SQL
                SELECT DISTINCT SUBSTRING(DIA.busstop_id, 1, CHAR_LENGTH(DIA.busstop_id) - 2) busstop_id
                FROM v_sbt_busdia DIA
                INNER JOIN v_sbt_busdia DIA2
                  ON DIA.buscompany_id = DIA2.buscompany_id
                 AND DIA.course_id     = DIA2.course_id
                 AND SUBSTRING(DIA2.busstop_id, 1, CHAR_LENGTH(DIA2.busstop_id) - 2) = :busstop_id 
                 AND DIA.stop_seq      > DIA2.stop_seq
                WHERE DIA.buscompany_id  = :buscompany_id 
                  AND SUBSTRING(DIA.busstop_id, 1, CHAR_LENGTH(DIA.busstop_id) - 2) != :busstop_id
                  AND DIA.course_id      = :course_id
                  AND DIA.first_last_flg = 'L'
SQL;
            $param = array(
                ":buscompany_id"    => $buscompany_id, 
                ":course_id"        => $course_id,
                ":busstop_id"   => $busstop_id
            );
        }

        return $this->fetchAll($sql, $param);
/*
        $lastBusstopList = array();
        foreach ($rs as $row) {
            $lastBusstopList[] = $row["busstop_id"];
        }

        return $lastBusstopList;
*/
    }

    /**
    * バス停IDからバス停名とバス停カナを取得
    */
    public function getBusstopName($busstop_id, $lang_cd) {
        $sql = <<< SQL
            SELECT busstop_id
                    ,busstop_name
                    ,busstop_kana
            FROM t_sbt_busstop_lang
            WHERE busstop_id = :busstop_id
              AND lang_cd = :lang_cd
SQL;
        $param = array(":busstop_id" => $busstop_id,":lang_cd" => $lang_cd);
        return $this->fetchRow($sql, $param);
    }
    /**
    * バス停IDを取得
    */
    public function getBusstopID($buscompany_id, $busstop_id8digit, $course_id) {
        $sql = <<< SQL
            SELECT busstop_id
            FROM v_sbt_busdia 
            WHERE buscompany_id = :buscompany_id
              AND course_id = :course_id
              AND busstop_id LIKE :busstop_id"%"
            LIMIT 1
SQL;
        $param = array(":buscompany_id" => $buscompany_id, ":busstop_id" => $busstop_id8digit,":course_id" => $course_id);
        return $this->fetchOne($sql, $param);
    }

    /**
     * バス停平仮名検索 日本語取得
     */
     public function getKanaBusstop($initial_name) {
        $sql = <<<SQL
            select bst.busstop_id
                  ,bsl.busstop_name
                  ,bsl.busstop_kana
                  ,group_concat(distinct rcl.route_direction order by rcl.course_id desc separator ',') as route_direction
                  ,group_concat(distinct bslfor.busstop_name order by bslfor.busstop_id desc separator ',') as last_busstop_name
            from t_sbt_busstop bst
            left outer join t_sbt_busstop_lang bsl
              on bst.busstop_id  = bsl.busstop_id
           inner join t_sbt_busstop_buscompany bbc
              on bst.busstop_id  = bbc.busstop_id
            left outer join t_sbt_busdia bsd
              on bst.busstop_id  = bsd.busstop_id
             and bbc.buscompany_id = bsd.buscompany_id
            left outer join t_sbt_busdia bsdfor
              on bbc.buscompany_id = bsdfor.buscompany_id
             and bsd.bin_no  = bsdfor.bin_no
            left outer join t_sbt_busstop_lang bslfor
              on bsdfor.busstop_id  = bslfor.busstop_id
            left outer join v_sbt_busbin vbb
              on vbb.buscompany_id = bbc.buscompany_id
             and vbb.bin_no = bsd.bin_no
            left outer join t_sbt_route_course_lang rcl
              on rcl.buscompany_id = bbc.buscompany_id
             and rcl.course_id     = vbb.course_id
             and rcl.route_direction != ''
             and rcl.lang_cd       = :lang_cd
           where bsl.busstop_kana LIKE :initial_name"%" collate utf8_unicode_ci
             and bsdfor.first_last_flg = 'L'
             and bsdfor.busstop_id != bst.busstop_id
             and bsl.lang_cd = :lang_cd
             and bslfor.lang_cd = :lang_cd
           group by bst.busstop_id;
SQL;
        $param = array("initial_name" => $initial_name, "lang_cd" => 'ja');
        $result = $this->fetchAll($sql, $param);
        return $result;
    }
}
