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
 * Helper Transfer
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Helper_Rule extends Mage_Core_Helper_Abstract
{
  	
	protected $_cr = array();
	protected $_sr = array();

	/**
	 * Fetches a cached shopping cart rule model
	 *
	 * @param integer $rule_id
	 * @return TBT_Rewards_Model_Salesrule_Rule
	 */
	public function &getSalesRule($rule_id) {
		if(!isset($this->_sr[$rule_id])) {
			$this->_sr[$rule_id] = Mage::getModel('rewards/salesrule_rule')->load($rule_id);	
		}
		return $this->_sr[$rule_id];
	}
    
	/**
	 * Fetches a cached catalog rule model
	 *
	 * @param integer $rule_id
	 * @return TBT_Rewards_Model_Catalogrule_Rule
	 */
	public function &getCatalogRule($rule_id) {
		if(!isset($this->_cr[$rule_id])) {
			$this->_cr[$rule_id] = Mage::getModel('rewards/catalogrule_rule')->load($rule_id);	
		}
		return $this->_cr[$rule_id];
	}
	
    public function storeHasAnyPointsShoppingCartRules() {
        $all_srules = Mage::getModel('rewards/salesrule_rule')
                ->getPointsRuleCollection(true);
        if(Mage::helper('rewards')->isMageVersion131()) {
            $this->filterOutRulesByWebsiteId($all_srules, $this->_getWebsiteId());
        } else {
            $all_srules->addWebsiteFilter($this->_getWebsiteId());
        }
        $has_srules = sizeof($all_srules) > 0;
        return $has_srules;
    }
    
    

    /**
     * Filter collection by specified website IDs
     *
     * @param Mage_CatalogRule_Model_Mysql4_Rule_Collection (or Salesrule collection) &$collection
     * @param int|array $websiteIds      
     * @return $this
     */
    public function filterOutRulesByWebsiteId(&$collection, $websiteIds)
    {
        if (!is_array($websiteIds)) {
            $websiteIds = array($websiteIds);
        }
        foreach ($websiteIds as $websiteId) {
            foreach($collection as &$item) {
                if (array_search($websiteId, $item->getWebsiteIds()) === false) {
                    $collection->removeItemByKey($item->getId());
                }
            }
        }
        return $this;
    }
	
    public function storeHasAnyPointsCatalogRules() {
        $all_crules = Mage::getModel('catalogrule/rule')
                ->getCollection()
                ->addFieldToFilter("points_action", array('neq' => ''))
                ->addFilter("is_active", '1');
        if(Mage::helper('rewards')->isMageVersion131()) {
            $this->filterOutRulesByWebsiteId($all_crules, $this->_getWebsiteId());
        } else {
            $all_crules->addWebsiteFilter($this->_getWebsiteId());
        }
        $has_cat_rules = sizeof($all_crules) > 0;
        return $has_cat_rules;
    }
    public function storeHasAnyCatalogDistriRules() {
        $all_distri_crules = Mage::getModel('catalogrule/rule')
                ->getCollection()
                ->addFieldToFilter("points_action", array('IN' => Mage::getModel('rewards/catalogrule_actions')->getDistributionActions()))
                ->addFilter("is_active", '1');
        if(Mage::helper('rewards')->isMageVersion131()) {
            $this->filterOutRulesByWebsiteId($all_distri_crules, $this->_getWebsiteId());
        } else {
            $all_distri_crules->addWebsiteFilter($this->_getWebsiteId());
        }
        $has_cat_rules = sizeof($all_distri_crules) > 0;
        return $has_cat_rules;
    }
    
    protected function _getWebsiteId() {
        return Mage::app()->getWebsite()->getId();
    }
	
	
	
    public function getQuickCartRedemEntry($salesrule, $cart=null) {
	    if($cart == null)  	$cart = Mage::getSingleton('rewards/session')->getQuote();
        $val = array();
        $points = Mage::getSingleton('rewards/session')->calculateCartPoints($salesrule->getId(), $cart->getAllItems(), true);
        $val = $points;
        $val['name'] = $salesrule->getName();
        if($salesrule->getSimpleFreeShipping()) {
            $val['action_str'] = Mage::helper('rewards')->__("Free Shipping");
        } else {
            $discount_amount = Mage::helper('rewards/transfer')->calculateCartDiscounts($salesrule->getId(), $cart->getAllItems());
            $discount_amount = (($discount_amount < 0) ? (-1 * $discount_amount) : $discount_amount);
            
            //@nelkaake Added on Wednesday May 5, 2010: 
            //@nelkaake Changed on Wednesday May 5, 2010: 
            if(strpos($salesrule->getSimpleAction(), "percent")  !== false) {
                $percent = round($discount_amount);
                $discount_amount_str = Mage::helper('rewards')->__('%s%%', $percent);
            } else {
                //@nelkaake Added on Sunday May 30, 2010: 
                $discount_amount = Mage::app()->getStore()->convertPrice($discount_amount);
                $discount_amount = Mage::app()->getStore()->roundPrice($discount_amount);
                $discount_amount_str = Mage::app()->getStore()->formatPrice($discount_amount);
            }
            $val['action_str'] = Mage::helper('rewards')->__("%s Off", $discount_amount_str);
        }
        
        $points_amt = (($points['amount'] < 0) ? ($points['amount'] * -1) : $points['amount']);
	    $val['points_cost'] = (string)(Mage::getModel('rewards/points')->set($points['currency'], $points_amt));
	    $val['is_coupon'] = $salesrule->getCouponCode(); $val['is_coupon'] = !empty($val['is_coupon']);
	    $val['is_dbps'] = ($salesrule->getPointsAction() == 'discount_by_points_spent');
        return $val;
    }
    public function sortQuickCartRedemEntries($entries) {
        usort($entries, array($this , 'compareRulePointsCost'));
        $sorted = $entries;
        return $sorted;
    }
    
    static function compareRulePointsCost($a, $b) { 
        return ($a['amount'] > $b['amount']); 
    } 
	
}