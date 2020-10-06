<?php

require_once ('../base/base/BaseModel.php');

class t_sbt_opinion extends BaseModel {

    /**
     * ご意見情報の登録
     */
    public function insert($datetime, $access_point, $device, $browser, $opinion) {
        $sql = <<<SQL
            INSERT INTO t_sbt_opinion VALUES(
                :datetime,
                :access_point,
                :device,
                :browser,
                :opinion
            )
SQL;
        $param = array(
            ":datetime"     => $datetime,
            ":access_point" => $access_point,
            ":device"       => $device,
            ":browser"      => $browser,
            ":opinion"      => $opinion
        );

        $this->query($sql, $param);
    }
}
