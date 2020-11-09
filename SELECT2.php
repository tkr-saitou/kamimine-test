* バス停選択肢一覧を取得
     * 末尾2桁を除いた8桁の
     * バス停コードの末尾の２桁は同一名バス停の連番
     */

路 なし 出 なし 到 なし  アクション 出
if ($course_id == 0 &&  $arrival == 0 && $orientation == 0) {

SELECT  DISTINCT
SUBSTRING(BS.busstop_id, 1, CHAR_LENGTH(BS.busstop_id) - 2) busstop_id8,
BSL.busstop_name,
BSL.busstop_kana
FROM t_sbt_busstop BS
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
AND VDIA.first_last_flg <> "L"
ORDER BY BSL.busstop_kana, BSL.busstop_name



路 なし 出 なし 到 なし アクション 到
else if ($course_id == 0 && $departure == 0 && $orientation != 0) {

SELECT  DISTINCT
SUBSTRING(BS.busstop_id, 1, CHAR_LENGTH(BS.busstop_id) - 2) busstop_id8,
BSL.busstop_name,
BSL.busstop_kana
FROM t_sbt_busstop BS
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
ORDER BY BSL.busstop_kana, BSL.busstop_name


路 ◯  出 なし 到 なし  アクション 出
else if ($course_id != 0 && $arrival == 0 && $orientation == 0)

SELECT  DISTINCT
SUBSTRING(BS.busstop_id, 1, CHAR_LENGTH(BS.busstop_id) - 2) busstop_id8,
BSL.busstop_name,
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
WHERE 1 = 1
AND VDIA.first_last_flg <> "L"
AND VDIA.bin_no = (
SELECT  MIN(bin_no)
FROM v_sbt_busdia
WHERE buscompany_id = "KMM"
AND course_id = "01"
AND stop_seq = (
SELECT  MAX(stop_seq)
FROM v_sbt_busdia
WHERE buscompany_id = "KMM"
AND course_id = "01"
)
)
AND VDIA.course_id ="01"
AND VDIA.first_last_flg <> "L"
<!-- ORDER BY VDIA.stop_seq, SUBSTRING(BS.busstop_id, 1, CHAR_LENGTH(BS.busstop_id) - 2) -->

<!-- AND C.srvdate = CURDATE()-->

路 ◯  出 なし 到 なし  アクション 到
else if ($course_id != 0 && $arrival == 0 && $orientation != 0) {

SELECT  DISTINCT
SUBSTRING(BS.busstop_id, 1, CHAR_LENGTH(BS.busstop_id) - 2) busstop_id8,
BSL.busstop_name,
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
WHERE 1 = 1
AND VDIA.bin_no = (
SELECT  MIN(bin_no)
FROM v_sbt_busdia
WHERE buscompany_id = "KMM"
AND course_id = "01"
AND stop_seq = (
SELECT  MAX(stop_seq)
FROM v_sbt_busdia
WHERE buscompany_id = "KMM"
AND course_id = "01"
)
)
AND VDIA.course_id ="01"
AND VDIA.first_last_flg <> "F"
<!-- ORDER BY VDIA.stop_seq, SUBSTRING(BS.busstop_id, 1, CHAR_LENGTH(BS.busstop_id) - 2) -->

<!-- AND C.srvdate = CURDATE() -->

路 ◯  出 ◯ 到 なし  アクション 到
else if ($course_id != 0 && $departure != 0 && $orientation != 0) {

SELECT  DISTINCT
SUBSTRING(BS.busstop_id, 1, CHAR_LENGTH(BS.busstop_id) - 2) busstop_id8,
BSL.busstop_name
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
WHERE 1 = 1
AND VDIA.bin_no = (
SELECT  MIN(bin_no)
FROM v_sbt_busdia
WHERE buscompany_id = "KMM"
AND course_id = "01"
AND stop_seq = (
SELECT  MAX(stop_seq)
FROM v_sbt_busdia
WHERE buscompany_id = "KMM"
AND course_id = "01"
)
)
AND VDIA.course_id  = "01"
AND VDIA.first_last_flg <> "F"
AND VDIA.stop_seq > ANY (
SELECT VDIA.stop_seq from v_sbt_busdia VDIA
WHERE VDIA.course_id = "01"
AND VDIA.busstop_id = "0000010401"
)
<!-- ORDER BY VDIA.stop_seq, SUBSTRING(BS.busstop_id, 1, CHAR_LENGTH(BS.busstop_id) - 2) -->





路 ◯  出 なし  到 ◯  アクション 出
else if ($course_id != 0 && $arrival != 0 && $orientation == 0) {

SELECT  DISTINCT
SUBSTRING(BS.busstop_id, 1, CHAR_LENGTH(BS.busstop_id) - 2) busstop_id8,
BSL.busstop_name
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
WHERE 1 = 1
AND VDIA.bin_no = (
SELECT  MIN(bin_no)
FROM v_sbt_busdia
WHERE buscompany_id = "KMM"
AND course_id = "01"
AND stop_seq = (
SELECT  MAX(stop_seq)
FROM v_sbt_busdia
WHERE buscompany_id = "KMM"
AND course_id = "01"
)
)
AND VDIA.course_id  = "01"
AND VDIA.first_last_flg <> "L"
AND VDIA.stop_seq < ANY (
SELECT VDIA.stop_seq from v_sbt_busdia VDIA
WHERE VDIA.course_id = "01"
AND VDIA.busstop_id = "0000010401"
)
<!-- ORDER BY VDIA.stop_seq, SUBSTRING(BS.busstop_id, 1, CHAR_LENGTH(BS.busstop_id) - 2) -->