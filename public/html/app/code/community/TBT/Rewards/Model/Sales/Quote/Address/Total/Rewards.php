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
 * Sales Quote Address Total Rewards
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Sales_Quote_Address_Total_Rewards extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
	protected $discount_amt = 0;
		
    public function __construct(){
        $this->setCode('rewards');
    }
    
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
    	// Only display for the shipping address quote
        if($address->getAddressType() == Mage_Sales_Model_Quote_Address::TYPE_BILLING) {
        	return $this;
        }
        
        if ($this->discount_amt != 0) {
	        $address->addTotal(array(
	            'code'=>$this->getCode(),
	            'title'=>Mage::helper('sales')->__('Item Discounts'),
	            'value'=>$this->discount_amt //This is for display only
	        ));
        }
       
        return $this; 
    }
    
    //This triggers right after the subtotal is calulated
    public function collect(Mage_Sales_Model_Quote_Address $address) 
    {     	
        // No support for multi-shipping
        if(Mage::helper('rewards')->isMultishipMode($address)) {
        	return $this;
        }
        
    	//Update the subtotals using the points discount
    	$final_price = $this->getFinalPrice($address);
    	$base_final_price = Mage::helper('rewards/price')->getReversedCurrencyPrice($final_price);
        $address->setSubtotal($address->getSubtotal() + $final_price );
        $address->setBaseSubtotal($address->getBaseSubtotal() + $base_final_price);
        //Then update the grandtotals
        $address->setGrandTotal($address->getSubtotal() );
        $address->setBaseGrandTotal($address->getBaseSubtotal() );
     
        return $this;
    }
      	
    
    
    /**
     * Loops through each item within the cart and gets the amount of money discounted by points
     * <font color="red"><b>Also updates the row total</b></font>
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return float amount of money discounted by points
     */
    public function getFinalPrice(Mage_Sales_Model_Quote_Address $address) {
        $acc_diff = 0; 
        $items = $address->getAllItems();
         
        if(!is_array($items)){
            $items = array($items);
        }       
        foreach($items as $item) { 
            if (!$item->getQuoteId() || !$item->getId()) {
                continue;
            }
            
            if(Mage::helper('rewards')->isMageVersion14()) {
                $item->setRowTotal($item->getRowTotalBeforeRedemptions());
            }
            $catalog_discount = $this->_getRedeemer()->getRowTotalAfterRedemptions($item);    
            $row_total =  $item->getRowTotal();
            $acc_diff +=  $catalog_discount - $row_total;
        	$new_redeemed_points = $this->_getRedeemer()->getUpdatedRedemptionsHash($item);
            $item->setRedeemedPointsHash( $new_redeemed_points)->save();
        
        }   
        
        $acc_diff = $address->getQuote()->getStore()->roundPrice($acc_diff);
        if($acc_diff == -0) $acc_diff = 0;
        $this->discount_amt = $acc_diff;
        //Mage::log($this->discount_amt);
    	  
        return $acc_diff ;
    }   
    
    /**
     * Fetches the redemption calculator model
     *
     * @return TBT_Rewards_Model_Redeem
     */
    private function _getRedeemer() {
    	return Mage::getSingleton('rewards/redeem');
    }

    /**
     * Fetches the rewards session.
     *
     * @return TBT_Rewards_Model_Session
     */
    private function _getRewardsSess() {
    	return Mage::getSingleton('rewards/session');
    }
}
