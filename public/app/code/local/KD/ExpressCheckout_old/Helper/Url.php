<?php

class KD_ExpressCheckout_Helper_Url extends Mage_Checkout_Helper_Url
{
    /**
     * Retrieve checkout url
     *
     * @return string
     */
    public function getCheckoutUrl()
    {
        return $this->_getUrl('expresscheckout', array('_secure'=>true));
    }
}
