<?php

class KD_ExpressCheckout_Adminhtml_Model_System_Config_Source_AgreementOutput
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'standard', 'label' => Mage::helper('expresscheckout')->__('Standard')),
            array('value' => 'minimal', 'label' => Mage::helper('expresscheckout')->__('Minimal'))
        );
    }
}
