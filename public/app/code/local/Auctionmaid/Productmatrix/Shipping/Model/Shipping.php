<?php
/**
 * Magento
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
 * @category   Mage
 * @package    Mage_Shipping
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Auctionmaid_Productmatrix_Shipping_Model_Shipping extends Mage_Shipping_Model_Shipping
{


    /**
     * Retrieve all methods for supplied shipping data
     *
     * @todo make it ordered
     * @param Mage_Shipping_Model_Shipping_Method_Request $data
     * @return Mage_Shipping_Model_Shipping
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
    	
    	$mergeResults = Mage::getStoreConfig('carriers/productmatrix/merge_ups');
    	
    	if (!$mergeResults) {
    		// call the default - will help with those not needing this functionality
    		return parent::collectRates($request);
    	}
        if (!$request->getOrig()) {
            $request
                ->setCountryId(Mage::getStoreConfig('shipping/origin/country_id', $request->getStore()))
                ->setRegionId(Mage::getStoreConfig('shipping/origin/region_id', $request->getStore()))
                ->setCity(Mage::getStoreConfig('shipping/origin/city', $request->getStore()))
                ->setPostcode(Mage::getStoreConfig('shipping/origin/postcode', $request->getStore()));
        }

        $limitCarrier = $request->getLimitCarrier();
        if (!$limitCarrier) {
            $carriers = Mage::getStoreConfig('carriers', $request->getStoreId());

            foreach ($carriers as $carrierCode=>$carrierConfig) {

            	if ($mergeResults) {
	                if ($carrierCode=='ups') {
	                	$upsResult = $this->collectSpecialCarrierRates($carrierCode, $request);
	                } else if ($carrierCode=='productmatrix') {
	                	$productResult = $this->collectSpecialCarrierRates($carrierCode, $request);
	                } else {
	                	$this->collectCarrierRates($carrierCode, $request);
	                }   
            	}   else {
	                $this->collectCarrierRates($carrierCode, $request);
            	}     
            }
        } else {
            if (!is_array($limitCarrier)) {
                $limitCarrier = array($limitCarrier);
            }
            $upsResult;
        	$productResult;
            foreach ($limitCarrier as $carrierCode) {
                $carrierConfig = Mage::getStoreConfig('carriers/'.$carrierCode, $request->getStoreId());
                if (!$carrierConfig) {
                    continue;
                }
                if ($mergeResults) {
                
	                if ($carrierCode=='ups') {
	                	$upsResult = $this->collectSpecialCarrierRates($carrierCode, $request);
	                } else if ($carrierCode=='productmatrix') {
	                	$productResult = $this->collectSpecialCarrierRates($carrierCode, $request);
	                } else {
	                	$this->collectCarrierRates($carrierCode, $request);
	                }
                }  else {
	                $this->collectCarrierRates($carrierCode, $request);
            	} 
            }
        }

        if ($mergeResults) {
        	$this->mergeUpsProductResults($upsResult,$productResult);
        }


        return $this;
    }



    public function collectSpecialCarrierRates($carrierCode, $request)
    {

        $carrier = $this->getCarrierByCode($carrierCode, $request->getStoreId());
        if (!$carrier) {
            return $this;
        }
        $result = $carrier->checkAvailableShipCountries($request);
        if (false !== $result && !($result instanceof Mage_Shipping_Model_Rate_Result_Error) &&
        	method_exists($carrier,'proccessAdditionalValidation')) {
            $result = $carrier->proccessAdditionalValidation($request);
        }
        /*
        * Result will be false if the admin set not to show the shipping module
        * if the devliery country is not within specific countries
        */

        if (false !== $result){
            if (!$result instanceof Mage_Shipping_Model_Rate_Result_Error) {
            	$result = $carrier->collectRates($request);
            }
            // sort rates by price
            if (method_exists($result, 'sortRatesByPrice')) {
                $result->sortRatesByPrice();
            }
        }
        return $result;
    }





    /**
     *
     * @param $upsResult
     * @param $productResult
     * @return unknown_type
     */
    private function mergeUpsProductResults($upsResult, $productResult)
    {

    	if (!empty($upsResult) &&
  			is_array($upsResult->getAllRates()) && count($upsResult->getAllRates()))
  		{


	  		if (!empty($productResult) &&
	  			is_array($productResult->getAllRates()) && count($productResult->getAllRates()))
	  		{

			    	$newUpsRates=$upsResult->getAllRates();

			    	foreach ($newUpsRates as $key=>$result)
			    	{

			    		$found=false;
			    		foreach ($productResult->getAllRates() as $data) {
			    			if ($result['method_title']==$data['method_title']) {
								// add to existing
								$newUpsRates[$key]['price']+=$data['price'];
								$found=true;
								break;
							}
			    		}
			    		if (!$found) {
			    			// no match so remove
			    			$newUpsRates[$key]="";
			    		}
			    	}
			    	// unset here so we dont upset the apple cart
			    	foreach ($newUpsRates as $key=>$result) {
			    		if (empty($newUpsRates[$key])) {
			    			unset($newUpsRates[$key]);
			    		}
			    	}

			    	$upsResult->reset();

			    	foreach ($newUpsRates as $rate) {
			    		$upsResult->append($rate);
			    	}


	  		}


	    	$this->getResult()->append($upsResult);
  		}

    }
}
