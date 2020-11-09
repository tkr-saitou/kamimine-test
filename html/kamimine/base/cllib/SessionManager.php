<?php

require_once ('Zend/Session.php');

class SessionManager {

	private $session;
	private $logger;
	private $controller;
	private $userSessionName;

	function __construct($logger,$controller) {
        $this->logger = $logger;
        $this->controller = strtolower($controller);

        // キャッシュ設定（有効期限切れ回避）
        session_cache_expire(0);
        session_cache_limiter('private_no_expire');
        header('Expires: -1');
        header('Cache-Control:');
        header('Pragma:');

        // セッションタイムアウト値設定
        ini_set('session.gc_maxlifetime', SESSION_TIMEOUT);

        // Session取得
        $this->userSessionName = "TCI".session_id();
		$this->session = new Zend_Session_Namespace($this->userSessionName);
        // Namespace名を退避
        $namespaceList = (array)$this->get('tciNamespaceList');
        if(!in_array($this->userSessionName,$namespaceList)) {
            array_push($namespaceList,$this->userSessionName);
            $this->set('tciNamespaceList',$namespaceList);
        }
        //$this->logger->writeDebug($_SESSION);

        // セッションタイムアウト判定
        if($this->controller != "auth" && isset($_SESSION['tci_session_timeout_time'])) {
            if($_SESSION['tci_session_timeout_time'] < time()) {
                // セッションタイムアウト発生
                $this->logger->writeDebug("######## SESSION TIMEOUT ########");
                $this->unsetUserSession();
            }
        } else {
            // 新規セッションの開始
            // $this->logger->writeDebug("Session Info --------------------------------------------");
            // $this->logger->writeDebug("session_id [".session_id()."]");
            // $this->logger->writeDebug("session_name [".session_name()."]");
            // $this->logger->writeDebug("session_statue [".session_status()."]");
            // $this->logger->writeDebug("session_module_name [".session_module_name()."]");
            // $this->logger->writeDebug("session_save_path [".session_save_path()."]");
            // $this->logger->writeDebug("session_cache_limiter [".session_cache_limiter()."]");
            // $this->logger->writeDebug("session_cache_expire [".session_cache_expire()."]");
            // $this->logger->writeDebug("session_get_cookie_params");
            // $this->logger->writeDebug(session_get_cookie_params());
            // $this->logger->writeDebug("Session Info --------------------------------------------");
        }
        // セッションタイムアウト判定用時刻設定
        $_SESSION['tci_session_timeout_time'] = time() + SESSION_TIMEOUT;

	}

	/*
	 * セッション情報SET
	 */
	public function set($key, $value) {
		$this->session->$key = $value;
        //$_SESSION[$key] = $value;
	}

	/*
	 * セッション情報GET
	 */
	public function get($key) {
		return $this->session->$key;
        //return $_SESSION[$key];
	}

	/*
	 * Controllerセッション情報SET ※メソッド名が長いため、別名も用意した
	 */
	public function setCtrlSession($key, $value,$controller=null) {
        $this->setControllerSession($key, $value,$controller);
    }
	public function setControllerSession($key, $value,$controller=null) {
        // $controllerがnullの場合は、自Controllerをセット
        if(is_null($controller)) $controller = $this->controller;
        // 小文字変換
        $controller = strtolower($controller);
        // Session格納
        //$_SESSION[$controller][$key] = $value;
        $namespace = $this->getNamespace($controller);
		$this->session->$namespace = new Zend_Session_Namespace($namespace);
		$this->session->$namespace->$key = $value;
        // Namespace名を退避
        $namespaceList = (array)$this->get('tciNamespaceList');
        if(!in_array($namespace,$namespaceList)) {
            array_push($namespaceList,$namespace);
            $this->set('tciNamespaceList',$namespaceList);
        }
	}

	/*
	 * Controllerセッション情報GET
	 */
	public function getCtrlSession($key) {
        return $this->getControllerSession($key);
    }
	public function getControllerSession($key) {
        //return $_SESSION[$this->controller][$key];
        $namespace = $this->getNamespace($this->controller);
		if(is_null($namespace)) {
			return null;
		} else {
			return $this->session->$namespace->$key;
		}
	}

	/*
	 * ユーザセッション情報削除
	 */
	public function unsetUserSession() {
        // 退避されていたNamespaceリストを元に残っているSessionをすべて削除
        $namespaceList = (array)$this->get('tciNamespaceList');
        foreach($namespaceList as $namespace) {
            Zend_Session::namespaceUnset($namespace);
        }
        // セッション変数を削除
        $_SESSION = array();
        // セッション クッキーを削除
        if( ini_get( 'session.use_cookies' ) ) {
            $params = session_get_cookie_params();
            setcookie( session_name(), '', time() - 3600, $params[ 'path' ] );
        }
        // セッション ファイルを削除
        session_destroy();
	}

	/*
	 * Controllerセッション情報削除
	 */
	public function unsetControllerSession($controller) {
        //unset($_SESSION[$controller]);
        // Sessionから削除
        $namespace = $this->getNamespace(strtolower($controller));
        Zend_Session::namespaceUnset($namespace);
        // 退避されていたNamespaceListから削除
        $namespaceList = (array)$this->get('tciNamespaceList');
        if(($key = array_search($namespace, $namespaceList)) !== false) {
            unset($namespaceList[$key]);
            $this->set('tciNamespaceList',$namespaceList);
        }
	}

	/*
	 * TransactionID設定
	 */
	public function setTransactionId($transactionId) {
		$this->session->tcitransactionId = $transactionId;
        //$_SESSION['tcitransactionId'] = $transactionId;
	}

	/*
	 * TransactionID取得
	 */
	public function getTransactionId() {
		return $this->session->tcitransactionId;
        //return $_SESSION['tcitransactionId'];
	}

    /**
     * Private: Namespace名取得
     */
    private function getNamespace($str) {
        return $this->userSessionName."_".$str;
    }
}
