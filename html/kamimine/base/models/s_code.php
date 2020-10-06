<?php

require_once (APP.'base/base/BaseModel.php');
require_once ('Zend/Registry.php');

class s_code extends BaseModel {

    /**
     * コードマスタ全件取得
     * 取得後にRegistryに登録
     */
    public function getAllCodes() {
        if(!Zend_Registry::isRegistered('tciCode')) {
            $result = $this->fetchAll(
                'SELECT * FROM s_code ORDER BY code_key,display_order, code_value'
                );
            Zend_Registry::set('tciCode', $result);
            return $result;
        } else {
            return Zend_Registry::get('tciCode');
        }
    }

    /**
     * コード名取得
     * @param code_key, code_value
     * @return code_nameの文字列
     */
	public function getCodeName($code_key, $code_value) {
        $result = array();
        $list = $this->getAllCodes();
        foreach ($list as $row) {
            if($row['code_key'] == $code_key && $row['code_value'] == $code_value){
                return $row['code_name'];
            }
        }
    }

    /**
     * コードマスタ取得
     * @param code_key
     * @return code_value/code_nameのarray配列
     * display_order順にソート済
     */
	public function getCodeList($code_key, $shortname=false) {
        $result = array();
        $list = $this->getAllCodes();
        foreach ($list as $row) {
            if($row['code_key'] == $code_key){
                if($shortname) {
                    array_push($result, array('code_value' => $row['code_value'], 'code_name' => $row['code_short_name']));
                } else {
                    array_push($result, array('code_value' => $row['code_value'], 'code_name' => $row['code_name']));
                }
            }
        }
        return $result;
    }

}
