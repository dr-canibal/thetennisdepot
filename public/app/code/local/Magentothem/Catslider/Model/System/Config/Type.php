<?php

class Magentothem_Catslider_Model_System_Config_Type
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'bestseller', 'label'=>Mage::helper('adminhtml')->__('Bestseller')),
            array('value' => 'featured', 'label'=>Mage::helper('adminhtml')->__('Featured Products')),
            array('value' => 'mostviewed', 'label'=>Mage::helper('adminhtml')->__('Most Viewed')),
            // mostviewed <=> popular
            //array('value' => 'popular', 'label'=>Mage::helper('adminhtml')->__('Popular Product')),
            array('value' => 'newproduct', 'label'=>Mage::helper('adminhtml')->__('New Products')),
            //newproduct <=> Recently Added
            //array('value' => 'recentlyadded', 'label'=>Mage::helper('adminhtml')->__('Recently Added')),
            array('value' => 'random', 'label'=>Mage::helper('adminhtml')->__('Random Products')),
            array('value' => 'saleproduct', 'label'=>Mage::helper('adminhtml')->__('Sale Products')),
            //specialproduct => saleproduct
            array('value' => 'specialproduct', 'label'=>Mage::helper('adminhtml')->__('Special Products'))
        );
    }
}