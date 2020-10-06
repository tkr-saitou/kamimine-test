<?php

require_once (APP.'base/cllib/Util.php');
require_once (APP.'base/cllib/TagUtil.php');
require_once (APP.'base/cllib/TagGridGenerator.php');
require_once (APP.'base/cllib/TagCheckboxGenerator.php');
require_once (APP.'base/cllib/TagDropdownGenerator.php');
require_once (APP.'base/cllib/TagRadiobuttonGenerator.php');
require_once (APP.'base/cllib/TagInputDatalistGenerator.php');

/**
 * Tag管理Class
 * @description 当初staticなTagUtilクラスでHTMLタグの生成を行っていたが、
 *              サーバ側Validation実装のためsessionを使用する設計に変更した経緯により、本Classを実装。
 *              HTMLタグ生成はTagUtilに処理を移譲する形となっている。
 */
class TagManager {

    private $logger;
    private $session;
    private $controller;
    private $lang;

    function __construct($controller,$session,$logger,$lang) {
        $this->controller = $controller;
        $this->session = $session;
        $this->logger = $logger;
        $this->lang = $lang;
    }

    /* -- 表示部品 ------------------------------------------------------------------------------------- */

    /**
     * ラベル 
     */
    public function label($text, $required=false, $id=null) {
        return TagUtil::label($text, $required, $id);
    }

    /**
     * ラベル (多言語対応版）
     */
    public function langLabel($resource_id, $required=false, $id=null) {
        return TagUtil::label($this->lang->get($resource_id), $required, $id);
    }

    /**
     * span 
     */
    public function span($text, $id=null, $class=null) {
        return TagUtil::span($text, $id, $class);
    }

    /**
     * ReadonlyText
     * @description POST送信の対象となる、入力不可・読み取り専用部品。
     *              <span>タグとは異なり、<input readonly="readonly">タグで出力される。
     */
    public function readonlyText($value, $idname, $length) {
        return TagUtil::readonlyText($value, $idname, $length);
    }

    /* -- 入力部品 ------------------------------------------------------------------------------------- */

    /**
     * テキスト入力
     */
    public function inputText($value, $idname, $length, $active=true, $placeholder=null) {
        // 最大桁数設定
        if($active) $this->setMaxlength($idname,$length);
        // HTMLタグ生成
        return TagUtil::inputText($value, $idname, $length, $active, $placeholder);
    }

    /**
     * 英数字入力
     */
    public function inputAlphaNumberText ($value, $idname, $length, $active=true) {
        // Validation設定
        if($active) $this->setValidation($idname,"alphanumber");
        // 最大桁数設定
        if($active) $this->setMaxlength($idname,$length);
        // HTMLタグ生成
        return TagUtil::inputAlphaNumberText ($value, $idname, $length, $active); 
        //return TagUtil::inputText ($value, $idname, 20, $active); 
    }

    /**
     * 数字入力
     * @description 小数点、マイナス記号を許可しない。左寄せ。
     *              ex.0001といった番号を入力させたい場合に使用する
     */
    public function inputNumberText($value,$idname,$length,$active=true) {
        // Validation設定
        if($active) $this->setValidation($idname,"number");
        // 最大桁数設定
        if($active) $this->setMaxlength($idname,$length);
        // HTMLタグ生成
        return TagUtil::inputNumberText($value, $idname, $length, $active);
    }

    /**
     * 数字入力(特殊文字許可)
     */
    public function inputSpCharNumberText($value,$arr,$idname,$length,$active=true) {
		$chars = '';
        // Validation設定
        if($active) $this->setValidation($idname,"spcharnumber",$arr);
        // 最大桁数設定
        if($active) $this->setMaxlength($idname,$length);
        // HTMLタグ生成
        $html = TagUtil::inputSpCharNumberText($value, $idname, $length, $active);
        foreach($arr as $i => $char) {
            $chars .= $char.',';
        }
        $html .= TagUtil::hidden($idname."_char", rtrim($chars,','));
        return $html;
    }

    /**
     * 数値入力
     * @description 小数点、マイナス記号を許可する数値入力部品。右寄せ。
     *              ex.-123.4といった数値を入力させたい場合に使用する 
     */
    public function inputNumericText($value,$idname,$length,$active=true) {
        // Validation設定
        if($active) $this->setValidation($idname,"numeric");
        // 最大桁数設定
        if($active) $this->setMaxlength($idname,$length);
        // HTMLタグ生成
        return TagUtil::inputNumericText($value, $idname, $length, $active);
    }

    /**
     * 金額入力
     * @description マイナス記号を許可する数値入力部品。右寄せ。
     *              3桁区切りのカンマが付与される。
     */
    public function inputCurrencyText ($value, $idname, $length, $active=true) {
        // Validation設定
        if($active) $this->setValidation($idname,"numeric");
        // 最大桁数設定
        if($active) $this->setMaxlength($idname,$length);
        // HTMLタグ生成
        return TagUtil::inputCurrencyText($value, $idname, $length, $active);
    }

    /**
     * 入力候補表示テキスト 
     * @return InputDatalistGeneratorインスタンス
     */
    public function inputDatalist($idname,$length) {
        // リフレクションによりインスタンス生成
        $reflectionClass = new ReflectionClass('TagInputDatalistGenerator');
        $reflectionInstance = $reflectionClass->newInstanceArgs(
                                  array($idname,$length,$this->logger,$this));
        return $reflectionInstance;
    }

    /**
     * textarea 
     */
    public function textarea ($value, $idname, $maxlength, $rows, $active=true, $placeholder=null) {
        // 最大桁数設定
        if($active) $this->setMaxlength($idname,$maxlength);
        return TagUtil::textarea ($value, $idname, $maxlength, $rows, $active, $placeholder); 
    }

    /**
     * 日付入力部品
     */
    public function datepicker ($value, $idname, $active=true) {
        // Validation設定
        if($active) $this->setValidation($idname,"date");
        // 最大桁数設定
        if($active) $this->setMaxlength($idname,10);
        return TagUtil::datepicker ($value, $idname, $active);
    }

    /**
     * 年月入力部品(YYYY/MM)
     */
    public function inputYearMonth($value, $idname, $active=true) {
        // Validation設定
        if($active) $this->setValidation($idname,"yearmonth");
        // 最大桁数設定
        if($active) $this->setMaxlength($idname,6);
        return TagUtil::inputYearMonth($value, $idname, $active);
    }

    /**
     * 時刻入力部品
     */
    public function inputTime($value, $idname, $active=true) {
        // Validation設定
        if($active) $this->setValidation($idname,"time");
        // 最大桁数設定
        if($active) $this->setMaxlength($idname,5);
        return TagUtil::inputTime($value, $idname, $active);
    }

    /* -- 特殊用途入力部品 ---------------------------------------------------------------------------- */

    /**
     * 郵便番号入力
     */
    public function inputZipCd($value, $idname, $active=true) {
        // Validation設定
        if($active) $this->setValidation($idname,"zipcd");
        // 最大桁数設定
        if($active) $this->setMaxlength($idname,8);
        return TagUtil::inputZipCd($value, $idname, $active);
    }

    /**
     * 電話番号入力
     */
    public function inputTelNo($value, $idname, $active=true) {
        // Validation設定
        if($active) $this->setValidation($idname,"telno");
        // 最大桁数設定
        if($active) $this->setMaxlength($idname,15);
        return TagUtil::inputTelNo($value, $idname, $active);
    }

    /**
     * メールアドレス入力
     */
    public function inputMailAddress($value, $idname, $active=true) {
        // Validation設定
        if($active) $this->setValidation($idname,"mailaddress");
        // 最大桁数設定
        if($active) $this->setMaxlength($idname,256);
        return TagUtil::inputMailAddress($value, $idname, $active);
    }

    /* -- ドロップダウン/ラジオボタン/チェックボックス ------------------------------------------------ */

    /**
     * ドロップダウン
     * @return DropdownGeneratorインスタンス
     */
    public function dropdown($id) {
        // リフレクションによりインスタンス生成
        $reflectionClass = new ReflectionClass('TagDropdownGenerator');
        $reflectionInstance = $reflectionClass->newInstanceArgs(
                                  array($id,$this->controller,$this->session,$this->logger,$this));
        return $reflectionInstance;
    }

    /**
     * ラジオボタン
     * @return RadiobuttonGeneratorインスタンス
     */
    public function radiobutton($id) {
        // リフレクションによりインスタンス生成
        $reflectionClass = new ReflectionClass('TagRadiobuttonGenerator');
        $reflectionInstance = $reflectionClass->newInstanceArgs(
                                  array($id,$this->controller,$this->session,$this->logger,$this));
        return $reflectionInstance;
    }

    /**
     * チェックボックス
     * @return CheckboxGeneratorインスタンス
     */
    public function checkbox($id) {
        // リフレクションによりインスタンス生成
        $reflectionClass = new ReflectionClass('TagCheckboxGenerator');
        $reflectionInstance = $reflectionClass->newInstanceArgs(
                                  array($id,$this->controller,$this->session,$this->logger,$this,"multi"));
        return $reflectionInstance;
    }

    /**
     * 単一チェックボックス
     * @return CheckboxGeneratorインスタンス
     */
    public function singleCheckbox($id) {
        // リフレクションによりインスタンス生成
        $reflectionClass = new ReflectionClass('TagCheckboxGenerator');
        $reflectionInstance = $reflectionClass->newInstanceArgs(
                                  array($id,$this->controller,$this->session,$this->logger,$this,"single"));
        return $reflectionInstance;
    }

    /* -- GRID ---------------------------------------------------------------------------------------- */

    /**
     * GridGenerator生成
     */
    public function grid($tableId) {
        // リフレクションによりインスタンス生成
        $reflectionClass = new ReflectionClass('TagGridGenerator');
        $reflectionInstance = $reflectionClass->newInstanceArgs(
                                  array($tableId,$this->controller,$this->session,$this->logger));
        return $reflectionInstance;
    }

    /* -- ボタン関連 ---------------------------------------------------------------------------------- */

    /**
     * ボタン
     */
    public function button ($text, $id, $color="blue") {
        return TagUtil::button ($text, $id, $color);
    }

    /**
     * リンク
     */
    public function a($text, $controller, $action) {
        return TagUtil::a($text, $controller, $action);
    }

    /**
     * ダイアログを開くボタン
     */
    public function openDialogBtn($id,$active=true) {
        return TagUtil::openDialogBtn($id,$active);
    }

    /* -- その他 -------------------------------------------------------------------------------------- */

    /**
     * hidden
     */
    public function hidden ($name, $value) {
        return TagUtil::hidden($name, $value);
    }

    /**
     * 画像
     */
    public function image($src, $id, $alt) {
        return TagUtil::image($src, $id, $alt);
    }

    /**
     * ファイルアップローダー
     * @param maxsize: アップロード上限サイズ（Byte指定）
     */
    public function fileUploader($idname, $maxsize, $active=true) {
        return TagUtil::fileUploader($idname, $maxsize, $active);
    }

    /* -- ユーティリティ ------------------------------------------------------------------------------ */

    /** 
     * add class 
     * @description HTMLタグにClassを追加する
     */
    public function addClass($tag,$class) {
        return TagUtil::addClass($tag,$class);
    }

    /* -- private ------------------------------------------------------------------------------------- */

    /*
     * Validation設定
     */
    private function setValidation($name,$type,$chars=null) {
        // Validation情報の格納
        $validationList = (array)$this->session->getControllerSession("tciValidation");
        $validationList = array_merge($validationList,array($name => $type));
        $this->session->setControllerSession("tciValidation",$validationList);
        // 特殊許可文字の格納
        if(!is_null($chars)) {
            $charList = (array)$this->session->getControllerSession("tciValidationChars");
            $charList = array_merge($charList,array($name => $chars));
            $this->session->setControllerSession("tciValidationChars",$charList);
        }
    }

    /*
     * Maxlength設定
     */
    public function setMaxlength($name,$maxlength) {
        $maxlengthList = (array)$this->session->getControllerSession("tciMaxlength");
        $maxlengthList = array_merge($maxlengthList,array($name => $maxlength));
        $this->session->setControllerSession("tciMaxlength",$maxlengthList);
    }

    public function setSession($key,$name,$val) {
        $list = (array)$this->session->getControllerSession($key);
        $list = array_merge($list,array($name => $val));
        $this->session->setControllerSession($key,$list);
    }

}
