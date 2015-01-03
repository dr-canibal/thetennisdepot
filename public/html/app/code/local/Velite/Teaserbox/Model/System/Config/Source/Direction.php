<?php

class Velite_Teaserbox_Model_System_Config_Source_Direction
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'horizontal', 'label'=>Mage::helper('teaserbox')->__('Horizontal')),
            array('value'=>'vertical', 'label'=>Mage::helper('teaserbox')->__('Vertical'))
        );
    }
}
