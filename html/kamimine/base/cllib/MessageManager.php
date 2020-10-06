<?php

class MessageManager {

    private $view;
    private $logger;
    private $errItem;
    private $modalMsg;
    private $modalMsgArray;
    private $errItemArray;
    private $headerMsg;
    private $regMsgOnFooter;

    function __construct($view,$logger) {
        $this->view = $view;
        $this->logger = $logger;
        $this->modalMsgArray =array();
        $this->errItemArray =array();
    }

    /*
     * エラー項目取得
     */
    public function getErrItems() {
        return $this->errItemArray;
    }

    /*
     * モーダル：メッセージ有無
     */
    public function hasModalMsg() {
        if (is_null($this->modalMsg)) {
            return false;
        } else {
            return true;
        }
    }

    /*
     * モーダル：メッセージ取得
     */
    public function getModalMsgs() {
        //return $this->modalMsg;
        return $this->modalMsgArray;
    }

    /*
     * モーダル：メッセージSET
     * $name: エラー項目のHTMLタグの"name"
     */
    public function setModalErrMsg($msg, $name=null) {
        array_push($this->modalMsgArray,$msg);
        if(!is_null($name)){
            array_push($this->errItemArray,$name);
        }
        $this->modalMsg = $msg;
        $this->errItem = $name;
    }

    /*
     * ヘッダーメッセージ表示
     */
    public function showHeaderMsg($text) {
        $this->headerMsg = $text;
    }
    /*
     * ヘッダーメッセージ取得
     */
    public function getHeaderMsg() {
        return $this->headerMsg;
    }

    /*
     * 登録確認メッセージ表示
     */
    public function showRegMsgOnFooter($msg=null) {
        if(is_null($msg)) $msg = "この内容で登録します。よろしいですか？";
        $this->regMsgOnFooter = $msg;
    }
    /*
     * 登録確認メッセージ取得
     */
    public function getRegMsgOnFooter() {
        return $this->regMsgOnFooter;
    }

    /*
     * 登録完了メッセージ表示
     */
    public function showRegisteredMsg($controller=null,$action=null,$msg=null) {
        $this->view->assign("tciRegistered",true);
        $this->view->assign("tciRegisteredCtrl",$controller);
        $this->view->assign("tciRegisteredAction",$action);
        $this->view->assign("tciRegisteredMsg",$msg);
    }
}
