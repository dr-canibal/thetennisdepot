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
 * Redeem
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Redeem extends Mage_Core_Model_Abstract
{  
   	const POINTS_RULE_ID = TBT_Rewards_Model_Catalogrule_Rule::POINTS_RULE_ID;
   	const POINTS_APPLICABLE_QTY = TBT_Rewards_Model_Catalogrule_Rule::POINTS_APPLICABLE_QTY;
   	const POINTS_EFFECT = TBT_Rewards_Model_Catalogrule_Rule::POINTS_EFFECT;
   	const POINTS_USES = TBT_Rewards_Model_Catalogrule_Rule::POINTS_USES;
   	const SALES_FLAT_QUOTE_ITEM = "sales_flat_quote_item";
   	
	/**
	 * Adds all applicable rules to the item's rule hash.
	 * Returns false if no changes were made.
	 * @deprecated Do not use
	 *
	 * @param Item_model $item
	 * @param array $rule_id_list
	 * @param Customer_model $customer
	 * @return boolean
	 */
    public function addCatalogRedemptionsToItem($item, $rule_id_list, $customer){
    	
    	
        // Check if customer is logged in.
        if(!$customer->getId()){
            throw new Exception($this->__("Please log in, or sign up to apply point redemptions!"));
        }
        
        //Check to make sure we can load the redeem points hash alright
        if(!$item->getRedeemedPointsHash()){
        	throw new Exception($this->__("Unable to load the redeem points hash" ));       	
        }
        $catalog_redemptions = Mage::helper('rewards')->unhashIt($item->getRedeemedPointsHash());
	   	
        //Load in a temp summary of the customers point balance, 
        //so we can check to see if the applied rules will overdraw their points
        $customer_point_balance = $customer->getUsablePoints();
        $doSave = false;
        

        foreach($rule_id_list as $rule_id){
            $rule = Mage::getModel('rewards/catalogrule_rule')->load($rule_id);

                //Check to make sure the rule applies
                if(array_search((int)$rule_id, Mage::helper('rewards/transfer')->getCatalogRewardsRuleIds($item)) === false){
                	throw new Exception("The rule entitled '". $rule->getName() ."' does not apply to this product.");
                }else{
                	//Check to see if the customer is allowed to use the currency of the rule
                	if(array_search($rule->getPointsCurrencyId(), $customer->getCustomerCurrencyIds()) === false){
                		throw new Exception("You are not allowed to use the "
                		              . Mage::getModel('rewards/currency')->getCurrencyCaption($rule->getPointsCurrencyId()
                		              ." currency.")
                		);
                	}else{
                		if ( !($points = Mage::helper('rewards/transfer')->calculateCatalogPoints($rule_id, $item, true)) ) {
                			throw new Exception("The rule entitled '"
                						. $rule->getName()
                						."' is invalid and could not be applied."
                			);
                			//continue;
                		}
                		
                		//make sure the customer has enough points
	                    if($customer_point_balance[$rule->getPointsCurrencyId()] < $points['amount']){
	                        throw new Exception("You do have have enough "
	                                   . Mage::getModel('rewards/currency')->getCurrencyCaption($rule->getPointsCurrencyId())
	                                   .".<BR/>The rule entitled '"
	                                   . $rule->getName()
	                                   ."' was not applied."
	                        );
	                    }else{
	                        //Get the rules that are already applied  
                            $rule_array = array(TBT_Rewards_Model_Catalogrule_Rule::POINTS_AMT => $points['amount'], 
		                            TBT_Rewards_Model_Catalogrule_Rule::POINTS_CURRENCY_ID => $rule->getPointsCurrencyId(), 
		                            TBT_Rewards_Model_Catalogrule_Rule::POINTS_RULE_ID => $rule->getId(),    
		                            TBT_Rewards_Model_Catalogrule_Rule::POINTS_APPLICABLE_QTY => $item->getQty());
                            
		                    $foundRuleIdIndex = false;
				            foreach ($catalog_redemptions as $index => $redemption) {
				                if ($redemption[TBT_Rewards_Model_Catalogrule_Rule::POINTS_RULE_ID] == $rule_id) {
				                    $foundRuleIdIndex = $index;
				                }
				            }
	                        if($foundRuleIdIndex === false){  // if the rule is not already listed
	                            $catalog_redemptions[] = $rule_array;
	                            $item->setRedeemedPointsHash(Mage::helper('rewards')->hashIt($catalog_redemptions));                          
	                            $doSave = true;      
	                        } else {
	                            throw new Exception("The rule entitled '"
	                                       . $rule->getName()
	                                       ."' is already applied to this product."
	                            );
	                        }
	                    }
                	}
                } 
            }
            if($doSave){
            	$item->save();
            	return true;
            }else{
            	return false; 
            }
	   }
	   	
	/**
     * Removes all applicable rules to the item's rule hash.
     * Returns false if no changes were made.
     *
     * @param Mage_Sales_Model_Quote_Item $item
     * @param array $rule_id_list
     * @param integer $inst_id redemption instance id (this comes out of the item redemptions hash)
     * @return boolean
     */  
    public function removeCatalogRedemptionsFromItem(&$item, $rule_id_list, $inst_id=0){
        //Check to make sure we can load the redeem points hash alright
        if(!$item->getRedeemedPointsHash()){
            throw new Exception($this->__("Unable to load the redeem points hash" ));           
        }
        $catalog_redemptions = Mage::helper('rewards')->unhashIt($item->getRedeemedPointsHash());
        foreach ($catalog_redemptions as $key => $redemption) {
        	$catalog_redemptions[$key] = (array) $redemption;
        }
        
        $doSave = false;   
   
        foreach($rule_id_list as $rule_id){
            $rule = Mage::getModel('rewards/catalogrule_rule')->load($rule_id);            
            $foundRuleIdIndex = false;
            foreach ($catalog_redemptions as $index => $redemption) {
            	$rule_id_is_same = ($redemption[TBT_Rewards_Model_Catalogrule_Rule::POINTS_RULE_ID] == $rule_id);
            	$inst_id_is_same = (($inst_id == 0) ? true : ($redemption[TBT_Rewards_Model_Catalogrule_Rule::POINTS_INST_ID] == $inst_id));
            	if ($rule_id_is_same && $inst_id_is_same) {
            		$foundRuleIdIndex = $index;
            	}
            }
            
            if($foundRuleIdIndex === false){
                throw new Exception("The rule entitled '". $rule->getName() ."' is not applied to this product.");
            }else{                      
                unset($catalog_redemptions[$foundRuleIdIndex]);
                $item->setRedeemedPointsHash(Mage::helper('rewards')->hashIt($catalog_redemptions)); 
                $doSave = true; 
            } 
        }
        
        
        if($doSave){
            $item->save();
            return true;
        }else{
            return false; 
        }
    }
    
    /**
     * Retenders the items listed in the item list
     *
     * @param array(Mage_Sales_Model_Quote_Item) $items
     */
    public function refactorRedemptions($items, $doSave=true) {
    	    	
    	if(!is_array($items)){
    		$items = array($items);
    	}    	

		foreach($items as $item) {
			$this->refactorRedemption($item, $doSave);
            $item->calcTaxAmount();  
    	}	
		$this->refactorGrandTotal($items);	    
    }
    
    /**
     * Retenders the item's redemption rules and final row total
     * @nelkaake Friday March 26, 2010 12:36:50 PM : Changed to protected vs private
     * @param Mage_Sales_Model_Quote_Item $item
     */
    protected function refactorRedemption(&$item, $doSave=true) {
    	
    	// Write to the database the new item row information
    	$r = $this->getUpdatedRedemptionData($item);
    	$row_total = $r['row_total'];
    	$redems = $r['redemptions_data'];
    	if(($row_total != $item->getRowTotal())) {
    	   $item->setRowTotalBeforeRedemptions($item->getRowTotal());
    	}
        $item->setRowTotal($row_total);
        $item->setBaseRowTotal(Mage::helper('rewards/price')->getReversedCurrencyPrice($row_total));
        
        
        $regular_discount = $item->getBaseDiscountAmount();
        if(empty($regular_discount)) {
            $item->setRowTotalWithDiscount($item->getRowTotal());
            $item->setBaseRowTotalWithDiscount($item->getBaseRowTotal());
        }
        
        if(!empty($redems)) {
        }
        
        if($doSave) {
    	   $item->save();
    	}
    }
    
    
    /**
     * Returns the item's updated row total after redemptions
     *
     * @param Mage_Sales_Model_Quote_Item $item
     * @return float
     */
    public function getRowTotalAfterRedemptions($item) {
    	$new_red_data = $this->getUpdatedRedemptionData($item);
    	$row_total = $new_red_data['row_total'];
    	return $row_total;
    }

   /**
     * Returns the item's updated redemption data as a hash
     *
     * @param Mage_Sales_Model_Quote_Item $item
     * @return string a hash of the new item redemptions
     */
    public function getUpdatedRedemptionsHash($item) {
    	$new_red_data = $this->getUpdatedRedemptions($item);
    	$redemptions_data = Mage::helper('rewards')->hashIt($new_red_data);
    	return $redemptions_data;
    }
    
    
   /**
     * Returns the item's updated redemption data
     *
     * @param Mage_Sales_Model_Quote_Item $item
     * @return array a map of the new item redemptions
     */
    public function getUpdatedRedemptions($item) {
    	$new_red_data = $this->getUpdatedRedemptionData($item);
    	$redemptions_data = $new_red_data['redemptions_data'];
    	return $redemptions_data;
    }
    
    
  /**
     * Retenders the item's redemption rules and final row total and returns it.
     * @param Mage_Sales_Model_Quote_Item $item
     * @return array a map of the new item redemption data: 
     * 					array('redemptions_data'=>{...}, 'row_total'=>float)
     */
    protected function getUpdatedRedemptionData($item) {
    	// Step 1: Create a map of usability for all applied redemptions
	    $redeemed_points = Mage::helper('rewards')->unhashIt($item->getRedeemedPointsHash());
	    
    	// Prepare data from item and initalize counters
    	$store_currency = round($item->getQuote()->getStoreToQuoteRate(), 4);
    	if ($item->hasCustomPrice()) {
    		$product_price = (float)$item->getCustomPrice()* $store_currency;
    	} else {
    		$product_price = (float)$item->getPrice() * $store_currency;
    	}
	    if($item->getParentItem() || sizeof($redeemed_points) == 0) {
            return array(
                'redemptions_data' => array(), 
                'row_total' => $item->getRowTotal(),
            );
        }                       
    	
    	

    	$total_qty = $item->getQty();
    	$total_qty_redeemed = 0.0000;
    	$row_total = 0.0000;
        $new_redeemed_points = array();
        $ret = array();
    	
    	// Loop through and apply all our rules.
    	foreach($redeemed_points as $key => &$redemption_instance) {
    		$redemption_instance = (array)$redemption_instance;
    		$applic_qty = $redemption_instance[self::POINTS_APPLICABLE_QTY]; // TODO: should we be getting max of total qty versus applic_qty?
    		$effect = $redemption_instance[self::POINTS_EFFECT];
    		if(!isset($redemption_instance[self::POINTS_USES])) $redemption_instance[self::POINTS_USES] = 1;
    		$uses = (int)$redemption_instance[self::POINTS_USES];
    		
    		$total_qty_remain = $total_qty - $total_qty_redeemed;
    		if ($total_qty_remain > 0) {
    			if ($total_qty_remain < $applic_qty) {
    				$applic_qty = $total_qty_remain;
                    $redemption_instance[TBT_Rewards_Model_Redeem::POINTS_APPLICABLE_QTY] = $applic_qty;
    			}
                $price_after_redem = Mage::helper('rewards')->priceAdjuster($product_price, $effect);
                $row_total += $applic_qty * (float)$price_after_redem;
                $total_qty_redeemed += $applic_qty;
                $new_redeemed_points[] = $redemption_instance;
            } else {
            	$redemption_instance[TBT_Rewards_Model_Catalogrule_Rule::POINTS_APPLICABLE_QTY] = 0;
            	$redemption_instance[TBT_Rewards_Model_Catalogrule_Rule::POINTS_USES] = 1; // used once by default
            	unset($redeemed_points[$key]);
            }
    	}
    	
    	$ret['redemptions_data'] = $new_redeemed_points;
    	
    	// Add in the left over products that perhaps weren't affected by qty adjustment.
    	$total_qty_remain = ($total_qty-$total_qty_redeemed);
    	if($total_qty_remain < 0) {
    		$total_qty_remain = 0;
    		$total_qty_redeemed = $total_qty;
    		//throw new Exception("Redemption rules may be overlapping.  Please notify the store administrator of this error.");
    	}
    	$row_total +=  $total_qty_remain * (float)$product_price;
    	
    	$ret['row_total'] = $row_total;
    	return $ret;
    }
    
    
    public function refactorGrandTotal($items) {
    	$acc_diff = 0;
        
        if(!is_array($items)){
            $items = array($items);
        }       

        foreach($items as $item) {            
        	// Tracking the differences in applying Catalog rules        
            $acc_diff += $item->getRowTotalBeforeRedemptions() - $item->getRowTotal();
        }        
    }
}