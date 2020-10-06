<?php

require_once (APP.'base/cllib/TagManager.php');
require_once (APP.'base/cllib/TagGridGenerator.php');
require_once (APP.'base/cllib/TagDropdownGenerator.php');
require_once (APP.'base/cllib/TagRadiobuttonGenerator.php');
require_once (APP.'base/cllib/TagCheckboxGenerator.php');
require_once (APP.'base/cllib/TagInputDatalistGenerator.php');

/**
 * Dialogクラスの継承元クラス
 *
 * Dialogの各クラス（controllers/dialog/Xxxx.php）よりextendsする。<br>
 * $view,$session,$tag,$messageなどのインスタンスを保持している。<br>
 * 本クラスを継承した上で、abstractのinit/show/getDataを実装すること。<br>
 *
 * @package Base/BaseClass
 */
abstract class BaseDialog {

    protected $logger;
    protected $view;
    private $class;
    private $args;
    protected $db;
    protected $authInfo;
    protected $session;
    protected $screen;
    private $controller;
    protected $post;
    protected $tag;
    protected $message;

	public function __construct($view,$class,$logger,$db,$authInfo,$session,$screen,$controller,$post,$tag,$message) {
        $this->logger = $logger;
        $this->view = $view;
        $this->class = $class;
        $this->args = array();
        $this->db = $db;
        $this->authInfo = $authInfo;
        $this->session = $session;
        $this->screen = $screen;
        $this->controller = $controller; // DialogのControllerではなく、呼び出し元Controller
        $this->post = $post;
        $this->tag = $tag;
        $this->message = $message;
    }

    /* Abstract --------------------------------------------------------------------------- */

    /**
     * abstract 初期化メソッド
     *
     * DialogManagerの$dialog->init($class,$args)メソッドを経由して呼ばれるメソッド<br>
     * viewへのアサインなどの初期処理を実装することを想定<br>
     * 呼び出し側は初期表示Action（=indexActionなど）で呼び出すこと<br>
     * @param object $args 必要に応じて使用する引数
     */
    abstract public function init($args=null);

    /**
     * abstract 表示メソッド
     *
     * Dialogが表示されるタイミングでフレームワークにより実行される<br>
     * 表示時にはじめて初期検索を行ってDialog上に表示する、などの用途を想定。<br>
     */
    abstract public function show();

    /**
     * abstract 値返却メソッド
     *
     * 親画面への値戻し用メソッド<br>
     * フレームワーク内では特に呼び出しを行っていない。<br>
     * 値戻し処理がDialogによってバラバラとなることを防ぐためにabstractで定義している。<br>
     * @param object $data 必要に応じて使用する引数
     */
    abstract public function getData($data);

    /* ユーティリティ -------------------------------------------------------------------- */

    /**
     * （フレームワーク内部使用専用）VIEWにアサインする 
     * @access private
     */
    public function assign4Show() {
        $this->view->assign("tciDialogId",$this->class);
        $this->view->assign("tciDialogArgs",$this->args);
    }

    /**
     * $nameに$valueをセット 
     * @access private
     * @deprecated
     */
    public function set($name,$value) {
        array_push($this->args, array($name => $value));
    }

}
