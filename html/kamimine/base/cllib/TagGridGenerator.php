<?php

require_once (APP.'base/cllib/LogWriter.php');

class TagGridGenerator {

	private $logger;
	private $tableId;
	private $header_array;
	private $header_class_array; 
    private $rowType;
	private $data_array;
    private $session;
    private $controller;

	function __construct($tableId,$controller=null,$session=null, $logger=null) {
        // 引数セット
        if(is_null($logger)) {
		    $this->logger = new LogWriter();
        } else {
		    $this->logger = $logger;
        }
		$this->tableId = $tableId;
		$this->controller = $controller;
		$this->session = $session;
        // 変数初期化
		$this->header_class_array = array();
		$this->data_array = array();
	}

	/*
	 * ヘッダthタグ情報設定
	 */
	public function setHeader ($arr) {
		$this->header_array = $arr;
	}

	/*
	 * thタグにclassを追加する
	 */
	public function addHeaderClass ($text, $class) {
		$array_key = array_keys($this->header_array,$text)[0];
		if(isset($array_key)) {
			if(isset($this->header_class_array[$array_key])) {
				$this->header_class_array[$array_key] = $this->header_class_array[$array_key]." ".$class;
			} else {
				$this->header_class_array[$array_key] = $class;
			}
		}
	}

	/*
	 * データ設定
	 */
	public function setData ($arr,$argname=null,$argval=null,$controller=null,$action=null) {
	  array_push($this->data_array, array($arr,$argname,$argval,$controller,$action));
	}

	/*
	 * データ設定(function付)
     * 行クリック時に処理を行う場合に使用する。ex.クリックによる画面遷移
     * @param $arr: データ行(<td>タグ)
     * @param $func: クリック時に呼び出されるJavaScriptのFunction
     * @param $key: 行の主キーとなる項目。HTMLにはRownumのみが記載され、
     *              key情報はセッションに格納される。$this->screen->getKeyByRownumメソッドで取得可能。
	 */
	public function setFunctionRow ($arr,$func,$key) {
        $this->rowType = "Function";
	    array_push($this->data_array, array($arr,$func,$key));
	}

	/*
	 * HTMLタグ生成
	 */
	public function generate () {

		// Tableタグ
		$html = '<table id="'.$this->tableId.'" class="tcitable">';

		// タイトル行
		$html .= '<thead><tr>';
		$cnt = 0;
		foreach ($this->header_array as $th) {
            $i = $cnt +1;
			if (isset($this->header_class_array[$cnt])){
				$html .= '<th class="col_'.$i.' '.$this->header_class_array[$cnt].'">'.$th.'</th>';
			} else {
				$html .= '<th class="col_'.$i.'">'.$th.'</th>';
			}
			$cnt++;
		}
		$html .= '</tr></thead>';

		// データ 
        $rows = array();
		foreach ($this->data_array as $rownum => $tr) {
            // tr生成（行情報埋め込み）
            if($this->rowType == "Function") {
			    $html .= '<tr class="tciFunctionRow tciclickable" event="'.$tr[1].'" rownum="'.$rownum.'" >';
                $rows = array_merge($rows,array($rownum => $tr[2]));
            } elseif(!empty($tr[1]) && !empty($tr[2])) {
                if(!empty($tr[3]) && !empty($tr[4])) {
			        $html .= '<tr class="tciclickable" argname="'.$tr[1].'" argval="'.$tr[2].'" ctrl="'.$tr[3].'" action="'.$tr[4].'" rownum="'.$rownum.'">';
                } else {
			        $html .= '<tr argname="'.$tr[1].'" argval="'.$tr[2].'" rownum="'.$rownum.'">';
                }
            } else {
			    $html .= '<tr rownum="'.$rownum.'">';
            }
            // td生成
			foreach ($tr[0] as $td) {
                if(gettype($td) == "array") {
                    // Array(文字、長さ）で渡された場合、長さをオーバーしている場合は文字をカット
                    if(Util::getStrWidth($td[0]) > $td[1]) {
                        $tdTmp = mb_substr($td[0],0,$td[1],"utf-8")."...";
				        $html .= '<td title="'.$td[0].'">'.$tdTmp.'</td>';
                    } else {
				        $html .= '<td title="'.$td[0].'">'.$td[0].'</td>';
                    }
                } else {
                    // Arrayではない（通常の場合）は、そのままセット
				    $html .= '<td>'.$td.'</td>';
                }
			}
			$html .= '</tr>';
		}

        // 行情報をSessionに格納　※「行番号－KEY」の配列が格納される
        if(!empty($this->session)) {
            $this->session->set('tciRowKeys_'.$this->tableId, $rows);
            //$this->session->setControllerSession('tciRowKeys_'.$this->tableId, $rows);
        }

		// 出力
		$html .= '</table>';
		return $html;
	}

}
