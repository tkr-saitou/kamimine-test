<?php

require_once("../base/base/BaseApi.php");
require_once("../models/t_sbt_buscategory.php");

// ### Class Definition -----------------------------------------------------------------
class getBusCategoryList extends BaseApi {

    protected function main() {
        // 分類一覧取得
        $args = array(BUSCOMPANY_ID); 
        $categoryList = $this->db->invoke('t_sbt_buscategory', 'getCategoryList', $args);
        if (count($categoryList) == 0) exit("{\"status\":0}");

        $data = array(
            "status"	    => 0,
            "categoryList"	=> $categoryList
        );

        return $data;
    }
}

// ### Run Process ---------------------------------------------------------------------
$class = new getBusCategoryList();
$class->run();

