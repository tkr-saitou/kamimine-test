<?php

class BaseActionHelper {

    protected $post;
    protected $params;
    protected $authInfo;
    protected $session;
    protected $db;
    protected $logger;
    protected $tag;
    protected $screen;

    public function __construct($post, $params, $authInfo, $session, $db, $logger, $tag, $screen) {
        $this->post = $post;
        $this->params = $params;
        $this->authInfo = $authInfo;
        $this->session = $session;
        $this->db = $db;
        $this->logger = $logger;
        $this->tag = $tag;
        $this->screen = $screen;
    }

    /**
     * GridGenerator取得
     */
    protected function getTagGridGenerator($tableId) {
        // リフレクションによりインスタンス生成
        $reflectionClass = new ReflectionClass(TagGridGenerator);
        $reflectionInstance = $reflectionClass->newInstanceArgs(
                                array($tableId,$this->controller,$this->session,$this->logger));
        return $reflectionInstance;
    }

    /**
     * Model層メソッド実行
     */
    protected function invoke($class,$method,$args=null) {
        // 引数NULLの場合でもarrayを渡さないと落ちるため
        if(is_null($args)) $args = array();
        // リフレクションにより実行
        try {
            $reflectionClass = new ReflectionClass($class);
            $reflectionInstance = $reflectionClass->newInstanceArgs(
                                    array($this->db, $this->authInfo->user_id, $this->session->getTransactionId(),$this->logger));
            $reflectionMethod = new ReflectionMethod($class, $method);
            $result = $reflectionMethod->invokeArgs($reflectionInstance, $args);
        } catch (Exception $e) {
            // エラー発生時はロールバック
            $this->rollBack();
            throw $e;
        }
        return $result;
    }

    /** COMMIT 発行 */
    protected function commit() {
        $this->db->commit();
    }

    /** ROLLBACK 発行 */
    protected function rollBack() {
        $this->db->rollBack();
    }

	/** ヘッダーボタン取得 */
	public function getHeaderBtn() {
	}
}
