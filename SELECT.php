SELECT
distinct X.course_id
FROM
(
SELECT
D.course_id, D.stop_seq
FROM
v_sbt_busdia AS D
INNER JOIN
t_sbt_route_course_lang AS R ON D.course_id = R.course_id
INNER JOIN
t_sbt_calendar AS C ON D.ybkbn = C.ybkbn
WHERE
C.srvdate = CURDATE() AND
SUBSTRING(D.busstop_id, 1, CHAR_LENGTH(D.busstop_id) - 2) = "00000103"
GROUP BY
D.course_id, D.busstop_id, D.stop_seq
) AS X
LEFT JOIN
(
SELECT
D.course_id, D.stop_seq
FROM
v_sbt_busdia AS D
INNER JOIN
t_sbt_calendar AS C ON D.ybkbn = C.ybkbn
WHERE
C.srvdate = CURDATE()
GROUP BY
D.course_id, D.busstop_id, D.stop_seq
) AS Y ON X.course_id = Y.course_id
WHERE
X.stop_seq < Y.stop_seq
ORDER BY
X.course_id

SELECT
distinct D.busstop_id, D.stop_seq
FROM
v_sbt_busdia AS D
INNER JOIN
t_sbt_calendar AS C ON D.ybkbn = C.ybkbn
WHERE
C.srvdate = CURDATE() AND
D.course_id = "03" AND
SUBSTRING(D.busstop_id, 1, CHAR_LENGTH(D.busstop_id) - 2) != "00000103" AND
D.stop_seq > 5
ORDER BY
D.stop_seq


public static function getBusstopOrder($link, $rosen, $busstop, $max)
{
<!-- $order = $max ? 'DESC' : 'ASC'; -->
SELECT
D.stop_seq
FROM
v_sbt_busdia AS D
INNER JOIN
t_sbt_calendar AS C ON D.ybkbn = C.ybkbn
WHERE
C.srvdate = CURDATE() AND
D.course_id = "03" AND
SUBSTRING(D.busstop_id, 1, CHAR_LENGTH(D.busstop_id) - 2) = "00000103"
ORDER BY
D.stop_seq
LIMIT 1

<!-- 路 なし 出 なし 到 なし  アクション　出 -->
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

<!-- 路 なし出 なし 到 なし アクション　到 -->
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
AND VDIA.first_last_flg <> "F"

<!-- 路 ◯  出 なし 到 なし  アクション　出 -->

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
AND VDIA.course_id = "01"
AND VDIA.first_last_flg <> "L"

:course_id

<!-- 路 ◯  出 なし 到 なし  アクション　到 -->

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
AND VDIA.course_id = "01"
AND VDIA.first_last_flg <> "F"

<!-- :course_id -->

<!-- 路 ◯  出 ◯ 到 なし  アクション　到 -->

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
AND VDIA.course_id ="01"
AND VDIA.first_last_flg <> "F"
AND VDIA.stop_seq  > 5

<!-- :course_id -->
<!-- :stop_seq -->



<!-- 路 ◯  出 なし  到 ◯  アクション　出 -->

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
AND VDIA.course_id  = "01"
AND VDIA.first_last_flg <> "L"
AND VDIA.stop_seq < 5

<!-- :course_id -->
<!-- :stop_seq -->

