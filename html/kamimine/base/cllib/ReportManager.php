<?php

/**
 * 帳票出力管理Class
 */
class ReportManager {

    private $view;
    private $logger;
    private $session;
    private $controller;
    private $db;

    function __construct($view,$logger,$session,$controller,$db) {
        $this->view = $view;
        $this->logger = $logger;
        $this->session = $session;
        $this->controller = $controller;
        $this->db = $db;
    }

    /**
     * 帳票出力(PDF/Excel/Word)
     * @description セッションにデータを格納後、一度クライアントに返却。
     *              クライアントからSUBMIT通信にて帳票出力処理が起動される。
     *              出力前のエラーチェック処理をAjax通信にて行うため。
     */
    public function generate($class,$data) {
        // Sessionにデータ格納
        $this->session->setControllerSession('tciReportClass',$class,$this->controller);
        $this->session->setControllerSession('tciReportData',$data,$this->controller);
        // 出力OKフラグをAssign
        $this->view->assign("tciOutputReport",true);
    }

    /**
     * 帳票出力処理
     */
    public function tcioutputreportAction() {
        // セッションよりデータ取得
        $class = $this->session->getControllerSession('tciReportClass');
        $data = $this->session->getControllerSession('tciReportData');
        //$this->logger->writeDebug($class);
        //$this->logger->writeDebug($data);
        // リフレクションによりインスタンス生成
        $reflectionClass = new ReflectionClass($class);
        $reflectionInstance = $reflectionClass->newInstanceArgs(array($class,$this->logger,$this->db));
        // generateメソッドの実行
        $reflectionMethod = new ReflectionMethod($class, "generate");
        $result = $reflectionMethod->invokeArgs($reflectionInstance, array($data));
        // セッションよりデータ削除
        $this->session->setControllerSession('tciReportClass','');
        $this->session->setControllerSession('tciReportData','');
    }

}
