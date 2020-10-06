<?php

require_once ('../base/base/BaseModel.php');

class t_sbt_buscategory extends BaseModel {

    /**
     * 分類一覧の取得
     */
    public function getCategoryList($buscompany_id) {
        $sql = <<< SQL
            SELECT  CA.buscategory_cd,
                    CAL.category_name
            FROM    t_sbt_buscategory CA
                    LEFT JOIN t_sbt_buscategory_lang CAL
                        ON CAL.buscompany_id = :buscompany_id
                        AND CAL.buscategory_cd = CA.buscategory_cd
                        AND CAL.lang_cd = :lang_cd
            WHERE   CA.buscompany_id = :buscompany_id
            ORDER BY CA.buscategory_cd
SQL;
        $param = array(
            ":buscompany_id" => $buscompany_id,
            ":lang_cd" => 'ja'
        );

        return $this->fetchAll($sql, $param);
    }

    /**
     * 分類名を取得
     */
    public function getCategoryName($buscompany_id, $buscategory_cd) {
        $sql = <<< SQL
            SELECT  category_name 
            FROM    t_sbt_buscategory_lang
            WHERE   buscompany_id = :buscompany_id
                    AND buscategory_cd = :buscategory_cd
                    AND lang_cd = :lang_cd
SQL;
        $param = array(
            ":buscompany_id"    => $buscompany_id,
            ":buscategory_cd"   => $buscategory_cd,
            ":lang_cd"          => 'ja'
        );

        return $this->fetchOne($sql, $param);
    }
}
