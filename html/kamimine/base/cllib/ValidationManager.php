<?php

require_once (APP.'base/cllib/Util.php');

class ValidationManager {

    private $logger;
    private $session;
    private $controller;
    private $post;
    private $message;
    private $validation;
    private $requiredErrItems;

    function __construct($controller,$session,$logger, $post, $message) {
        $this->controller = $controller;
        $this->session = $session;
        $this->logger = $logger;
        $this->post = $post;
        $this->message = $message;
    }

    /* -- 必須チェックValidation ------------------------------------------------------------------------- */

    /**
     * 必須チェック対象項目設定
     */
    public function setRequired($name) {
        //$this->logger->writeDebug("setRequired: ".$name);
        $tmp = $this->session->getControllerSession("tciRequired");
        if(empty($tmp)) {
            $tmp = $name;
            $this->session->setControllerSession("tciRequired",$tmp);
        } elseif(!Util::contains($tmp, $name)) {
            $tmp = $tmp.",".$name;
            $this->session->setControllerSession("tciRequired",$tmp);
        }
    }

    /**
     * 必須チェック実行
     */
    private function checkRequired() {
        //$this->logger->writeDebug("checkRequired");
        $this->requiredErrItems = array();
        $items = explode(',',$this->session->getControllerSession("tciRequired"));
        //$this->logger->writeDebug($items);
        foreach ($items as $item) {
            if($this->post[$item] == "0") {
                // 0の場合はチェックOKとする（emptyでは0はtrueと判定されてしまうため）
            } elseif(!empty($item) && empty($this->post[$item])) {
                array_push($this->requiredErrItems, $item);
            }
        }
        //$this->logger->writeDebug($this->requiredErrItems);
        if(empty($this->requiredErrItems)){
            return true;
        } else {
            return false;
        }
    }

    /**
     * 必須チェック実行結果取得
     */
    public function getRequiredErrItems() {
        return $this->requiredErrItems;
    }

    /* -- Validation ------------------------------------------------------------------------------------- */

    /**
     * Validation情報のセット
     * @description TagManagerで設定したタグにはValidation情報を内部で設定している
     *              上記以外で個別に設定したい場合のみ、本メソッドを使用する。（通常使用しない）
     * @param $name: POSTのname属性
     * @param $type: Validationの種類
     */
    public function setValidate($name,$type) {
        $validationList = (array)$this->session->getControllerSession("tciValidation");
        $validationList = array_merge($validationList,array($name => $type));
        $this->session->setControllerSession("tciValidation",$validationList);
        //$this->logger->writeDebug("setValidate");
        //$this->logger->writeDebug($this->session->getControllerSession("tciValidation"));
    }

    /**
     * 画面全項目のValidation実行
     * @description Validation/Maxlengthチェックは1件でもエラーがあれば返却
     *              ※不正操作によりクライアントValidationが無効になったレアケースのみの動作のため。
     * @return boolean: チェック結果　※呼び出し元にて、falseの場合は原則後続処理を実行せずreturnすること
     */
    public function validateAll() {
        // 必須チェック実行
        if(!$this->checkRequired()) return false;
        // サーバ側Validation&Maxlengthチェック実行
        $validationList = (array)$this->session->getControllerSession("tciValidation");
        $validationCharsList = (array)$this->session->getControllerSession("tciValidationChars");
        $dropdownList = (array)$this->session->getControllerSession("tciDropdownValidation");
        $radiobuttonList = (array)$this->session->getControllerSession("tciRadiobuttonValidation");
        $checkboxList = (array)$this->session->getControllerSession("tciCheckboxValidation");
        $maxlengthList = (array)$this->session->getControllerSession("tciMaxlength");
        //$this->logger->writeDebug($validationList);
        //$this->logger->writeDebug($validationCharsList);
        //$this->logger->writeDebug($dropdownList);
        //$this->logger->writeDebug($radiobuttonList);
        //$this->logger->writeDebug($checkboxList);
        //$this->logger->writeDebug($maxlengthList);
        foreach($this->post as $key => $value) {
            //$this->logger->writeDebug($key);
            //$this->logger->writeDebug($value);
            if(!empty($value)) {
                //$this->logger->writeDebug($validationList[$key]);
                if($validationList[$key]) {
                    $chars = $validationCharsList[$key];
                    // Validationチェック
                    if(!$this->doValidate($key,$value,$validationList[$key],$chars)) return false;
                } elseif ($dropdownList[$key]) {
                    // ドロップダウンチェック
                    if(!in_array($value,$dropdownList[$key])){
                        // 登録確認画面用に2行に分けて"_display"付も出力
                        $this->message->setModalErrMsg('ドロップダウンが正しく選択されていません。',$key);
                        $this->message->setModalErrMsg('再確認をお願いします。',$key."_display");
                        return false;
                    }
                } elseif ($radiobuttonList[$key]) {
                    // ラジオボタンチェック
                    if(!in_array($value,$radiobuttonList[$key])){
                        $this->message->setModalErrMsg('ラジオボタンが正しく選択されていません。',$key);
                        $this->message->setModalErrMsg('再確認をお願いします。',$key."_display");
                        return false;
                    }
                } elseif ($checkboxList[$key]) {
                    if(is_array($value)) {
                        // チェックボックスチェック(複数選択のため、さらにループさせる)
                        foreach($value as $i => $val) {
                            if(!in_array($val,$checkboxList[$key])){
                                $this->message->setModalErrMsg('チェックボックスが正しく選択されていません。',$key);
                                $this->message->setModalErrMsg('再確認をお願いします。',$key."_display");
                                return false;
                            }
                        }
                    } else {
                        // 単一チェックボックスチェック
                        if(!in_array($value,$checkboxList[$key])){
                            $this->message->setModalErrMsg('チェックボックスが正しく選択されていません。',$key);
                            $this->message->setModalErrMsg('再確認をお願いします。',$key."_display");
                            return false;
                        }
                    }
                }
                // Maxlengthチェック
                if($maxlengthList[$key]) {
                    if(mb_strlen($value,'UTF-8') > $maxlengthList[$key]) {
                        $this->message->setModalErrMsg('桁数をオーバしています。(入力可能桁数:'.$maxlengthList[$key].')',$key);
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /* 以降はValidationManager内での使用を想定したメソッド。publicにはしているが。 ------------------ */

    /**
     * Validation実行
     */
    public function doValidate($key,$value,$type,$chars=null) {
        // 数字チェック
        if(strtolower($type) == "number") {
            if(!$this->isNumber($value)) {
                $this->message->setModalErrMsg("数字で入力してください",$key);
                return false;
            }
        // 数字チェック(特殊文字許可)
        } elseif(strtolower($type) == "spcharnumber") {
            if(!is_null($chars)) {
                $value = str_replace($chars,"",$value);
                if(!$this->isNumber($value)) {
                    $this->message->setModalErrMsg("数字でまたは ".implode(' ',$chars)." で入力してください",$key);
                    return false;
                }
            }
        // 数値チェック
        } elseif(strtolower($type) == "numeric") {
            if(!$this->isNumeric($value)) {
                $this->message->setModalErrMsg("数値で入力してください",$key);
                return false;
            }
        // 英数字チェック
        } elseif(strtolower($type) == "alphanumber") {
            if(!$this->isAlphaNumber($value)) {
                $this->message->setModalErrMsg("英数字で入力してください",$key);
                return false;
            }
        // 日付チェック
        } elseif(strtolower($type) == "date") {
            if(!$this->isDate($value)) {
                $this->message->setModalErrMsg("実在する日付で入力してください",$key);
                return false;
            }
        // 年月チェック
        } elseif(strtolower($type) == "yearmonth") {
            if(!$this->isYearMonth($value)) {
                $this->message->setModalErrMsg("年月の形式で入力してください",$key);
                return false;
            }
        // 時刻チェック
        } elseif(strtolower($type) == "time") {
            if(!$this->isTime($value)) {
                $this->message->setModalErrMsg("時刻で入力してください",$key);
                return false;
            }
        // 郵便番号チェック
        } elseif(strtolower($type) == "zipcd") {
            if(!$this->isZipCd($value)) {
                $this->message->setModalErrMsg("郵便番号で入力してください",$key);
                return false;
            }
        // 電話番号チェック
        } elseif(strtolower($type) == "telno") {
            if(!$this->isTelNo($value)) {
                $this->message->setModalErrMsg("電話番号で入力してください",$key);
                return false;
            }
        // メールアドレスチェック
        } elseif(strtolower($type) == "mailaddress") {
            if(!$this->isMailAddress($value)) {
                $this->message->setModalErrMsg("メールアドレスの形式で入力してください",$key);
                return false;
            }
        }
        return true;
    }

    /**
     * Validation: 数値チェック
     * @description 小数点、マイナス記号が許可される
     */
    public function isNumeric($value) {
        if(is_numeric($value)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Validation: 数字チェック
     */
    public function isNumber($value) {
        if(ctype_digit($value)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Validation: 英数字チェック
     */
    public function isAlphaNumber($value) {
        if (preg_match("/^[a-zA-Z0-9-\/]+$/", $value)) {
        //if(ctype_alnum($value)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Validation: 日付妥当性チェック
     * @param $date: YYYY/MM/DD形式を前提
     */
    public function isDate($value) {
        $year = mb_substr($value,0,4);
        $month = mb_substr($value,5,2);
        $day = mb_substr($value,8,2);
        if(!$this->isNumber($year) || !$this->isNumber($month) || !$this->isNumber($day)) return false;
        if(checkdate($month,$day,$year)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Validation: 年月チェック
     */
    public function isYearMonth($value) {
        $year = mb_substr($value, 0, 4);
        $month = '';
        if (mb_strlen($value) == 7) {
            $month = mb_substr($value, 5, 2);
            if (strcmp(mb_substr($value, 4, 1), '/') != 0) return false;
        } else {
            $month = mb_substr($value, 4, 2);
        }
        if ($month >= 1 && $month <= 12) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Validation: 時刻チェック
     */
    public function isTime($value) {
        if(preg_match('/^([01]?[0-9]|2[0-3]):([0-5][0-9])$/' ,$value)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Validation: 郵便番号チェック
     */
    public function isZipCd($value) {
        if(mb_strlen($value) == 7) $value = mb_substr($value, 0, 3).'-'.mb_substr($value, 3, 4);
        $val1 = mb_substr($value, 0, 3);
        $val3 = mb_substr($value, 4, 4);
        if (mb_strlen($value) != 8) return false;
        if (preg_match('/[^0-9 .]+/', $val1)) return false;
        if (preg_match('/[^0-9 .]+/', $val3)) return false;
        return true;
    }

    /**
     * Validation: 電話番号チェック
     */
    public function isTelNo($value) {
        // 電話番号チェック
        $data1 = preg_match('/^[0-9-]{6,9}$|^[0-9-]{12}$/', $value);
        $data2 = preg_match('/^\d{1,4}-\d{4}$|^\d{2,5}-\d{1,4}-\d{4}$/', $value);
        // 携帯番号チェック
        $data3 = preg_match('/^\d{3}-\d{4}-\d{4}$|^\d{11}$/', $value);
        if (!$data1 && !$data2) {
            if (!$data3) {
                return false;
            }
        }
        return true;
    }

    /**
     * Validation: メールアドレスチェック
     */
    public function isMailAddress($value) {
        //if (!preg_match('/^[A-Za-z0-9]+[\w-]+@[\w\.-]+\.\w{2,}$/', $value)) {
        //if (!preg_match('/^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/', $value)) {
        if (!preg_match('/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/', $value)) {
            return false;
        } else {
            return true;
        }
    }

}
