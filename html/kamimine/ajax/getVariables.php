<?php

require_once("../base/base/BaseApi.php");

// ### Class Definition -----------------------------------------------------------------
class getVariables extends BaseApi {

    protected function main() {

        // デジタルサイネージ有無
        $signage_enable = array_key_exists('signage_enable', $_POST) && $_POST['signage_enable'] == 1;

        // *************** JS用定数 *************** //
        // JSで使用する定数は基本的にここから渡す
        // **************************************** //
        $variables = array();
        // デモモード切替
        $variables["demo"] = DEMO;
        // 現在地識別コード
        $variables["current_pos"] = CURRENT_POS;
        // 緯度経度初期値
        $variables["defaultLat"] = DEFAULTLAT;
        $variables["defaultLng"] = DEFAULTLNG;
        // ズームレベル
        $variables["zoom"] = $signage_enable ? SIGNAGE_ZOOM : ZOOM;
        // バス分類コード
        $variables["buscategory_cd"] = BUSCATEGORY_CD;
        // 検索状況確認グラフ表示ON/OFF
        $variables["search_graph"] = SEARCH_GRAPH;

        $data = array(
            "status"    => 0,
            "variables"	=> $variables
        );

        return $data;
    }
}

// ### Run Process ---------------------------------------------------------------------
$class = new getVariables();
$class->run();

