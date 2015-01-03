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
 * Rewards Catalog Product
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Catalog_Product extends Mage_Catalog_Model_Product
{
	protected static $rule_usage_map = null;
	/**
	 * Loads in a salesrule and returns a points salesrule
	 *
	 * @param Mage_Catalog_Model_Product $product
	 * @return TBT_Rewards_Model_Catalog_Product
	 */
	public static function wrap(Mage_Catalog_Model_Product $product) {
       	$rewards_product = Mage::getModel('rewards/catalog_product')
       			->setData($product->getData())
       			->setId($product->getId());
		return $rewards_product;
	}
	
	/**
	 * Calculates all the points being earned from distribution rules.
	 *
	 * @return array
	 */
	public function getDistriRules() {
		if(!$this->getId()) {
			return array();
		}
		
		$ruleIds = Mage::helper('rewards/transfer')->getCatalogRewardsRuleIdsForProduct($this->getId());
		$rules = array();

		if($ruleIds) {
			foreach ($ruleIds as $ruleId) {
				$pointsEarned = Mage::helper('rewards/transfer')->calculateCatalogPoints($ruleId, $this, false);
				if($pointsEarned['amount'] == 0){
					continue;
				}
				$crModel = Mage::getModel('rewards/catalogrule_rule')->load($ruleId);
				if(  $crModel->isDistributionAction() ) {
					$rules[] = array(
						'rule_id'		=>	$ruleId,
						'caption'		=>	$crModel->getName(),
						'points'		=>	$pointsEarned,
					);
				}
			}
		}
		
		return $rules;
	}
	/**
	 * This returns the product's price before tax. This is useful for when the magento store
	 * includes the tax within the price.
	 * 
	 * Currently this function has error with it. For now it will just return the final price
	 * 
	 * @return decimal
	 */
	public function getFinalPriceBeforeTax() {
		return $this->getFinalPrice();
		/*$price = $this->getFinalPrice();
		
		$percent = $this->getTaxPercent();
        $includingPercent = null;
        $priceIncludesTax = Mage::helper('tax')->priceIncludesTax();

        $taxClassId = $this->getTaxClassId();
        if (is_null($percent)) {
            if ($taxClassId) {
                $request = Mage::getSingleton('tax/calculation')->getRateRequest(false, false, false, $this->getStore());
                $percent = Mage::getSingleton('tax/calculation')->getRate($request->setProductClassId($taxClassId));
            }
        }
        if ($taxClassId && $priceIncludesTax) {
            $request = Mage::getSingleton('tax/calculation')->getRateRequest(false, false, false, $this->getStore());
            $includingPercent = Mage::getSingleton('tax/calculation')->getRate($request->setProductClassId($taxClassId));
        }

        if ($percent === false || is_null($percent)) {
            if ($priceIncludesTax && !$includingPercent) {
                return $price;
            }
        }
        
		return $this->getStore()->roundPrice($price - ($price/(100+$percent)*$percent)); */
	}
	
	
	public function getEarnablePoints() {
		return $this->getDistriRewards();
	}
	

	/**
	 * Get distribution rule rewards.
	 * Sums up the rewards in the standard currency=>amt array format
	 *
	 * @return array
	 */
	public function getDistriRewards() {
		$rules = $this->getDistriRules();
		$rewards = array();
		if(sizeof($rules) > 0) {
			foreach ($rules as $rule_data) {
				$c  = $rule_data['points']['currency'];
				if(!isset($rewards[$c])) $rewards[$c] = 0;
				$rewards[$c] += $rule_data['points']['amount'];
			}
		}
		
		return $rewards;
	}

    
    /**
     * Returns an array of the lowest possible price using points, 
     * and the points used to obtain that price
     * 
     * @return array
     */
    public function getRewardAdjustedPrice()
    {        
        $best_rules = $this->getBestPriceRules();
        if(!$best_rules){        	
        	return array('points_price' => Mage::helper('core')->formatCurrency($this->getFinalPriceBeforeTax()), 'points_string' => '');
        }
        $best_rule_array = $this->getBestValuedRule($best_rules);
        
        $iteration = $best_rule_array['iteration'];
        $best_rule_id = $best_rule_array['rule_id'];
        $best_rule = Mage::getModel('rewards/catalogrule_rule')->load($best_rule_id);
        
        $discounted_price = $this->getFinalPriceBeforeTax() - $this->getPriceDisposition($best_rule) * $iteration; 
        if($discounted_price <0) {
            $discounted_price = 0;
        }       
       
        $points_for_rule = $this->getCatalogPointsForRule($best_rule);
        
        if(is_array($points_for_rule)) {
            $points_for_rule = $points_for_rule['amount'];
        }
        if($points_for_rule < 0) { // redemptions usually come up as a negative pts amt
            $points_for_rule = $points_for_rule * -1;
        }
        
        $points_used = array(
            $best_rule->getPointsCurrencyId() => $points_for_rule * $iteration
        );   
        
        
        $points_string = Mage::getModel('rewards/points')->set($points_used);
        
        $ret = array(
            'points_price' => Mage::helper('core')->formatCurrency($discounted_price), 
            'points_string' => $points_string
        );
        
               
        return $ret;	
    }

    /**
     * Returns how much one iteration of the rule will change the price by
     *
     * @param TBT_Rewards_Model_Catalogrule_Rule||int $rule
     * @param int $product_price
     */
    private function getPriceDisposition($rule){
        if(!($rule instanceof TBT_Rewards_Model_Catalogrule_Rule)) {
            // Assume integer was passed
            $rule = Mage::getModel('rewards/catalogrule_rule')->load($rule);
        }
    	$effect = $rule->getEffect();
    	$temp_price = Mage::helper('rewards')->priceAdjuster($this->getFinalPriceBeforeTax(), $effect);  				
  		return $this->getFinalPriceBeforeTax() - $temp_price;
    	
    }
    
    /**
     * Returns an array of the rules which lower the price the most.
     * There may be more then one rule in this array.
     * the key is the rule, and the value is the number of iterations for the rule
     * 
     * @param array
     * @param float
     * @return array
     */
    private function getBestPriceRules(){
        $customer_point_balance = array();
        
        //Create a map of all the currencies and the customers balance in each         
        $customer = $this->_getRewardsSess()->getSessionCustomer();
        if($this->_getRewardsSess()->isCustomerLoggedIn()){        
            $customer_point_balance = $customer->getUsablePoints();
        }else{  // Check if customer is logged in. if not, show them the potential price using how many points possible
            foreach(Mage::helper('rewards/currency')->getAvailCurrencyIds() as $curr_id){
                $customer_point_balance[$curr_id] = Mage::helper('rewards/config')->getSimulatedPointMax();
            }
        }        
    	$best_rules = array();
    	$lowest_price = $this->getFinalPriceBeforeTax();
    	$rule_array =  $this->getCatalogRedemptionRules($customer);

    	foreach($rule_array as $rule_hash){
    		$rule = (array)$rule_hash;
    		$rule = Mage::getModel('rewards/catalogrule_rule')->load($rule['rule_id']);
    			
    		$price_diff = $this->getPriceDisposition($rule->getId());
    		$points_cost = $rule->getPointsAmount();
        	$points_curr = $rule->getPointsCurrencyId();
        	
        	if($price_diff == 0){
        		$priceIteration = Mage::helper('rewards/config')->getSimulatedPointMax();
        	}else{        	
    			$priceIteration = ceil($this->getFinalPriceBeforeTax() / $price_diff);  //How many calls till price is 0
        	}
        	
        	if($points_cost == 0){
        		$pointIteration = Mage::helper('rewards/config')->getSimulatedPointMax();
        	}else{
        		$pointIteration = floor($customer_point_balance[$points_curr] / $points_cost); //How many calls till poitns are 0
        	}
        	
        	if($rule->getPointsUsesPerProduct() == 0){ 
        		$usesIteration = Mage::helper('rewards/config')->getSimulatedPointMax();
        	}else{
        		$usesIteration = $rule->getPointsUsesPerProduct(); //How many allowed calls
        	}
        	
        	$lowestIteration = min($priceIteration, $pointIteration, $usesIteration);
        	
        	$temp_price = $this->getFinalPriceBeforeTax() - $price_diff*$lowestIteration;
        	        	
        	if($temp_price < 0) $temp_price = 0;
        	
        	if($temp_price < $lowest_price){
        		$lowest_price = $temp_price;
        		$best_rules = array();
        		$best_rules[$rule->getId()] = $lowestIteration;
        	}elseif($temp_price == $lowest_price){
        		$best_rules[$rule->getId()] = $lowestIteration;
        	}
        	
    	}    	
    	return $best_rules;    	
    }
    
    /**
     * Figures out which rule costs the customer the least in the number of points
     * if there is a tie, it will choose the first one.
     * returns in the format array('rule' => rule, 'iteration' => iteration)
     * 
     * @param array
     * @return array
     */
    private function getBestValuedRule($rule_array){
    	$best_rule = array();
    	$best_value = Mage::helper('rewards/config')->getSimulatedPointMax();
    	foreach($rule_array as $rule_id => $iteration){
    		$rule = Mage::getModel('rewards/catalogrule_rule')->load($rule_id);
    		$temp_value = $rule->getBaseCurrencyValue()*$iteration*$rule->getPointsAmount();
    		if($temp_value<$best_value){
    			$best_value = $temp_value;
    			$best_rule = array('rule_id' =>$rule_id, 'iteration' => $iteration);
    		}
    	}
    	return $best_rule;
    }
    
    
    /**
     * Calculates how many points 
     * TODO: refactor this method
     *
     * @param TBT_Rewards_Model_Catalogrule_Rule|int $rule      : id or model
     * @return unknown
     */
    public function getCatalogPointsForRule($rule) {
    
        if($rule instanceof TBT_Rewards_Model_Catalogrule_Rule) {
            $rule_id = $rule->getId();
        } else {
            $rule_id = $rule;
        }
        
		// calculate the proper points quantity based on rule and item
		$points = Mage::helper('rewards/transfer')->calculateCatalogPoints(
			$rule_id, 
			$this, 
			true
		);
	
		
		return $points;
    }
    
    /**
     * Fetches a list of all the applicable rules for this product.
     *
     * @param unknown_type $date
     * @param integer $wId website id
     * @param integer $gId group id
     * @return array
     */
    public function getApplicableCatalogRules($date, $wId, $gId) {
    	$res = Mage::getResourceModel('rewards/catalogrule_rule');
		$applicable_rules = $res->getApplicableRedemptionRewards($date, $wId, $gId, $this->getId());
		return $applicable_rules;
    }

    
    /**
     * Fetches redemption catalog rules for this products
     *
     * @param TBT_Rewards_Model_Customer $customer
     * @return array
     */
    public function getCatalogRedemptionRules($customer) {       	
	    $date = Mage::helper('rewards')->now();
        $wId = Mage::app()->getStore(true)->getWebsiteId();
    	if($customer) {
		    $gId = $customer->getGroupId();
    	} else {
			$gId = Mage_Customer_Model_Group::NOT_LOGGED_IN_ID;
    	}
        $rule_array = $this->getApplicableCatalogRules($date, $wId, $gId);
        return $rule_array;
    }
    
    protected function _getAllPointsCatalogrules() {
    }
    
    /**
     * Fetches the rewards session model
     *
     * @return TBT_Rewards_Model_Session
     */
    protected function _getRewardsSess() {
    	return Mage::getSingleton('rewards/session');
    }
}
