<?php

/*----------------------------------------------------------------------------------*/
/* リリース時に変更が必要な設定
/*----------------------------------------------------------------------------------*/
define('APP', '/var/www/html/kamimine/');
define('ROOT_DIR', '/var/www/html/kamimine/');
define('SHARE_DIR', '/var/www/share/kamimine/');
define('HOME_URL', 'http://subtour-z.com/kamimine/');
// リンク
define('USAGE_URL', 'https://www.town.kamimine.lg.jp/');
define('ROUTEMAP_URL', 'http://www.town.kamimine.lg.jp/view.php?pageId=2497');
define('TIMETABLE_URL', 'http://www.town.kamimine.lg.jp/view.php?pageId=2497');
// 緯度経度初期値
define('DEFAULTLAT', 33.327);
define('DEFAULTLNG', 130.422);
// ズームレベル
define('ZOOM', 13);
define('SIGNAGE_ZOOM', 14.8);
// バス会社ID
define("BUSCOMPANY_ID", "KMM");
// タイトル
define('TITLE', '上峰町');

// ご意見入力欄ON/OFF
define('OPINION', true);
// 検索状況確認欄ON/OFF
define('SEARCH_GRAPH', true);

/*----------------------------------------------------------------------------------*/
/* 基本設定　※各アプリで変更不可
/*----------------------------------------------------------------------------------*/
/*** アプリケーション情報 ***/
define('APP_NAME', 'SUBTOURZサイト');
define('APP_ID', 'kamimine');

/*** ディレクトリ設定 ***/
// 帳票テンプレートDIR
define("REPORT_TEMP_DIR", ROOT_DIR."template/report/");
// ログ出力DIR
define('LOG_DIR', '/tmp/log/');
$LOG_DIR = LOG_DIR;
// ジャーナル出力DIR
define('JOURNAL_DIR', '/tmp/journal/');
// 帳票出力DIR
define("REPORT_OUTPUT_DIR", "/tmp/output/");

/*** Zend 読み込み ***/
define("USE_ZEND", true);
if(USE_ZEND) {
    // include_path to ZendFramework's Library
    set_include_path('/usr/share/ZendFramework-1.12.3/library'.PATH_SEPARATOR.get_include_path());
}

/*** 多言語対応 ***/
define("MULTI_LANG", true);
define("RESOURCE_DIR", ROOT_DIR."resource/");
// 多言語対応対象言語 ※先頭をデフォルト言語コードとして、配列にない言語コードはデフォルトに変換
$LANG_LIST = array('en','ja');

/*** ログ/ジャーナル出力 ***/
// DEBUGモード設定 ※falseに設定すると、/tmp/log配下のデバッグログ(D_始まりのログ)の出力OFF
define('DEBUG', true);
// Accessジャーナル出力
define('ACCESS_JOURNAL', true);
// SQLジャーナル出力
define('SQL_JOURNAL', false);
define('QUERYSQL_JOURNAL', true);
// Exceptionログ出力対象外文字列の定義　※多用しないこと
$ignore_exception = array(
     'tcioutputreport.phtml\' not found in path '
     );

/*** システム制御 ***/
// Sessionタイムアウト時間(秒)
define('SESSION_TIMEOUT', 54000);
// オンラインサービス(false→メッセージ表示)
define('ONLINE_SERVICE', true);
define('ONLINE_SERVICE_MESSAGE',
		'メンテナンスのため、サービスを停止しています。<br>
		ご迷惑をおかけしますが、ご了承ください。');

// 共通HEAD
$common_head = '
	<meta charset="utf-8">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

	<title>'.TITLE.' | バス運行状況</title>

	<meta name="description" content="バスの運行状況がリアルタイムにわかります。" />
	<meta name="keywords" content="" />
	<meta name="msvalidate.01" content="680A7331D78BFA809FAD77E6028B67CF" />
	<meta name="viewport" content="width=device-width">

    <!-- アイコン設定 -->
    <link rel="shortcut icon" href="favicon.ico">
    <link rel="shortcut icon" href="favicon.png">
    <link rel="apple-touch-icon-precomposed" href="favicon.png">

	<!--[if lt IE 9]>
		<script src="./lib/js/html5shiv.js"></script>
		<script src="./lib/js/respond.min.js"></script>
		<link rel="stylesheet" type="text/css" href="./lib/css/style_IE8.css">
	<![endif]-->
    <link href="./base/css/tciutil.css" rel="stylesheet" type="text/css"/>
    <!--<link href="./base/css/tcibase.css" rel="stylesheet" type="text/css"/>-->
';

// 共通JS
//$googlemap_api_key = 'AIzaSyAOC6Y35_GMLE8eltJo-GFpSfoKx_jayfE';   //本番用
$googlemap_api_key = 'AIzaSyAOC6Y35_GMLE8eltJo-GFpSfoKx_jayfE';   //本番用
$common_js = '
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js"></script>
	<script type="text/javascript" src="./lib/js/jquery.activity-indicator-1.0.0.min.js"></script>
	<script type="text/javascript" src="./js/GoogleAnalytics.js"></script>
	<script type="text/javascript" src="//maps.googleapis.com/maps/api/js?key=' . $googlemap_api_key . '"></script>
    <script type="text/javascript" src="./base/js/tciutil.js"></script>
    <script type="text/javascript" src="./base/js/tcistbase.js"></script>
';

/*----------------------------------------------------------------------------------*/
/* アプリ設定
/*----------------------------------------------------------------------------------*/
/* デモモード */
define('DEMO', false);

/** アプリ内定数 **/
/* 現在地（路線選択ドロップダウン固定値） */
define('CURRENT_POS', '9999');

/* 現在地付近のバス停検索範囲 */
define('SEARCH_RANGE', 0.1); // 単位:km

/* Android端末からの送信判定用定数 */
define("MARK", "subtour");

/* バス分類CD */
define("BUSCATEGORY_CD", "001");

// バステロップ初期表示
define('DEFAULTTELOP', 'バスの運行状況がリアルタイムにわかります。');

// 最後にバス停を通過してから次のバス停に到着するまでの限度時間
define('BORDER_ELAPSE_TIME', 10);

// ランキング表示の上限値
define('RANK_MAX', 20);

// ご意見入力欄HTML
$opinion_html = '
        <div class="footer-right">
            <p>本サイトについてご意見をお願いします。<br />※ご返答を要するお問合せの場合には、メールアドレスを記載の上ご連絡ください。</p>

            <textarea rows="2" class="opinion" id="opinion" maxlength="1000"></textarea>
            <input type="button" id="opinionBtn" value="送信">
        </div>
';

// 検索状況確認用グラフから除外するIPアドレス
$EXCEPT_IP_ADDRESS = array('153.232.254.54');
