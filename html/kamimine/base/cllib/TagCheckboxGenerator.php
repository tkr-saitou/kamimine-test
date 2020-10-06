<?php

require_once (APP.'base/cllib/LogWriter.php');
require_once (APP.'base/cllib/TagUtil.php');

class TagCheckboxGenerator {

    private $idname;
    private $controller;
    private $session;
    private $logger;
    private $tag;
	private $data_array;
    private $single_multi;

	function __construct($idname,$controller=null,$session=null,$logger=null,$tag=null, $single_multi=null) {
		//$this->logger = new LogWriter();
        $this->idname = $idname;
        $this->controller = $controller;
        $this->session = $session;
        $this->logger = $logger;
        $this->tag = $tag;
        $this->data_array = array();
        $this->single_multi = $single_multi;
	}

	/*
	 * データ設定
     * 引数:value/text/checkedのarray配列
	 */
	public function setData ($arr) {
	  array_push($this->data_array, $arr);
	}

    /*
     * Codeマスタ取得結果より一括設定
     * 引数: ['code_value']/['code_name']より構成される配列
     */
    public function setByCodelist ($codelist) {
        foreach ($codelist as $row) {
            array_push($this->data_array, array($row['code_value'],$row['code_name']));
        }
    }

	/*
	 * HTMLタグ生成
	 */
	public function generate ($active=true) {

        // session格納用配列
        $val_arr = array();

		// sectionタグ
		$html = '<section id="'.$this->idname.'" class="tciCheckBox">';

		// データ 
		foreach ($this->data_array as $arr) {
			$html .= '<input type="checkbox" id="tciCheckBox_'.$this->idname.$arr[0].'" value="'.$arr[0].'" ';
            // 単一チェックボックス or 複数選択チェックボックス
            if($this->single_multi == "single") {
                $html .= 'name="'.$this->idname.'" ';
            } else {
                $html .= 'name="'.$this->idname.'[]" ';
            }
            // 活性 or 不活性
//            $this->logger->writeDebug($active);
            if(!$active) {
                $html .= 'disabled="disabled" ';
                if($this->single_multi == "single") {
                    $hidden = TagUtil::hidden($this->idname,$arr[0]);
                } else {
                    $hidden = TagUtil::hidden($this->idname.'[]',$arr[0]);
                }
            }
            // チェック
            if ($arr[2] || $arr[2] == "1") {
                $html .= 'checked >';
            } else {
                $html .= '>';
            }
            if(!$active) {
                $html .= '<label class="tciCheck tciDisabled" for="tciCheckBox_'.$this->idname.$arr[0].'">'.$arr[1].'</label>';
            } else {
                $html .= '<label class="tciCheck" for="tciCheckBox_'.$this->idname.$arr[0].'">'.$arr[1].'</label>';
            }
            /*
            if ($arr[2]) {
                if($this->single_multi == "single") {
			        $html .= '<input type="checkbox" id="tciCheckBox_'.$this->idname.$arr[0].'" name="'.$this->idname.'" value="'.$arr[0].'" checked>';
                } else {
			        $html .= '<input type="checkbox" id="tciCheckBox_'.$this->idname.$arr[0].'" name="'.$this->idname.'[]" value="'.$arr[0].'" checked>';
                }
                $html .= '<label for="tciCheckBox_'.$this->idname.$arr[0].'">'.$arr[1].'</label>';
            } else {
                if($this->single_multi == "single") {
			        $html .= '<input type="checkbox" id="tciCheckBox_'.$this->idname.$arr[0].'" name="'.$this->idname.'" value="'.$arr[0].'">';
                } else {
			        $html .= '<input type="checkbox" id="tciCheckBox_'.$this->idname.$arr[0].'" name="'.$this->idname.'[]" value="'.$arr[0].'">';
                }
                $html .= '<label for="tciCheckBox_'.$this->idname.$arr[0].'">'.$arr[1].'</label>';
            }
            */
            // session格納用配列
            array_push($val_arr,$arr[0]);
            // 不活性時の値渡し 
            if (!$active && ($arr[2] || $arr[2] == "1")) {
                $html .= $hidden;
            }
		}

		// 出力
		$html .= '</section>';

        // session格納
        $this->tag->setSession("tciCheckboxValidation",$this->idname,$val_arr);

		return $html;
	}

}
