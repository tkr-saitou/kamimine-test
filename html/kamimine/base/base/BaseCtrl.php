<?php

require_once ('Zend/Controller/Action.php');
require_once ('Zend/Auth.php');
require_once ('Zend/Config/Ini.php');
require_once (APP.'base/base/BaseHtmlGenerator.php');
require_once (APP.'base/cllib/Util.php');
// Writer 
require_once (APP.'base/cllib/LogWriter.php');
require_once (APP.'base/cllib/JournalWriter.php');
// Manager
require_once (APP.'base/cllib/MessageManager.php');
require_once (APP.'base/cllib/SessionManager.php');
require_once (APP.'base/cllib/ScreenManager.php');
require_once (APP.'base/cllib/ValidationManager.php');
require_once (APP.'base/cllib/DataManager.php');
require_once (APP.'base/cllib/ModelManager.php');
require_once (APP.'base/cllib/DialogManager.php');
require_once (APP.'base/cllib/ReportManager.php');
require_once (APP.'base/cllib/LangManager.php');
// Tag
require_once (APP.'base/cllib/TagManager.php');
// Common Model Class
require_once (APP.'base/models/s_code.php');
require_once (APP.'base/models/s_numbering.php');
require_once (APP.'base/models/s_tax.php');
require_once (APP.'base/models/s_user.php');
// Action Helper
require_once (APP.'controllers/ActionHelper.php');

class BaseCtrl extends Zend_Controller_Action {

	protected $post;
	protected $params;
	protected $authInfo;
	protected $message;
	protected $logger;
	protected $journal;
	protected $session;
	protected $screen;
	protected $validator;
	protected $tag;
	protected $helper;
	private $datam;
	private $conn;
	protected $db;
	protected $controller;
	protected $action;
	private $screentitle;
	private $basehtml;

	public function init() {

		// HTTPリクエスト取得
		$req = $this->getRequest();
		$this->post = $req->getPost();
		$this->params = $req->getUserParams();
		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity()) {
			$this->authInfo = $auth->getIdentity();
		}

        // Controller名/Action名取得
        $this->controller = mb_strtolower($req->getControllerName());
        $this->action = mb_strtolower($req->getActionName());

		// トランザクションIDの発行
		$transactionId = uniqid(date("YmdHis")."-");

		// 使用クラス読み込み
		$this->logger = new LogWriter($this->authInfo->user_id,$transactionId);
        $this->logger->writeDebug("+- PROCESS START (".$this->controller."/".$this->action.") --------------------------");
		$this->journal = new JournalWriter($transactionId,$this->authInfo->user_id,$this->controller,$this->action);
		$this->message = new MessageManager($this->view,$this->logger);
		$this->session = new SessionManager($this->logger,$this->controller);
		$this->screen = new ScreenManager($this->controller,$this->session,$this->logger,$this->view);
		$this->basehtml = new BaseHtmlGenerator($this->logger,$this->screen,$this->message,$this->controller);
		$this->validator = new ValidationManager($this->controller,$this->session,$this->logger,$this->post,$this->message);
        $this->lang = new LangManager(null,$this->logger);
		$this->tag = new TagManager($this->controller,$this->session,$this->logger,$this->lang);
		$this->datam = new DataManager($this->logger);
        $this->conn = $this->datam->getConnection(); // DBコネクション取得
        $this->db = new ModelManager($this->datam->getConnection(),$this->authInfo->user_id,$transactionId,$this->logger,$this->journal);
        $this->dialog = new DialogManager($this->view,$this->logger,$this->db,$this->authInfo,
                                          $this->session,$this->screen,$this->controller,$this->post,$this->tag, $this->message);
        $this->report = new ReportManager($this->view,$this->logger,$this->session,$this->controller,$this->db);
        $this->ajax = $this->_helper->getHelper('AjaxContext');
		$this->helper = new ActionHelper($this->post, $this->params, $this->authInfo, $this->session, $this->db,
                                         $this->logger, $this->tag, $this->screen);

        if(!empty($this->post['tciTransitionPrevCtrl']) && $this->post['tciTransitionPrevCtrl'] != $this->controller) {
		    // 遷移元画面のControllerSession消去 
            $this->session->unsetControllerSession($this->post['tciTransitionPrevCtrl']);
            // 遷移元Controller名の保持
            $this->session->setControllerSession("tciPrevCtrl",$this->post['tciTransitionPrevCtrl']);
            // ジャーナル書き込み(画面遷移)
            $this->journal->transition($this->post['tciTransitionPrevCtrl'],$this->controller);
        } else {
            // ジャーナル書き込み(アクション)
            $this->journal->action($this->authInfo->user_id);
        }

        // 調査用
        //$this->logger->writeDebug(getallheaders());
        //$this->logger->writeDebug($_SERVER['HTTP_USER_AGENT']);
        //$this->logger->($_SESSION);

		// Session格納
		if(empty($this->session->get("tciUserName"))) {
            $config = new Zend_Config_Ini(APP.'data/application.ini',array('database', 'auth'));
            if($config->auth->tableName == "t_user") {
                // s_userテーブルを使用しない場合(互換性担保)
                $t_user = $this->db->invoke('s_user','selectOldUserTable',array($this->authInfo->user_id));
                $this->session->set("tciUserName",$t_user['name']);
            } else {
                // s_userテーブルを使用する場合
                $s_user = $this->db->invoke('s_user','selectByPk',array($this->authInfo->user_id));
                $this->session->set("tciUserName",$s_user['user_name']);
            }
        }
        $this->session->setTransactionId($transactionId);
		//if(isset($_SERVER["REMOTE_ADDR"])) $this->session->set("ip", $_SERVER["REMOTE_ADDR"]);
		if(isset($this->post['innerWidth'])) $this->session->set("tciInnerWidth", $this->post['innerWidth']);
		if(isset($this->post['innerHeight'])) $this->session->set("tciInnerHeight", $this->post['innerHeight']);

        // AjaxAction登録
        $this->registerAjaxAction('tcishowdialog');   // Dialog表示
        $this->registerAjaxAction('tcidialog');       // Dialog内Action
        $this->registerAjaxAction('tcigetaddress');   // 郵便番号→住所変換
        $this->registerAjaxAction('tcioutputreport'); // 帳票出力

	}

	public function preDispatch() {
	  
	}

	public function indexAction() {
	
	}
	
	public function postDispatch() {

        // Head
		$this->view->assign('tci_head', $this->basehtml->get_tcihead(getApplicationUrl($this->getRequest()), $this->screentitle));

        // Global Header
		$headerBtn = $this->helper->getHeaderBtn();
        $global_header = $this->basehtml->getTciGlobalHeader($this->screentitle,$this->authInfo->user_id,$this->session->get("tciUserName"), $headerBtn);
		$this->view->assign('tci_global_header', $global_header);

        // 画面遷移情報設定
        if (!is_null($this->screen->getController()) and 
            !is_null($this->screen->getAction())) {
            $this->view->assign('tciTransition', true);
            $this->view->assign('tciTransitionController', $this->screen->getController());
            $this->view->assign('tciTransitionAction', $this->screen->getAction());
            $this->view->assign('tciTransitionOpenWindow', $this->screen->isOpenWindow());
        }

        // モーダルメッセージ
		$this->view->assign('tciModalMsgs', implode(',',$this->message->getModalMsgs()));
		$this->view->assign('tciErrItems',  implode(',',$this->message->getErrItems()));

        // 必須チェックエラー
		$this->view->assign('tci_required_err_items', $this->validator->getRequiredErrItems());

        // JS 
		$this->view->assign('tci_js', $this->basehtml->get_tcijs());
		$this->view->assign('tcimap_js', $this->basehtml->get_tcimapjs());

        // DEBUGログ出力
        // $this->logger->writeDebug("+- PROCESS END -----------------------------------------------");
	}

    /* ユーティリティ ---------------------------------------------------------------------------- */

    /**
     * 画面タイトルを設定
     */
    protected function setTitle($title) {
        $this->screentitle = $title;
    }

    /**
     * Ajaxで動作させるActionをZendのActionContextに登録
     */
    protected function registerAjaxAction($actionName) {
        $this->ajax->addActionContext($actionName, array('html','json'))->initContext();
    }

    /* Action --------------------------------------------------------------------------------- */

    /**
     * Dialog表示
     * @description Dialogを開いた際に呼ばれる。　※画面自体のrender時に呼ばれるinitとは別
     */
    public function tcishowdialogAction() {
        $this->dialog->tcishowdialogAction();
    }

    /**
     * DialogAction
     * @description Dialog内から呼び出されるAction
     */
    public function tcidialogAction() {
        $this->dialog->tcidialogAction();
    }

    /**
     * 帳票出力処理
     */
    public function tcioutputreportAction() {
        $this->report->tcioutputreportAction();
    }

    /**
     * 郵便番号→住所変換
     */
    public function tcigetaddressAction() {
        $result = Util::getAddress($this->post['tciZipCd4Address']);
        if(empty($result)) {
            $this->message->setModalErrMsg("存在しない郵便番号です",$this->post['tciZipCdId4Address']);
        } else {
            $this->view->assign("address", Util::getAddress($this->post['zip_cd']));
        }
    }

}
