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
 * Manage Promo Catalog Edit
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Manage_Promo_Catalog_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'manage_promo_catalog';
        $this->_blockGroup = 'rewards';
        
        parent::__construct();

        $this->_updateButton('save', 'label', Mage::helper('catalogrule')->__('Save Rule'));
        $this->_updateButton('delete', 'label', Mage::helper('catalogrule')->__('Delete Rule'));

        $this->_addButton('save_apply', array(
            'class'=>'save',
            'label'=>Mage::helper('catalogrule')->__('Save and Apply'),
            'onclick'=>"$('rule_auto_apply').value=1; editForm.submit()",
        ));
        
        $give_points_action = 				TBT_Rewards_Model_Catalogrule_Actions::GIVE_POINTS_ACTION;
        $give_by_amount_spent_action = 		TBT_Rewards_Model_Catalogrule_Actions::GIVE_BY_AMOUNT_SPENT_ACTION;
        $give_by_profit_action = 			TBT_Rewards_Model_Catalogrule_Actions::GIVE_BY_PROFIT_ACTION;
        $deduct_points_action = 			TBT_Rewards_Model_Catalogrule_Actions::DEDUCT_POINTS_ACTION;
        $deduct_by_amount_spent_action = 	TBT_Rewards_Model_Catalogrule_Actions::DEDUCT_BY_AMOUNT_SPENT_ACTION;
        $this->_formInitScripts[] = "
	        function toggleActionsSelect(action) {  
	    		if(action == '$give_points_action' || action == '$deduct_points_action') { 
	    			$('rule_points_amount_step').up().up().hide(); 
	    		} else if(action == '$give_by_amount_spent_action' || action == '$deduct_by_amount_spent_action' || action == '$give_by_profit_action' ) { 
	    			$('rule_points_amount_step').up().up().show(); 
	    		} else {
	    			$('rule_points_amount_step').up().up().show(); 
	    		}
	    	}
	    ";
        $this->_formInitScripts[] = "toggleActionsSelect($('rule_points_action').value)";
	    	
        $no_discount = "";
	    $this->_formInitScripts[] = "
	        function toggleDiscountActionsSelect(action) {  
	    		if(action == '$no_discount') { 
	    			$('rule_points_catalogrule_discount_amount').up().up().hide();
	    			if($('rule_points_catalogrule_discount_amount').value == '') {
	    				$('rule_points_catalogrule_discount_amount').value = 0;
    				}
	    		} else {
	    			$('rule_points_catalogrule_discount_amount').up().up().show(); 
	    		}
	    	}
    	";
        $this->_formInitScripts[] = "toggleDiscountActionsSelect($('rule_points_catalogrule_simple_action').value)";
    }

    public function getHeaderText()
    {
        $rule = $this->_getCatalogRule();
        if ($rule->getRuleId()) {
            return Mage::helper('catalogrule')->__("Edit Rule '%s'", $this->htmlEscape($rule->getName()));
        }
        else {
            return Mage::helper('catalogrule')->__('New Rule');
        }
    }

    /**
     * Fetches the currently open catalogrule.
     *
     * @return TBT_Rewards_Model_Catalogrule_Rule
     */
    protected function _getCatalogRule() {
    	return Mage::registry('current_promo_catalog_rule');
    }

    /**
     * <<override>>
     */
    public function getBackUrl()
    {
        if($this->getRequest()->getParam('type')) {
            $typeId = $this->getRequest()->getParam('type');
        }
        if($this->_getCatalogRule()->getRuleTypeId()) {
            $typeId = $this->_getCatalogRule()->getRuleTypeId();
        }
        return $this->getUrl('*/*/', array('type'=>$typeId));
    }

}
