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
 * @copyright  Copyright (c) 2008 Auction Maid (http://www.auctionmaid.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Karen Baker <enquiries@auctionmaid.com>
*/


class Auctionmaid_Productmatrix_Model_Carrier_Productmatrix
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    protected $_code = 'productmatrix';
    protected $_default_condition_name = 'per_package';

    protected $_conditionNames = array();
    

    public function __construct()
    {
        parent::__construct();
        foreach ($this->getCode('condition_name') as $k=>$v) {
            $this->_conditionNames[] = $k;
        }
    }

    /**
     * Enter description here...
     *
     * @param Mage_Shipping_Model_Rate_Request $data
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $request->setConditionName($this->getConfigData('condition_name') ? $this->getConfigData('condition_name') : $this->_default_condition_name);
        if ($this->getConfigFlag('custom_sorting')) {
        	$result = Mage::getModel('productmatrix_shipping/rate_result');
        } else {
			$result = Mage::getModel('shipping/rate_result');
        }
        
        $freeBoxes = 0;
        $found=false;
        $total=0;
        
     	try {
	        foreach ($request->getAllItems() as $item) {
	        	if ($item->getFreeShipping() && $item->getProductType()!= Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL ) {
	                    $freeBoxes+=$item->getQty();
	            }
	            if ($item->getProductType() != Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL &&
	                 $item->getProductType() != 'downloadable') {
	                    $total+= $item->getBaseRowTotal();
	                    $found=true;
	           	}
	        }
        } catch (Exception $e) {
        	// this is really bad programmtically but we are going to ignore this, as in some cases there wont be
        	// anything in getAllItems.
        }
        if ($found && $this->getConfigFlag('remove_virtual')) {
        	// this fixes bug in Magento where package value is not set correctly, but at expense of sacrificing discounts
        	$request->setPackageValue($total);
        }
        
        $this->setFreeBoxes($freeBoxes);
        $freeFound=false;
        if ($request->getFreeShipping() === true || $request->getPackageQty() == $this->getFreeBoxes()) {
        	
		  	$method = Mage::getModel('shipping/rate_result_method');
			$method->setCarrier('productmatrix');
			$method->setCarrierTitle($this->getConfigData('title'));
			$method->setMethod(strtolower('productmatrix_'.$this->getConfigData('free_shipping_text')));
			$method->setPrice('0.00');
			$method->setMethodTitle($this->getConfigData('free_shipping_text'));			
			$result->append($method);
			$freeFound=true;
		}
		
     	$ratearray = $this->getRate($request);
  
     	if (empty($ratearray)) {
     		if (!($freeFound) && $this->getConfigData('specificerrmsg')!='') {
	            $error = Mage::getModel('shipping/rate_result_error');
	            $error->setCarrier('productmatrix');
	            $error->setCarrierTitle($this->getConfigData('title'));
	            //$error->setErrorMessage($errorText);
	            $error->setErrorMessage($this->getConfigData('specificerrmsg'));
	            $result->append($error);
     		}
			return $result;
     		
     	}
     	$max_shipping_cost=$this->getConfigData('max_shipping_cost');
     	
	    foreach ($ratearray as $rate)
		{
		   if (!empty($rate) && $rate['price'] >= 0) {
			  $method = Mage::getModel('shipping/rate_result_method');

				$method->setCarrier('productmatrix');
				$method->setCarrierTitle($this->getConfigData('title'));
			
				$price=$rate['price'];
				if (!empty($max_shipping_cost) && $max_shipping_cost>0) {
					if ($price>$max_shipping_cost) {
						$price=$max_shipping_cost;
					}
				} 
				if (!$this->getConfigData('apply_handling') && $price==0) {
					$shippingPrice = $price;
				} else {
					$shippingPrice = $this->getFinalPriceWithHandlingFee($price);
				} 
				if ($price==0  && $this->getConfigData('zero_shipping_text')!='') {
					$modifiedName=str_replace(" ","_",$this->getConfigData('zero_shipping_text'));
					$method->setMethodTitle($this->getConfigData('zero_shipping_text'));
				} else {
					$modifiedName=str_replace(" ","_",$rate['delivery_type']);
					$method->setMethodTitle(Mage::helper('shipping')->__($rate['delivery_type']));
				}
				
				$method->setMethod(strtolower($modifiedName));
				
				$method->setPrice($shippingPrice);
				$method->setCost($rate['cost']);
				$method->setDeliveryType($rate['delivery_type']);

				$result->append($method);
			}
		}
        return $result;
    }
    
    public function getCode($type, $code='')
    {
        $codes = array(

            'condition_name'=>array(
                'per_item_bare' => Mage::helper('shipping')->__('Per Item Bare Totalling'),
                'per_item' => Mage::helper('shipping')->__('Per Item Totalling'),
                'per_product' => Mage::helper('shipping')->__('Per Product Totalling'),
        	'per_package'  => Mage::helper('shipping')->__('Per Package Totalling'),
            	'highest'  => Mage::helper('shipping')->__('Highest Price Totalling'),
            	'lowest'  => Mage::helper('shipping')->__('Lowest Price Totalling'),
            	'order'  => Mage::helper('shipping')->__('Defined Order Totalling'),
        ),

            'condition_name_short'=>array(
                'per_item_bare' => Mage::helper('shipping')->__('Per Item Bare Totalling'),
                'per_item' => Mage::helper('shipping')->__('Per Item Totalling'),
                'per_product' => Mage::helper('shipping')->__('Per Product Totalling'),
            	'per_package'  => Mage::helper('shipping')->__('Per Package Totalling'),
            	'highest'  => Mage::helper('shipping')->__('Highest Price Totalling'),
        		'lowest'  => Mage::helper('shipping')->__('Lowest Price Totalling'),
        		'order'  => Mage::helper('shipping')->__('Defined Order Totalling'),
        ),

        );

        if (!isset($codes[$type])) {
            throw Mage::exception('Mage_Shipping', Mage::helper('shipping')->__('Invalid Product Matrix code type: %s', $type));
        }

        if (''===$code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
            throw Mage::exception('Mage_Shipping', Mage::helper('shipping')->__('Invalid Product Matrix  code for type %s: %s', $type, $code));
        }

        return $codes[$type][$code];
    }

    public function getRate(Mage_Shipping_Model_Rate_Request $request)
    {
        return Mage::getResourceModel('productmatrix_shipping/carrier_productmatrix')->getNewRate($request,
        $this->getConfigFlag('star_include_all'),$this->getConfigFlag('zip_range'));
    }

    
    
    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
     	$collection = Mage::getResourceModel('productmatrix_shipping/carrier_productmatrix_collection');
       $collection->load();
       $allowedMethods=array();
       $deliveryTypes=array();
       foreach ($collection->getItems() as $item) {
       		$newDelType=strtolower(str_replace(" ","_",$item->getData('delivery_type')));
       	    if (!in_array($newDelType,$deliveryTypes)) {
       			$deliveryTypes[]=$newDelType;
	       		$allowedMethods[$newDelType] = $item->getData('delivery_type');
	       		
       		}
       }
       if ($this->getConfigData('free_shipping_text')!='') {
       	$allowedMethods[strtolower(str_replace(" ","_",$this->getConfigData('free_shipping_text')))]=
       		$this->getConfigData('free_shipping_text');
       }
       
       return $allowedMethods;    
    }
    
 

}
