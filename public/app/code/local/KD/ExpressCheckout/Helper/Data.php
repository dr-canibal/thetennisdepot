<?php

class KD_ExpressCheckout_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_agreements = null;

    /**
     * Get express checkout availability
     *
     * @return bool
     */
    public function canExpressCheckout()
    {
        return (bool)Mage::getStoreConfig('expresscheckout/general/enabled');
    }

    public function getRequiredAgreementIds()
    {
        if (is_null($this->_agreements)) {
            if (!Mage::getStoreConfigFlag('expresscheckout/agreements/enabled')) {
                $this->_agreements = array();
            } else {
                $this->_agreements = Mage::getModel('checkout/agreement')->getCollection()
                    ->addStoreFilter(Mage::app()->getStore()->getId())
                    ->addFieldToFilter('is_active', 1)
                    ->getAllIds();
            }
        }
        return $this->_agreements;
    }

    public function isAllowedGuestCheckout()
    {
        return Mage::getStoreConfig('expresscheckout/general/guest_checkout');
    }

    public function getIsSubscribed()
    {
        $customerSession = Mage::getSingleton('customer/session');
        if (!$customerSession->isLoggedIn()) {
            return false;
        }
        return Mage::getModel('newsletter/subscriber')->getCollection()
            ->useOnlySubscribed()
            ->addStoreFilter(Mage::app()->getStore()->getId())
            ->addFieldToFilter('subscriber_email', $customerSession->getCustomer()->getEmail())
            ->getAllIds();
    }

    public function canShowNewsletter()
    {
        if (!Mage::getStoreConfig('expresscheckout/general/newsletter_checkbox')) {
            return false;
        }

        $customerSession = Mage::getSingleton('customer/session');
        if (!$customerSession->isLoggedIn() && !Mage::getStoreConfig('newsletter/subscription/allow_guest_subscribe')) {
            return false;
        }

        return !Mage::helper('expresscheckout')->getIsSubscribed();
    }
}