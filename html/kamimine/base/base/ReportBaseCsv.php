<?php

require_once (APP.'base/cllib/Util.php');

/*
 * CSV出力ベースクラス
 */
abstract class ReportBaseCsv {

	protected $logger;

	/**
	 * コンストラクタ 
	 */
	function __construct($logger=null) {
		$this->logger = $logger;
	}

	/**
	 * 出力
	 */
	public function generate($data) {

		// 編集処理
        $this->edit($data);

        $now = date("Ymd_His");
        mb_http_output("EUC-JP");

        header("Content-Type: application/octet-stream");
        header("Content-disposition: attachment; filename=filename_".$now.".csv");
        $str = "カラム１, カラム2, カラム3, カラム4\n";
        foreach ($data_array as $row) {
            $str .= "\"".$row[1]."\",";
            $str .= "\"".$row[2]."\",";
            $str .= "\"".$row[3]."\",";
            $str .= "=\"".$row[4]."\","; // 数字はExcelで綺麗に表示されるように ="数字" の形に出力する
            $str .= "\n";
        }
        echo mb_convert_encoding($str,"SJIS","EUC-JP");
        exit;

	}

    /* abstracty ---------------------------------------------------------------------------- */

	/**
	 * 編集（オーバーライド用）
	 */
	abstract protected function edit($data);

}
