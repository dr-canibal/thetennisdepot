<?php

/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 * 
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Zblocks
 * @copyright  Copyright (c) 2010-2011 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */
class AW_Zblocks_Model_Mysql4_Zblocks_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('zblocks/zblocks');
    }
    
    /**
     * Filters collection by store ids
     * @param $stores
     * @return AW_Featured_Model_Mysql4_Blocks_Collection
     */
    public function addStoreFilter($stores = null, $breakOnAllStores = false) {
        $_stores = array(Mage::app()->getStore()->getId());
        if(is_string($stores)) $_stores = explode(',', $stores);
        if(is_array($stores)) $_stores = $stores;
        if(is_integer($stores)) $_stores = array((string)$stores);
        if(!in_array('0', $_stores))
            array_push($_stores, '0');
        if($breakOnAllStores && $_stores == array(0)) return $this;
        $_sqlString = '(';
        $i = 0;
        foreach($_stores as $_store) {
            $_sqlString .= sprintf('find_in_set(%s, store_ids)', $this->getConnection()->quote($_store));
            if(++$i < count($_stores))
                $_sqlString .= ' OR ';
        }
        $_sqlString .= ')';
        $this->getSelect()->where($_sqlString);

        return $this;
    }
    
    public function addExcludeStoreFilter($stores) {
        if(is_string($stores)) $stores = explode(',', $stores);
        if(is_integer($stores)) $stores = array($stores);
        foreach($stores as $store)
            $this->getSelect()->where(sprintf('NOT find_in_set(%s, store_ids)', $this->getConnection()->quote($store)));
        return $this;
    }

    /*
     * Covers original bug in Varien_Data_Collection_Db
     */
    public function getSelectCountSql(){
        $this->_renderFilters();

        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(Zend_Db_Select::COLUMNS);
        $countSelect->reset(Zend_Db_Select::GROUP);
        $countSelect->reset(Zend_Db_Select::HAVING);

        $countSelect->from('', 'COUNT(*)');
        return $countSelect;
    }
}
