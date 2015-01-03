<?php
/**
 * Magento Auctionmaid Shipping Module
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Auctionmaid
 * @package    Auctionmaid_Productmatrix
 * @copyright  Copyright (c) 2009 Auction Maid (http://www.auctionmaid.com)
 * @license    http://www.auctionmaid.com/license/license.txt
 * @author     Karen Baker <enquiries@auctionmaid.com>
*/


class Auctionmaid_Productmatrix_Block_Productmatrix  extends Mage_Core_Block_Template {

   
    public function getShippingRates($package_id=null, $weight=null) 
    {
    	$collection = Mage::getResourceModel('productmatrix_shipping/carrier_productmatrix_collection');

        $collection->setCountryFilter(Mage::getConfig()->getNode('general/country/default', 'store', Mage::app()->getStore()->getCode()));
        if (!empty($package_id)) {
       	 $collection->setPackageId($package_id);
        }
        if (!empty($weight)) {
        	$collection->setWeightRange($weight);
        }
       
    	return $collection->load();
    }
    
    public  function getRow($item) {
    	return $item->getData('dest_country').','.$item->getData('price').','.$item->getData('delivery_type');
    }
    
    public function getPrice($item) {
    	return $item->getData('price');
    }
    
    public function getDeliveryType($item) {
    	return $item->getData('delivery_type');
    }
}