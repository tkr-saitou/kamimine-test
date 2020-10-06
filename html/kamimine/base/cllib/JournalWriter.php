<?php

require_once 'Zend/Log.php';
require_once 'Zend/Log/Writer/Stream.php';

class JournalWriter {

    private $access_logger;
    private $sql_logger;
    private $user_id;
    private $transaction_id;
    private $controller;
    private $action;

	function __construct($transaction_id,$user_id=null,$controller=null,$action=null) {
        // Accessジャーナル
		if(ACCESS_JOURNAL == 'true') {
		    $access_journal = JOURNAL_DIR.APP_ID."_ACCESS_".date('Ymd').".log";
		    $access_writer = new Zend_Log_Writer_Stream($access_journal);
		    $this->access_logger = new Zend_Log($access_writer);
        }
        // SQLジャーナル
		if(SQL_JOURNAL == 'true' || QUERYSQL_JOURNAL == 'true') {
		    $sql_journal = JOURNAL_DIR.APP_ID."_SQL_".date('Ymd').".log";
		    $sql_writer = new Zend_Log_Writer_Stream($sql_journal);
		    $this->sql_logger = new Zend_Log($sql_writer);
        }
        $this->user_id = $user_id;
        $this->transaction_id = $transaction_id;
        $this->controller = $controller;
        $this->action = $action;
	}

	/**
	 * Accessジャーナル書き出し
	 */
	public function access ($str) {
		if(ACCESS_JOURNAL == 'true') {
		    $this->access_logger->info($this->format($str));
        }
	}

	public function login () {
        $this->access('LOGIN ,');
        $this->access('LOGIN ,USER_AGENT: '.$_SERVER['HTTP_USER_AGENT']);
    }

	public function action () {
        $this->access('ACTION,'.$this->controller.','.$this->action);
    }

	public function transition ($from,$to) {
        $this->access('TRANS ,'.$from.','.$to);
    }

	/**
	 * API処理結果出力用
	 */
	public function apiResult ($status,$str=null) {
        // ファイル名
        $fileName = $_SERVER["PHP_SELF"];
        // IPアドレス
        if (isset($SERVER["REMOTE_ADDR"])) {
            $accessPoint = $_SERVER["REMOTE_ADDR"];
        } else {
            $accessPoint = "localhost";
        }
        // Arrayの場合
        if(is_array($str)) {
            $str = print_r($str,true);
        }
		if(ACCESS_JOURNAL == 'true') {
            $device_browser = Util::getDeviceBrowserName($_SERVER["HTTP_USER_AGENT"]);
		    $this->access_logger->info("["
                                       .$accessPoint
                                       .",".$device_browser['device'].",".$device_browser['browser']
                                       .",".$_SERVER["REQUEST_METHOD"]
                                       .",".$fileName.",".$this->transaction_id
                                       ."] "
                                       ."STATUS: ".$status." ".$str);
        }
    }

    /**
     * SQLジャーナル書き出し
     * SELECTを対象に使用することを想定。　※大量となるため、本番では通常OFFにするべき。
     */
	public function sql ($method,$sql,$param) {
		if(SQL_JOURNAL == 'true') {
		    $this->sql_logger->info($this->format($method.": ".$sql));
		    $this->sql_logger->info($this->format($param));
        }
	}

    /**
     * SQLジャーナル書き出し(更新QUERY用）
     * INSERT/UPDATE/DELETE/COMMIT/ROLLBACKを対象に使用することを想定。
     */
	public function querysql ($method,$sql,$param) {
		if(QUERYSQL_JOURNAL == 'true') {
            if(is_null($sql)) {
                $this->sql_logger->info($this->format($method));
            } else {
		        $this->sql_logger->info($this->format($method.": ".$sql));
            }
		    $this->sql_logger->info($this->format($param));
        }
	}

    /**
     * ログ出力文字列の整形
     */
    private function format($str) {
        // Arrayの場合
        if(is_array($str)) {
            $str = print_r($str,true);
        }
        // ユーザID/TransactionIDを付与してreturn
        return "[U:".$this->user_id.",T:".$this->transaction_id."]".$str;
    }

}
