<?php

class Ebizmarts_Customerprofile_Model_Gendertype extends Mage_Core_Model_Abstract
{

    public function toOptionArray()
    {
        return array(
            array('value'=>'male', 'label'=>Mage::helper('customer')->__('Male')),
            array('value'=>'female', 'label'=>Mage::helper('customer')->__('Female')),
        );
    }


}