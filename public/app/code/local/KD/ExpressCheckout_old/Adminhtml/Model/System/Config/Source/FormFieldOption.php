<?php

class KD_ExpressCheckout_Adminhtml_Model_System_Config_Source_FormFieldOption
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'optional', 'label' => Mage::helper('expresscheckout')->__('Optional')),
            array('value' => 'required', 'label' => Mage::helper('expresscheckout')->__('Required')),
            array('value' => 'hidden', 'label' => Mage::helper('expresscheckout')->__('Hidden'))
        );
    }
}
