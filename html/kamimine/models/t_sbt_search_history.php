<?php

require_once ('../base/base/BaseModel.php');

class t_sbt_search_history extends BaseModel {

    /**
     * 検索履歴保存
     */
    public function recordSearchHistory($search_datetime,
                                        $access_point,
                                        $device,
                                        $browser,
                                        $class,
                                        $buscompany_id,
                                        $course_id,
                                        $origin_busstop_id,
                                        $destination_busstop_id,
                                        $result_count) {
        $sql = <<<SQL
            INSERT INTO t_sbt_search_history VALUES(
                        :search_datetime,
                        :access_point,
                        :device,
                        :browser,
                        :class,
                        :buscompany_id,
                        :course_id,
                        :origin_busstop_id,
                        :destination_busstop_id,
                        :result_count
            );
SQL;
        $param = array(
            ":search_datetime"          => $search_datetime,
            ":access_point"             => $access_point,
            ":device"                   => $device,
            ":browser"                  => $browser,
            ":class"                    => $class,
            ":buscompany_id"            => $buscompany_id,
            ":course_id"                => $course_id,
            ":origin_busstop_id"        => $origin_busstop_id,
            ":destination_busstop_id"   => $destination_busstop_id,
            ":result_count"             => $result_count
        );

        return $this->query($sql, $param);
    }

    /**
     * 指定期間の検索履歴を取得
     */
    public function getSearchHistory($buscompany_id, $from, $to, $EXCEPT_IP_ADDRESS) {
        $sql = <<<SQL
            SELECT  srvdate date, 
                    device, 
                    IFNULL(count, 0) count 
            FROM    t_sbt_calendar 
                    LEFT JOIN (
                        SELECT  date(search_datetime) date, 
                                device, 
                                COUNT(*) count 
                        FROM    t_sbt_search_history
                        WHERE   buscompany_id = :buscompany_id
                        GROUP BY date(search_datetime), device
                    ) t1 
                    ON t1.date = srvdate 
            WHERE   srvdate BETWEEN :from and :to
                    AND buscompany_id = :buscompany_id
SQL;
        $param = array(
            ":buscompany_id"    => $buscompany_id,
            ":from"             => $from, 
            ":to"               => $to
        );

        return $this->fetchAll($sql, $param);
    }

    /**
     * 指定期間のバス停検索ランキングを取得
     */
    public function getSearchRanking($buscompany_id, $from, $to) {
        $sql = <<<SQL
            SELECT  SH.origin_busstop_id busstop_id,
                    BSL.busstop_name,
                    count(*) count
            FROM    t_sbt_search_history SH
                    LEFT JOIN (
                        SELECT  DISTINCT
                                SUBSTRING(busstop_id, 1, CHAR_LENGTH(busstop_id) - 2) busstop_id8,
                                busstop_name
                        FROM    t_sbt_busstop_lang
                        WHERE   lang_cd = :lang_cd
                    ) BSL
                        ON BSL.busstop_id8 = SH.origin_busstop_id
            WHERE   date(search_datetime) BETWEEN :from and :to
                    AND buscompany_id = :buscompany_id
            GROUP BY SH.origin_busstop_id, BSL.busstop_name
            ORDER BY count(*) DESC, SH.origin_busstop_id;
SQL;

        $param = array(
            ":buscompany_id"    => $buscompany_id,
            ":lang_cd"          => 'ja',
            ":from"             => $from, 
            ":to"               => $to
        );

        return $this->fetchAll($sql, $param);
    }

}
