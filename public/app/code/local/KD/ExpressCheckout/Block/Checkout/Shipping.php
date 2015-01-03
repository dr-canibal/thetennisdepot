<?php

class KD_ExpressCheckout_Block_Checkout_Shipping extends Mage_Checkout_Block_Onepage_Shipping
{
    public function getAddress()
    {
        return $this->getQuote()->getShippingAddress();
    }

    public function getAddressesHtmlSelect($type)
    {
        if ($this->isCustomerLoggedIn()) {
            $options = array();
            foreach ($this->getCustomer()->getAddresses() as $address) {
                $options[] = array(
                    'value'=>$address->getId(),
                    'label'=>$address->format('oneline')
                );
            }

            if ($type=='billing') {
                $address = $this->getCustomer()->getPrimaryBillingAddress();
            } else {
                $address = $this->getCustomer()->getPrimaryShippingAddress();
            }
            if ($address) {
                $addressId = $address->getId();
            } else {
                $addressId = $this->getAddress()->getId();
            }

            $select = $this->getLayout()->createBlock('core/html_select')
                ->setName($type.'_address_id')
                ->setId($type.'-address-select')
                ->setClass('address-select')
                ->setExtraParams('onchange="'.$type.'.newAddress(!this.value)"')
                ->setValue($addressId)
                ->setOptions($options);

            $select->addOption('', Mage::helper('checkout')->__('New Address'));

            return $select->getHtml();
        }
        return '';
    }
}
