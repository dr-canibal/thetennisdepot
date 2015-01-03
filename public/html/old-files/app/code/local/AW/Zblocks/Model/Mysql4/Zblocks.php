<?php

class AW_Zblocks_Model_Mysql4_Zblocks extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the zblocks_id refers to the key field in your database table.
        $this->_init('zblocks/zblocks', 'zblocks_id');
    }
}