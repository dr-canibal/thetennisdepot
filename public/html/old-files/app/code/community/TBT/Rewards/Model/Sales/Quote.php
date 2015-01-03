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
 * Sales quote model
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Sales_Quote extends Mage_Sales_Model_Quote
{
	/**
	 * Loads in a quote and returns a points quote
	 *
	 * @param Mage_Sales_Model_Quote $product
	 * @return TBT_Rewards_Model_Sales_Quote
	 */
	public static function wrap(Mage_Sales_Model_Quote &$quote) {
		return $quote;
	}
	
	/**
	 * Returns true if the item has redemptions within it.
	 *
	 * @param Mage_Sales_Model_Quote_Item $item
	 */
	public function _hasAppliedCatalogRedemptions($item) {
		$redeemed_point_totals = $item->getRedeemedPointsHash();
		$redeemed_point_totals = Mage::helper('rewards')->unhashIt($redeemed_point_totals);
		$hash_is_empty = empty($redeemed_point_totals);
		return !$hash_is_empty;
	}
	
	/**
	 * True if the quote object has any applied redemptions
	 *
	 * @param TBT_Rewards_Model_Quote $quote
	 * @return boolean
	 */
	public function _hasAppliedCartRedemptions ($quote = null) {
		if($quote == null) { $quote = &$this; }
		$redeem_rules = explode(',', $quote->getAppliedRedemptions());
		if(empty($redeem_rules)) {
			return false;
		}
		foreach($redeem_rules as $rr) {
			if(!empty($rr)) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Updates this quotes' item catalog points.
	 * @param TBT_Rewards_Model_Sales_Quote|Mage_Sales_Model_Quote $quote = null
	 * @return TBT_Rewards_Model_Sales_Quote
	 *
	 */
	public function updateItemCatalogPoints($quote = null) {
		if($quote == null) { $quote = &$this; }
		
        $quote_items = $quote->getAllItems();
        foreach ($quote_items as &$item)
        { 
        	
        	if (!$item->getId()) {
        		continue;
        	}
        	
        	// Should we ignore the distributions because of a catalog redemption?
        	if($this->_getCfg()->doIgnoreCDWhenCR()) {
	        	if($this->_hasAppliedCatalogRedemptions($item)) {
	        		$item->setEarnedPointsHash( Mage::helper('rewards')->hashIt(  array()  ) );
	        		continue;
	        	}
        	}
        	
        	// Should we ignore the distri rules because of a shopping cart redemption?
        	if($this->_getCfg()->doIgnoreCDWhenSCR()) {
	        	if($this->_hasAppliedCartRedemptions($quote)) {
	        		$item->setEarnedPointsHash( Mage::helper('rewards')->hashIt(  array()  ) );
	        		continue;
	        	}
        	}
        	
        	
        	
            $earned_point_totals = array();
            
            $catalog_rule_ids = $this->_getTransferHelp()->getCatalogRewardsRuleIds($item);
            if ($catalog_rule_ids) {
	            foreach ($catalog_rule_ids as $rule_id)
	            {
	                if (!$rule_id) {
	                    continue;
	                }
	                
	                $points = $this->_getTransferHelp()->calculateCatalogPoints($rule_id, $item, false);
	                if ($points) {
	                	if ($points['amount']) {
		                    $earned_point_totals[] = array(
		                        TBT_Rewards_Model_Catalogrule_Rule::POINTS_CURRENCY_ID      => $points['currency'],
		                        TBT_Rewards_Model_Catalogrule_Rule::POINTS_AMT              => $points['amount'],
		                        TBT_Rewards_Model_Catalogrule_Rule::POINTS_RULE_ID          => $rule_id,
		                        TBT_Rewards_Model_Catalogrule_Rule::POINTS_APPLICABLE_QTY   => 1
		                    );
	                	}
	                }
	            }
            }            
            $item->setEarnedPointsHash( Mage::helper('rewards')->hashIt($earned_point_totals) );
        }
        
        return $quote;
        
	}
	
	/**
	 * 
	 *
	 * @return TBT_Rewards_Model_Observer_Sales_Catalogtransfers
	 */
	private function _getCatalogTransfersSingleton() {
		return Mage::getSingleton('rewards/observer_sales_catalogtransfers');
	}
	
	/**
	 * 
	 *
	 * @return TBT_Rewards_Model_Observer_Sales_Carttransfers
	 */
	private function _getCartTransfersSingleton() {
		return Mage::getSingleton('rewards/observer_sales_carttransfers');
	}
	
	public function collectQuoteToOrderTransfers() {
    	if($this->_getRewardsSession()->isAdminMode()) {
    		$this->updateItemCatalogPoints();	
    	}
        
    	
        $order_items = $this->getAllItems();
        $is_login_notice_given = false;
        
        $catalog_transfers = $this->_getCatalogTransfersSingleton();
        foreach ($order_items as $item) {
        	$redeemed_point_totals = $this->_getRH()->unhashIt( $item->getRedeemedPointsHash() );
            if (!empty($redeemed_point_totals)) {
		        if ($this->_getRewardsSession()->isCustomerLoggedIn()) {
					$customer = $this->_getRewardsSession()->getSessionCustomer();
					if ( !$customer->canAffordFromPointsHash($redeemed_point_totals) ) {
						throw new Mage_Core_Exception(
							Mage::helper('rewards')->__('You do not have enough points to spend on this order.  '
								.'Please return to your cart and remove necessary point redemptions.'
							)
						);
	        		}
		        	$catalog_transfers->addRedeemedPoints($redeemed_point_totals);
		        }
		        else {
		            throw new Mage_Core_Exception(
                        $this->_getRH()->__(
                            'You must be logged in to spend points.  Please return to your cart and remove the applied point redemptions.'));
		        }
            }
            
            $earned_point_totals = $this->_getRH()->unhashIt(  $item->getEarnedPointsHash()  );
            if (!empty($earned_point_totals)) {
		        if ($this->_getRewardsSession()->isCustomerLoggedIn()) {
                	$catalog_transfers->addEarnedPoints($earned_point_totals);
		        }
		        
		        //TODO:Fix for bug 108, will be moved for abstraction in the rewards session
                else if($this->_getRewardsSession()->isAdminMode()){
                	$catalog_transfers->addEarnedPoints($earned_point_totals);                	
                }

                
		        else {
		        	if (!$is_login_notice_given) {
	        			Mage::getSingleton('core/session')->addNotice(
	        				Mage::helper('rewards')->__(
	        					'If you had created a customer account, you would have earned points for this order.')
	        			);
	        			$is_login_notice_given = true;
		        	}
		        }
            }
        }
        
        $cart_redemptions = $this->_getCartTransfersSingleton();
        $cart_redemptions->setRedemptionRuleIds( explode(',', $this->getAppliedRedemptions()) );
        if ($this->_getRewardsSession()->getCustomerId()) {
        	$points_earning = $this->_getRewardsSession()->getTotalPointsEarningAsString();
        	$points_spending = $this->_getRewardsSession()->getTotalPointsSpendingAsString();
	        $cart_redemptions->setEarnedPointsString($points_earning);
	        $cart_redemptions->setRedeemedPointsString($points_spending);
        }
        
        $this->reserveOrderId();
        $catalog_transfers->setIncrementId($this->getReservedOrderId());
	}
	

    /**
     * Fetches the rewards session
     *
     * @return TBT_Rewards_Model_Session
     */
    protected function _getRewardsSession() {
 		return Mage::getSingleton('rewards/session');   	
    }

    /**
     * Fetches the rewards transfer helper
     *
     * @return TBT_Rewards_Helper_Transfer
     */
    protected function _getTransferHelp() {
 		return Mage::helper('rewards/transfer');   	
    }

    /**
     * Fetches the rewards config helper
     *
     * @return TBT_Rewards_Helper_Config
     */
    protected function _getCfg() {
 		return Mage::helper('rewards/config');   	
    }

    /**
     * Fetches the rewards generic helper
     *
     * @return TBT_Rewards_Helper_Data
     */
    protected function _getRH() {
 		return Mage::helper('rewards');   	
    }
    
}

