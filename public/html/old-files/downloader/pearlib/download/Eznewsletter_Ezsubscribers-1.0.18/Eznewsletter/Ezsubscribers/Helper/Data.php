<?php
class Eznewsletter_Ezsubscribers_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isEnabled()
    {
        return Mage::getStoreConfigFlag('newsletter/ezsubscribers/checkout_enable');
    }

    public function isChecked()
    {
        return Mage::getStoreConfigFlag('newsletter/ezsubscribers/checkout_checked');
    }

    public function isVisibleGuest()
    {
        return Mage::getStoreConfigFlag('newsletter/ezsubscribers/checkout_visible_guest');
    }

    public function isVisibleRegister()
    {
        return Mage::getStoreConfigFlag('newsletter/ezsubscribers/checkout_visible_register');
    }
}
