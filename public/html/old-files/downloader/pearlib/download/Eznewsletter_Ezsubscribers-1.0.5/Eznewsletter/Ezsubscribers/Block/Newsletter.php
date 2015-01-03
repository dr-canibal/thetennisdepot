<?php
class Eznewsletter_Ezsubscribers_Block_Newsletter extends Mage_Checkout_Block_Onepage_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('eznewsletter/checkout.phtml');
    }

    public function isChecked()
    {
        return (bool) $this->getCheckout()->getCustomerIsSubscribed();
    }
}