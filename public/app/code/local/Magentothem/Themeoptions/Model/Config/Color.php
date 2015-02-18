<?php
/*------------------------------------------------------------------------
# Websites: http://www.plazathemes.com/
-------------------------------------------------------------------------*/ 
class Magentothem_Themeoptions_Model_Config_Color
{

    public function toOptionArray()
    {
        return array(
            array('value'=>'cadet_blue', 'label'=>Mage::helper('adminhtml')->__('cadet_blue')),
            array('value'=>'coral', 'label'=>Mage::helper('adminhtml')->__('coral')),
            array('value'=>'cornflower_blue', 'label'=>Mage::helper('adminhtml')->__('cornflower_blue')),
            array('value'=>'green', 'label'=>Mage::helper('adminhtml')->__('green')),
            array('value'=>'light_sea_green', 'label'=>Mage::helper('adminhtml')->__('light_sea_green')),
            array('value'=>'manatee', 'label'=>Mage::helper('adminhtml')->__('manatee')),
            array('value'=>'medium_purple', 'label'=>Mage::helper('adminhtml')->__('medium_purple')), 
            array('value'=>'shamrock', 'label'=>Mage::helper('adminhtml')->__('shamrock')),
            array('value'=>'tumbleweed', 'label'=>Mage::helper('adminhtml')->__('tumbleweed')),
            array('value'=>'watermelon', 'label'=>Mage::helper('adminhtml')->__('watermelon'))
             
        );
    }

}
