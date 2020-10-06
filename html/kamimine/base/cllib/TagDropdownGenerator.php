<?php

require_once (APP.'base/cllib/LogWriter.php');
require_once (APP.'base/cllib/TagUtil.php');

class TagDropdownGenerator {

	private $idname;
    private $controller;
    private $session;
	private $logger;
	private $tag;
	private $data_array;
	private $class_array;
    private $select_value;
    private $default_wording;

	function __construct($idname,$controller=null,$session=null,$logger=null,$tag=null) {
		$this->idname = $idname;
        $this->controller = $controller;
        $this->session = $session;
        $this->logger = $logger;
        $this->tag = $tag;
        $this->data_array = array();
        $this->class_array = array();
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
	 */
	public function select ($value) {
        $this->select_value = $value;
	}

	/*
	 * addClass 
	 */
	public function addClass ($class) {
        array_push($this->class_array, $class);
	}

    /**
     * デフォルト文言の修正
     * 「選択してください...」から変更したい場合のみ使用
     */
    public function setDefaultWording($str) {
        $this->default_wording = $str;
    }

	/*
	 * HTMLタグ生成
	 */
	public function generate ($active=true) {
		$html = '';
        // session格納用配列
        $val_arr = array(""); // 未選択の""を入れて初期化

        if($active) {
		    // selectタグ
		    $html .= '<select id="'.$this->idname.'" name="'.$this->idname.'" class="tcidropdown';
            foreach($this->class_array as $i => $class) {
                $html .= ' '.$class;
            }
            $html .= '">';
            if(is_null($this->default_wording)) {
		        $html .= '<option value="">選択してください...</option>';
            } else {
		        $html .= '<option value="">'.$this->default_wording.'</option>';
            }
		    // データ 
		    foreach ($this->data_array as $arr) {
                if (strcmp($arr[0],$this->select_value) == 0) {
			        $html .= '<option value="'.$arr[0].'" selected>'.$arr[1].'</option>';
                } else {
			        $html .= '<option value="'.$arr[0].'">'.$arr[1].'</option>';
                }
                // session格納用配列
                array_push($val_arr,$arr[0]);
		    }
		    // 出力
		    $html .= '</select>';
            $html .= '<img id="tcidropdownImg_'.$this->idname.'" src="./base/images/select.png" for="'.$this->idname.'" alt="ドロップダウン" title="" class="tcidropdownImg';
            foreach($this->class_array as $i => $class) {
                $html .= ' '.$class;
            }
            $html .= '">';
        } else {
		    foreach ($this->data_array as $arr) {
                if (strcmp($arr[0],$this->select_value) == 0) {
                    if(empty($this->class_array)) {
                        $html .= TagUtil::readonlyText($arr[1],$this->idname."_display",12);
                    } else {
                        $html_tmp = TagUtil::readonlyText($arr[1],$this->idname."_display",12);
                        foreach($this->class_array as $i => $class) {
                            $html_tmp .= TagUtil::addClass($html_tmp,$class);
                        }
                        $html .= $html_tmp;
                    }
                    $html .= TagUtil::hidden($this->idname, $arr[0]);
                    // session格納用配列
                    $val_arr = array($arr[0]);
                }
            }
            if(empty($html)){
                if(empty($this->class_array)) {
                    $html = TagUtil::readonlyText("(未選択)",$this->idname."_display",12);
                } else {
                    $html_tmp = TagUtil::readonlyText("(未選択)",$this->idname."_display",12);
                    foreach($this->class_array as $i => $class) {
                        $html_tmp .= TagUtil::addClass($html_tmp,$class);
                    }
                    $html .= $html_tmp;
                }
            }
        }
        // session格納
        $this->tag->setSession("tciDropdownValidation",$this->idname,$val_arr);
		return $html;
	}

}
