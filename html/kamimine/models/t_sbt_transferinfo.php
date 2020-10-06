<?php

require_once (APP.'base/base/BaseModel.php');

class t_sbt_transferinfo extends BaseModel {

    /**
     * 乗換情報一覧を取得
     */
    public function getTransferInfoList($buscompany_id, $busstop_id) {
        $sql = <<< SQL
            SELECT  TR.busstop_id,
                    TR.transfer_url,
                    TRL.transferinfo_name
            FROM    t_sbt_transferinfo TR
                    LEFT JOIN t_sbt_transferinfo_lang TRL
                        ON TRL.buscompany_id = :buscompany_id
                        AND TRL.busstop_id = TR.busstop_id
                        AND TRL.seq = TR.seq
                        AND TRL.lang_cd = :lang_cd
            WHERE   TR.buscompany_id = :buscompany_id
                    AND TR.busstop_id = :busstop_id
SQL;
        $param = array(
            ":buscompany_id" => $buscompany_id, 
            ":lang_cd" => 'ja', 
            ":busstop_id" => $busstop_id
        );

        return $this->fetchAll($sql, $param);
    }

}
