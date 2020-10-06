<?php

require_once (APP.'lib/pdf/tcpdf/examples/lang/eng.php');
require_once (APP.'lib/pdf/tcpdf/examples/lang/jpn.php');
require_once (APP.'lib/pdf/tcpdf/tcpdf.php');
require_once (APP.'lib/pdf/fpdi/fpdi.php');
require_once (APP.'base/cllib/LogWriter.php');

/*
 * PDF帳票出力ベースクラス
 */
abstract class ReportBasePdf {
			
	protected $logger;
	private $pdf;
	private $reportTempName; // 帳票テンプレートファイル名（拡張子は含まない）
	protected $def;          // 帳票オブジェクト位置定義

	/*
	 * コンストラクタ: PDFテンプレートファイル名の設定 
	 */
	function __construct($reportTempName) {
		$this->logger = new LogWriter();
		$this->reportTempName = $reportTempName;
		$this->pdf =& new FPDI();
		$this->setDefaultPdf();
		$this->definePosition();
	}
	/*
	 * pdfのデフォルト設定
	 */
 	private function setDefaultPdf() {
		// ヘッダー・フッター無し
		$this->pdf->setPrintHeader(false);
		$this->pdf->setPrintFooter(false);
		// 自動改ページOFF
		$this->pdf->SetAutoPageBreak(false);
		// 余白0
		$this->pdf->SetMargins(0,0,0);
		$this->pdf->setFooterMargin(0);
		// フォント
		$this->pdf->SetFont('kozgopromedium', '', 10);
	}

	/*
	 * pdf出力
	 */
	public function generate($data, $dest = 'I') {

		// 帳票読み込み
		$pdf =& $this->pdf;
		$page = $pdf->setSourceFile(REPORT_TEMP_DIR.$this->reportTempName.".pdf");
		$import = $pdf->ImportPage($page);
		$pdf->addPage();
		$pdf->useTemplate($import);

		// 編集処理
		$this->edit($data);

		// 出力処理
		ob_end_clean();
		$outDir = ROOT_DIR."output/".date('YmdHis')."/";
		if (!file_exists($outDir)) {
			mkdir($outDir);
			chmod($outDir,0777);
		} else {
		  foreach (glob($outDir.$this->reportTempName."_*.pdf") as $filename) {
			  unlink($filename);
			}
		}
		$pdfFile = $this->reportTempName."_".date('YmdHis').".pdf";
		$this->pdf->Output($pdfFile, $dest);

	}

	/*
	 * Abstract: 帳票オブジェクトの位置定義メソッド
	 * setPositionメソッドを使用して定義すること
	 */
	abstract protected function definePosition();

	/*
	 * Abstract: 帳票編集メソッド
	 */
	abstract protected function edit($data);

	/*
	 * Util: オブジェクト位置設定
	 * $position: array型（0:X座標、1:Y座標、2:セル幅、3:セル最小高さ）
	 * セル幅不要の場合は、XY座標のみ指定可能
	 */
	protected function setPosition($key, $position) {
		$this->def[$key] = $position;
  }

	/*
	 * Util: テキスト設定（折り返しなし）
	 */
	protected function setText($key, $value) {
		$pdf =& $this->pdf;
		$def =& $this->def;
		$pdf->Text($def[$key][0], $def[$key][1], $value);
	}

	/*
	 * Util: テキスト設定（折り返しあり）
	 * デフォルトでは左寄せでセットされる
	 */
	protected function setMultiCellText($key, $value,$lr = "L") {
		$pdf =& $this->pdf;
		$def =& $this->def;
		$pdf->MultiCell($def[$key][2], $def[$key][3], $value, 0, $lr, 0, 0, $def[$key][0], $def[$key][1]);
	}
}
