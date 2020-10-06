<?php

require_once (APP.'lib/excel/PHPExcel.php');
require_once (APP.'lib/excel/PHPExcel/IOFactory.php');
require_once (APP.'lib/excel/PHPExcel/Style/Border.php'); // 罫線
require_once (APP.'lib/excel/PHPExcel/Style/Fill.php');   // 背景色
require_once (APP.'base/cllib/Util.php');

/*
 * Excel帳票出力ベースクラス
 */
abstract class ReportBaseExcel {

	protected $logger;
    protected $excel;
	private $reportTempName;   // テンプレートファイル名＝Class名となる
    private $downloadFileName; // ダウンロードされてユーザの目に触れるファイル名
    protected $db;
    private $extension = '.xlsx';

	/**
	 * コンストラクタ 
	 */
	function __construct($class=null,$logger=null, $db) {
		$this->logger = $logger;
		$this->db = $db;
        /*
        $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
        $cacheSettings = array( 'memoryCacheSize' => '800MB');
        PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
        //$objPHPExcel = PHPExcel_IOFactory::load($xlsxfile);

        $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_discISAM;
        $cacheSettings = array('dir' => '/tmp/log');
        PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
        //$objPHPExcel = PHPExcel_IOFactory::load($xlsxfile);
        */

        // テンプレートファイル読み込み 
		$this->reportTempName = $class;
        $reader = PHPExcel_IOFactory::createReader('Excel2007');
        $this->excel = $reader->load(REPORT_TEMP_DIR.$this->reportTempName.$this->extension);
	}

	/**
	 * 出力
	 */
	public function generate($data) {

		// 編集処理
        $this->edit($data);

        // デフォルト設定
        for ($i = 0; $i < $this->excel->getSheetCount(); $i++) {
            // フォントをMeiryoUIに統一
            $this->excel->getSheet($i)->getDefaultStyle()->getFont()->setName('Meiryo UI');
            // マージン設定
            $this->excel->getSheet($i)->getPageMargins()->setTop(0.4);
            $this->excel->getSheet($i)->getPageMargins()->setBottom(0.4);
            $this->excel->getSheet($i)->getPageMargins()->setLeft(0.4);
            $this->excel->getSheet($i)->getPageMargins()->setRight(0.4);
            $this->excel->getSheet($i)->getPageMargins()->setHeader(0.2);
            $this->excel->getSheet($i)->getPageMargins()->setFooter(0.2);
            // ヘッダ・フッタ設定
            //$this->excel->getSheet($i)->getHeaderFooter()->setOddHeader('&K0000ff' . mb_convert_encoding('これは重要機密書類です', 'UTF-8'));
            // フッタ設定（左右はテンプレートより取得した設定を残し、中央をページ番号で上書き）
            $footers = explode("&C",$this->excel->getSheet($i)->getHeaderFooter()->getOddFooter());
            $f_left = $footers[0];
            if(empty(explode("&R",$footers[1])[0])) {
                $f_center = '&C&P / &N';
            } else {
                $f_center = explode("&R",$footers[1])[0];
            }
            $f_right = explode("&R",$footers[1])[1];
            $this->excel->getSheet($i)->getHeaderFooter()->setOddFooter($f_left.$f_center."&R".$f_right); // &C:中央,&P:ページ番号,&N:総ページ数
        }
        // プロパティ情報設定
        $this->excel->getProperties()->setCompany("");
        $this->excel->getProperties()->setCreator("");
        $this->excel->getProperties()->setCreated("");
        $this->excel->getProperties()->setLastModifiedBy("");
        $this->excel->getProperties()->setModified("");

        // ダウンロードファイル名が設定されていない場合、Exception（開発者向け）
        if(empty($this->downloadFileName)) throw new Exception("ダウンロードファイル名を設定してください"); 
        // UI調整（1シート目をアクティブに設定。すべてのシートでA1を選択）
        $this->excel->setActiveSheetIndex(0);
        for ($i = 0; $i < $this->excel->getSheetCount(); $i++) {
            $this->excel->getSheet($i)->setSelectedCells('A1');
        }
        // 文字コード指定
        //mb_convert_encoding($filename, 'sjis', 'utf-8');
        mb_http_output("UTF-8");
        // ファイル名を設定
        $filename = $this->downloadFileName.date("YmdHis").$this->extension;
        // Excel2007形式で出力する準備
        header('Content-Type: application/octet-stream');
        //$downloadFileName = mb_convert_encoding($this->downloadFileName.date("YmdHis").".xlsx", 'sjis', 'utf-8');
        $downloadFileName = mb_convert_encoding($filename, 'sjis', 'UTF-8');
        header('Content-Disposition: attachment;filename="'.mb_convert_encoding($filename, 'sjis', 'UTF-8'));
        header('Cache-Control: max-age=0');
        //header('Content-Disposition: attachment;filename="'.$this->downloadFileName.date('YmdHis').'.xlsx"');
        //header("Content-Length:".filesize($this->excel));
        // 破損エラー防止
        ob_end_clean();
        // ファイルサーバに出力する場合は下記
        //$writer->save(REPORT_OUTPUT_DIR.$filename);
		// 出力処理
        $writer = PHPExcel_IOFactory::createWriter($this->excel, "Excel2007");
        $writer->save('php://output');
	}

    /* abstracty ---------------------------------------------------------------------------- */

	/**
	 * 編集（オーバーライド用）
	 */
	abstract protected function edit($data);

    /* Utility ------------------------------------------------------------------------------ */
    // PHPExcel自体ライブラリなので、その機能を使用すれば問題ないが、調べる手間を省くために本ユーティリティを提供する
    // これをそのまま使用して問題ない。また、カバーしていない操作については本ユーティリティはリファレンスにしかならないため、独自に実装すること。
    // 要するに「XX帳票で実装したはず」で探してコピペするのではなく、フレームワークとして蓄積するため、本クラスに取り込んでいる。

	/**
	 * ダウンロードファイル名の設定 ※editメソッドの中で呼ぶこと
	 */
	public function setDownloadFileName($name) {
        $this->downloadFileName = $name;
        $this->excel->getProperties()->setTitle($name);
    }

	/**
	 * Sheetコピー 
	 */
	public function copySheet($sheet, $title) {
        $copied = $sheet->copy();
        $copied->setTitle($title);
        $this->excel->addSheet($copied, null);
    }

	/**
	 * 書式設定
	 */
	public function setFormat($sheet, $cellname, $format) {
        $sheet->getStyle($cellname)->getNumberFormat()->setFormatCode($format);
    }

	/**
	 * フォントカラー 
     * @param $color: #は不要。ex.FFFFFF
	 */
	public function setFontColor($sheet, $cellname, $color) {
        $sheet->getStyle($cellname)->getFont()->getColor()->setARGB($color);
    }

	/**
	 * 背景色
     * @param $color: #は不要。ex.FFFFFF
	 */
	public function setBgColor($sheet,$cellname,$color) {
        $sheet->getStyle($cellname)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB($color);
    }

	/**
	 * 罫線を引く
	 */
	public function setBorder($sheet, $cellname, $borderstyle=null) {
        if(is_null($borderstyle)) {
            $sheet->getStyle($cellname)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        } else {
            $sheet->getStyle($cellname)->getBorders()->getAllBorders()->setBorderStyle($borderstyle);
        }
    }

	/**
	 * セル結合
	 */
    public function mergeCells($sheet, $cellname) {
        $sheet->mergeCells($cellname);
    }

	/**
	 * フォントサイズ 
	 */
	public function setFontSize($sheet,$cellname,$fontsize) {
        $sheet->getStyle($cellname)->getFont()->setSize($fontsize);
    }

	/**
	 * Align設定
	 */
	public function setAlign($sheet,$cellname,$align) {
        if(strtoupper($align) == "CENTER") {
            $sheet->getStyle($cellname)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        } elseif (strtoupper($align) == "LEFT") {
            $sheet->getStyle($cellname)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        } elseif (strtoupper($align) == "RIGHT") {
            $sheet->getStyle($cellname)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        } else {
            $sheet->getStyle($cellname)->getAlignment()->setHorizontal($align);
        }
    }

	/**
	 * 行高さ設定 
	 */
	public function setRowHeight($sheet,$rownum,$height) {
        $sheet->getRowDimension($rownum)->setRowHeight($height);
    }

	/**
	 * 列幅指定 
	 */
	public function setColWidth($sheet,$colnum,$width) {
        $sheet->getColumnDimension($colnum)->setWidth($width);
    }

	/**
	 * ウィンドウ枠固定 
	 */
	public function freezePane($sheet,$cellname) {
        $sheet->freezePane($cellname);
    }

	/**
	 * ページレイアウト：タイトル行（印刷時の行固定） 
	 */
	public function printTitleRow($sheet,$start,$end) {
        $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd($start,$end);
    }

	/**
	 * 画像ファイル表示
     * @param filename: imagesフォルダ配下のファイル名（拡張子付で指定、他のフォルダは指定不可）
     * @param cell: セル指定（ex.A1）
     * @param height: 画像の高さを％で指定
     * @return drawingオブジェクト。シートに出力するには下記のように発行
     *         $drawingObject->setWorksheet($excel->getActiveSheet());
     *         ※使用する側でカスタマイズ可能とするため、本メソッド内ではシート出力しない
	 */
	public function getDrawingObject($filename,$cell,$height=null) {
        // drawingオブジェクト生成
        $objDrawing = new PHPExcel_Worksheet_Drawing();
        $objDrawing->setPath('./images/'.$filename);
        $objDrawing->setCoordinates($cell); // 位置
        if(is_null($height)) $height = 100;
        $objDrawing->setHeight($height); // 高さ（%指定となる）

        // 画像のプロパティを見たときに表示される情報を設定
        $objDrawing->setName(''); // ファイル名
        $objDrawing->setDescription(''); // 画像の概要
        //$objDrawing->setOffsetX(50); // 横方向へ何ピクセルずらすかを指定
        //$objDrawing->setRotation(25); // 回転の角度
        //$objDrawing->getShadow()->setVisible(true); // ドロップシャドウをつけるかどうか。

        // PHPExcelオブジェクトに張り込み
        //$objDrawing->setWorksheet($this->excel->getActiveSheet());

        return $objDrawing;
    }
    
    /**
     * 拡張子setter
     */
    protected function setExtension($extension) {
        $this->extension = $extension;
    }
}
