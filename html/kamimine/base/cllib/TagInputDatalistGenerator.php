<?php

require_once (APP.'base/cllib/LogWriter.php');
require_once (APP.'base/cllib/TagUtil.php');

class TagInputDatalistGenerator {

	private $idname;
	private $length;
	private $logger;
	private $tag;
	private $val;
	private $option_array;

	function __construct($idname,$length,$logger,$tag) {
        if(empty($idname) || empty($length)) {
                throw new Exception('TagUtil NULL Exception: idname['.$idname.'] length['.$length.']');
        } else {
		    $this->idname = $idname;
		    $this->length = $length;
		    $this->logger = $logger;
		    $this->tag = $tag;
            $this->option_array = array();
        }
	}

	/**
	 * 入力候補設定
	 */
	public function setOption ($str) {
	  array_push($this->option_array, $str);
	}

	/**
	 * 入力候補一括設定
	 */
	public function setOptions ($arr) {
	  $this->option_array = $arr;
	}

	/**
	 * 値value設定 
	 */
	public function setValue ($val) {
	  $this->val = $val;
	}

	/**
	 * HTMLタグ生成
	 */
	public function generate ($active=true) {
        //$size = $this->length * 2;
        $width= TagUtil::getWidth($this->length);
        if($active) {
            $html = '<input type="text" id="'.$this->idname.'" name="'.$this->idname.'" class="tciInputText"';
            //$html .= ' list="datalist_'.$this->idname.'" maxlength="'.$this->length.'" size="'.$size.'" value="'.$this->val.'">';
            $html .= ' list="datalist_'.$this->idname.'" maxlength="'.$this->length.'" style="width:'.$width.'px;" value="'.$this->val.'">';
            $html .= '<datalist id="datalist_'.$this->idname.'">';
		    foreach ($this->option_array as $arr) {
                $html .= '<option value="'.$arr.'"></option>';
		    }
            $html .= '</datalist>';
        } else {
            $html = TagUtil::readonlyText($this->val,$this->idname,$this->length);
        }
        // 最大桁数設定
        $this->tag->setMaxlength($this->idname,$this->length);
		return $html;
	}

    /**
     * <option>タグのリストのみ取得
     * オブジェクト生成後にAjaxでリストのみ書き換える場合を想定
     */
	public function getOptionList () {
		foreach ($this->option_array as $arr) {
            $html .= '<option value="'.$arr.'"></option>';
		}
		return $html;
    }

}
