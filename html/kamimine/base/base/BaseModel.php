<?php

require_once (APP.'base/cllib/Util.php');
require_once (APP.'base/cllib/DbManager.php'); // 既存互換

class BaseModel {

    protected $logger;
	protected $db; // 直接アクセス不可にしたかったが、既存互換のためprotectedに設定
    private $userId;
    private $transactionId;
    private $journal;

    /**
     * コンストラクタ
     * @description TCIフレームワーク以前の既存システムと互換性を保持するため、
     *              コネクションを取得して直接$dbからSQL発行することを許可
     */
	public function __construct($connection=null,$userId=null,$transactionId=null,$logger=null,$journal=null) {
        $this->logger = $logger;
        if(is_null($connection)) {
            // 既存互換
            $this->db = DbManager::getConnection();
        } else {
		    $this->db = $connection;
        }
		$this->userId = $userId;
		$this->transactionId = $transactionId;
        $this->journal = $journal;
    }

    /** 
     * SELECT文発行 単一行1カラム目のみ返却
     * @return String文字列
     */
    protected function fetchOne($sql, $params) {
        if(!is_null($this->journal)) {
            $this->journal->sql('fetchOne',$sql,$params);
        }
        return $this->db->fetchOne($sql,$params);
    }

    /** 
     * SELECT文発行 単一行返却
     * @return 行オブジェクト
     */
    protected function fetchRow($sql, $params) {
        if(!is_null($this->journal)) {
            $this->journal->sql('fetchRow',$sql,$params);
        }
        return $this->db->fetchRow($sql,$params);
    }

    /** 
     * SELECT文発行 N件返却
     * @return 行オブジェクト配列
     */
    protected function fetchAll($sql, $params=null) {
        if(!is_null($this->journal)) {
            $this->journal->sql('fetchAll',$sql,$params);
        }
        return $this->db->fetchAll($sql,$params);
    }

    /** INSERT/UPDATE/DELETE文発行 */
    protected function query($sql, $params) {
        if(!is_null($this->journal)) {
            $this->journal->querysql('query',$sql,$params);
        }
        return  $this->db->query($sql,$params)->rowCount();
    }

    /** COMMIT発行 */
    protected function commit() {
        if(!is_null($this->journal)) {
            $this->journal->querysql('commit',$sql,$params);
        }
        return $this->db->commit();
    }

    /** ROLLBACK発行 */
    protected function rollBack() {
        if(!is_null($this->journal)) {
            $this->journal->querysql('rollBack',$sql,$params);
        }
        return $this->db->rollBack();
    }

    /** USER ID 取得 */
    protected function getUserId() {
        return $this->userId;
    }

    /** 現在時刻(datetime型)取得 */
    protected function getDatetime() {
        return date("Y-m-d H:i:s");
    }

    /** TRANSACTION ID 取得 */
    protected function getTransactionId() {
        return $this->transactionId;
    }

}
