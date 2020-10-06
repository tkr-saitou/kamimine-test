<?php

//require_once (APP.'base/models/s_lang.php');
require_once (APP.'base/cllib/LogWriter.php');

/**
 * 多言語対応クラス
 *
 * variables.phpにてMULTI_LANGがtrueに設定されている場合のみ本Classを使用可能。
 * $LANG_LISTを使用する。(以前s_langテーブルを参照していたが廃止している）
 * /var/www/html/xxx/resource配下のリソースファイルにて表示文言を管理する。
 * 言語設定は、現状HTTPリクエストより取得し、取得できない場合はデフォルトの言語を返す。
 */
class LangManager {

    private $logger;
    private $label_resource = array();
    private $lang_cd;

	function __construct($lang_cd=null,$logger=null) {
        // logger ※ZendLoggerが渡された場合のみセット
        if(!is_null($logger)) {
            $this->logger = $logger;
        } else {
            $this->logger = new LogWriter();
        }
        //$this->logger->writeDebug("LANG MANAGER: ".$lang_cd);
        if(MULTI_LANG) {
            // 言語コードの設定
            if(is_null($lang_cd)) {
                $this->lang_cd = Util::getHttpLangCd($_SERVER['HTTP_ACCEPT_LANGUAGE']);
            } else {
                $this->lang_cd = $this->getAppLangCd($lang_cd);
            }
            // リソースファイル読み込み
            $file_name = RESOURCE_DIR.'resource_'.$this->lang_cd.'.xml';
            if(file_exists($file_name)) {
                $xml_obj = simplexml_load_file($file_name);
                $this->label_resource = json_decode(json_encode($xml_obj), true);
            }
        }
	}

    /**
     * リソースファイル取得
     */
    public function get($resource_id) {
        return $this->label_resource[$resource_id];
    }

    /**
     * 言語情報全件取得
     * @return s_lang
     */
    public function getAllLangs() {
        global $LANG_LIST;
        return $LANG_LIST;
    }

    public function getLangCd($lang_cd=null) {
        if(is_null($lang_cd) || is_null($LANG_LIST($lang_cd))) {
            return $this->lang_cd;
        } else {
            return $lang_cd;
        }
    }

    /**
     * 言語情報取得
     * 引数指定された言語コードに対応する言語情報を返却する
     * 存在しない場合は、NULLを返却
     * @param lang_cd
     * @return array
     */
    public function getLang($lang_cd=null) {
        global $LANG_LIST;
        if(is_null($lang_cd) || is_null($LANG_LIST($lang_cd))) {
            return $LANG_LIST($this->lang_cd);
        } else {
            return $LANG_LIST($lang_cd);
        }
    }

    /**
     * 使用可能言語コード取得
     * アプリケーションで扱うことができる言語コードを返却する
     * @param lang_cd 省略時→デフォルト言語を返却
     *                入力時→入力言語が$LANG_LISTにあるときはそれを返却。ないときはデフォルト言語を返却
     * @return lang_cd
     */
    public function getAppLangCd($lang=null) {
        return $this->getAppLang($lang)['lang_cd'];
    }

    /**
     * 使用可能言語取得
     * アプリケーションで扱うことができる言語コードを返却する
     * @param lang_cd 省略時→デフォルト言語を返却
     *                入力時→入力言語が$LANG_LISTにあるときはそれを返却。ないときはデフォルト言語を返却
     * @return s_lang
     */
    public function getAppLang($lang_cd=null) {
        //$result = array();
        $list = $this->getAllLangs();
        if(is_null($list[$lang_cd])) {
            foreach ((array)$list as $i => $row) {
                return $row;
            }
        } else {
            return $list[$lang_cd];
        }
        /*
        foreach ((array)$list as $row) {
            if($row['lang_cd'] == $lang_cd){
                return $row;
            } elseif ($row['default_flg'] == '1') {
                $result = $row;
            }
        }
        return $result;
        */
    }

    /**
     * 使用可能言語チェック
     * アプリケーションで扱うことができる言語か否かをチェックする
     */
    public function checkAppLang($lang_cd) {
        $list = $this->getAllLangs();
        if(is_null($list[$lang_cd])) {
            return false;
        } else {
            return true;
        }
        /*
        foreach ((array)$list as $row) {
            if($row['lang_cd'] == $lang_cd){
                 return true;
            }
        }
        return false;
        */
    }

    /**
     * HTTPリクエストヘッダより言語コードを取得する
    public function getHttpLangCd() {
        if(!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
            $lgs = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
            $ein = explode(',', $lgs);
            foreach($ein as $zwei){
                $drei = explode(';', $zwei);
                $fier[] = $drei[0];
            }
            foreach($fier as $fuenf){
                if(!empty($fuenf)){
                    $first_lang[]   = $this->formatLangcode($fuenf);
                }
            }
            return $first_lang[0];
        } else {
            $langDef = $this->getAppLangCd(null);
            return $langDef;
        }
    }
     */

    /**
     * 言語コード整形
     *
     * $_SERVER[‘HTTP_ACCEPT_LANGUAGE’] に代入されている言語名の統一を行う。
     * @param string $a = ブラウザが発行する言語コード
    private function formatLangcode($a){
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
     */

}
