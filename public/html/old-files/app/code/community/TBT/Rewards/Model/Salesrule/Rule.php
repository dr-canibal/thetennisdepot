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
 * Sales Rule Rule
 *
 * TODO: Add REWARDS getResourceCollection functionality to this model and the Rewards Catalogrule model
 * 
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Salesrule_Rule extends Mage_SalesRule_Model_Rule
{
	const POINTS_CURRENCY_ID = 'points_currency_id';
	const POINTS_AMT = 'points_amt';
	const POINTS_EFFECT = 'effect';
	const POINTS_RULE_ID = 'rule_id';
	const POINTS_APPLICABLE_QTY = 'applicable_qty';
	/**
	 * This number of times the customer is using the redemption rule
	 * to spend points and earn a discount.
	 * For example, $1 off for every 25 points spent => # times 25 points is spent
	 * is the 'uses' of that rule.
	 *
	 */
	const POINTS_USES = 'uses';

	
	/**
	 * Loads in a salesrule and returns a points salesrule
	 *
	 * @param Mage_SalesRule_Model_Rule $salesrule
	 * @return TBT_Rewards_Model_Salesrule_Rule
	 */
	public static function wrap(Mage_SalesRule_Model_Rule $salesrule) {
       	$pointsrule = Mage::getModel('rewards/salesrule_rule')->setData($salesrule->getData())->setId($salesrule->getId());
		return $pointsrule;
	}
	
	/**
	 * Returns true if this a redemption rule
	 *
	 * @return boolean
	 */
	public function isRedemptionRule() {
		$ruleActionSing = Mage::getSingleton('rewards/salesrule_actions');
		return $ruleActionSing->isRedemptionAction($this->getPointsAction());
	}
	/**
	 * Returns true if this a distribution rule
	 *
	 * @return boolean
	 */
	public function isDistributionRule() {
		$ruleActionSing = Mage::getSingleton('rewards/salesrule_actions');
		return $ruleActionSing->isDistributionAction($this->getPointsAction());
	}
	/**
	 * Returns the rule time id
	 *
	 * @return int
	 */
	public function getRuleTypeId() {
		$ruleActionSing = Mage::getSingleton('rewards/salesrule_actions');
		return $ruleActionSing->getRuleTypeId($this->getPointsAction());
	}
	
	/**
	 * Fetches a list of IDs for all SALESRULE rules that
	 * have a points action
	 *
	 * @return array
	 */
	public function getPointsRuleIds() {
		$ids = Mage::getModel('salesrule/rule')->getCollection()
            ->addFieldToFilter("points_action", array('neq' => ''))
            ->getAllIds();
        return $ids;
	}
	
	/**
	 * True if this is a valid points salesrule
	 *
	 * @return boolean
	 */
	public function isPointsRule() {
		$paction = $this->getPointsAction();
		$valid = !empty($paction);
        return $valid;
	}

//	This was used to support variable points rules
	/**
//	 * True if this is a valid points salesrule
//	 *
//	 * @return boolean
//	 */
//	public function getIsPointsRule() {
//        return $this->isPointsRule();
//	}
//	public function getPointsSpending() {
//         return Mage::getSingleton('rewards/session')->getPointsSpending();
//    }
//	public function _setPointsSpending($qty) {
//         Mage::getSingleton('rewards/session')->setPointsSpending( $qty);
//         return $this;
//    }
//    
}