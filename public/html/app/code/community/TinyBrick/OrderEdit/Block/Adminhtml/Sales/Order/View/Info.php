<?php 
/**
 * TinyBrick Commercial Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the TinyBrick Commercial Extension License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.delorumcommerce.com/license/commercial-extension
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@tinybrick.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this package to newer
 * versions in the future. 
 *
 * @category   TinyBrick
 * @package    TinyBrick_LightSpeed
 * @copyright  Copyright (c) 2010 TinyBrick Inc. LLC
 * @license    http://store.delorumcommerce.com/license/commercial-extension
 */
class TinyBrick_OrderEdit_Block_Adminhtml_Sales_Order_View_Info extends Mage_Adminhtml_Block_Sales_Order_View_Info
{	
	public function getCountryList()
    {
		return Mage::getResourceModel('directory/country_collection')
				->addFieldToFilter('country_id', array('in' => explode(",", Mage::getStoreConfig('general/country/allow'))))
				->toOptionArray();
    }
    
    public function getStateList()
    {
    	$states = Mage::getResourceModel('directory/region_collection')
    			->addFieldToFilter('country_id', array('in' => explode(",", Mage::getStoreConfig('general/country/allow'))))
    			->setOrder('country_id', 'DESC')
    			->setOrder('default_name', 'ASC')
    			->load();
		$states = $states->getData();
		return $states;
    }
    
}