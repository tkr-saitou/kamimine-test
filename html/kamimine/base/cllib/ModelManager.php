<?php

/**
 * Model管理Class
 */
class ModelManager {

    private $conn;
    private $user_id;
    private $transaction_id;
    private $logger;
    private $journal;

    function __construct($conn,$user_id,$transaction_id,$logger=null,$journal=null) {
        $this->conn = $conn;
        $this->user_id = $user_id;
        $this->transaction_id = $transaction_id;
        $this->logger = $logger;
        $this->journal = $journal;
    }

    /* INVOKE ------------------------------------------------------------------------------------------ */

    /**
     * Model層メソッド実行
     */
    public function invoke($class,$method,$args=null) {
        // 引数NULLの場合でもarrayを渡さないと落ちるため
        if(is_null($args)) $args = array();
        // リフレクションにより実行
        try {
            $reflectionClass = new ReflectionClass($class);
            $reflectionInstance = $reflectionClass->newInstanceArgs(
                array($this->conn, $this->user_id, $this->transaction_id,$this->logger,$this->journal));
            $reflectionMethod = new ReflectionMethod($class, $method);
            $result = $reflectionMethod->invokeArgs($reflectionInstance, $args);
        } catch (Exception $e) {
            // エラー発生時はロールバック
            $this->rollBack();
            throw $e;
        }
        return $result;
    }

    /* COMMIT/ROLLBACK --------------------------------------------------------------------------------- */

    /** 
     * COMMIT 発行
     */
    public function commit() {
        if(!is_null($this->journal)) {
            $this->journal->querysql('commit',$sql,$params);
        }
        $this->conn->commit();
    }

    /** 
     * ROLLBACK 発行
     */
    public function rollBack() {
        if(!is_null($this->journal)) {
            $this->journal->querysql('rollBack',$sql,$params);
        }
        $this->conn->rollBack();
    }

    /* RESET ------------------------------------------------------------------------------------------- */

    /**
     * Connectionを取得し直す
     * 1リクエスト内でやむを得ずDBトランザクションを分ける場合に、使用する。
     * ex. INSERT処理の後COMMIT発行 → 外部PGM呼び出し → 再度INSERT処理 など
     */
    public function resetConnection() {
        $datam = new DataManager($this->logger);
        $this->conn = $datam->getConnection();
    }

}
