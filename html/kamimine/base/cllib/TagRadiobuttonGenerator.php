<?php

require_once (APP.'base/cllib/LogWriter.php');

class TagRadiobuttonGenerator {

	private $idname;
    private $controller;
    private $session;
    private $logger;
    private $tag;
	private $data_array;
    private $checked_value;

	function __construct($idname,$controller=null,$session=null,$logger=null,$tag) {
        $this->idname = $idname;
        $this->controller = $controller;
        $this->session = $session;
        $this->logger = $logger;
        $this->tag = $tag;
		//$this->logger = new LogWriter();
		//$this->idname = $idname;
        $this->data_array = array();
	}

	/*
	 * データ設定
     * 引数:value/textのarray配列
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
	 * 選択 
     * $valueが空の場合、任意引数の$defaultがセットされる
	 */
	public function check ($value,$default=null) {
        if($value == "") {
            $this->checked_value = $default;
        } else {
            $this->checked_value = $value;
        }
	}

	/**
	 * HTMLタグ生成
	 */
	public function generate ($active=true) {
		// sectionタグ
		$html = '<section id="'.$this->idname.'" class="tciRadioButton">';
        $html .= $this->generateRadio("HTML",$active);
		// 出力
		$html .= '</section>';
		return $html;
	}

	/**
	 * HTMLタグ生成(Array返却)
	 */
	public function generateArray ($active=true) {
        return $this->generateRadio("ARRAY",$active);
    }

	/**
	 * private: 内部使用HTMLタグ生成
	 */
	private function generateRadio ($generateType,$active=true) {
        // session格納用配列
        $val_arr = array(); 
        $resultarr = array();
		$resulthtml = '';
		foreach ($this->data_array as $arr) {
            $html = "";
            if (strcmp($arr[0],$this->checked_value) == 0) {
			    $html .= '<input type="radio" id="tciRadioButton_'.$this->idname.$arr[0].'" name="'.$this->idname.'" value="'.$arr[0].'" checked>';
                if($active) {
                    $html .= '<label for="tciRadioButton_'.$this->idname.$arr[0].'" class="tciRadio">'.$arr[1].'</label>';
                } else {
                    $html .= '<label for="tciRadioButton_'.$this->idname.$arr[0].'" class="tciRadio tciDisabled">'.$arr[1].'</label>';
                }
                // session格納用配列
                array_push($val_arr,$arr[0]);
            } else {
                if($active) {
			        $html .= '<input type="radio" id="tciRadioButton_'.$this->idname.$arr[0].'" name="'.$this->idname.'" value="'.$arr[0].'">';
                    $html .= '<label for="tciRadioButton_'.$this->idname.$arr[0].'" class="tciRadio">'.$arr[1].'</label>';
                    // session格納用配列
                    array_push($val_arr,$arr[0]);
                } else {
			        $html .= '<input type="radio" id="tciRadioButton_'.$this->idname.$arr[0].'" name="'.$this->idname.'" value="'.$arr[0].'" disabled="" >';
                    $html .= '<label for="tciRadioButton_'.$this->idname.$arr[0].'" class="tciRadio tciDisabled">'.$arr[1].'</label>';
                }
            }
            $resulthtml .= $html;
            array_push($resultarr, $html);
        }
        // session格納
        $this->tag->setSession("tciRadiobuttonValidation",$this->idname,$val_arr);
        //$this->logger->writeDebug($this->session->getControllerSession("tciRadiobuttonValidation"));
        // 出力
        if($generateType == "HTML") {
            return $resulthtml;
        } elseif ($generateType == "ARRAY"){
            return $resultarr;
        }
    }
}
