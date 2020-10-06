<?php

require_once 'Zend/Log.php';
require_once 'Zend/Log/Writer/Stream.php';

class LogWriter {

    private $logger;
    private $debuglogger;
    private $user_id;
    private $transaction_id;

	function __construct($user_id=null,$transaction_id=null) {
        // 通常ログ
		//$logfile = LOG_DIR.APP_ID."_Zend_".$_SERVER["REMOTE_ADDR"]."_".date('Ymd').".log";
		$logfile = LOG_DIR.APP_ID."_".date('Ymd').".log";
		$writer = new Zend_Log_Writer_Stream($logfile);
		$this->logger = new Zend_Log($writer);
        // Debugログ
		if(DEBUG) {
		    //$debuglogfile = LOG_DIR."D_".APP_ID."_Zend_".$_SERVER["REMOTE_ADDR"]."_".date('Ymd').".log";
		    $debuglogfile = LOG_DIR."D_".APP_ID."_".date('Ymd').".log";
		    $debugwriter = new Zend_Log_Writer_Stream($debuglogfile);
		    $this->debuglogger = new Zend_Log($debugwriter);
        }
        $this->user_id = $user_id;
        $this->transaction_id = $transaction_id;
	}

	/**
	 * エラーログ出力（トラブルコール対象）
	 */
	public function writeErr ($str) {
		$this->logger->err($this->format($str));
	}

	/**
	 * エラーログ出力（Exception用、トラブルコール対象）
     * variablesの$ignore_exceptionに登録されている文字列が含まれる場合は、出力対象外
     * ※帳票出力時のVIEWが見つからないエラーを無視することを想定。多用しないこと。
	 */
	public function writeException ($str) {
        // 出力抑止対象であればreturn ※帳票出力時のVIEWが見つからないエラーは無視
        global $ignore_exception;
        foreach((array)$ignore_exception as $i => $expr) {
            if(strstr($str, $expr)) return;
        }
        // エラーログ出力
		$this->logger->err("Exception start ---------------------------------------------------------------");
		$this->logger->err("[U:".$this->user_id.",T:".$this->transaction_id."]".$str);
		$this->logger->err("Exception end -----------------------------------------------------------------");
        // DEBUGログにも出力
        $this->writeDebug($str);
	}

	/**
	 * インフォメーションログ出力
	 */
	public function writeInfo ($str) {
		$this->logger->info($this->format($str));
	}

	/**
	 * デバッグ用ログ出力（自由に使用、本番環境では出力されない）
	 */
	public function writeDebug ($str) {
		if(DEBUG) {
			$this->debuglogger->debug($this->format($str, true));
		}
	}

    /* ログ出力文字列の整形 */
    private function format($str, $isDebug = false) {
        // writeDebugかObjectの場合
        if ($isDebug || is_object($str)) {
            ob_start();
            var_dump($str);
            $str = ob_get_contents();
            ob_end_clean();
        }
        // Arrayの場合
        if(is_array($str)) {
            $str = print_r($str,true);
        }
        // ユーザID/TransactionIDを付与してreturn
        return "[U:".$this->user_id.",T:".$this->transaction_id."]".$str;
    }

}
