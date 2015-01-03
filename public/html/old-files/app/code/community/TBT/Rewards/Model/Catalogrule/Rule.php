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
 * Catalog Rule Rule
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Catalogrule_Rule extends Mage_CatalogRule_Model_Rule
{
	const POINTS_CURRENCY_ID = 'points_currency_id';
	const POINTS_AMT = 'points_amt';
	const POINTS_EFFECT = 'effect';
	const POINTS_RULE_ID = 'rule_id';
	const POINTS_APPLICABLE_QTY = 'applicable_qty';
  	const POINTS_USES = 'uses';
  	const POINTS_INST_ID = 'redemption_inst_id';
  	
    const CACHE_TAG              = 'rewards_catalogrule';
    protected $_cacheTag         = 'rewards_catalogrule';
	
	public function _construct()
    	{
        	parent::_construct();
        	$this->_init('rewards/catalogrule_rule');
    	}
    
    
    /**
     * Clear cache related with rule_id
     *
     * @return TBT_Rewards_Model_Catalogrule_Rule
     */
    public function cleanCache()
    {
        Mage::app()->cleanCache(self::CACHE_TAG . '_' . $this->getId());
        return $this;
    }
    
    /**
     * Clear chache related with product
     *
     * @return TBT_Rewards_Model_Catalogrule_Rule
     */
    protected function _beforeDelete()
    {
        $this->cleanCache();
        return parent::_beforeDelete();
    }
    
    /**
     * Check clean cache before save
     */
    protected function _beforeSave()
    {
        $this->cleanCache();
        parent::_beforeSave();
    }
    
	public function getResourceCollection()
    {
        return Mage::getResourceModel('rewards/catalogrule_rule_collection');
    }
    
	/**
	 * Returns true if this a redemption rule
	 *
	 * @return boolean
	 */
	public function isRedemptionRule() {
		$ruleActionSing = Mage::getSingleton('rewards/catalogrule_actions');
		return $ruleActionSing->isRedemptionAction($this->getPointsAction());
	}
	/**
	 * Returns true if this a distribution rule
	 *
	 * @return boolean
	 */
	public function isDistributionRule() {
		$ruleActionSing = Mage::getSingleton('rewards/catalogrule_actions');
		return $ruleActionSing->isDistributionAction($this->getPointsAction());
	}
	
	/**
	 * Returns true if this a redemption rule
	 *
	 * @return boolean
	 */
	public function isRedemptionAction() {
		return $this->isRedemptionRule();
	}
	/**
	 * Returns true if this a distribution rule
	 *
	 * @return boolean
	 */
	public function isDistributionAction() {
		return $this->isDistributionRule();
	}
	/**
	 * Returns the rule time id
	 *
	 * @return int
	 */
	public function getRuleTypeId() {
		$ruleActionSing = Mage::getSingleton('rewards/catalogrule_actions');
		return $ruleActionSing->getRuleTypeId($this->getPointsAction());
	}
	
	/**
	 * Fetches a list of all CATALOGRULE rules that
	 * have a points action
	 *
	 * @return Collection
	 */
	public function getPointsRuleIds() {
		$col = $this->getCollection()
            ->addFieldToFilter("points_action", array('neq' => ''));
        return $col;
	}
	
	/**
	 * Checks to see if the customer group id is applicable to this rule
	 * TODO WDCA: any way to optimize this array_search? perhaps a map?
	 * @param integer $customer_group_id
	 * @return boolean	: true if the group id is applicable to this rule, false otherwise
	 */
	public function isApplicableToCustomerGroup($customer_group_id) {
		return array_search($customer_group_id, $this->getCustomerGroupIds()) !== false;
	}
	
	
	/**
	 * Checks to see if the website id is applicable to this rule
	 * TODO WDCA: any way to optimize this array_search? perhaps a map?
	 * @param integer $website_id
	 * @return boolean	: true if the website is applicable to this rule, false otherwise
	 */
	public function isApplicableToWebsite($website_id) {
		return array_search($website_id, $this->getWebsiteIds()) !== false;
	}
	
	
	/**
	 * Generates and returns the effect code for this catalogrule 
	 *
	 * @return string
	 */
	public function getEffect() {
		if ($this->getPointsCatalogruleSimpleAction() == 'by_percent') {
		    $effect = '-'. $this->getPointsCatalogruleDiscountAmount() .'%';
		}
		else if ($this->getPointsCatalogruleSimpleAction() == 'by_fixed') {
		    $effect = '-'. $this->getPointsCatalogruleDiscountAmount();
		}
		else if ($this->getPointsCatalogruleSimpleAction() == 'to_percent') {
		    $effect = $this->getPointsCatalogruleDiscountAmount() .'%';
		}
		else if ($this->getPointsCatalogruleSimpleAction() == 'to_fixed') {
		    $effect = $this->getPointsCatalogruleDiscountAmount();
		} else {
			$effect = null;
		}
		return $effect;
	}
	
	/**
	 * Generates and returns a hash that contains:
	 * - the Points amount
	 * - the points currency id
	 * - the rule id
	 * - applicable quantity
	 * - effect
	 *
	 * @return array : a map of the above mentioned fields
	 */
	public function getHashEntry($applicable_quantity=0) {
		$item_rule = array(
		    self::POINTS_AMT => $this->getPointsAmount(),
		    self::POINTS_CURRENCY_ID => $this->getPointsCurrencyId(),
		    self::POINTS_RULE_ID => $this->getId(),
		    self::POINTS_APPLICABLE_QTY => $applicable_quantity,
		    self::POINTS_EFFECT => $this->getEffect()
		);
		return $item_rule;
	}
}