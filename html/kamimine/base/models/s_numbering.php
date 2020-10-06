<?php

require_once (APP.'base/base/BaseModel.php');
require_once (APP.'base/cllib/DataManager.php');
require_once (APP.'base/cllib/LogWriter.php');

class s_numbering extends BaseModel {
    
    /**
     * 採番処理
     */
    public function increment($id_key) {
        // 新規DBコネクション取得
        $this->datam = new DataManager($this->logger);
        $newDb = $this->datam->getNewConnection();
        // 現在値取得
        $select = 'SELECT * FROM s_numbering WHERE id_key = :id_key';
        $selectParams = array("id_key" => $id_key);
        $row = $newDb->fetchRow($select,$selectParams);
        // インクリメント
        if($row['id_type'] == "char") {
            $next_value = str_pad($row['current_value'] + $row['increment_by'], $row['digits'], "0", STR_PAD_LEFT);
        } else {
            $next_value = $row['current_value'] + $row['increment_by'];
        }
        // DB更新
        $update = 'UPDATE s_numbering SET '
                 .'current_value = :current_value '
                 .',upd_user_id = :upd_user_id '
                 .',upd_time = :upd_time '
                 .',upd_transaction_id = :upd_transaction_id '
                 .'WHERE id_key = :id_key';
        $updateParams = array(
                             "id_key" => $id_key
                            ,"current_value" => $next_value
                            ,":upd_user_id" => $this->getUserId()
                            ,":upd_time" => $this->getDatetime()
                            ,":upd_transaction_id" => $this->getTransactionId()
                            );
        $result = $newDb->query($update,$updateParams);
        // 更新結果判定
        if($result == 1) {
            $newDb->commit();
            return $next_value;
        } else {
            $newDb->rollBack();
            throw new Exception("採番テーブルの更新に失敗しました。KEY[".$id_key."]");
        }
    }

}
