<?php

require_once (APP.'base/cllib/TagUtil.php');

class BaseHtmlGenerator {

    private $logger;
    private $screen;
    private $message;
    private $controller;

    function __construct($logger,$screen,$message,$controller) {
        $this->logger = $logger;
        $this->screen = $screen;
        $this->message = $message;
        $this->controller = $controller;
    }

	/*
	 * 変更通知カラー表示
	public function get_checkonchange() {
		return TagUtil::hidden("tciCheckOnChange","true");
    }
	 */

    /**
     *  get tcihead
     */
    public function get_tcihead($url,$title) {
        $html = '<meta charset="UTF-8">';
        $html .= '<meta http-equiv="X-UA-Compatible" content="IE=edge">';
	    $html .= '<meta name="viewport" content="width=device-width,user-scalable=no,initial-scale=1, maximum-scale=1" />';
        $html .= '<base href="'.$url.'/"/>';
		$html .= ' <!-- CSS -->';
		$html .= '<link rel="stylesheet" type="text/css" href="./lib/css/reset.css" />';
		$html .= '<link rel="stylesheet" type="text/css" href="./lib/js/jquery-ui-1.11.4.custom/jquery-ui.min.css" />';
		$html .= '<link rel="stylesheet" type="text/css" href="./lib/js/jquery-ui-1.11.4.custom/jquery-ui.structure.min.css" />';
		$html .= '<link rel="stylesheet" type="text/css" href="./lib/js/jquery-ui-1.11.4.custom/jquery-ui.theme.min.css" />';
	    $html .= '<link rel="stylesheet" type="text/css" href="./lib/js/select2-3.5.0/select2.css" />';
	    $html .= '<link rel="stylesheet" type="text/css" href="./lib/css/jquery.dataTables.min.css" />';
		$html .= '<link rel="stylesheet" type="text/css" href="./lib/css/bootstrap.min.css" rel="stylesheet">';
	    $html .= '<!--[if lt IE 9]>';
	    $html .= '<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>';
	    $html .= '<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>';
	    $html .= '<![endif]-->';
	    $html .= '<link rel="stylesheet" type="text/css" href="./base/css/tcibase.css" />';
	    //$html .= '<link rel="stylesheet" type="text/css" href="./css/tcicustom.css" />';  
	    $html .= '<title>'.$title.' | '.APP_NAME.'</title>';
        return $html;
    }

    /**
     *  get global_header
     */
    public function getTciGlobalHeader($screentitle,$userId,$userName,$headerBtn) {

        $html = '<section id="tci_global_header">';

        // Header情報
        $html .= '<h1>'.$screentitle.'</h1>';
        $html .= '<ul class="tciHeaderBtnArea">';
		$html .= $headerBtn;
        $html .= '</ul>';
        $html .= '<ul>';
        $html .= '<li class="tciUserInfo"><p>'.$userName.'</p><p>'.$userId.'</p></li>';
        //$html .= '<li class="tciLogout"><a href="'.HOME_URL.'auth/logout/" tabIndex="-1">Log-Out<img id="logoutBtn" src="./base/images/logout.png" alt="ログアウト"></a></li>';
        $html .= '<li class="tciLogout"><a id="tciLogout">Log-Out<img id="logoutBtn" src="./base/images/logout.png" alt="ログアウト"></a></li>';
        $html .= '</ul>';

        // hidden情報
        // hidden:変更有無チェックフラグ
        if ($this->screen->getCheckOnChange()) {
            //$global_header .= $this->basehtml->get_checkonchange();
            $html .= TagUtil::hidden("tciCheckOnChange","true");
        }
        // hidden:ControllerID
        $html .= '<input type="hidden" name="tciCtrl" value="'.$this->controller.'" />';
        // hidden: 地図関連情報
        $html .= TagUtil::hidden('cen_lat',''); // 中心緯度
        $html .= TagUtil::hidden('cen_lng',''); // 中心経度
        $html .= TagUtil::hidden('ne_lat','');  // 北東緯度
        $html .= TagUtil::hidden('ne_lng','');  // 北東経度
        $html .= TagUtil::hidden('sw_lat','');  // 南西緯度
        $html .= TagUtil::hidden('sw_lng','');  // 南西経度
        // シーケンス番号（Backボタン制御に使用）
        //$html .= TagUtil::hidden('tciReqSeqNo',$this->session->get("tciReqSeqNo"));
        // Footerメッセージ
        $html .= '<section id="tciBeforeRegMsg">'.$this->message->getRegMsgOnFooter().'</section>';
        // Assign
        $html .= '</section>';
        $html .= '<div id="tciHeaderMsg">'.$this->message->getHeaderMsg().'</div>';
        //$global_header .= $this->basehtml->get_screensize();
		$html .= TagUtil::hidden("innerWidth","");
		$html .= TagUtil::hidden("innerHeight","");

        return $html;
    }

	/*
	 * Global Footer: 画面解像度
	public function get_screensize() {
		$html .= TagUtil::hidden("innerWidth","");
		$html .= TagUtil::hidden("innerHeight","");
		return $html;
	}
	 */

	/*
	 * Global Footer: メッセージ
	 */
     /*
	public function get_message_footer($message) {
		// モーダルメッセージ 
		$html .= '<section id="tciModalMsg">';
        if ($message->hasModalMsg()) {
		    $html .= TagUtil::hidden("tciHasModalMessage","true");
		    $html .= TagUtil::hidden("tciModalMessage",implode(',',$message->getModalMsgs()));
        }
		$html .= '</section>';
        // エラー項目
		//$html .= TagUtil::hidden("tciErrItem",$message->getErrItem());
		$html .= TagUtil::hidden("tciErrItem",implode(',',$message->getErrItems()));
		return $html;
	}
    */

	/*
	 * Global Footer: 画面遷移
	 */
	public function get_transition_footer($controller,$action,$openWindow) {
		$html .= TagUtil::hidden("tciTransitionCtrl",$controller);
		$html .= TagUtil::hidden("tciTransitionAction",$action);
        if($openWindow) {
		    $html .= TagUtil::hidden("tciTransitionOpenWindow",$openWindow);
        }
		return $html;
	}

    /*
     * JS読み込み
     */
    public function get_tcijs() {
        $html = '<script type="text/javascript" src="./lib/js/jquery-2.1.3.min.js"></script>';
        $html .= '<script type="text/javascript" src="./lib/js/jquery-ui-1.11.4.custom/jquery-ui.min.js"></script>';
        $html .= '<script type="text/javascript" src="./lib/js/jquery.ui.datepicker-ja.js"></script>';
        $html .= '<script type="text/javascript" src="./lib/js/jquery.activity-indicator-1.0.0.min.js"></script>';
        $html .= '<script type="text/javascript" src="./lib/js/select2-3.5.0/select2.js"></script>';
        $html .= '<script type="text/javascript" src="./lib/js/jquery.dataTables.min.js"></script>';
        $html .= '<script type="text/javascript" src="./lib/js/bootstrap.min.js"></script>';
        $html .= '<script type="text/javascript" src="./base/js/tcibase.js"></script>';
        return $html;
    }

    /*
     * JS読み込み(MAP)
     */
    public function get_tcimapjs() {
        $html = '<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?sensor=false"></script>';
        $html .= '<script type="text/javascript" src="./lib/js/infobox.js"></script>';  // infoWindowカスタマイズ用JS
        $html .= '<script type="text/javascript" src="./base/js/tcimap.js"></script>';
        return $html;
    }
}
