<?php

class AW_Zblocks_Model_Zblocks extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('zblocks/zblocks');
    }
}