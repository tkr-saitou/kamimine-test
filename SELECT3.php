

1 路 なし 出 なし 到 なし
if ($course_id == 0 &&  $departure == 0 && $arrival == 0)

SELECT  DISTINCT
 BS.busstop_id,
                    BSL.busstop_name,
                    BS.lat,
                    BS.lng,
                    VDIA.stop_seq
            FROM    t_sbt_busstop BS
                    LEFT JOIN t_sbt_busstop_lang BSL
                        ON BSL.busstop_id = BS.busstop_id
                        AND BSL.lang_cd = "ja"
                    LEFT JOIN v_sbt_busdia VDIA
                        ON VDIA.buscompany_id = "KMM"
                        AND VDIA.busstop_id = BS.busstop_id
                    LEFT JOIN v_sbt_busstop_category VBSC
                        ON VBSC.buscompany_id = "KMM"
                        AND VBSC.busstop_id = BS.busstop_id
                    INNER JOIN t_sbt_calendar AS C
                    ON VDIA.ybkbn = C.ybkbn
            WHERE   1 = 1

<!-- 2 路 なし 出 なし 到 ◯
else if ($course_id == 0 &&  $departure == 0 && $arrival != 0)

SELECT  DISTINCT
 BS.busstop_id,
                    BSL.busstop_name,
                    BS.lat,
                    BS.lng,
                    VDIA.stop_seq
            FROM    t_sbt_busstop BS
                    LEFT JOIN t_sbt_busstop_lang BSL
                        ON BSL.busstop_id = BS.busstop_id
                        AND BSL.lang_cd = "ja"
                    LEFT JOIN v_sbt_busdia VDIA
                        ON VDIA.buscompany_id = "KMM"
                        AND VDIA.busstop_id = BS.busstop_id
                    LEFT JOIN v_sbt_busstop_category VBSC
                        ON VBSC.buscompany_id = "KMM"
                        AND VBSC.busstop_id = BS.busstop_id
                    INNER JOIN t_sbt_calendar AS C
                    ON VDIA.ybkbn = C.ybkbn
            WHERE   1 = 1
AND C.srvdate = CURDATE()
AND VDIA.first_last_flg <> "L"

= <!-- 路 なし 出 なし 到 なし  アクション　出 -->

<!-- 3 路 なし  出 ◯ 到 なし
else if ($course_id == 0 &&  $departure != 0 && $arrival == 0)

SELECT  DISTINCT
 BS.busstop_id,
                    BSL.busstop_name,
                    BS.lat,
                    BS.lng,
                    VDIA.stop_seq
            FROM    t_sbt_busstop BS
                    LEFT JOIN t_sbt_busstop_lang BSL
                        ON BSL.busstop_id = BS.busstop_id
                        AND BSL.lang_cd = "ja"
                    LEFT JOIN v_sbt_busdia VDIA
                        ON VDIA.buscompany_id = "KMM"
                        AND VDIA.busstop_id = BS.busstop_id
                    LEFT JOIN v_sbt_busstop_category VBSC
                        ON VBSC.buscompany_id = "KMM"
                        AND VBSC.busstop_id = BS.busstop_id
                    INNER JOIN t_sbt_calendar AS C
                    ON VDIA.ybkbn = C.ybkbn
            WHERE   1 = 1
AND C.srvdate = CURDATE()
AND VDIA.first_last_flg <> "F" --> -->

<!-- 4 路 なし  出 ◯ 到 ◯
else if ($course_id == 0 &&  $departure != 0 && $arrival != 0) -->



5 路 ◯  出 なし 到 なし
else if ($course_id != 0 &&  $departure == 0 && $arrival == 0)

SELECT  DISTINCT
 BS.busstop_id,
                    BSL.busstop_name,
                    BS.lat,
                    BS.lng
            FROM    t_sbt_busstop BS
                    LEFT JOIN t_sbt_busstop_lang BSL
                        ON BSL.busstop_id = BS.busstop_id
                        AND BSL.lang_cd = "ja"
                    LEFT JOIN v_sbt_busdia VDIA
                        ON VDIA.buscompany_id = "KMM"
                        AND VDIA.busstop_id = BS.busstop_id
                    LEFT JOIN v_sbt_busstop_category VBSC
                        ON VBSC.buscompany_id = "KMM"
                        AND VBSC.busstop_id = BS.busstop_id
                    INNER JOIN t_sbt_calendar AS C
                    ON VDIA.ybkbn = C.ybkbn
            WHERE   1 = 1
AND VDIA.course_id = "01"


6 路 ◯  出 なし  到 ◯
else if ($course_id != 0 &&  $departure == 0 && $arrival != 0)

SELECT  DISTINCT
                    BSL.busstop_name,
                    BS.lat,
                    BS.lng,
                    VDIA.stop_seq
            FROM  t_sbt_busstop BS
                    LEFT JOIN t_sbt_busstop_lang BSL
                        ON BSL.busstop_id = BS.busstop_id
                        AND BSL.lang_cd = "ja"
                    LEFT JOIN v_sbt_busdia VDIA
                        ON VDIA.buscompany_id = "KMM"
                        AND VDIA.busstop_id = BS.busstop_id
                    LEFT JOIN v_sbt_busstop_category VBSC
                        ON VBSC.buscompany_id = "KMM"
                        AND VBSC.busstop_id = BS.busstop_id
                    INNER JOIN t_sbt_calendar AS C
                        ON VDIA.ybkbn = C.ybkbn
                        WHERE   1 = 1
                        AND VDIA.bin_no = (
                            SELECT  MIN(bin_no)
                            FROM    v_sbt_busdia
                            WHERE   buscompany_id = "KMM"
                                    AND course_id = "01"
                                    AND stop_seq = (
                                        SELECT  MAX(stop_seq)
                                        FROM    v_sbt_busdia
                                        WHERE   buscompany_id = "KMM"
                                                AND course_id = "01"
                                    )
                            )
                AND     VDIA.course_id = "01"
                AND     VDIA.first_last_flg <> "L"
                AND     VDIA.stop_seq <=  ANY (
                            SELECT  VDIA.stop_seq
                            FROM    v_sbt_busdia VDIA
                            WHERE   VDIA.course_id = "01"
                            AND     VDIA.busstop_id = "0000010401"
                                    )

7 路 ◯  出 ◯ 到 なし
else if ($course_id != 0 &&  $departure != 0 && $arrival == 0)

SELECT  DISTINCT
                    BSL.busstop_name,
                    BS.lat,
                    BS.lng,
                    VDIA.stop_seq
            FROM  t_sbt_busstop BS
                    LEFT JOIN t_sbt_busstop_lang BSL
                        ON BSL.busstop_id = BS.busstop_id
                        AND BSL.lang_cd = "ja"
                    LEFT JOIN v_sbt_busdia VDIA
                        ON VDIA.buscompany_id = "KMM"
                        AND VDIA.busstop_id = BS.busstop_id
                    LEFT JOIN v_sbt_busstop_category VBSC
                        ON VBSC.buscompany_id = "KMM"
                        AND VBSC.busstop_id = BS.busstop_id
                    INNER JOIN t_sbt_calendar AS C
                        ON VDIA.ybkbn = C.ybkbn
                    WHERE   1 = 1
                        AND VDIA.bin_no = (
                            SELECT  MIN(bin_no)
                            FROM    v_sbt_busdia
                            WHERE   buscompany_id = "KMM"
                                    AND course_id = "01"
                                    AND stop_seq = (
                                        SELECT  MAX(stop_seq)
                                        FROM    v_sbt_busdia
                                        WHERE   buscompany_id = "KMM"
                                                AND course_id = "01"
                                    )
                            )
                AND     VDIA.course_id = "01"
                AND     VDIA.first_last_flg <> "F"
                AND     VDIA.stop_seq >=  ANY (
                            SELECT  VDIA.stop_seq
                            FROM    v_sbt_busdia VDIA
                            WHERE   VDIA.course_id = "01"
                            AND     VDIA.busstop_id = "0000010401"
                                    )


8 路 ◯  出 ◯ 到 ◯
else if ($course_id != 0 &&  $departure != 0 && $arrival != 0)

SELECT  DISTINCT
                    BSL.busstop_name,
                    BS.lat,
                    BS.lng,
                    VDIA.stop_seq
            FROM  t_sbt_busstop BS
                    LEFT JOIN t_sbt_busstop_lang BSL
                        ON BSL.busstop_id = BS.busstop_id
                        AND BSL.lang_cd = "ja"
                    LEFT JOIN v_sbt_busdia VDIA
                        ON VDIA.buscompany_id = "KMM"
                        AND VDIA.busstop_id = BS.busstop_id
                    LEFT JOIN v_sbt_busstop_category VBSC
                        ON VBSC.buscompany_id = "KMM"
                        AND VBSC.busstop_id = BS.busstop_id
                    INNER JOIN t_sbt_calendar AS C
                        ON VDIA.ybkbn = C.ybkbn
                        WHERE   1 = 1
                        AND VDIA.bin_no = (
                            SELECT  MIN(bin_no)
                            FROM    v_sbt_busdia
                            WHERE   buscompany_id = "KMM"
                                    AND course_id = "01"
                                    AND stop_seq = (
                                        SELECT  MAX(stop_seq)
                                        FROM    v_sbt_busdia
                                        WHERE   buscompany_id = "KMM"
                                                AND course_id = "01"
                                    )
                            )
                        AND VDIA.course_id = "01"
                        AND VDIA.stop_seq = ANY (
                            SELECT  VDIA.stop_seq
                            FROM    v_sbt_busdia VDIA
                            WHERE   VDIA.course_id = "01"
                            AND     VDIA.busstop_id IN("0000010401", "0000010601")
                                    )
                        <!-- AND VDIA.course_id = "01"
                        AND VDIA.stop_seq >=  ANY (
                            SELECT  VDIA.stop_seq
                            FROM    v_sbt_busdia VDIA
                            WHERE   VDIA.course_id = "01"
                            AND     VDIA.busstop_id = "0000010601" -->
                                    )

