<?php

require_once (APP.'base/base/BaseModel.php');
require_once ('Zend/Registry.php');

class s_lang extends BaseModel {

    /**
     * 言語マスタ全件取得
     * 取得後にRegistryに登録
     */
    public function getAllLangs() {
        if(!Zend_Registry::isRegistered('tciLang')) {
            $result = $this->fetchAll(
                'SELECT * FROM s_lang ORDER BY display_order,lang_name '
                );
            Zend_Registry::set('tciLang', $result);
            return $result;
        } else {
            return Zend_Registry::get('tciLang');
        }
    }

    /**
     * 言語取得
     * @param lang_cd 
     * @return s_lang
     */
	public function getLang($lang_cd) {
        $result = array();
        $list = $this->getAllLangs();
        foreach ((array)$list as $row) {
            if($row['lang_cd'] == $lang_cd){
                return $row;
            }
        }
    }

    /**
     * アプリケーション言語コード取得
     * アプリケーションで扱うことができる言語コードを返却する
     * @param lang_cd 省略時→DBのデフォルト言語を返却
     *                入力時→入力言語がDBにあるときはそれを返却。ないときはデフォルト言語を返却
     * @return s_lang
	public function getAppLang($lang_cd=null) {
        $result = array();
        $list = $this->getAllCodes();
        foreach ((array)$list as $row) {
            if($row['lang_cd'] == $lang_cd){
                return $row;
            } elseif ($row['default_flg'] == '1') {
                $result = $row;
            }
        }
        return $result;
    }
     */

    /**
     * 言語コードチェック
     * アプリケーションで扱うことができる言語か否かをチェックする
	public function checkAppLang($lang_cd) {
        $list = $this->getAllCodes();
        foreach ((array)$list as $row) {
            if($row['lang_cd'] == $lang_cd){
                return true;
            }
        }
        return false;
    }
     */
}
