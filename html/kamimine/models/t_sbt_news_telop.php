<?php

require_once ('../base/base/BaseModel.php');

class t_sbt_news_telop extends BaseModel {

    /**
     * 表示期間中のニューステロップを取得
     */
    public function getCurrentNewsTelop() {
        $sql = "SELECT msg FROM t_sbt_news_telop WHERE CURDATE() between display_start_date AND display_end_date";

        return $this->fetchOne($sql);
    }

}
