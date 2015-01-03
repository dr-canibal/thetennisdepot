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
 * Mysql Catalog Rule Rule
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Mysql4_CatalogRule_Rule extends Mage_CatalogRule_Model_Mysql4_Rule
{

//	
//	public function _construct()
//    {    
//        // Note that the rewards_transfer_id refers to the key field in your database table.
//        $this->_init('catalogrule/rule', 'rule_id');
//    }
    
    /**
     *
     * @param   int|string $date
     * @param   int $wId
     * @param   int $gId
     * @param   int $pId
     * @return  array | false	applicable redemption rules hash.
     */
    public function getApplicableRedemptionRewards($date, $wId, $gId, $pId)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select()
            ->from($this->getTable('catalogrule/rule_product_price'), 'rules_hash')
            ->where('rule_date=?', $this->formatDate($date, false))
            ->where('website_id=?', $wId)
            ->where('customer_group_id=?', $gId)
            ->where('product_id=?', $pId);
        $rules_hash = $read->fetchOne($select);
        if($rules_hash) {
    		$rules = Mage::helper('rewards')->unhashIt($rules_hash);
        } else {
        	$rules = array();
        }
        if(!isset($rules['0'])) {
        	$rules = array();
        }
        return $rules;
    }
    
    /**
     * Returns the applicable reward array from the catalog product price table.
     *
     * @param date $date
     * @param int $wId
     * @param int $gId
     * @param int $pId
     * @param int $ruleId
     * @return array | false
     */
    public function getApplicableReward($date, $wId, $gId, $pId, $ruleId) {
    	$applicable_rules = $this->getApplicableRedemptionRewards($date, $wId, $gId, $pId);

    	foreach($applicable_rules as &$applicable_rule) {
    		$applicable_rule = (array)$applicable_rule;
    		if($applicable_rule['rule_id'] == $ruleId) {
    			return $applicable_rule;
    		}
    	}    	
    	return false;    	
    }
}