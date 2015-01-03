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
 * Shopping Cart Rule Validator
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Salesrule_Validator extends Mage_SalesRule_Model_Validator
{
	private $num_items_validated = 0;
	private $total_discount_reversed = 0;
	private $total_base_discount_reversed = 0;
	private $cart_fixed_rules = array();
	protected $_rrules = array();
	
	/**
	 * Fetches a cached rule model
	 *
	 * @param integer $rule_id
	 * @return TBT_Rewards_Model_Salesrule_Rule
	 */
	protected function &getRule($rule_id) {
		if(!isset($this->_rrules[$rule_id])) {
			$this->_rrules[$rule_id] = Mage::getModel('rewards/salesrule_rule')->load($rule_id);	
		}
		return $this->_rrules[$rule_id];
	}
	
	public function process(Mage_Sales_Model_Quote_Item_Abstract $item)
    {
    	
    	if ($this->num_items_validated == 0) {
    		$item->getQuote()->setAppliedRuleIds(array());
    	}
    	$this->originalProcess($item);
    	Varien_Profiler::start("TBT_REWARDS: Salesrule Validator");
    	
        $this->num_items_validated++;
    	
        $quote = $item->getQuote();
        if ($item instanceof Mage_Sales_Model_Quote_Address_Item) {
            $address = $item->getAddress();
        } elseif ($quote->isVirtual()) {
            $address = $quote->getBillingAddress();
        } else {
            $address = $quote->getShippingAddress();
        }
        
        $applicable_redemptions = explode(',', $quote->getCartRedemptions());
        $applied_redemptions = explode(',', $quote->getAppliedRedemptions());
        
        $validated_applicable_rule_ids = array();
        $validated_applied_rule_ids = array();
        $item->setDiscountAmount(0);
        $item->setBaseDiscountAmount(0);
       	
        // Get a map of all points sales rules
        $salesrule_rids = Mage::getModel('rewards/salesrule_rule')->getPointsRuleIds();
        $all_points_salesrule_ids_map = array_flip($salesrule_rids);

        foreach(explode(',', $quote->getAppliedRuleIds()) as $rule_id) {
            $salesrule = $this->getRule($rule_id);
        	 // Get the points salesrule versus $salesrule which is any type of salesrule
        	$rule = &$salesrule;
        	
            if (!$rule->validate($address)) {
                if (array_search($rule_id, $validated_applied_rule_ids) !== false) {
                    unset($validated_applied_rule_ids[$rule_id]);
                }
            	continue;
            }
            
            // Here we're basically checking to see if the cart rule is enabled
            // for this particular item or quotation if the rule is a redemption points rule.
            if ( $rule->isRedemptionRule() ) {
                if (array_search($rule_id, $applied_redemptions) === false) {               // the rule is not already applied
                    if (array_search($rule_id, $applicable_redemptions) === false) {    // the rule is not already applicable
                        $applicable_redemptions[] = $rule_id;
                    }
                    
                    $validated_applicable_rule_ids[] = $rule_id;
                    if (array_search($rule_id, $validated_applied_rule_ids) !== false) {
                        unset($validated_applied_rule_ids[$rule_id]);
                    }
                    continue;
                }               
            }
            $validated_applied_rule_ids[$rule_id] = $rule_id;
        }
        
        foreach ($applicable_redemptions as $key => $rid) {
            if (array_search($rid, $validated_applicable_rule_ids) === false) {
                unset($applicable_redemptions[$key]);
            }
        }
            
        // 1\ Check for if the rule was acutally validated (I think this also done earlier)
        // 2\ Fulfil StopRulesProcessing condition.   
        foreach ($applied_redemptions as $key => $rid) {
            $rr = $this->getRule($rid);
            if (array_search($rid, $validated_applied_rule_ids) === false ) {
                unset($applied_redemptions[$key]);
            } else {
                if($rr->getStopRulesProcessing()) {
                    $applied_redemptions = array($rid);
    		        $applicable_redemptions = array();
    		        $validated_applied_rule_ids = array($rid);
                    break;
                }
            }
        }
        
        // The next few commented lines are for the discount by points spent action
//        $vraw = array();
//        foreach($applicable_redemptions as $key => $rid) {
//            $vraw[$rid] = $rid;
//        }
//        foreach($validated_applied_rule_ids as $key => $rid) {
//            $vraw[$rid] = $rid;
//        }
//        foreach($vraw as $rid => $rid) {
//            $rule = $this->getRule($rid);
//            if($rule->getPointsAction() == TBT_Rewards_Model_Salesrule_Actions::ACTION_DISCOUNT_BY_POINTS_SPENT) {
//                $applied_redemptions[] = $rid;
//                unset($applicable_redemptions[$key]);
//            }
//        }
        
        foreach($validated_applied_rule_ids as $rule_id) {
            $this->recalculateDiscounts($quote, $address, $item, $rule_id);
            $salesrule = $this->getRule($rule_id);
        }
    
        // No support for multi-shipping
        if( Mage::helper('rewards')->isMultishipMode($quote) ) {
    		$applicable_redemptions = array();
    		$applied_redemptions = array();
    		$validated_applied_rule_ids = array();
        }
        
        $this->recalculateShippingDiscounts($address, $item, $applied_redemptions);
        
        $applicable_redemptions = array_unique($applicable_redemptions);
        $applicable_redemptions_str = implode(',', $applicable_redemptions);
        
        $applied_redemptions = array_unique($applied_redemptions);
        $applied_redemptions_str = implode(',', $applied_redemptions);
        
        $validated_applied_rule_ids = array_unique($validated_applied_rule_ids);
        $validated_applied_rule_ids_str = implode(',', $validated_applied_rule_ids);
 
        $item	->setAppliedRuleIds($validated_applied_rule_ids_str);
        $address->setAppliedRuleIds($validated_applied_rule_ids_str);
        $quote	->setCartRedemptions($applicable_redemptions_str)
            	->setAppliedRedemptions($applied_redemptions_str)
            	->setAppliedRuleIds($validated_applied_rule_ids_str);
        
        //echo "Valid Applied rules: $validated_applied_rule_ids_str, Applicable Rules: $applicable_redemptions_str, Applied Rules: $applied_redemptions_str <BR />";
        
        $this->total_discount_reversed = 0;
        $this->total_base_discount_reversed = 0;
        
    	Varien_Profiler::stop("TBT_REWARDS: Salesrule Validator");
        return $this;
    }
    
    /**
     * Recalulates the shipping discount taking into account whether or not
     * a redemption rule is applied by the user.
     *
     * @author Jay El <nelkaake@wdca.ca>
     * 
     * @param Mage_Sales_Model_Quote_Address &$address
     * @param Mage_Sales_Model_Quote_Item_Abstract &$item
     * @param array $applied_redemptions
     */
    protected function recalculateShippingDiscounts(Mage_Sales_Model_Quote_Address &$address, 
    		&$item, array $applied_redemptions) {
        // Enabled/disable free shipping
        $item->setFreeShipping(false);
        $address->setFreeShipping(false);
    	foreach ($this->_rules as &$salesrule) {
        	$rule_id = $salesrule->getId();
        	 // Get the points salesrule versus $salesrule which is any type of salesrule
        	$pointsrule = $this->getRule($rule_id);
            
        	if (!$pointsrule->isPointsRule() || array_search($rule_id, $applied_redemptions) !== false) {
		        switch ($pointsrule->getSimpleFreeShipping()) {
		            case Mage_SalesRule_Model_Rule::FREE_SHIPPING_ITEM:
		                $item->setFreeShipping($pointsrule->getDiscountQty() ? $pointsrule->getDiscountQty() : true);
		                break;
		
		            case Mage_SalesRule_Model_Rule::FREE_SHIPPING_ADDRESS:
		                $address->setFreeShipping(true);
		                break;
		        }
        	}
    	}
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
     * TODO doc this... also does this have to be private?
     * 
     * @author Jared Ty <jtyler@wdca.ca>
     * 
     * @param unknown_type $quote
     * @param unknown_type $address
     * @param unknown_type $item
     * @param unknown_type $rule_id
     */
    private function recalculateDiscounts($quote, $address, $item, $rule_id)
    {
    	// TODO WDCA - start figuring out what to reverse here for discounts
        // TODO WDCA - move all of this into a method, to be called when we set the applicable_redemptions above
        $rule = $this->getRule($rule_id);
        $store = $item->getStore();
        
        if (!$rule->getId()) {
        	return $this;
        }
        if (!$rule->getActions()->validate($item)) {
        	return $this;
        }
        
        $qty = $item->getQty();
        if ($item->getParentItem()) {
            $qty*= $item->getParentItem()->getQty();
        }
        $qty = $rule->getDiscountQty() ? min($qty, $rule->getDiscountQty()) : $qty;
        $rulePercent = min(100, $rule->getDiscountAmount());
        $discountAmount = 0;
        $baseDiscountAmount = 0;
        switch ($rule->getSimpleAction()) {
            case 'to_percent':
                $rulePercent = max(0, 100-$rule->getDiscountAmount());
                //no break;

            case 'by_percent':
                if ($step = $rule->getDiscountStep()) {
                    $qty = floor($qty/$step)*$step;
                }
            
                // WDCA CODE BEGIN
                $row_total = $this->_getRedeemer()->getRowTotalAfterRedemptions($item);
                
                $discountAmount    = ($row_total) * $rulePercent/100;
                $baseDiscountAmount= ($row_total) * $rulePercent/100;
                // WDCA CODE END

                if (!$rule->getDiscountQty() || $rule->getDiscountQty()>$qty) {
                    $discountPercent = min(100, $item->getDiscountPercent()+$rulePercent);
                    $item->setDiscountPercent($discountPercent);
                }
                break;

            case 'to_fixed':
                $quoteAmount = $quote->getStore()->convertPrice($rule->getDiscountAmount());
                $discountAmount    = $qty*($item->getCalculationPrice()-$quoteAmount);
                $baseDiscountAmount= $qty*($item->getBaseCalculationPrice()-$rule->getDiscountAmount());
                break;

            case 'by_fixed':
                if ($step = $rule->getDiscountStep()) {
                    $qty = floor($qty/$step)*$step;
                }
                $quoteAmount = $quote->getStore()->convertPrice($rule->getDiscountAmount());
                $discountAmount    = $qty*$quoteAmount;
                $baseDiscountAmount= $qty*$rule->getDiscountAmount();
                break;

            case 'cart_fixed':
                $cartRules = $this->cart_fixed_rules;
                if (!isset($cartRules[$rule->getId()])) {
                    $cartRules[$rule->getId()] = $rule->getDiscountAmount();
                }
                if ($cartRules[$rule->getId()] > 0) {
                    $quoteAmount = $quote->getStore()->convertPrice($cartRules[$rule->getId()]);
                    $discountAmount = min($item->getRowTotal()-$item->getDiscountAmount(), $quoteAmount);
                    $baseDiscountAmount = min($item->getBaseRowTotal()-$item->getBaseDiscountAmount(), $cartRules[$rule->getId()]);
                    $cartRules[$rule->getId()] -= $baseDiscountAmount;
                }
                $this->cart_fixed_rules = $cartRules;
                break;

            case 'buy_x_get_y':
                $x = $rule->getDiscountStep();
                $y = $rule->getDiscountAmount();
                if (!$x || $y>=$x) {
                    break;
                }
                $buy = 0; $free = 0;
                while ($buy+$free<$qty) {
                    $buy += $x;
                    if ($buy+$free>=$qty) {
                        break;
                    }
                    $free += min($y, $qty-$buy-$free);
                    if ($buy+$free>=$qty) {
                        break;
                    }
                }
                $discountAmount    = $free*$item->getCalculationPrice();
                $baseDiscountAmount= $free*$item->getBaseCalculationPrice();
                break;
            
            
        }

        if ( $rule->getPointsAction() == TBT_Rewards_Model_Salesrule_Actions::ACTION_DISCOUNT_BY_POINTS_SPENT ) {
            $price = $item->getRowTotal()+$item->getTaxAmount();
            $base_price = $item->getBaseRowTotal() + $this->getBaseTaxAmount();
            
            $max_points = $rule->getPointsAmount() * floor($price / $rule->getPointsAmountStep());
            $points_to_transfer = min($max_points, (int)$rule->getPointsSpending());
            
            $discountAmount = $rule->getPointsAmountStep() * $points_to_transfer;
            $baseDiscountAmount= Mage::helper('rewards/price')->getReversedCurrencyPrice($discountAmount);
            
            
            if($points_to_transfer == $max_points) {
                $discountAmount = $price;
                $baseDiscountAmount = $base_price;
            }
            
        }
            
        $this->total_discount_reversed += $discountAmount;
        $this->total_base_discount_reversed += $baseDiscountAmount;

        $discountAmount     = $quote->getStore()->roundPrice($discountAmount);
        $baseDiscountAmount = $quote->getStore()->roundPrice($baseDiscountAmount);
        
        if (Mage::helper('tax')->discountTax($store) && !Mage::helper('tax')->applyTaxAfterDiscount($store)) {
            $discountAmount     = min($item->getDiscountAmount()+$discountAmount, $item->getRowTotal()+$item->getTaxAmount());
            $baseDiscountAmount = min($item->getBaseDiscountAmount()+$baseDiscountAmount, $item->getBaseRowTotal() + $this->getBaseTaxAmount());
        } else {
            $discountAmount     = min($item->getDiscountAmount()+$discountAmount, $item->getRowTotal());
            $baseDiscountAmount = min($item->getBaseDiscountAmount()+$baseDiscountAmount, $item->getBaseRowTotal());
        }

        $item->setDiscountAmount($discountAmount);
        $item->setBaseDiscountAmount($baseDiscountAmount);
        //$item->save();
        
        return $this;
    }
    
    
    public function originalProcess(Mage_Sales_Model_Quote_Item_Abstract $item)
    {
        $item->setFreeShipping(false);
        $item->setDiscountAmount(0);
        $item->setBaseDiscountAmount(0);
        $item->setDiscountPercent(0);

        $quote = $item->getQuote();
        if ($item instanceof Mage_Sales_Model_Quote_Address_Item) {
            $address = $item->getAddress();
        } elseif ($quote->isVirtual()) {
            $address = $quote->getBillingAddress();
        } else {
            $address = $quote->getShippingAddress();
        }

        $customerId = $quote->getCustomerId();
        $ruleCustomer = Mage::getModel('salesrule/rule_customer');
        $appliedRuleIds = array();
        
        foreach ($this->_rules as $rule) {
            /* @var $rule Mage_SalesRule_Model_Rule */
            /**
             * already tried to validate and failed
             */
            if ($rule->getIsValid() === false) {
                continue;
            }

        	// WDCA CODE BEGIN
        	$rewards_rule = $this->getRule($rule->getId());
        	// WDCA CODE END
        	
            if ($rule->getIsValid() !== true) {
            	
                /**
                 * too many times used in general
                 */
                if ($rule->getUsesPerCoupon() && ($rule->getTimesUsed() >= $rule->getUsesPerCoupon())) {
                    $rule->setIsValid(false);
                    continue;
                }
                /**
                 * too many times used for this customer
                 */
                $ruleId = $rule->getId();
                if ($ruleId && $rule->getUsesPerCustomer()) {
                    $ruleCustomer->loadByCustomerRule($customerId, $ruleId);
                    if ($ruleCustomer->getId()) {
                        if ($ruleCustomer->getTimesUsed() >= $rule->getUsesPerCustomer()) {
                            continue;
                        }
                    }
                }
                $rule->afterLoad();
                /**
                 * quote does not meet rule's conditions
                 */
                if (!$rule->validate($address)) {
                    $rule->setIsValid(false);
                    continue;
                }
                /**
                 * passed all validations, remember to be valid
                 */
                $rule->setIsValid(true); 
            }

            /**
             * although the rule is valid, this item is not marked for action
             */
            if (!$rule->getActions()->validate($item)) {
                continue;
            }
            $qty = $item->getQty();
            if ($item->getParentItem()) {
                $qty*= $item->getParentItem()->getQty();
            }
            $qty = $rule->getDiscountQty() ? min($qty, $rule->getDiscountQty()) : $qty;
            $rulePercent = min(100, $rule->getDiscountAmount());
            $discountAmount = 0;
            $baseDiscountAmount = 0;
            switch ($rule->getSimpleAction()) {
                case 'to_percent':
                    $rulePercent = max(0, 100-$rule->getDiscountAmount());
                    //no break;

                case 'by_percent':
                    if ($step = $rule->getDiscountStep()) {
                        $qty = floor($qty/$step)*$step;
                    }
                    $discountAmount    = ($qty*$item->getCalculationPrice() - $item->getDiscountAmount()) * $rulePercent/100;
                    $baseDiscountAmount= ($qty*$item->getBaseCalculationPrice() - $item->getBaseDiscountAmount()) * $rulePercent/100;

                    if (!$rule->getDiscountQty() || $rule->getDiscountQty()>$qty) {
                        $discountPercent = min(100, $item->getDiscountPercent()+$rulePercent);
                        $item->setDiscountPercent($discountPercent);
                    }
                    break;

                case 'to_fixed':
                    $quoteAmount = $quote->getStore()->convertPrice($rule->getDiscountAmount());
                    $discountAmount    = $qty*($item->getCalculationPrice()-$quoteAmount);
                    $baseDiscountAmount= $qty*($item->getBaseCalculationPrice()-$rule->getDiscountAmount());
                    break;

                case 'by_fixed':
                    if ($step = $rule->getDiscountStep()) {
                        $qty = floor($qty/$step)*$step;
                    }
                    $quoteAmount = $quote->getStore()->convertPrice($rule->getDiscountAmount());
                    $discountAmount    = $qty*$quoteAmount;
                    $baseDiscountAmount= $qty*$rule->getDiscountAmount();
                    break;

                case 'cart_fixed':
                    $cartRules = $address->getCartFixedRules();
                    if (!isset($cartRules[$rule->getId()])) {
                        $cartRules[$rule->getId()] = $rule->getDiscountAmount();
                    }
                    if ($cartRules[$rule->getId()] > 0) {
                        $quoteAmount = $quote->getStore()->convertPrice($cartRules[$rule->getId()]);
                        $discountAmount = min($item->getRowTotal(), $quoteAmount);
                        $baseDiscountAmount = min($item->getBaseRowTotal(), $cartRules[$rule->getId()]);
                        $cartRules[$rule->getId()] -= $baseDiscountAmount;
                    }
                    $address->setCartFixedRules($cartRules);
                    break;

                case 'buy_x_get_y':
                    $x = $rule->getDiscountStep();
                    $y = $rule->getDiscountAmount();
                    if (!$x || $y>=$x) {
                        break;
                    }
                    $buy = 0; $free = 0;
                    while ($buy+$free<$qty) {
                        $buy += $x;
                        if ($buy+$free>=$qty) {
                            break;
                        }
                        $free += min($y, $qty-$buy-$free);
                        if ($buy+$free>=$qty) {
                            break;
                        }
                    }
                    $discountAmount    = $free*$item->getCalculationPrice();
                    $baseDiscountAmount= $free*$item->getBaseCalculationPrice();
                    break;
            }

            $result = new Varien_Object(array(
                'discount_amount'      => $discountAmount,
                'base_discount_amount' => $baseDiscountAmount,
            ));
            Mage::dispatchEvent('salesrule_validator_process', array(
                'rule'    => $rule,
                'item'    => $item,
                'address' => $address,
                'quote'   => $quote,
                'qty'     => $qty,
                'result'  => $result,
            ));

            $discountAmount = $result->getDiscountAmount();
            $baseDiscountAmount = $result->getBaseDiscountAmount();

            $discountAmount     = $quote->getStore()->roundPrice($discountAmount);
            $baseDiscountAmount = $quote->getStore()->roundPrice($baseDiscountAmount);
            $discountAmount     = min($item->getDiscountAmount()+$discountAmount, $item->getRowTotal());
            $baseDiscountAmount = min($item->getBaseDiscountAmount()+$baseDiscountAmount, $item->getBaseRowTotal());

            $item->setDiscountAmount($discountAmount);
            $item->setBaseDiscountAmount($baseDiscountAmount);

            switch ($rule->getSimpleFreeShipping()) {
                case Mage_SalesRule_Model_Rule::FREE_SHIPPING_ITEM:
                    $item->setFreeShipping($rule->getDiscountQty() ? $rule->getDiscountQty() : true);
                    break;

                case Mage_SalesRule_Model_Rule::FREE_SHIPPING_ADDRESS:
                    $address->setFreeShipping(true);
                    break;
            }

            $appliedRuleIds[$rule->getRuleId()] = $rule->getRuleId();

            if ($rule->getCouponCode() && ( strtolower($rule->getCouponCode()) == strtolower($this->getCouponCode()))) {
                $address->setCouponCode($this->getCouponCode());
            }

            if ($rule->getStopRulesProcessing()) {
            	if($rewards_rule->isRedemptionRule() ) {
            		
            	} else {
                	break;
            	}
            }
        }
        $item->setAppliedRuleIds(join(',',$appliedRuleIds));
        $address->setAppliedRuleIds($this->mergeIds($address->getAppliedRuleIds(), $appliedRuleIds));
        $quote->setAppliedRuleIds($this->mergeIds($quote->getAppliedRuleIds(), $appliedRuleIds));
        
        return $this;
    }
}