<?php

require_once (APP.'base/cllib/Util.php');

class ScreenManager {

    private $logger;
    private $session;
    private $view;
    private $controller;
    private $transitionController;
    private $transitionAction;
    private $args;
    private $openWindow = false;
    private $checkOnChange = false;
    private $requiredErrItems;

    function __construct($controller,$session,$logger,$view) {
        $this->logger = $logger;
        $this->session = $session;
        $this->controller = $controller;
        $this->view = $view;
    }

    /*
     * 画面遷移情報設定 
     */
    public function setTransition($controller,$action) {
        $this->transitionController = $controller;
        $this->transitionAction = $action;
        // 遷移元画面情報の設定(次画面Controller名をPrevとして設定)
        $this->session->setControllerSession("tciPrevCtrl",$this->controller,$this->transitionController);
        // 他画面へ遷移する場合、遷移元画面のControllerSessionのクリア
        /*
        if(mb_strtolower($this->controller) != mb_strtolower($controller)){
            $this->session->unsetControllerSession($this->controller);
        }
        */
    }

    /*
     * 画面遷移情報取得:controller 
     */
    public function getController() {
        return $this->transitionController;
    }

    /*
     * 画面遷移情報取得:action
     */
    public function getAction() {
        return $this->transitionAction;
    }

    /*
     * 画面遷移パラメータ設定 
     */
    public function setArg($controller,$key,$arg) {
        $this->session->setControllerSession($key,$arg,mb_strtolower($controller));
    }

    /*
     * 画面遷移パラメータ取得
     * ※パラメータ取得後にセッションより情報を削除
     */
    public function getArg($key) {
        return $this->session->getControllerSession($key);
    }

    /*
     * 遷移元Controller名取得
     */
    public function getPrevCtrl() {
        return mb_strtolower($this->session->getControllerSession("tciPrevCtrl"));
    }

    /*
     * 別ウィンドウ起動設定
     */
    public function setOpenWindowOn() {
        $this->openWindow = true;
    }

    /*
     * 別ウィンドウ起動取得
     */
    public function isOpenWindow() {
        return $this->openWindow;
    }

    /*
     * 変更通知カラー表示ON 
     */
    public function setCheckOnChange() {
        $this->checkOnChange = true;
    }

    /*
     * 変更通知カラー表示取得
     */
    public function getCheckOnChange() {
        return $this->checkOnChange;
    }

    /**
     * Table KEY一覧取得
     */
    public function getRowKeys($tableId) {
        return $this->session->get("tciRowKeys_".$tableId);
    }

    /**
     * Table KEY取得
     */
    public function getKeyByRownum($tableId,$rownum) {
        $rowKeys = $this->getRowKeys($tableId);
        return $rowKeys[(string)$rownum];
    }

    /*
     * 必須チェック対象項目設定 
    public function setRequired($name) {
        $tmp = $this->session->getControllerSession("tciRequired");
        if(empty($tmp)) {
            $tmp = $name;
            $this->session->setControllerSession("tciRequired",$tmp);
        } elseif(!Util::contains($tmp, $name)) {
            $tmp = $tmp.",".$name;
            $this->session->setControllerSession("tciRequired",$tmp);
        }
    }
     */

    /*
     * 必須チェック実行
    public function checkRequired($post) {
        $this->requiredErrItems = array();
        $items = explode(',',$this->session->getControllerSession("tciRequired"));
        foreach ($items as $item) {
            if($post[$item] == "0") {
                // 0の場合はチェックOKとする（emptyでは0はtrueと判定されてしまうため）
            } elseif(!empty($item) && empty($post[$item])) {
                array_push($this->requiredErrItems, $item);
            }
        }
        if(empty($this->requiredErrItems)){
            return true;
        } else {
            return false;
        }
    }
     */

    /*
     * 必須チェック実行結果取得
    public function getRequiredErrItems() {
        return $this->requiredErrItems;
    }
     */

    /**
     * JSFunction実行
     */
    public function invokeFunc($name) {
        $this->view->assign("tciInvokeFunc",$name);
    }
}
