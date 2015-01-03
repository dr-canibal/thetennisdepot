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
 * Catalog Rule Observer
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Catalogrule_Observer extends Mage_CatalogRule_Model_Observer
{
	
	//TODO: Clean up alot of this code. Remove all commented sections.
  	const APPLICABLE_QTY = 			TBT_Rewards_Model_Catalogrule_Rule::POINTS_APPLICABLE_QTY;
  	const POINTS_RULE_ID = 			TBT_Rewards_Model_Catalogrule_Rule::POINTS_RULE_ID;
  	const POINTS_AMT = 				TBT_Rewards_Model_Catalogrule_Rule::POINTS_AMT;
  	const POINTS_CURRENCY_ID = 		TBT_Rewards_Model_Catalogrule_Rule::POINTS_CURRENCY_ID;
  	const POINTS_USES = 			TBT_Rewards_Model_Catalogrule_Rule::POINTS_USES;
  	const POINTS_EFFECT = 			TBT_Rewards_Model_Catalogrule_Rule::POINTS_EFFECT;
  	const POINTS_INST_ID = 			TBT_Rewards_Model_Catalogrule_Rule::POINTS_INST_ID;
  	
  	private $is_already_saving = false;
  	
  	
    protected $_rulePrices = array();
    
    private function checkRule($rule){
        $localDate = Mage::getModel('core/date')->gmtDate();   
        
        //Check to see if its active
        if(!$rule->getIsActive())
            return false;   
            
        //Make sure its between the active dates    
        if(!(strtotime($rule->getFromDate()) <= strtotime($localDate) && strtotime($rule->getToDate()) >= strtotime($localDate) ))
            return false;
                                    
        //Make sure the customer is within the allowed group for the rule   
        if(!$this->isInGroup(Mage::getSingleton('customer/session')->getCustomerId(), explode(",",$rule->getCustomerGroupIds())))
            return false;

        return true;
    }
    
    /**
     * Returns true if customerId is within the customer groups listed
     * @param string $customerId                : current customer id
     * @param array $groupIds                   : customer group ids array
     * 
     * @return boolean                          
     */
    private function isInGroup($customerId, array $groupIds) {
        return array_search(Mage::getModel('rewards/customer')->load($customerId)->getGroupId(), $groupIds) !== false;
    }
    
    public function applyAllRulesOnProduct($observer)
    {
        parent::applyAllRulesOnProduct($observer);
        
        $product_id = $observer->getProduct()->getId();
        $this->updateRulesHashOnProduct($product_id);
        
//        Mage::getResourceSingleton('catalogrule/rule')->applyAllRulesForDateRange();
//        Mage::app()->removeCache('catalog_rules_dirty');
        
        return $this;
    }

    public function applyRulesOnProductAfterSave($observer)
    {
		$action = $observer->getControllerAction();
		$request = $action->getRequest();
        $product_id = $request->getParam("id");
        $this->updateRulesHashOnProduct($product_id);
        //Mage::log("Updated rules on product $product_id");
        return $this;
    }


    /**
     * Daily update catalog price rule by cron
     * Update include interval 3 days - current day - 1 days before + 1 days after
     * This method is called from cron process, cron is workink in UTC time and
     * we shold generate data for interval -1 day ... +1 day
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogRule_Model_Observer
     */
    public function dailyCatalogUpdate($observer)
    {
        //Mage::log('*****************dailyCatalogUpdate*****************');
        Mage::getResourceSingleton('catalogrule/rule')->applyAllRulesForDateRange();
        return $this;
    }

    public function flushPriceCache()
    {
        $this->_rulePrices = array();
    }
    
    
    /**
     * Retrieve shopping cart model object
     *
     * @return Mage_Checkout_Model_Cart
     */
    protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }
    /**
     * Get current active quote instance
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote()
    {
        return $this->_getCart()->getQuote();
    }
    
    public function updateRedemptions($o) {
        //Mage::log('*****************updateRedemptions*****************');
    	try {
	    	// initialize all required data.
	    	$event = $o->getEvent();
	    	
	    	$cart = $event->getCart();    // UPDATE
	    	if ($cart) {
	    		$quote = $cart->getQuote();  // UPDATE
	    	}
	    	else {
	    		$quote = $event->getQuote();  // SAVE quote ??
	    	}
	    	if (!$quote) {
	    		return $this;
	    	}
	    	
	    	$data = $event->getInfo();    // UPDATE
	    	if ($data) {
	    		$is_on_quote_save = false;
	    		$items = $data;
	    	}
	    	else {
	    		$is_on_quote_save = true;
	    		$items = $quote->getAllItems();
	    	}
	    	
	    	if (!is_array($items)) {
	    		$items = array($items);
	    	}
    		
	    	$refactorItems = array();
	        foreach ($items as $key => $itemInfo) {
	        	if ($is_on_quote_save) {
	        		$itemId = $itemInfo->getId();
	        		$item = $itemInfo;
	        	}
	        	else {
	        		$itemId = $key;
	        		$item = $quote->getItemById($itemId);
	        	}
	        	
	            if (!$itemId || !$item) {
	                continue;
	            }
	            
	        	if (!$is_on_quote_save) {
		            if (!empty($itemInfo['remove']) || (isset($itemInfo['qty']) && $itemInfo['qty']=='0')) {
		            	continue;
		            }
	        	}
	            
	   	        $product    = $item->getProduct();
	   	        $pId        = $product->getId();
		        $storeId    = $product->getStoreId();
		
		        if ($o->hasDate()) {
		            $date = $o->getDate();
		        } else {
	                $date = Mage::helper('rewards')->now();
		        }
		
		        if ($o->hasWebsiteId()) {
		            $wId = $o->getWebsiteId();
		        } else {
		            $wId = Mage::app()->getStore($storeId)->getWebsiteId();
		        }
		        
	        
		        $gId = Mage::getSingleton('customer/session')->getCustomerGroupId();
		        if ($gId !== 0 && empty($gId)) {
		    		$gId = Mage_Customer_Model_Group::NOT_LOGGED_IN_ID;
		        }
			    
		        if ($is_on_quote_save) {
		        	$qty = $item->getQty();
		        }
		        else {
                    $qty = isset($itemInfo['qty']) ? (float) $itemInfo['qty'] : false;
		        }

	            
	            $refactorItems[] = $item;            
	        }
	        
	        Mage::getSingleton('rewards/redeem')->refactorRedemptions($refactorItems);
	        

    	} catch (Exception $e) {
			die($e->getMessage());
    	}
    }
    
    public function updateGrandTotal($o)
    {
        //Mage::log('*****************updateGrandTotal*****************');
        if ($this->is_already_saving) {
        	return $this;
        }
        $this->is_already_saving = true;
        
        try {
            // initialize all required data.
            $event = $o->getEvent();
            
            $quote = $event->getQuote();  // SAVE quote
            
            if (!$quote) {
                return $this;
            }
            
            $items = $quote->getAllItems();
            
            $refactorItems = array();
            foreach ($items as $key => $itemInfo) {
                $itemId = $itemInfo->getId();
                $item = $itemInfo;
                
                if (!$itemId || !$item) {
                    continue;
                }
                
                if (!empty($itemInfo['remove']) || (isset($itemInfo['qty']) && $itemInfo['qty']=='0')) {
                    continue;
                }
                
                $product    = $item->getProduct();
                $pId        = $product->getId();
                $storeId    = $product->getStoreId();
        
                if ($o->hasDate()) {
                    $date = $o->getDate();
                } else {
	               $date = Mage::helper('rewards')->now();
                }
        
                if ($o->hasWebsiteId()) {
                    $wId = $o->getWebsiteId();
                } else {
                    $wId = Mage::app()->getStore($storeId)->getWebsiteId();
                }
                
            
                $gId = Mage::getSingleton('customer/session')->getCustomerGroupId();
                if ($gId !== 0 && empty($gId)) {
                    $gId = Mage_Customer_Model_Group::NOT_LOGGED_IN_ID;
                }
                
                $qty = $item->getQty();
                
                /*
                 * TODO WDCA - Jay: this killed the process (due to rId of -1) whenever I try to load the shopping cart
                 *                because the quantity of any item is always more than 0... what is this supposed to do ??
                 */
//              if ($qty > 0) {
//                  $this->updateRedeemedPointsHash($date, $wId, $gId, $pId, $item, -1, $qty);
//              } else {
//                  // ???
//              }
                
                $refactorItems[] = $item;            
            }
            
            //Mage::getSingleton('rewards/redeem')->refactorGrandTotal($refactorItems);
            $this->is_already_saving = false;
        } catch (Exception $e) {
 
        }
    }
    
    // Mage::dispatchEvent('checkout_cart_add_product_complete', array('product'=>$product, 'request'=>$this->getRequest()));
    public function appendPointsQuote($o) {
        //Mage::log('*****************appendPointsQuote*****************');
    	try {
	    	// initialize all required data.
	    	$product = $o->getEvent()->getProduct();
	    	$request = $o->getEvent()->getRequest();
	    	
	    	
	    	$apply_rule_id = $request->getParam('redemption_rule');
	    	$apply_rule_uses = $request->getParam('redemption_uses');
	    	
	    	
	    	$qty = $request->getParam('qty');
            if(empty($qty)) $qty = 1;
	    	
	    	
   	        $pId        = $product->getId();
	        $storeId    = $product->getStoreId();
	        
	    	$item = $this->_getQuote()->getItemByProduct($product);
	
	        if ($o->hasDate()) {
	            $date = $o->getDate();
	        } else {
	           $date = Mage::helper('rewards')->now();
	        }
	
	        if ($o->hasWebsiteId()) {
	            $wId = $o->getWebsiteId();
	        } else {
	            $wId = Mage::app()->getStore($storeId)->getWebsiteId();
	        }
    	
	        $gId = Mage::getSingleton('customer/session')->getCustomerGroupId();
	        if ($gId !== 0 && empty($gId)) {
	    		$gId = Mage_Customer_Model_Group::NOT_LOGGED_IN_ID;
	        }
	    	
	    	if($item) {
	    		// 1. Validate rule
		    	if(empty($apply_rule_id) && $apply_rule_id != '0') {
		    		// No new rule applied, so no need to adjust redeemed points set.
	        		Mage::getSingleton('rewards/redeem')->refactorRedemptions($item);
		    		return $this;
		    	}
	    		
		    	$this->updateRedeemedPointsHash($date, $wId, $gId, $pId, $item, $apply_rule_id, $qty, true, $apply_rule_uses);
	    		
	        	Mage::getSingleton('rewards/redeem')->refactorRedemptions($item);
	        	
		    	//var_dump($item->getRedeemedPointsHash());
		    	//$item->setRedeemedPointsHash('')->save();
		    	//flush(); //die(); 	
		    	
	    	}
    	} catch (Exception $e) {
    		Mage::getSingleton('core/session')->addError(
    			Mage::helper('rewards')->__("An error occured trying to apply the redemption while adding the product to your cart: ") 
    			. $e->getMessage()
    		);	
    	}
    }
    
    /**
     * Fetches the rewards session model
     *
     * @return TBT_Rewards_Model_Session
     */
    private function _getRewardsSession() {
    	return Mage::getSingleton('rewards/session');
    }
    
    /**
     * Adjusts a reedeemed points hash
     * 
     * TODO: move this to the Redeem Singleton
     * 
     * @throws Exception
     *
     * @param timestamp $date
     * @param int $wId
     * @param int $gId
     * @param int $pId
     * @param Mage_Sales_Model_Quote_Item $item
     * @param int $apply_rule_id
     * @param int $qty
     * @param boolean $adjustQty	if true will set the price for that rule to the given qty, otherwise will add to the qty
     */
    private function updateRedeemedPointsHash($date, $wId, $gId, $pId, $item, $apply_rule_id, $qty, 
    										$adjustQty=true, $uses=1) {		
		
   		/** @var boolean $addedFlag true when the rule qty has been applied to the hash **/
   		$mod_flag = false;
   		/** @var boolean $customerCantAfford FALSE when the customer can't afford the attempted points redemption **/
   		$customer_can_afford = true;
   		/** @var boolean $guestNotAllowed FALSE when the customer is not logged in and guest redemptions are not enabled **/
   		$guest_allowed = true;
   		
    	// 1.a Get Applicable rules
		$applicable_rule = Mage::getResourceModel('rewards/catalogrule_rule')
								->getApplicableReward($date, $wId, $gId, $pId, $apply_rule_id);
		$rule = Mage::getModel('rewards/catalogrule_rule')->load($apply_rule_id);
		$product = Mage::getModel('rewards/catalog_product')->load($pId);
			
		$currency_id = $applicable_rule[self::POINTS_CURRENCY_ID];	
		$points_amount = $applicable_rule[self::POINTS_AMT];
		$to_spend = array($currency_id => $points_amount);
		
		// Double check that the customer can use this rule.
		if($this->_getRewardsSession()->isCustomerLoggedIn()){
			//check logged in conditions
			$customer = $this->_getRewardsSession()->getSessionCustomer();
			$customer_can_afford = $customer->canAfford($to_spend);
		} else {
			// check logged out conditions
			$guest_allowed = Mage::helper('rewards/config')->canUseRedemptionsIfNotLoggedIn();
		}
		$max_uses = $rule->getPointsUsesPerProduct();
		if(!empty($max_uses)) {
			if($max_uses < $uses) {
				$uses = $max_uses;
				// TODO: log an error quietly here!
			}
		}
		
		if(empty($uses)) {
			$uses = 1;
		}
				
		// 1.b Check if requested rule is in applicable rules. 
		if(!$applicable_rule) {
			//throw new Exception("Rule $apply_rule_id no longer available for product $pId, group $gId, date $date and website $wId.");
			// A more friendly error:
			throw new Exception("One or more of points redemptions you are trying to do are no longer available. Please refresh the page.");
		}
   			
		$redeemed_points = $applicable_rule;
		$redeemed_points[self::APPLICABLE_QTY] = $qty;
		$redeemed_points[self::POINTS_USES] = $uses;
		
		
	    $product_price = Mage::helper('rewards/price')->getItemProductPrice($item);
	    
        $cc_ratio = 0;
    	if($product_price > 0) {
    	   $cc = $item->getQuote()->getStore()->getCurrentCurrency();
    	   $bc = 1/($item->getQuote()->getStore()->getBaseCurrency()->getRate($cc));
    	   $cc_ratio = $bc; 
        }
    	$product_price = $cc_ratio * $product_price;
    	
		$redeemed_points[self::POINTS_EFFECT] = $this->_getHelp()->amplifyEffect(
			$product_price, 
			$redeemed_points[self::POINTS_EFFECT], 
			$uses
		);
		$points = Mage::helper('rewards/transfer')->calculateCatalogPoints($apply_rule_id, $item, true);
		if ( !$points ) {
			throw new Exception(  Mage::helper('rewards')->__('The catalog redemption rule entitled %s is invalid and cannot be applied.', $rule->getName())  );
		}
		$redeemed_points[self::POINTS_AMT] = $uses * $points['amount'] * -1;
		
   		$old_redeemed_points = Mage::helper('rewards')->unhashIt($item->getRedeemedPointsHash());
   		
   		$new_redeemed_points = $old_redeemed_points; // copy data from OLD to NEW
   		
   		$num_products_currently_affected = 0;
   		foreach ($new_redeemed_points as $i => &$old_redeemed_points_line) {
   			$old_redeemed_points_line = (array)$old_redeemed_points_line;
   			$num_products_currently_affected += $old_redeemed_points_line[self::APPLICABLE_QTY];
   		}
   		/**
   		 * @var int $avail_extra_applic - the qty we have to work with.  That is, if we're increasing/adding
   		 * 	any sort of redemptions the qty must be less than this amount.
   		 */
   		$avail_extra_applic = $item->getQty() - $num_products_currently_affected;
   		
   		$num_redemption_instances = 1;
   		foreach ($new_redeemed_points as $i => &$old_redeemed_points_line) {
   			$same_rule_id = $old_redeemed_points_line[self::POINTS_RULE_ID] == $apply_rule_id;
   			$same_effects = $old_redeemed_points_line[self::POINTS_EFFECT] == $redeemed_points[self::POINTS_EFFECT];
   			$same_num_uses = $old_redeemed_points_line[self::POINTS_USES] == $uses;
   			if ($same_rule_id && $same_effects && $same_num_uses) {
   				
				// Double check that the customer can use the rule that many times
				
				if($adjustQty) {
					// Just append the cost with the adjustment qty
					$new_applic_qty = ( $redeemed_points[self::APPLICABLE_QTY] + 
										$old_redeemed_points_line[self::APPLICABLE_QTY] );
					// Check if we have room to add this redemption rule
					if($redeemed_points[self::APPLICABLE_QTY] > $avail_extra_applic) {
						throw new Exception("You cannot apply $qty redemptions (max is $avail_extra_applic) ".
											"without overlapping with the other redemptions ".
											"(product id is $pId rule was $apply_rule_id and website $wId. ");
						
					}
				} else {
					// Set the qty manually.
   					$new_applic_qty = $redeemed_points[self::APPLICABLE_QTY] ;
					if($qty > 0) {
						// set the qty
						if($new_applic_qty > $avail_extra_applic) {
							throw new Exception("You cannot apply $qty redemptions (max is $avail_extra_applic) ".
												"without overlapping with the other redemptions ".
												"(product id is $pId rule was $apply_rule_id and website $wId. ");
						}
					}
					
				}
   				$old_redeemed_points_line[self::APPLICABLE_QTY] = $new_applic_qty;
   				if(!isset($old_redeemed_points_line[self::POINTS_USES])) $old_redeemed_points_line[self::POINTS_USES] = 0;
   				//$old_redeemed_points_line[self::POINTS_USES] = (int)$old_redeemed_points_line[self::POINTS_USES] + $uses;
   				$mod_flag = true;
   			}
			$num_redemption_instances++;
   		}
		if (!$mod_flag && $qty != 0) {
			$redeemed_points[self::POINTS_INST_ID] = $num_redemption_instances;
			$new_redeemed_points[] = $redeemed_points;
			$mod_flag = true;
		}
   		
   		$new_redeemed_points_hash = Mage::helper('rewards')->hashIt($new_redeemed_points);
   		$item->setRedeemedPointsHash($new_redeemed_points_hash);
   		$item->unsetData("row_total_before_redemptions");
		//Mage::log(print_r($new_redeemed_points_hash, true));
   		$item->save();
	    		
    }
    
    private function getRuleHash($date, $wId, $gId, $pId, $item, $apply_rule_id)  {
		$applicable_rule = Mage::getResourceModel('rewards/catalogrule_rule')
								->getApplicableReward($date, $wId, $gId, $pId, $apply_rule_id);
    
		return $applicable_rule;
    }
    
    /**
     * Fetches the customer session singleton
     *
     * @return Mage_Customer_Model_Sesssion
     */
    protected function _getCustSession() {
    	return Mage::getSingleton('customer/session');
    }
    /**
     * Gets the default rewards helper
     *
     * @return TBT_Rewards_Helper_Data
     */
    private function _getHelp() {
    	return Mage::helper('rewards');
    }
    
    /* TODO WDCA: move this into an external method */
    public function updateRulesHashOnProduct($product_id)
    {
        Varien_Profiler::start("TBT_Rewards:: Update rewards rule information on product");
        $associated_rule_ids = Mage::helper('rewards/transfer')->getCatalogRewardsRuleIdsForProduct($product_id);
        $loaded_rules = array();
        $is_redemption_rule = array();
        
        $now = date("Y-m-d", strtotime(now()));
        
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select = $read->select()
            ->from( Mage::getConfig()->getTablePrefix() ."catalogrule_product_price", 'customer_group_id')
            ->where("`rule_date`='{$now}' AND `product_id`='{$product_id}'");
        $collection = $read->fetchAll($select);
        
		foreach ($collection as $row) {
			$customer_group_id = $row['customer_group_id'];
			
			if (!$associated_rule_ids) {
				return $this;
			}
			
			$row_hash = array();
			foreach ($associated_rule_ids as $rule_id) {
                if ( isset($loaded_rules[$rule_id]) ) {
                    $rule = &$loaded_rules[$rule_id];
                }
                else {
                    $rule = Mage::getModel('catalogrule/rule')->load($rule_id);
                    $loaded_rules[$rule_id] = $rule;
                }
                
				if (!$rule) {
					continue;
				}
				
				/* <TODO WDCA - take this out once helper:rewards/transfer/getCatalogRewardsRuleIdsForProduct() is fixed.
                 *  the method noted above should be ensuring ITSELF that returned rules are REWARDS rules */
				/* on second thought, this check is still needed either way... but the above method still should validate for REWARDS only */
				
				if ( !isset($is_redemption_rule[$rule_id]) ) {
    	            $is_redemption_rule[$rule_id] = Mage::getModel('rewards/salesrule_actions')  // TODO WDCA: change this to use catalogrule_actions
    	                    ->isRedemptionAction($rule->getPointsAction());
    	        }
    	        
	            if($is_redemption_rule[$rule_id]) {
	                      /* TODO> */
	                // TODO WDCA: any way to optimize this array_search?
	                if ( array_search($customer_group_id, $rule->getCustomerGroupIds()) !== false ) {
						/* TODO WDCA - validate that this rule exists within the current website */
						
						$effect = "";
						if ($rule->getPointsCatalogruleSimpleAction() == 'by_percent') {
                            $effect = '-'. $rule->getPointsCatalogruleDiscountAmount() .'%';
                        }
                        else if ($rule->getPointsCatalogruleSimpleAction() == 'by_fixed') {
                            $effect = '-'. $rule->getPointsCatalogruleDiscountAmount();
                        }
                        else if ($rule->getPointsCatalogruleSimpleAction() == 'to_percent') {
                            $effect = $rule->getPointsCatalogruleDiscountAmount() .'%';
                        }
                        else if ($rule->getPointsCatalogruleSimpleAction() == 'to_fixed') {
                            $effect = $rule->getPointsCatalogruleDiscountAmount();
                        }
                        else {
                        	continue;
                        }
						
						$item_rule = array(
						    TBT_Rewards_Model_Catalogrule_Rule::POINTS_AMT => $rule->getPointsAmount(),
						    TBT_Rewards_Model_Catalogrule_Rule::POINTS_CURRENCY_ID => $rule->getPointsCurrencyId(),
						    TBT_Rewards_Model_Catalogrule_Rule::POINTS_RULE_ID => $rule_id,
						    TBT_Rewards_Model_Catalogrule_Rule::POINTS_APPLICABLE_QTY => 0,
						    TBT_Rewards_Model_Catalogrule_Rule::POINTS_EFFECT => $effect
						);
						
						$row_hash[] = $item_rule;
						
						break;
					}
                }
			}
			
            /* TODO WDCA - the method of getting table name is TERRIBLE - integrate properly with Magento! */
			$write = Mage::getSingleton('core/resource')->getConnection('core_write');
			try {
				$write->beginTransaction();
				$updateData = array("rules_hash" => base64_encode(json_encode($row_hash)));
				$updateWhere = array(
					"`product_id`='{$product_id}' ",
					"`customer_group_id`='{$customer_group_id}' ",
					"`rule_date`='{$now}'",
				);
	            $write->update(
	            	Mage::getConfig()->getTablePrefix() ."catalogrule_product_price", 
	            	$updateData,
	            	$updateWhere
	            );

	            $write->commit();
				
			} catch (Exception $e) {
				$write->rollback();
			}
		}
		
        Varien_Profiler::stop("TBT_Rewards:: Update rewards rule information on product");
		return $this;
    }
}
