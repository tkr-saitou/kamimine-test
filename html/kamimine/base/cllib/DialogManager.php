<?php

/**
 * Dialog管理Class
 */
class DialogManager {

    private $view;
    private $logger;
    private $db;
    private $authInfo;
    private $session;
    private $screen;
    private $controller;
    private $post;
    private $tag;
    private $message;

    function __construct($view,$logger,$db,$authInfo,$session,$screen,$controller,$post,$tag,$message) {
        $this->view = $view;
        $this->logger = $logger;
        $this->db = $db;
        $this->authInfo = $authInfo;
        $this->session = $session;
        $this->screen = $screen;
        $this->controller = $controller;
        $this->post = $post;
        $this->tag = $tag;
        $this->message = $message;
    }

    /**
     * Dialogインスタンス生成
     */
    public function getInstance($class) {
        // リフレクションによりインスタンス生成
        $reflectionClass = new ReflectionClass($class);
        $reflectionInstance = $reflectionClass->newInstanceArgs(
                                array($this->view,$class,$this->logger,$this->db,$this->authInfo,$this->session,$this->screen,$this->controller,$this->post,$this->tag,$this->message));
        // インスタンスを返却
        return $reflectionInstance;
    }

    /**
     * Dialog初期化
     */
    public function init($class,$args=array()) {
        // リフレクションによりインスタンス生成
        $reflectionInstance = $this->getInstance($class);
        // initメソッドの実行
        $reflectionMethod = new ReflectionMethod($class, "init");
        //$reflectionMethod->invoke($reflectionInstance);
        return $reflectionMethod->invokeArgs($reflectionInstance, $args);
    }

    /**
     * Dialog表示
     * @description Dialogを開いた際に呼ばれる。　※画面自体のrender時に呼ばれるinitとは別
     */
    public function tcishowdialogAction() {
        // セッションに情報を退避 ※１画面に複数Dialogが配置されることがあるため、どこから呼ばれたのかを保持
        $this->session->setControllerSession("tciDialogFromId",$this->post['tciDialogFromId']);
        // ダイアログインスタンス取得してダイアログ表示
        $dialog = $this->getInstance($this->post['tciDialogId']);
        $dialog->assign4Show();
        // showメソッドの実行
        $reflectionMethod = new ReflectionMethod($this->post['tciDialogId'], "show");
        $reflectionMethod->invoke($dialog);
        // 呼び出し元の識別IDを返却する　※JS側で利用可能とするため
        $this->view->assign('tciDialogFromId',$this->post['tciDialogFromId']);
    }

    /**
     * DialogAction
     * @description Dialog内から呼び出されるAction
     */
    public function tcidialogAction() {
        $reflectionInstance = $this->getInstance($this->post['tciDialogClass']);
        // メソッドの実行
        $reflectionMethod = new ReflectionMethod($this->post['tciDialogClass'],$this->post['tciDialogAction']);
        $reflectionMethod->invokeArgs($reflectionInstance, array($this->post));
    }

}
