<?php

// BaseApi.phpのrequireはindex.phpの１階層下の階層(ex.ajax, cgi)のみから実行可能
// それ以外から呼び出す場合は、別途variables.phpを呼び出す
require_once ("../data/variables.php");
// Manager Class
require_once (APP."base/cllib/LogWriter.php"); 
require_once (APP."base/cllib/JournalWriter.php"); 
require_once (APP."base/cllib/LangManager.php"); 
require_once (APP."base/cllib/DataManager.php"); 
require_once (APP."base/cllib/ModelManager.php"); 
require_once (APP.'base/cllib/Util.php');

/**
 * API基底クラス
 * GETパラメータのTYPE=Dの場合は、ダウンロード呼び出しとして動作
 * 上記以外の場合は、JSONエンコードして返却する
 */
abstract class BaseApi {

    protected $conn;
    protected $lang;
    private $datam;
    protected $db;
    private $class;
    protected $logger;

    const TYPE_DOWNLOAD = 'D';
   
    /**
     * コンストラクタ
     */
    function __construct($class=null) {
        $this->class = $class;
    }

    /**
     * Abstract: 処理実行メソッド
     */
    abstract protected function main();

    /**
     * run: 処理実行メソッド本体→main()の呼び出し
     */
    public function run() {
        try {

            // トランザクションIDの発行
            $transactionId = uniqid(date("YmdHis")."-");

            // 使用Class読み込み
            $this->logger = new LogWriter(null,$transactionId);

            // $this->logger->writeDebug("###### PROCESS RUN (".$_SERVER["PHP_SELF"].") ######");
            $this->journal = new JournalWriter($transactionId);
            $this->datam = new DataManager();
            $this->db = new ModelManager($this->datam->getConnection(),"API",$transactionId,$this->logger,$this->journal);
            $this->lang = new LangManager(null,null,$this->db);

            // エラーチェック
            // ダウンロード呼び出しではない場合は、POST通信のみ受け付ける
            if($_GET['TYPE'] != self::TYPE_DOWNLOAD) {
                if ($_SERVER["REQUEST_METHOD"] != "POST") {
                    // POSTでなければ
                    // $this->logger->writeDebug("Request Method is not POST!");
                    $this->journal->apiResult("1","Request Method is not POST!");
                    exit("{\"status\":1}");
                }
            }

            // 処理実行
            $data = $this->main();

            if (!is_null($data)) {
                // 処理結果判定(status)
                if(is_null($data['status'])) {
                    $data = array_merge((array)$data, array("status" => 0));
                }

                // 出力
                if($_GET['TYPE'] != self::TYPE_DOWNLOAD) {
                    print(json_encode($data));
                    $this->journal->apiResult($data['status'],"API");
                } else {
                    $this->journal->apiResult($data['status'],"DOWNLOAD");
                }
            }

        } catch (Exception $e) {
            // DBロールバック
            try {
                $this->db->rollBack();
            } catch (Exception $dbe) {
                // トランザクション開始していない状態でrollBackを発行するとExceptionが発生するが、無視する
            }
            // ログ・ジャーナル書き出し
            $this->logger->writeDebug($e);
            $this->logger->writeException($e);
            $this->journal->apiResult("-1","Exception");
            // -1を返却
            exit("{\"status\":-1}");
        }
        $this->logger->writeDebug("###### PROCESS END (".$_SERVER["PHP_SELF"].") ######");
    }

    /**
     * システムエラー発生時に呼び出すメソッド
     */
    protected function exitSystemErr($str) {
        throw new Exception($str);
    }

}
