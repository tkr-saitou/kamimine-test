<?php

/**
 * 各種ユーティリティクラス
 *
 * すべてstaticなメソッドとして定義しているため、Util::で呼び出し可能
 */
class Util {

    /**
     * htmlspecialchars省略版<br>
     * 特殊文字をエスケープする。セキュリティ対策のため、HTML上に文字列を表示する場合は必ず使用すること。
     */
    public static function h($str) {
        return htmlspecialchars($str, ENT_QUOTES);
    }

    /**
     * 使用可能タグを置換後htmlspecialchars()をかけて使用可能タグを元に戻す<br>
     * (<B>,<I>,<U>の三種)
     */
    public static function setAllowedTag($str) {
        $str = preg_replace("/<([\/]?)([BIU]{1})>/", "%$1$2%", $str);
        $str = self::h($str);
        $str = preg_replace("/%([\/]?)([BUI]{1})%/", "<$1$2>", $str);
        return $str;
    }

    /**
     * print_r整形版
     */
    function fpr($array) {
        if (!is_array($array)) {
            print("not array!");
        } else {
            print("<pre>");
            print_r($array);
            print("</pre>");
        }
    }

    /**
     * 空文字だった場合にNULLに置換<br>
     * DBのdate型やint型に''空文字を入れるとエラーとなるためNULL置換する際などに使用
     */
    public static function emptyToNull ($value) {
        if($value === 0) {
            return $value;
        } elseif($value == "") {
            return null;
        } elseif(empty($value)) {
            return null;
        } else {
            return $value;
        }
    }

    /**
     * NULLを空文字に置換<br>
     */
    public static function nullToEmpty($value) {
        if(is_null($value)) {
            return "";
        } elseif(strtoupper($value) == "NULL") {
            return "";
        } else {
            return $value;
        }
    }

    /**
     * 0だった場合に""に置換する 
     */
    public static function zeroToEmpty ($value) {
        if($value == 0) {
            return "";
        } else {
            return $value;
        }
    }

    /* 入力チェック ----------------------------------------------------------------------------------------- */

    /**
     * 必須チェック
     */
    public static function chkNotNull($obj, $idx, &$errs, $text, $opt = 0) {
        if (!isset($obj[$idx]) || $obj[$idx] == NULL || strcmp($obj[$idx], '') == 0) {
            if ($opt == 0) {
                $errs[$idx] = $text.'が入力されていません。';
            } else {
                $errs[$idx] = $text.'が選択されていません。';
            }
        }
    }

    /*
     * 入力文字数チェック
     */
    public static function chkMaxLength($obj, $idx, $maxLength, &$errs, $text) {
        if (isset($obj[$idx]) && $maxLength < mb_strlen($obj[$idx],"utf-8")) {
            $errs[$idx] = $text.'は'.$maxLength.'文字以内で入力してください。';
        }
    }

    /**
     * 範囲内の整数（十進数）かどうか
     */
    public static function isNum($nStr, $min, $max) {
        if (preg_match("/^(0|[1-9][0-9]*)$/", $nStr)) {
            if ($nStr >= $min && $nStr <= $max) {
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * 配列にいずれか１つでも値が入っているかチェックする
     * @return true: 値入っているデータ有り, false: すべての要素に値が入っていない
     */
    public static function checkInputAny($arr) {
        foreach($arr as $i => $value) {
            if(!empty($value)) return true;
        }
        return false;
    }

    /**
     * 配列にすべて値が埋まっているかチェックする
     * @return : チェックOK(すべてに値が埋まっている)          →　空配列array()を返却
     * @return : チェックNG(1つでも値が埋まっていない要素あり) →　該当要素の添字をarrayで返却
     */
    public static function checkInputAll($arr) {
        $result_arr = array();
        $cnt = 0;
        foreach($arr as $i => $value) {
            if(empty($value)) {
                array_push($result_arr, $i);
            } else {
                $cnt++;
            }
        }
        if($cnt == count($arr)) {
            return array();
        } else {
            return $result_arr;
        }
    }

    /* 文字列操作 ------------------------------------------------------------------------------------------- */

    /**
     * $textに文字列$wordが含まれている場合はtrueを返却
     */
    public static function contains ($text, $word) {
        if(strpos($text, $word) !==false ){
            return true;
        } else {
            return false;
        }
    }

    /**
     * 文字幅を取得する 
     * 全角文字を1、半角文字を0.5としてカウント
     */
    public static function getStrWidth ($str) {
        return mb_strwidth($str,'UTF-8')/2;
    }

    /* 数値操作 --------------------------------------------------------------------------------------------- */

    /**
     * 通貨表示
     *
     * 3桁区切りカンマを付与、小数点以下切り捨て 
     */
    public static function formatCurrency ($currency) {
        return number_format(floor($currency));
    }

    /**
     * 小数点以下が0の場合は非表示
     * ex. 1.0 -> 1, 1.1 -> 1.1
     */
    public static function removePointZero($num) {
        return strval(doubleval($num));
    }

    /* 日付・時刻 ------------------------------------------------------------------------------------------- */

    /**
     * YYYYを返却 
     * 業務年度ではなく単純に年を返却。ex.2015/03/01の場合、2014ではなく2015が返却される
     * @param date $date 日付
     * $dateには6桁のYYYYMMを渡すことも可能
     */
    public static function getYear($date,$option='Y') {
        if(mb_strlen($date) == 6) {
            return mb_substr($date,0,4);
        } else {
            return date($option,strtotime($date));
        }
    }

    /**
     * MMを返却 
     * @param date $date 日付
     * $dateには6桁のYYYYMMを渡すことも可能
     */
    public static function getMonth($date,$option='m') {
        if(mb_strlen($date) == 6) {
            return mb_substr($date,4,2);
        } else {
            return date($option,strtotime($date));
        }
    }

    /**
     * DDを返却 
     */
    public static function getDay($date,$option='d') {
        return date($option,strtotime($date));
    }

    /**
     * YYYY/MM/DD形式を返却 
     */
    public static function formatDate ($date) {
        if(empty($date)) {
            return "";
        } else {
            return substr(str_replace("-","/",$date),0,10);
        }
    }
    /**
     * YYYY/MM形式を返却
     */
    public static function formatDateYYYYMM($date) {
        if(empty($date)) {
            return "";
        } else {
            return substr(str_replace("-","/",$date),0,7);
        }
    }
    /**
     * YY/MM/DD形式を返却 (年2桁）
     */
    public static function formatDateYYMMDD ($date) {
        if(empty($date)) {
            return "";
        } else {
            return substr(str_replace("-","/",$date),2,8);
        }
    }
    /**
     * MM/DD形式を返却 
     */
    public static function formatDateMMDD ($date) {
        if(empty($date)) {
            return "";
        } else {
            return substr(str_replace("-","/",$date),5,5);
        }
    }

    /**
     * YYYY年MM月DD日形式を返却  ※ゼロは付かない ex.2015/08/08 -> 2015年8月8日
     */
    public static function formatDateJa ($date) {
        if(empty($date)) {
            return "";
        } else {
            //$date = DateTime::createFromFormat('Y年m月d日H時i分s秒', '2014年5月9日16時30分45秒');
            $date = DateTime::createFromFormat('Y-m-d', $date);
            return $date->format('Y年n月j日');
        }
    }

    /**
     * 和暦記号YY.MM.DD形式を返却 ex.H26.01.01
     * ※平成のみ対応している
     */
    public static function formatDateEra ($date,$arg="PERIOD") {
        if(empty($date)) {
            return "";
        } else {
            $year = self::getEra(substr($date,0,4));
            $month = substr($date,5,2);
            $day = substr($date,8,2);
            if($arg == "PERIOD") {
                return "H".$year.".".$month.".".$day;
            } else {
                return "H".$year."/".$month."/".$day;
            }
        }
    }

    /**
     * 和暦記号YY.MM.DD形式を返却 ex.平成26年01月01日
     * ※平成のみ対応している
     */
    public static function formatDateEraJa ($date) {
        if(empty($date)) {
            return "";
        } else {
            $year = self::getEra(substr($date,0,4));
            $month = substr($date,5,2);
            $day = substr($date,8,2);
            return "平成".$year."年".$month."月".$day."日";
        }
    }

    /**
     * 和暦記号YY.MM形式を返却 ex.H26.01
     * ※平成のみ対応している
     */
    public static function formatDateYYMMEra ($date) {
        if(empty($date)) {
            return "";
        } else {
            $year = self::getEra(substr($date,0,4));
            $month = substr($date,5,2);
            return "H".$year.".".$month;
        }
    }

    /**
     * 時刻を返却 ex.9:00
     */
    public static function formatTime ($time) {
        $hantei = substr($time,0,1);
        if($hantei == "0") {
            $jikoku = substr($time,1,4);
        } else {
            $jikoku = substr($time,0,5);
        }
            return $jikoku;
    }

    /**
     * 西暦→和暦変換
     * ※平成のみ対応している
     * @param $arg: 指定なし→年のみ返却  (ex. 26)
     *              SIMBOL  →記号付で返却(ex. H26)
     *              ERA     →年号付で返却(ex. 平成26)
     */
    public static function getEra ($year,$arg=null) {
        $era = $year - 1988;
        if(strtoupper($arg) == "SIMBOL") {
            return "H".$era;
        } elseif(strtoupper($arg) == "ERA") {
            return "平成".$era;
        } else {
            return $era;
        }
    }

    /**
     * 曜日取得 
     */
    public static function getDayOfWeek ($date) {
        if(empty($date)) return "";
        $datetime = new DateTime($date);
        $week = array("日", "月", "火", "水", "木", "金", "土");
        $w = (int)$datetime->format('w');
        return $week[$w];
    }

    /**
     * 月数計算
     * $fromと$toの間の月数を返却する
     * ex. $from=2015-08-01/$to=2015-09-15の場合→2
     */
    public static function getMonthNum($from,$to) {
        $datefrom=strtotime($from);
        $dateto=strtotime($to);
        $monthfrom=date("Y",$datefrom)*12+date("m",$datefrom);
        $monthto=date("Y",$dateto)*12+date("m",$dateto);
        return $monthto - $monthfrom + 1;
    }

    /**
     * 翌月取得
     * @return 翌月初日(ex.2015-01-31 -> 2015-02-01)を返却
     *         ※02-31は存在しないため03月を戻してしまうのを避けるため。
     */
    public static function getNextMonth($date) {
        $first_date = date("Y-m-1", strtotime($date));
        return date("Y-m-d",strtotime($first_date . "+1 month"));
    }

    /**
     * YYYYMMより月初日取得
     * @return YYYY-MM-DD形式
     */
    public static function getFirstDate($yearmonth) {
        $ym =  substr($yearmonth,0,4).'-'.substr($yearmonth,4,2);
        return date('Y-m-d', strtotime('first day of ' . $ym));
    }

    /**
     * YYYYMMより月末日取得
     * @return YYYY-MM-DD形式
     */
    public static function getLastDate($yearmonth) {
        $ym =  substr($yearmonth,0,4).'-'.substr($yearmonth,4,2);
        return date('Y-m-d', strtotime('last day of ' . $ym));
    }

    /**
     * 年度末日取得(3月にのみ対応している)
     */
    public static function getLastDateOfBizYear($date) {
        $date = new DateTime($date);
        //$year = $date->format('Y');
        //return $date->format('Y-m');
        return date('Y-m-d', strtotime('last day of ' . $month));
        return date('Y-m-d', strtotime('last day of 2015-03'));
    }


    /**
     * 年度取得
     * @description 実年度ではなく業務年度を取得する(2015/03 → 2014)
     */
    public static function getBizYear($date, $start_month=4) {
        if ($start_month < 1 || $start_month > 12) {
            return false;
        }
        $year = $date['year'];
        $month = $date['mon'] - ($start_month - 1);
        $result = getdate(mktime(0, 0, 0, $month, 1, $year));
        return $result['year'];
    }

    /**
     * 2つの日付間の差分を取得
     * yyyy/mm/dd等(時刻無)
     * 開始は00:00、終了は24:00とみなし+1
     */
    public static function diffDate($from, $to) {
        $fromT = strtotime($from);
        $toT = strtotime($to);
        $diff = $toT - $fromT;
        if ($diff < 0) return -1;
        else if ($diff == 0) return 0;
        $diffD = (int) ($diff / (24 * 60 * 60));
        return $diffD + 1;
    }

    /*
     * 2つの時刻間の差分を取得
     * 秒まで計算なら引数$isSecを1に
     * 控除有りなら引数$dedに
     */
    public static function diffTime($from, $to, $isSec, $ded = "00:00:00") {
        //  $fromT = strtotime($from);
        //  $toT = strtotime($to);
        list($fHour, $fMin, $fSec) = explode(":", $from) + array(0,0,0);
        $fromT = $fHour * 3600 + $fMin * 60 + $fSec;
        list($tHour, $tMin, $tSec) = explode(":", $to) + array(0,0,0);
        $toT = $tHour * 3600 + $tMin * 60 + $tSec;
        list($dHour, $dMin, $dSec) = explode(":", $ded) + array(0,0,0);
        $diff = $toT - $fromT;
        if ($diff < 0) $diff += 24 * 60 * 60;
        $hour = (int) ($diff / 3600);
        $min = (int) (($diff - $hour * 3600) / 60);
        if ($isSec == 1) {
            $sec = $diff % 60;
            $sec = $sec - $dSec;
            if ($sec < 0) {
                $sec += 60;
                $min += -1;
                if ($min < 0) {
                    $min += 60;
                    $hour += -1;
                }
            }
            if ($sec < 10) $sec = "0".$sec;
        }
        $min = $min - $dMin;
        if ($min < 0) {
            $min += 60;
            $hour += -1;
        }
        if ($min < 10) $min = "0".$min;
        $hour = $hour - $dHour;
        if ($hour < 0) return -1;
        if ($isSec != 1) {
            return $hour.":".$min;
        } else {
            return $hour.":".$min.":".$sec;
        }
    }

    /**
     * 2つの時刻の大小を比較
     */
    public static function cmpTime($time1, $time2) {
        list($hour1, $min1, $sec1) = explode(":", $time1) + array(0,0,0);
        list($hour2, $min2, $sec2) = explode(":", $time2) + array(0,0,0);
        if ($hour1 > $hour2) {
            return 1;
        } else if ($hour1 < $hour2) {
            return -1;
        } else {
            if ($min1 > $min2) {
                return 1;
            } else if ($min1 < $min2) {
                return -1;
            } else {
                if ($sec1 > $sec2) {
                    return 1;
                } else if ($sec1 < $sec2) {
                    return -1;
                } else {
                    return 0;
                }
            }
        }
    }

    /* 住所関連 --------------------------------------------------------------------------------------------- */

    /**
     * 郵便番号フォーマット作成
     * 1234567 →123-4567 にする
    */
    public static function formatZipCd ($zipcd) {
        return substr($zipcd,0,3).'-'.substr($zipcd,3);
    }

    /**
     * 住所フォーマット整形
     */
    public static function formatAddress($state_cd,$city_name,$address) {
        return self::$state[$state_cd].$city_name.$address;
    }
    private static $state = array("1" => "北海道", "2" => "青森県", "3" => "岩手県", "4" => "宮城県", "5" => "秋田県", "6" => "山形県", "7" => "福島県",
     "8" => "茨城県", "9" => "栃木県", "10" => "群馬県", "11" => "埼玉県", "12" => "千葉県", "13" => "東京都", "14" => "神奈川県", "15" => "新潟県",
     "16" => "富山県", "17" => "石川県", "18" => "福井県", "19" => "山梨県", "20" => "長野県", "21" => "岐阜県", "22" => "静岡県", "23" => "愛知県",
     "24" => "三重県", "25" => "滋賀県", "26" => "京都府", "27" => "大阪府", "28" => "兵庫県", "29" => "奈良県", "30" => "和歌山県", "31" => "鳥取県",
     "32" => "島根県", "33" => "岡山県", "34" => "広島県", "35" => "山口県", "36" => "徳島県", "37" => "香川県", "38" => "愛媛県", "39" => "高知県",
     "40" => "福岡県", "41" => "佐賀県", "42" => "長崎県", "43" => "熊本県", "44" => "大分県", "45" => "宮崎県", "46" => "鹿児島県", "47" => "沖縄県");

    /**
     * 都道府県コード取得
     */
    public static function getStateCd($state_name) {
        return self::$statename[$state_name];
    }
    private static $statename = array("北海道" => "1", "青森県" => "2", "岩手県" => "3", "宮城県" => "4", "秋田県" => "5", "山形県" => "6", "福島県" => "7",
    "茨城県" => "8", "栃木県" => "9", "群馬県" => "10", "埼玉県" => "11", "千葉県" => "12", "東京都" => "13", "神奈川県" => "14", "新潟県" => "15",
    "富山県" => "16", "石川県" => "17", "福井県" => "18", "山梨県" => "19", "長野県" => "20", "岐阜県" => "21", "静岡県" => "22", "愛知県" => "23",
    "三重県" => "24", "滋賀県" => "25", "京都府" => "26", "大阪府" => "27", "兵庫県" => "28", "奈良県" => "29", "和歌山県" => "30", "鳥取県" => "31",
    "島根県" => "32", "岡山県" => "33", "広島県" => "34", "山口県" => "35", "徳島県" => "36", "香川県" => "37", "愛媛県" => "38", "高知県" => "39",
    "福岡県" => "40", "佐賀県" => "41", "長崎県" => "42", "熊本県" => "43", "大分県" => "44", "宮崎県" => "45", "鹿児島県" => "46", "沖縄県" => "47");

    /**
     * 郵便番号から住所検索
     * @param int $zip
     * @return mixed 住所情報
     */
    public static function getAddress($zip) {
        // 郵便番号CSV
        $file_name = 'lib/zipcd/KEN_ALL.CSV';
        $fp = @fopen($file_name, 'r');

        $i = 0;
        while (($buffer = fgetcsv($fp, 200, ',')) !== FALSE) {
            // 郵便番号
            $data[$i]['zip_cd'] = $buffer[2];
            if ($zip == $data[$i]['zip_cd']) {
                // 都道府県
                $data[$i]['state_name'] = mb_convert_encoding($buffer[6], 'UTF-8', 'SJIS');
                $data[$i]['state_cd'] = self::getStateCd($data[$i]['state_name']);
                // 市区町村
                $data[$i]['city_name'] = mb_convert_encoding($buffer[7], 'UTF-8', 'SJIS');
                // 町名
                $data[$i]['address'] = mb_convert_encoding($buffer[8], 'UTF-8', 'SJIS');
                // 半角英数字へ置換
                $data[$i]['address'] = mb_convert_kana($data[$i]['address'], 'a', 'UTF-8');
                // 以下に掲載がない場合、（次のビルを除く）（地階・階層不明）などを削除
                $data[$i]['address'] = preg_replace('/以下に掲載がない場合|\(.*\)/', '', $data[$i]['address']);
                return $data[$i];
            } 
            $i++;
        }
        fclose($fp);
        /*
        // 事業所CSV
        $file_name ='lib/zipcd/JIGYOSYO.CSV';
        $fp = @fopen($file_name, 'r');

        while (($buffer = fgetcsv($fp, 200, ',')) !== FALSE) {
            // 郵便番号
            $data[$i]['zip'] = $buffer[7];

            if ($zip == $data[$i]['zip']) {
                // 社名
                // $data[$i]['company'] = mb_convert_encoding($buffer[2], 'UTF-8', 'SJIS');
                // 都道府県
                $data[$i]['state'] = mb_convert_encoding($buffer[3], 'UTF-8', 'SJIS');
                $data[$i]['state_cd'] = self::getStateCd($data[$i]['state']);
                // 市区町村
                $data[$i]['city'] = mb_convert_encoding($buffer[4], 'UTF-8', 'SJIS');
                // 町名
                $data[$i]['street'] = mb_convert_encoding($buffer[5] . $buffer[6], 'UTF-8', 'SJIS');
                // 半角英数字へ置換
                $data[$i]['street'] = mb_convert_kana($data[$i]['street'], 'a', 'UTF-8');
                return $data[$i];
            }
            $i++;
        }
        fclose($fp);    
        */
        return;
    }

    /* 地図関連 --------------------------------------------------------------------------------------------- */

    const a = 6378137.000000;   // WGS84 楕円体モデルの地球赤道半径 (長半径) (m)
    const b = 6356752.314245;   // WGS84 楕円体モデルの地球極半径 (短半径) (m)
    /**
     * 2点間の緯度経度より距離を算出 
     * ヒュベニの公式に基づき緯度経度で与えられた２点間の距離を計算する。
     * @param $point1: array('lon' => float, 'lat' => float)
     * @param $point2: array('lon' => float, 'lat' => float)
     */
    public static function calcDistance($lat1,$lng1,$lat2,$lng2) {
        /*
        $lat1 = $point1['lat'];
        $lon1 = $point1['lon'];
        $lat2 = $point2['lat'];
        $lon2 = $point2['lon'];
        */
        if(empty($lat1)||empty($lng1)||empty($lat2)||empty($lng2)) return null;

        $e2 = (pow(self::a, 2) - pow(self::b, 2))/pow(self::a, 2);  // 第一離心率の二乗値

        $x1 = deg2rad($lng1);   // 経度 ⇒ ラジアン変換
        $y1 = deg2rad($lat1);   // 緯度 ⇒ ラジアン変換
        $x2 = deg2rad($lng2);   // 経度 ⇒ ラジアン変換
        $y2 = deg2rad($lat2);   // 緯度 ⇒ ラジアン変換

        $dy = $y1 - $y2;    // 緯度の差
        $dx = $x1 - $x2;    // 経度の差
        $mu_y = ($y1 + $y2) / 2.0;  // 緯度の平均値
        $W = sqrt(1.0 - ($e2 * pow(sin($mu_y), 2)));

        $N = self::a / $W;  // 卯酉線曲率半径

        $M = (self::a * (1 - $e2)) / pow($W, 3);    // 子午線曲率半径
        
        return sqrt(pow($dy * $M, 2) + pow($dx*$N*cos($mu_y), 2)) / 1000;  // ２点間の距離 (km);
    }

    /* HTTP関連 --------------------------------------------------------------------------------------------- */

    /**
     * HTTP_USER_AGENTよりデバイスおよびブラウザの種類を判定して返却する
     * @return デバイス/ブラウザのarrayを返却  ex. array([device] => Windows, [browser] => IE11)
     */
    public static function getDeviceBrowserName($ua) {

        // デバイス判定
        $device = null;
        if (preg_match("/iPhone/", $ua)) {
            $device = 'iPhone';
        } elseif (preg_match("/iPad/", $ua)) {
            $device = 'iPad';
        } elseif (preg_match("/Android/", $ua)) {
            $device = 'Android';
        } elseif (preg_match("/Windows/", $ua)) {
            $device = 'Windows';
        } elseif (preg_match("/Macintosh/", $ua)) {
            $device = 'Mac';
        } else {
            $device = 'Unknown';
        }

        // ブラウザ判定
        $browser = null;
        $version = null;
        if (preg_match("/Chrome/", $ua)) {
            $browser = 'Chrome';
        } elseif (preg_match("/Firefox/", $ua)) {
            $browser = 'Firefox';
        } elseif (preg_match("/Safari/", $ua)) {
            $browser = 'Safari';
        } elseif (preg_match('/Trident\/(\d{1,}(.\d{1,}){1,}?)/i', $ua, $mtcs)) {
            $browser = 'IE';
            if ((float)$mtcs[1] >= 7) {
                if (preg_match('/rv:(\d{1,}(.\d{1,}){1,}?)/i', $ua, $mtcs)) {
                    $version = (float)$mtcs[1];
                } else {
                    $version = 11.0;
                }
            } elseif ((float)$mtcs[1] >= 6) {
                $version = 10.0;
            } elseif ((float)$mtcs[1] >= 5) {
                $version = 9.0;
            } elseif ((float)$mtcs[1] >= 4) {
                $version = 8.0;
            }
            if (empty($browser)) {
                if (preg_match('/MSIE\s(\d{1,}(.\d{1,}){1,}?);/i', $ua, $mtcs)) {
                    $browser = 'IE';
                    $version = (float)$mtcs[1];
                }
            }
        } else {
            return "Unknown";
        }
        return array('device' => $device, 'browser' => $browser.$version);
    }

    /**
     * MimeType取得
     * 引数で渡された拡張子より適したMimeTypeを返却する
     */
    public static function getMimeType($extension) {
        switch ($extension) {
            case "txt":
                $mimeType = "tetx/plain";
                break;
            case "xml":
                $mimeType = "text/xml";
                break;
            case "zip":
                $mimeType = "application/x-zip-compressed";
                break;
            case "gif":
                $mimeType = "image/gif";
                break;
            case "jpg":
                case "jpeg":
                $mimeType = "image/jpeg";
                break;
            case "png":
                $mimeType = "image/x-png";
                break;
            case "pdf":
                $mimeType = "application/pdf";
                break;
            default:
                $mimeType = "application/octet-stream";
        }
        return $mimeType;
    }

    /**
     * HTTPヘッダの$_SERVER['HTTP_ACCEPT_LANGUAGE']より言語コードを取得する 
     */
    public static function getHttpLangCd($http_accept_language) {
        if(!empty($http_accept_language)){
            $lgs = $http_accept_language;
            $ein = explode(',', $lgs);
            foreach($ein as $zwei){
                $drei = explode(';', $zwei);
                $fier[] = $drei[0];
            }
            foreach($fier as $fuenf){
                if(!empty($fuenf)){
                    $first_lang[]   = self::formatLangcode($fuenf);
                }
            }
            return $first_lang[0];
        } else {
            return NULL;
        }
    }

    /**
     * 言語コード整形
     *
     * $_SERVER[‘HTTP_ACCEPT_LANGUAGE’] に代入されている言語名の統一を行う。
     * @param string $a = ブラウザが発行する言語コード
     */
    public static function formatLangcode($a){
        $lang = substr($a, 0, 2);
        // アラビア語
        if($lang == 'ar') { $b = 'ar'; } // アラビア語
        // ドイツ語
        if($lang == 'de') { $b = 'de'; } // ドイツ語
        // 英語
        if($lang == 'en') { $b = 'en'; } // 英語
        // スペイン語
        if($lang == 'es') { $b = 'es'; } // スペイン語
        // フランス語
        if($lang == 'fr') { $b = 'fr'; } // フランス語
        // イタリア語
        if($lang == 'it') { $b = 'it'; } // イタリア語
        // オランダ語
        if($lang == 'nl') { $b = 'nl'; } // オランダ語
        // ノルウェー語
        if($lang == 'nn') { $b = 'nn'; } // ノルウェー語
        if($lang == 'nb') { $b = 'nn'; } // ノルウェー語
        if($lang == 'no') { $b = 'nn'; } // ノルウェー語
        // ポルトガル語
        if($lang == 'pt') { $b = 'pt'; } // ポルトガル語
        // ルーマニア語
        if($lang == 'ro') { $b = 'ro'; } // ルーマニア語
        // ロシア語?
        if($lang == 'ru') { $b = 'ru'; } // ロシア語
        // セルビア語
        if($lang == 'sr') { $b = 'sr'; } // セルビア語/キリル
        // スウェーデン語
        if($lang == 'sv') { $b = 'sv'; } // スウェーデン語
        // ウズベク語
        if($lang == 'uz') { $b = 'uz'; } // ウズベク語/キリル
        // 中国語
        if($lang == 'zh') { $b = 'zh'; } // 中国語
        // その他
        if(empty($b)) { $b = $lang; } // 入力値をそのまま返す。
        return $b;
    }

}
