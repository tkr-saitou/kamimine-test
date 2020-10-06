<?php

require_once (APP.'base/base/BaseModel.php');
require_once ('Zend/Registry.php');

class s_tax extends BaseModel {

    const SALES_TAX_ID = "01";

    /**
     * 税率マスタ全件取得
     * 取得後にRegistryに登録
     */
    private function getAllTaxes() {
        if(!Zend_Registry::isRegistered('tciTax')) {
            $result = $this->fetchAll(
                'SELECT * FROM s_tax ORDER BY tax_id,upd_date ',
                array()
                );
            Zend_Registry::set('tciTax', $result);
            return $result;
        } else {
            return Zend_Registry::get('tciTax');
        }
    }

    /**
     * 消費税率取得
     * 税率マスタ(s_tax)より税率を取得
     * $target_date時点での税率を適用。
     * 経過措置日付($transitional_date)≠NULLの場合
     * →経過措置期間に該当していれば、経過措置税率が適用
     * @param target_date,transitional_date
     * @return 消費税率
     */
	public function getSalesTaxRate($target_date,$transitional_date=null) {
        $result = array();
        $list = $this->getAllTaxes();
        foreach ($list as $row) {
            if($row['tax_id'] == self::SALES_TAX_ID && $row['upd_date'] <= $target_date){
                if(is_null($transitional_date) || empty($row['transitional_rate'])) {
                    // 経過措置考慮不要
                    $tax_rate = $row['tax_rate'];
                } else {
                    if($transitional_date <= Util::formatDate($row['transitional_period_to'])) {
                        // 経過措置対象
                        $tax_rate = $row['transitional_rate'];
                    } else {
                        // 経過措置対象外(=通常税率)
                        $tax_rate = $row['tax_rate'];
                    }
                }
            }
        } 
        return $tax_rate;
    }

    /**
     * 消費税額計算
     * $amountに対する消費税額を計算。小数点以下の端数は切り捨て。
     * 消費税率を外から渡せるように引数に入れているが、通常は不要
     * @param amount,target_date,transitional_date
     * @return 消費税額（税込金額ではなく、税額のみの数字）
     */
	public function calcSalesTax($amount, $target_date,$transitional_date=null, $tax_rate=null) {
        if(is_null($tax_rate)) {
            $tax_rate = $this->getSalesTaxRate($amount, $target_date,$transitional_date);
        }
        return floor($amount * $tax_rate / 100);
    }
}
