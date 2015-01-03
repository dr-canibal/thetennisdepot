<?php
/**
 * WDCA - Sweet Tooth
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the WDCA SWEET TOOTH POINTS AND REWARDS 
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL: 
 *      http://www.wdca.ca/sweet_tooth/sweet_tooth_license.txt
 * The Open Software License is available at this URL: 
 *      http://opensource.org/licenses/osl-3.0.php
 * 
 * DISCLAIMER
 * 
 * By adding to, editing, or in any way modifying this code, WDCA is 
 * not held liable for any inconsistencies or abnormalities in the 
 * behaviour of this code. 
 * By adding to, editing, or in any way modifying this code, the Licensee
 * terminates any agreement of support offered by WDCA, outlined in the 
 * provided Sweet Tooth License. 
 * Upon discovery of modified code in the process of support, the Licensee 
 * is still held accountable for any and all billable time WDCA spent 
 * during the support process.
 * WDCA does not guarantee compatibility with any other framework extension. 
 * WDCA is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 * If you did not receive a copy of the license, please send an email to 
 * contact@wdca.ca or call 1-888-699-WDCA(9322), so we can send you a copy 
 * immediately.
 * 
 * @category   [TBT]
 * @package    [TBT_Rewards]
 * @copyright  Copyright (c) 2009 Web Development Canada (http://www.wdca.ca)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

/**
 * Helper for the prices of products and quote items with monetary currencies
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Helper_Price extends Mage_Core_Helper_Abstract
{

    const CURRENCY_RATE_ROUND = 4;
    
    public function getReversedCurrencyPrice($price, $target_currency_rate=null, $do_round=true) {
        if ($target_currency_rate == null) {
        	$store_currency_model = Mage::app()->getStore()->getCurrentCurrency();
        	$store_base_currency_model = Mage::app()->getStore()->getBaseCurrency();
        	if($store_currency_model->getCode() == $store_base_currency_model->getCode()) {
                return $price;
            }
    		$target_currency_rate = $store_base_currency_model->getRate($store_currency_model);
    		if($do_round) {
                $target_currency_rate = round($target_currency_rate, self::CURRENCY_RATE_ROUND);
            }
        }
		$final_price = $price * $target_currency_rate;
		return $final_price;
    }

    public function getItemProductPrice($item) {
    	// Prepare data from item and initalize counters
    	$store_currency = round($item->getQuote()->getStoreToQuoteRate(), 4);
    	if ($item->hasCustomPrice()) {
    		$product_price = (float)$item->getCustomPrice()* $store_currency;
    	} else {
            if(Mage::helper('tax')->priceIncludesTax()) {
                if($item->getRowTotalBeforeRedemptions()) {
                    $rt = $item->getRowTotal();
                    $item->setRowTotal($item->getRowTotalBeforeRedemptions());
                }
                
                $product_price = (float)Mage::helper('checkout')->getPriceInclTax($item);
                $item->setRowTotal($rt);
                
            } else {
                $product_price = (float)$item->getPrice() * $store_currency;
            }
    	}
    	
    	return $product_price;
    }

}