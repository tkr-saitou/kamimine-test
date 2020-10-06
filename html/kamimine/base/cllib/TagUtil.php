<?php

require_once (APP.'base/cllib/Util.php');

class TagUtil {

    const PX = 19; // lengthあたりのpx

    function __construct() {
    }

	/** span **/
	public static function span ($text, $id=null, $class=null) {
        $html = '<section class="tcispan '.$class.'">';
		$html .= '<span id="'.$id.'" class="tcispan '.$class.'">'.Util::h($text).'</span>';
        $html .= '</section>';
		return $html;
	}
	
	/** label **/
	public static function label ($text, $required=false, $id=null) {
        $html = '<section class="tcilabel">';
		$html .= '<span id="'.$id.'" class="tcilabel">'.Util::h($text).'</span>';
        if($required){
            $html .= '<span class="tcirequired">*</span>';
        }
        $html .= '</section>';
        return $html;
	}
	
	/** readonly text **/
	public static function readonlyText ($value, $idname, $length) {
		$html = '';
		if(empty($idname) || empty($length)) {
            throw new Exception('TagUtil NULL Exception: idname['.$idname.'] length['.$length.']');
		} else {
			//$width = $length * self::PX; 
            $width = self::getWidth($length);
			$html .= '<input type="text" id="'.$idname.'" name="'.$idname.'" class="tciReadonlyText" value="'.$value.'" title="'.$value.'" ';
            $html .= ' readonly="readonly" tabIndex="-1" style="width:'.$width.'px;">'; 
        }
        return $html;
    }

	/** input text **/
	public static function inputText ($value, $idname, $length, $active=true, $placeholder=null) {
		$html = '';
		if(empty($idname) || empty($length)) {
            throw new Exception('TagUtil NULL Exception: idname['.$idname.'] length['.$length.']');
		} else {
            $width = self::getWidth($length);
            if($active) {
                if(empty($placeholder)) {
			        $html .= '<input type="text" id="'.$idname.'" name="'.$idname.'" class="tciInputText" value="'.$value.'"';
                    $html .= ' maxlength="'.$length.'" style="width:'.$width.'px;">'; 
                } else {
			        $html .= '<input type="text" id="'.$idname.'" name="'.$idname.'" class="tciInputText" value="'.$value.'"';
                    $html .= ' maxlength="'.$length.'" style="width:'.$width.'px;" placeholder="'.$placeholder.'" />';
                }
            } else {
                $html = TagUtil::readonlyText($value, $idname, $length);
            }
		}
        return $html;
	}

    /** input text (数値) **/
    public static function inputNumericText ($value, $idname, $length,$active=true) {
		if(empty($idname) || empty($length)) {
            throw new Exception('TagUtil NULL Exception: idname['.$idname.'] length['.$length.']');
        } else {
            $width = self::getWidth($length);
            if($active) {
			    return '<input type="text" id="'.$idname.'" name="'.$idname.'" class="tciInputText tciNumericText"'
                      .' value="'.$value.'" maxlength="'.$length.'" style="width:'.$width.'px;" />';
            } else {
                $html = TagUtil::readonlyText($value, $idname, $length);
                return self::addClass($html, "tciNumericText");
            }
        }
    }

    /** input text (数字) **/
    public static function inputNumberText ($value, $idname, $length,$active=true) {
		if(empty($idname) || empty($length)) {
            throw new Exception('TagUtil NULL Exception: idname['.$idname.'] length['.$length.']');
        } else {
            $width = self::getWidth($length);
            if($active) {
			    return '<input type="text" id="'.$idname.'" name="'.$idname.'" class="tciInputText tciNumberText"'
                      .' value="'.$value.'" maxlength="'.$length.'" style="width:'.$width.'px;" />';
            } else {
                return TagUtil::readonlyText($value, $idname, $length);
            }
        }
    }

    /** input sp char text (数字入力 - 特殊文字許可) **/
    public static function inputSpCharNumberText ($value, $idname, $length,$active=true) {
		if(empty($idname) || empty($length)) {
            throw new Exception('TagUtil NULL Exception: idname['.$idname.'] length['.$length.']');
        } else {
            $width = self::getWidth($length);
            if($active) {
			    return '<input type="text" id="'.$idname.'" name="'.$idname.'" class="tciInputText tciSpCharNumberText"'
                      .' value="'.$value.'" maxlength="'.$length.'" style="width:'.$width.'px;" />';
            } else {
                return TagUtil::readonlyText($value, $idname, $length);
            }
        }
    }

    /** input text (英数字) **/
    public static function inputAlphaNumberText ($value, $idname, $length,$active=true,$placeholder=null) {
		if(empty($idname) || empty($length)) {
            throw new Exception('TagUtil NULL Exception: idname['.$idname.'] length['.$length.']');
        } else {
            $width = self::getWidth($length);
            if($active) {
                if(empty($placeholder)) {
			        return '<input type="text" id="'.$idname.'" name="'.$idname.'" class="tciInputText tciAlphaNumberText"'
                          .'value="'.$value.'" maxlength="'.$length.'" style="width:'.$width.'px;" />';
                } else {
			        return '<input type="text" id="'.$idname.'" name="'.$idname.'" class="tciInputText tciAlphaNumberText"'
                          .'value="'.$value.'" maxlength="'.$length.'" style="width:'.$width.'px;" placeholder="'.$placeholder.'"/>';
                }
            } else {
                return TagUtil::readonlyText($value, $idname, $length);
            }
        }
    }

    /** input text (通貨) **/
    public static function inputCurrencyText ($value, $idname, $length, $active=true) {
		if(empty($idname) || empty($length)) {
            throw new Exception('TagUtil NULL Exception: idname['.$idname.'] length['.$length.']');
        } else {
            if($value === 0) $value = "";
            if($active) {
                $width = self::getWidth($length);
			    return '<input type="text" id="'.$idname.'" name="'.$idname.'" class="tciInputText tciCurrencyText" value="'.$value.'" maxlength="'.$length.'" style="width:'.$width.'px;" />';
            } else {
                if($value != "") $value = number_format((double)$value); 
                $html = TagUtil::readonlyText($value, $idname, $length);
                return TagUtil::addClass($html,"tciCurrencyText");
            }
        }
    }

	/** textarea **/
	public static function textarea ($value, $idname, $maxlength, $rows, $active=true, $placeholder=null) {
		$html = '';
		if(empty($idname) || empty($maxlength)) {
            throw new Exception('TagUtil NULL Exception: idname['.$idname.'] length['.$maxlength.']');
		} else {
            if(empty($rows)) $rows = 2 ;
            // 基準値×maxlength÷行数でwidth計算
            $width = self::getWidth($maxlength) / $rows;
            if($active) {
                if(empty($placeholder)) {
			        $html .= '<textarea id="'.$idname.'" name="'.$idname.'" class="tciInputText"';
                    $html .= ' maxlength="'.$maxlength.'" rows="'.$rows.'" style="width:'.$width.'px;">';
                    $html .= $value.'</textarea>';
                } else {
			        $html .= '<textarea id="'.$idname.'" name="'.$idname.'" class="tciInputText"';
                    $html .= ' maxlength="'.$maxlength.'" rows="'.$rows.'" style="'.$width.'px;" placeholder="'.$placeholder.'">';
                    $html .= $value.'</textarea>';
                }
            } else {
			    $html .= '<textarea id="'.$idname.'" name="'.$idname.'" class="tciReadonlyText" title="'.$value.'" ';
                $html .= ' maxlength="'.$maxlength.'" rows="'.$rows.'" style="width:'.$width.'px;" readonly >';
                $html .= $value.'</textarea>';
            }
		}
        return $html;
	}

    /** date picker **/
    public static function datepicker ($value, $idname, $active=true) {
		$html = '';
		$dayOfWeek = '';
		if(empty($idname)) {
            throw new Exception('TagUtil NULL Exception: idname['.$idname.']');
        } else {
            if(!empty($value) && !strptime($value, '%Y-%m-%d %H:%i:%s' ) ){
                // MySQLのDateTime型で渡された場合はYYYY/MM/DDのStringに変換
                $value = substr(str_replace("-","/",$value),0,10); 
            }
            if(!empty($value)) $dayOfWeek = '('.Util::getDayOfWeek($value).')';
            if($active) {
                $html = '<div class="tciDatePickerWrap">';
                $html .= '<label for="'.$idname.'">'.$dayOfWeek.'</label>';
                $html .= '<input type="text" id="'.$idname.'" name="'.$idname.'" value="'.$value.'" class="tciInputText" maxlength="10" style="width:180px;">';
                $html .= '</div>';
            } else {
                $html = TagUtil::readonlyText($value.' '.$dayOfWeek, $idname.'_display',10);
                $html .= TagUtil::hidden($idname,$value);
            }
            return $html;
        }
    }

    /** 年月 **/
    public static function inputYearMonth($value, $idname, $active=true) {
		$yyyymm = '';
        if(strlen($value) == 10) $yyyymm = Util::formatDateYYMMDD($value);
        if(strlen($value) == 6)  $yyyymm = mb_substr($value,0,4).'/'.mb_substr($value,4,2);
        $html = self::inputAlphaNumberText($yyyymm, $idname, 7, $active,'YYYY/MM');
        return self::addClass($html, "tciInputYearMonth");
    }

    /** 時刻 **/
    public static function inputTime($value, $idname, $active=true) {
        $time = mb_substr($value,0,5);
        if($time == "00:00") $time = "";
        $html = self::inputText($time, $idname, 5, $active);
        return self::addClass($html, "tciInputTime");
    }

	/** image **/
	public static function image ($src, $id, $alt) {
        return '<input type="image" src="'.$src.'" id="'.$id.'" alt="'.$alt.'" onclick="return false;">';
    }
	/** a link **/
	public static function a ($text, $controller, $action) {
        return '<a href="'.$controller.'/'.$action.'" tabIndex="0">'.Util::h($text).'</a>';
    }

	/** button **/
	public static function button ($text, $id, $color="blue") {
            $html = '<div style="position:relative;">';
            $html .= '<p id="'.$id.'" class="tcibtn '.$color.'" tabIndex="0">'.Util::h($text).'</p>';
            $html .= '</div>';
        return $html;
    }

	/** hidden **/
	public static function hidden ($name, $value) {
		return '<input type="hidden" name="'.$name.'" value="'.Util::h($value).'"/>';
	}

    /** add class **/
    public static function addClass($tag,$class) {
        if(Util::contains($tag,'class="')) {
            $arr = explode('class="',$tag);
            $html = $arr[0].'class="'.$class.' '.$arr[1];
        } else {
            $html = substr($tag,0,-1).' class="'.$class.'" >';
        }
        return $html;
    }

    /** 検索ダイアログボタン追加 **/
    public static function openDialogBtn($id,$active=true) {
		$html = '';
        if($active) {
            $html = self::image("base/images/opendialog.png",$id,"検索ダイアログ");
            $html = self::addClass($html,"tciOpenDialogBtn");
        }
        return $html;
    }

    /**
     * 郵便番号入力
     */
    public static function inputZipCd($value, $idname, $active=true) {
        if(!empty($value)) $zipCd = mb_substr($value,0,3)."-".mb_substr($value,3,4);
        $html = self::inputText($zipCd, $idname, 8, $active);
        return self::addClass($html, "tciInputZipCd");
    }

    /**
     * 電話番号入力
     */
    public static function inputTelNo($value, $idname, $active=true) {
        $html = self::inputText($value, $idname, 13, $active);
        return self::addClass($html, "tciInputTelNo");
    }

    /**
     * メールアドレス入力
     */
    public static function inputMailAddress($value, $idname, $active=true) {
        $html = self::inputText($value, $idname, 256, $active);
        return self::addClass($html, "tciInputMailAddress");
    }

    /**
     * ファイルアップローダー
     * @param maxsize: アップロード上限サイズ（Byte指定）
     */
    public static function fileUploader($idname, $maxsize, $active=true) {
        $html = '';
        $class = 'class="'.$cls.'"';
        $agent = getenv('HTTP_USER_AGENT');
        if (!preg_match("/MSIE/", $agent)) {
            //$html .= '<input type="text" id="fake_file" '.$class.' value="ファイルを選択してください">';
            //$html .= '<input type="image" class="searchBtn" id="refBtn" alt="参照" src="./images/common/search.jpg" align="middle">';
            $html .= '<input type="file" class="tciUploader" id="'.$idname.'" name="'.$idname.'" maxsize="'.$maxsize.'">';
        } else {
            $html .= '<input type="file" '.$class.' id="'.$idname.'" name="'.$idname.'" maxsize="'.$maxsize.'">';
        }
        //$html .= '<input type="hidden" name="upload" id="upload" value="0">';
        //$html .= '<input type="hidden" name="tcimaxfilesize" id="upload" value="0">';
        return $html;
    }

    /* Width算出 */
    public static function getWidth($length) {
        $width = $length * self::PX - 2;
        if($width < 53) $width = 54;
        return $width;
    }
}
