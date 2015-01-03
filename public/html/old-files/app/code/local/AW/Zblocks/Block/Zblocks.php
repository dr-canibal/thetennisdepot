<?php
class AW_Zblocks_Block_Zblocks extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getZblocks()     
     { 
        if (!$this->hasData('zblocks')) {
            $this->setData('zblocks', Mage::registry('zblocks'));
        }
        return $this->getData('zblocks');
        
    }
}