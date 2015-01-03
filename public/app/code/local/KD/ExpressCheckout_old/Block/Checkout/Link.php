<?php

class KD_ExpressCheckout_Block_Checkout_Link extends Mage_Core_Block_Template
{
    public function getCheckoutUrl()
    {
        return $this->getUrl('expresscheckout', array('_secure'=>true));
    }

    public function isDisabled()
    {
        return !Mage::getSingleton('checkout/session')->getQuote()->validateMinimumAmount();
    }

    public function isPossibleExpressCheckout()
    {
        return $this->helper('expresscheckout')->canExpressCheckout();
    }
}
