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
 * Manage Promo Quote Edit Tab Abstract
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
abstract class TBT_Rewards_Block_Manage_Promo_Quote_Edit_Tab_Abstract extends Mage_Adminhtml_Block_Widget_Form
{

	/**
	 * @param Varien_Data_Form &$form
	 * @param TBT_Rewards_Model_Salesrule_Rule &$model
	 */ 
    protected function _getPriceActionFieldset(Varien_Data_Form &$form, TBT_Rewards_Model_Salesrule_Rule &$model) {
    
    	$model = $this->_getRule();
      	if($this->_isRedemptionType()) {
    		$field_title = 'Update prices using the following information';
    		$actions = array(
                'by_percent' => Mage::helper('salesrule')->__('Percent of product price discount'),
                'by_fixed' => Mage::helper('salesrule')->__('Fixed amount discount'),
                'cart_fixed' => Mage::helper('salesrule')->__('Fixed amount discount for whole cart'),
                'buy_x_get_y' => Mage::helper('salesrule')->__('Buy X get Y free (discount amount is Y)'),
            );
            $apply_caption = 'Apply';
    	} else {
    		$field_title = 'ADDITIONALLY, update prices using the following information - even if the customer does not spend any points';
    		$actions = array(
                '' => Mage::helper('rewards')->__('No Additional Discount--'),
                'by_percent' => Mage::helper('salesrule')->__('Percent of product price discount'),
                'by_fixed' => Mage::helper('salesrule')->__('Fixed amount discount'),
                'cart_fixed' => Mage::helper('salesrule')->__('Fixed amount discount for whole cart'),
                'buy_x_get_y' => Mage::helper('salesrule')->__('Buy X get Y free (discount amount is Y)'),
            );
            $apply_caption = 'Apply Additional Discount';
    	}
    	
        $fieldset = $form->addFieldset('action_fieldset', array('legend'=>Mage::helper('salesrule')->__($field_title)));

        $fieldset->addField('simple_action', 'select', array(
            'label'     => Mage::helper('salesrule')->__($apply_caption),
            'name'      => 'simple_action',
            'options'   => $actions,
        	'onchange'	=> 'toggleDiscountActionsSelect(this.value)',
        ));
        
        $fieldset->addField('discount_amount', 'text', array(
            'name' => 'discount_amount',
            'required' => true,
            'class' => 'validate-not-negative-number',
            'label' => Mage::helper('salesrule')->__('Discount amount'),
        ));
        $model->setDiscountAmount($model->getDiscountAmount()*1);

        $fieldset->addField('discount_qty', 'text', array(
            'name' => 'discount_qty',
            'label' => Mage::helper('salesrule')->__('Maximum Qty Discount is Applied to'),
        ));
        $model->setDiscountQty($model->getDiscountQty()*1);

        $fieldset->addField('discount_step', 'text', array(
            'name' => 'discount_step',
            'label' => Mage::helper('salesrule')->__('Discount Qty Step (Buy X)'),
        ));

        $fieldset->addField('simple_free_shipping', 'select', array(
            'label'     => Mage::helper('salesrule')->__('Free shipping'),
            'title'     => Mage::helper('salesrule')->__('Free shipping'),
            'name'      => 'simple_free_shipping',
            'options'    => array(
                0 => Mage::helper('salesrule')->__('No'),
                Mage_SalesRule_Model_Rule::FREE_SHIPPING_ITEM => Mage::helper('salesrule')->__('For matching items only'),
                Mage_SalesRule_Model_Rule::FREE_SHIPPING_ADDRESS => Mage::helper('salesrule')->__('For shipment with matching items'),
            ),
        ));

        $fieldset->addField('stop_rules_processing', 'select', array(
            'label'     => Mage::helper('salesrule')->__('Stop further rules processing'),
            'title'     => Mage::helper('salesrule')->__('Stop further rules processing'),
            'name'      => 'stop_rules_processing',
            'options'    => array(
                '1' => Mage::helper('salesrule')->__('Yes'),
                '0' => Mage::helper('salesrule')->__('No'),
            ),
        ));
        
        return $this;
    	
    }
    
    
    protected function _getApplyToActionFieldset(&$form, &$model) {
       	$renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('promo/fieldset.phtml');

        $renderer->setNewChildUrl($this->getUrl('*/manage_promo_quote/newActionHtml/form/rule_actions_fieldset'));
        
        
        $fieldset = $form->addFieldset('actions_fieldset', array(
            'legend'=>Mage::helper('salesrule')->__('Apply the rule only to cart items matching the following conditions (leave blank for all items)')
        ))->setRenderer($renderer);

        $fieldset->addField('actions', 'text', array(
            'name' => 'actions',
            'label' => Mage::helper('salesrule')->__('Apply to'),
            'title' => Mage::helper('salesrule')->__('Apply to'),
            'required' => true,
        ))->setRule($model)->setRenderer(Mage::getBlockSingleton('rule/actions'));

        return $this;
    }
    
    
    protected function _getPointsActionFieldset(&$form)
    {
    	$model = $this->_getRule();
      	if($this->_isRedemptionType()) {
    		$field_caption = 'Customer redeems the following points';
    		$action_caption = "Customer spends";
    		$points_max_qty_caption = "Maximum Redeemable Points";
    	} else {
    		$field_caption = 'Reward With Points';
    		$action_caption = "Customer Spending Style";
    		$points_max_qty_caption = 'Maximum Distributed Points (0 for unlimited)';
    	}
        $fieldset = $form->addFieldset(
        	'points_action_fieldset', 
        	array(	'legend' => Mage::helper('rewards')->__($field_caption))
        );
        
        $fieldset->addField('points_action', 'select', array(
            'label'     => Mage::helper('rewards')->__($action_caption),
            'name'      => 'points_action',
            'options'    => $this->_getPointsActionOptions(),
        	'onchange'	=>	'toggleActionsSelect(this.value)',
        ));
    	
        
        // SETUP OUR CURRENCY SELECTION
        $currencyData = Mage::helper('rewards/currency')->getAvailCurrencies();
        if(sizeof($currencyData) > 1) {
        	$currencyDataType = 'select';
        	$currencyValueType = 'options';
        } elseif(sizeof($currencyData) == 1) {
	        $currencyData = array_keys($currencyData);
	        $currencyData = array_pop($currencyData);
        	$currencyDataType = 'hidden';
        	$currencyValueType = 'value';
            $model->setPointsCurrencyId($currencyData);
        } else {
        	throw new Exception("No currency specifed.");
        }
        
        $fieldset->addField('points_currency_id', $currencyDataType, array(
            'label'     => Mage::helper('salesrule')->__('Points Currency'),
            'title'     => Mage::helper('salesrule')->__('Points Currency'),
            'name'      => 'points_currency_id',
            $currencyValueType    => $currencyData,
        ));
       
        $fieldset->addField('points_amount', 'text', array(
            'name' => 'points_amount',
            'required' => false,
            'class' => 'validate-not-negative-number',
            'label' => Mage::helper('salesrule')->__('Points Amount (X)')
        ));
        

        $fieldset->addField('points_amount_step', 'text', array(
            'name' => 'points_amount_step',
            'label' => Mage::helper('salesrule')->__('Monetary Step (Y) (in base currency)'),
        ));

        $fieldset->addField('points_qty_step', 'text', array(
            'name' => 'points_qty_step',
            'label' => Mage::helper('salesrule')->__('Quantity Step (Z)'),
        ));

        $fieldset->addField('points_max_qty', 'text', array(
            'name' => 'points_max_qty',
            'label' => Mage::helper('salesrule')->__($points_max_qty_caption),
        ));
        
        return $fieldset;
    }
    protected function _getCurrencyList()
    {
        if (is_null($this->_currencyList)) {
            $this->_currencyList = $this->_getCurrencyModel()->getConfigAllowCurrencies();
        }
        return $this->_currencyList;
    }
    
    protected function _getCurrencyModel()
    {
        if (is_null($this->_currencyModel))
            $this->_currencyModel = Mage::getModel('directory/currency');

        return $this->_currencyModel;
    }
    /**
     * Returns the model for this form.
     *
     * @return TBT_Rewards_Model_Salesrule_Rule
     */
    protected function _getRule() {
        $model = Mage::registry('current_promo_quote_rule');
    	return $model;
    }
    
    /**
     * Fetches the action option array that should be displayed.
     *
     * @return array;
     */
    protected function _getPointsActionOptions() {
    	if($this->_isRedemptionType()) {
    		$options = Mage::getSingleton('rewards/salesrule_actions')->getRedemptionsOptionArray();
    	} else {
    		$options = Mage::getSingleton('rewards/salesrule_actions')->getDistributionsOptionArray();
    	}
    	return $options;
    }
    
    /**
     * Returns true if this should display redemption
     *
     * @return boolean
     */
    protected function _isRedemptionType() {
    	if($ruleTypeId = $this->_getRule()->getRuleTypeId()) {
    		return $this->_getRule()->isRedemptionRule();
    	}
    	if($type = (int)$this->getRequest()->getParam('type')) {
    		return $type === TBT_Rewards_Helper_Rule_Type::REDEMPTION;
    	}
    	Mage::getSingleton('rewards/session')->addError(
    		"Could not determine rule type in ".
    		"Quote/Edit/Tab/Actions so assumed distribution.");
    	return true;
    }
    /**
     * Returns true if this should display distribution
     *
     * @return boolean
     */
    protected function _isDistributionType() {
    	if($ruleTypeId = $this->_getRule()->getRuleTypeId()) {
    		return $this->_getRule()->isDistributionRule();
    	}
    	if($type = (int)$this->getRequest()->getParam('type')) {
    		return $type === TBT_Rewards_Helper_Rule_Type::DISTRIBUTION;
    		
    	}
    	Mage::getSingleton('rewards/session')->addError(
    		"Could not determine rule type in ".
    		"Quote/Edit/Tab/Actions so assumed distribution.");
    	return true;
    }
    
    
    
    
}