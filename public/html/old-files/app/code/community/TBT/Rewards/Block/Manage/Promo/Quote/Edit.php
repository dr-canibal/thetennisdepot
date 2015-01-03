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
 * Manage Promo Quote Edit
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Manage_Promo_Quote_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'manage_promo_quote';
        $this->_blockGroup = "rewards";

        parent::__construct();

        $this->_updateButton('save', 'label', Mage::helper('salesrule')->__('Save Rule'));
        $this->_updateButton('delete', 'label', Mage::helper('salesrule')->__('Delete Rule'));
        
        $give_points_action = TBT_Rewards_Model_Salesrule_Actions::ACTION_GIVE_POINTS;
        $give_by_amount_spent_action = TBT_Rewards_Model_Salesrule_Actions::ACTION_GIVE_BY_AMOUNT_SPENT;
        $give_by_qty_action = TBT_Rewards_Model_Salesrule_Actions::ACTION_GIVE_BY_QTY;
        $deduct_points_action = TBT_Rewards_Model_Salesrule_Actions::ACTION_DEDUCT_POINTS;
        $deduct_by_qty_action = TBT_Rewards_Model_Salesrule_Actions::ACTION_DEDUCT_BY_QTY;
        $deduct_by_amount_spent_action = TBT_Rewards_Model_Salesrule_Actions::ACTION_DEDUCT_BY_AMOUNT_SPENT;
        $discount_by_points_spent = TBT_Rewards_Model_Salesrule_Actions::ACTION_DISCOUNT_BY_POINTS_SPENT;
        $this->_formInitScripts[] = "
	        function toggleActionsSelect(action) {  
	    		if(action == '$give_points_action' || action == '$deduct_points_action') { 
	    			$('rule_points_amount_step').up().up().hide(); 
	    			$('rule_points_qty_step').up().up().hide(); 
	    		} else if(action == '$give_by_amount_spent_action' || action == '$deduct_by_amount_spent_action' || action == '$discount_by_points_spent') { 
	    			$('rule_points_amount_step').up().up().show(); 
	    			$('rule_points_qty_step').up().up().hide(); 
	    		} else if(action == '$give_by_qty_action' || action == '$deduct_by_qty_action') { 
	    			$('rule_points_amount_step').up().up().hide(); 
	    			$('rule_points_qty_step').up().up().show(); 
	    		} else {
	    			$('rule_points_amount_step').up().up().show(); 
	    			$('rule_points_qty_step').up().up().show(); 
	    		}
	    	}
    	";
        $this->_formInitScripts[] = "toggleActionsSelect($('rule_points_action').value)";
        
        $no_discount = "";
	    $this->_formInitScripts[] = "
	        function toggleDiscountActionsSelect(action) {  
	    		if(action == '$no_discount') { 
    				$('rule_discount_amount').up().up().hide();
    				$('rule_discount_qty').up().up().hide();
    				$('rule_discount_step').up().up().hide();
    				$('rule_simple_free_shipping').up().up().hide();
	    			if($('rule_discount_amount').value == '') {
	    				$('rule_discount_amount').value = 0;
    				}
	    		} else {
    				$('rule_discount_amount').up().up().show();
    				$('rule_discount_qty').up().up().show();
    				$('rule_discount_step').up().up().show();
    				$('rule_simple_free_shipping').up().up().show();
	    		}
	    	}
    	";
        $this->_formInitScripts[] = "toggleDiscountActionsSelect($('rule_simple_action').value)";
        #$this->setTemplate('promo/quote/edit.phtml');
    }

    public function getHeaderText()
    {
        $rule = $this->_getRule();
        if ($rule->getRuleId()) {
            return Mage::helper('salesrule')->__("Edit Rule '%s'", $this->htmlEscape($rule->getName()));
        }
        else {
            return Mage::helper('salesrule')->__('New Rule');
        }
    }
    
    public function getProductsJson()
    {
        return '{}';
    }
    

    /**
     * Fetches the currently open salesrule.
     *
     * @return TBT_Rewards_Model_Salesrule_Rule
     */
    protected function _getRule() {
    	return Mage::registry('current_promo_quote_rule');
    }

    /**
     * <<override>>
     */
    public function getBackUrl()
    {
    	if($this->getRequest()->getParam('type')) {
            $typeId = $this->getRequest()->getParam('type');
        }
        if($this->_getRule()->getRuleTypeId()) {
            $typeId = $this->_getRule()->getRuleTypeId();
        }
        return $this->getUrl('*/*/', array('type'=>$typeId));
    }
}
