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
 * Shipping MatrixRates
 *
 * @category   Auctionmaid
 * @package    Auctionmaid_Productmatrix
 * @copyright  Copyright (c) 2008 Auction Maid (http://www.auctionmaid.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Karen Baker <enquiries@auctionmaid.com>
*/
class Auctionmaid_Productmatrix_Model_Mysql4_Carrier_Productmatrix extends Mage_Core_Model_Mysql4_Abstract
{
	
	private $_request;
	private $_zipSearchString;
	private $_table;
	private $_customerGroupCode;
	private $_starIncludeAll;
	private $_minusOne;
	private $_exclusionList;
	private $_structuredItems;
	private $_hasEmptyPackages;
	private $_prioritySet;
	private $_maxPriceSet;
	
	
    protected function _construct()
    {
        $this->_init('shipping/productmatrix', 'pk');
    }

    public function getNewRate(Mage_Shipping_Model_Rate_Request $request,$starIncludeAll=0,$zipRangeSet=0)
    {
		$this->_hasEmptyPackages=false;
    	$this->_prioritySet = false;
        $read = $this->_getReadAdapter();
        $this->_maxPriceSet = false;
        
    	$postcode = $request->getDestPostcode();
        $this->_table = Mage::getSingleton('core/resource')->getTableName('productmatrix_shipping/productmatrix');
    	
		if ($zipRangeSet) {
			#  Want to search for postcodes within a range
			$zipSearchString = $read->quoteInto(" AND dest_zip<=? ", $postcode).
								$read->quoteInto(" AND dest_zip_to>=? )", $postcode);
		} else {
			$zipSearchString = $read->quoteInto(" AND ? LIKE dest_zip )", $postcode);
		}
        
        // make global as used all around
        $this->_request=$request;
        $this->_zipSearchString=$zipSearchString;
        $this->_starIncludeAll=$starIncludeAll;
        
  
		$items = $request->getAllItems();
		if (!empty($items) && ($items!="")) {
			$this->_customerGroupCode = Mage::getModel('customer/group')->load
    			($items[0]->getQuote()->getCustomerGroupId())->getCode();
		} else {
			return;
			}
		
		// get the package_id's for the items in the cart
    		
		$conditionName=$this->_request->getConditionName();
		$this->populateStructuredItems($items,$conditionName);
		
		
		$first=true;
		foreach ($this->_structuredItems as $structuredItem) {
			if ($structuredItem['package_id']=='none' && $this->_starIncludeAll) { continue; }
			$this->_minusOne=false;
			if(!$first) {
				$data=$this->runSelectStmt($read,$structuredItem);
				if (!empty($data)) {
					if ($conditionName=='highest') {
						$this->mergeHighest($data,$finalResults);
					} else if ($conditionName=='lowest') {
						$this->mergeLowest($data,$finalResults);
					} else if ($conditionName=='order') {
						$this->mergeOrdered($data,$finalResults);
					} else {
						$this->mergeResults($data,$finalResults);
					}
				} else if (!$this->_starIncludeAll  || (!$this->_starIncludeAll && $this->_minusOne)){
					return;
				}
			} else {
				$data=$this->runSelectStmt($read,$structuredItem);
				if (!empty($data)) {
					$first=false;
					$finalResults=$data;
				} else if (!$this->_starIncludeAll  || (!$this->_starIncludeAll && $this->_minusOne)) {
					return;
				}
			}
		}		
		
		if (empty($finalResults)) { return; }
		if (!empty($this->_exclusionList)) {
			foreach ($finalResults as $key=>$result) {
				foreach ($this->_exclusionList as $ekey=>$exclusionItem) {
					if ($result['delivery_type']==$exclusionItem['delivery_type']) {
						$finalResults[$key]="";
						break;
						}
				}
				
			}
		    foreach ($finalResults as $key=>$result) {
	    		if (empty($finalResults[$key])) {
	    			unset($finalResults[$key]);
	    		}
	    	}
		}
		
		if (empty($finalResults)) { return; }
		
		if ($this->_prioritySet) {
		    foreach ($finalResults as $i => $rate) {
		        $priceArr[$i] = $rate['price'];
		        $priority[$i] = $rate['priority'];
		    }
	
			array_multisort($priceArr, SORT_ASC, $priority, SORT_ASC, $finalResults);
			$previousPrice=-100;
			foreach ($finalResults as $data) {
				if ($previousPrice==$data['price'] && $data['priority']!=$previousePriority) {
					continue;
				} else {
					$previousPrice=$data['price'];
					$previousePriority=$data['priority'];
					$absoluteResults[]=$data;
				}
			}
		} else {
			$absoluteResults=$finalResults;
		}
		if ($conditionName=="highest")
		{ 
			foreach ($absoluteResults as $key=>$data) {
				$absoluteResults[$key]['price'] = $data['price'] + $data['additional_price'];
				if ($data['qty']>1 && $data['multiprice']>0 && !$data['override']) {
					$absoluteResults[$key]['price'] = $absoluteResults[$key]['price'] + $data['multiprice']*($data['qty']-1);
				}
			}
		}
		
		if ($this->_maxPriceSet) {
			foreach ($absoluteResults as $key=>$data) {
				if ($data['price']>$data['max_price']) {
					$absoluteResults[$key]['price'] = $data['max_price'];
				}
			}
		}
				
		return $absoluteResults;
    }
    
    private function mergeHighest($indResults,&$baseResults) 
    {
    	foreach ($baseResults as $key=>$result)
    	{
    	   	$found=false;
    		foreach ($indResults as $indKey=>$data) {
    			if ($result['delivery_type']==$data['delivery_type']) {
    				if (!$baseResults[$key]['override'] && ($data['price']>$baseResults[$key]['price'] || $data['override'])) { // if higher get higher
						$baseResults[$key]['price']=$data['price'];
						$baseResults[$key]['additional_price']+=$baseResults[$key]['multiprice']*$baseResults[$key]['qty'];						
						$baseResults[$key]['multiprice']=$data['multiprice'];						
						$baseResults[$key]['qty']=$data['qty'];			
					} else {
						$baseResults[$key]['additional_price']+=$data['multiprice']*$data['qty'];						
					}
					if ($baseResults[$key]['max_price']<$data['max_price']) {
						$baseResults[$key]['max_price']=$data['max_price'];
					}
					$indResults[$indKey]['found']=true;
					$found=true;
					break;
				}
    		}
    		if (!$found && !$this->_starIncludeAll) {   		
    		    // no match so remove
    			$baseResults[$key]="";
    		}   	
    	}
    	if ($this->_starIncludeAll) {
    	   	// check for missing
	    	foreach ($indResults as $data) {
	    		if (empty($data['found'])) {
	    			$baseResults[]=$data;
	    		}
	    	} 		
    	} else {
	     	// unset here so we dont upset the apple cart
	    	foreach ($baseResults as $key=>$result) {
	    		if (empty($baseResults[$key])) {
	    			unset($baseResults[$key]);
	    		}
	    	}
    	}
    	
    }
    

    
    /**
     * Merge results together, ignore any not in base result set
     * @param $indResults
     * @param $baseResults - passed by reference
     * @return unknown_type
     */
    private function mergeResults($indResults,&$baseResults) 
    {
    	//Mage::log(print_r($indResults,true));
    	//Mage::log(print_r($baseResults,true));
    	foreach ($baseResults as $key=>$result)
    	{
    		$found=false;
    		foreach ($indResults as $indKey=>$data) {
    			if ($result['delivery_type']==$data['delivery_type']) {
    				if ($baseResults[$key]['max_price']<$data['max_price']) {
						$baseResults[$key]['max_price']=$data['max_price'];
					}
    				if ($baseResults[$key]['override']) {
    					$found=true;
    					break;
    				} else if ($data['override']) {
    					$baseResults[$key]['price']=$data['price'];
    					$baseResults[$key]['override']=true;
    					$baseResults[$key]['package_id']=$baseResults[$key]['package_id'].",".$data['package_id'];
						$indResults[$indKey]['found']=true;
						$found=true;
						break;
    				} else {
						// add to existing
						$baseResults[$key]['price']+=$data['price'];
						$baseResults[$key]['package_id']=$baseResults[$key]['package_id'].",".$data['package_id'];
						$indResults[$indKey]['found']=true;
						$found=true;
						break;
    				}
				}
    		}
    		if (!$found) {  // no match
    			if ( !$this->_starIncludeAll) {   		
    				$baseResults[$key]="";
    			} else {
    				if ($result['package_id']!="" && count($this->_structuredItems)>1 && $indResults[0]['package_id']!="" ) {
    					$baseResults[$key]="";
    				}
    			}
    		}
    	}
    	if ($this->_starIncludeAll) {
    	   	// check for missing
	    	foreach ($indResults as $data) {
	    		if (empty($data['found']) && $data['package_id']=="") {
	    			$baseResults[]=$data;
	    		}
	    	} 	
	    	
	    	// this was changed to be ==package_id from != - reason unclear
	    	foreach ($baseResults as $key=>$result) {
	    		if ($result!="" && $this->_hasEmptyPackages && $result['package_id']=="") {	
    				$baseResults[$key]="";
	    		}
	    	}
    	}
    	// unset here so we dont upset the apple cart
    	foreach ($baseResults as $key=>$result) {
    		if (empty($baseResults[$key])) {
    			unset($baseResults[$key]);
    		}
    	}
    }
    
    private function mergeOrdered($indResults,&$baseResults) {
    	
   		//this also supports sorting 
   		
   		if (!array_key_exists('order',$indResults[0]) || !array_key_exists('order',$baseResults[0])) {
    		return;
    	}
    	// just take the first
    	$order=$indResults[0]['order'];
    	if ($order<$baseResults[0]['order']) {
    		reset($baseResults);
    		$baseResults=$indResults;
    	}
    	
    	
    }

    private function mergeLowest($indResults,&$baseResults) 
    {
    	
    	foreach ($baseResults as $key=>$result)
    	{
    	   	$found=false;
    		foreach ($indResults as $indKey=>$data) {
    			if ($result['delivery_type']==$data['delivery_type']) {
					// if lower get lower
					if ($data['price']<$baseResults[$key]['price']) {
						$baseResults[$key]['price']=$data['price'];
					}
					$indResults[$indKey]['found']=true;
					$found=true;
					break;
				}
    		}
    		if (!$found && !$this->_starIncludeAll) {   		
    			// no match so remove
    			$baseResults[$key]="";
    		}  
    	}
    	
    	if ($this->_starIncludeAll) {
    	   	// check for missing
	    	foreach ($indResults as $data) {
	    		if (empty($data['found'])) {
	    			$baseResults[]=$data;
	    		}
	    	} 		
    	} else {
	    	// unset here so we dont upset the apple cart
	    	foreach ($baseResults as $key=>$result) {
	    		if (empty($baseResults[$key])) {
	    			unset($baseResults[$key]);
	    		}
	    	}
    	}
    }		
	private function runSelectStmt($read,$structuredItem) 
	{
		$conditionName=$this->_request->getConditionName();
		for ($j=0;$j<9;$j++)
		{
			//$select = $read->select()->from($table);
			$select = $read->select()->from(array('productmatrix'=>$this->_table),
							array(	'pk'=>'pk',
									'price'=>'price',
									'delivery_type'=>'delivery_type',
									'package_id'=>'package_id',
									'weight_from_value'=>'weight_from_value',
									'item_from_value'=>'item_from_value',
									'algorithm'=>'algorithm',
									'notes'=>'notes',
									'cost'=>'cost'));
			
			switch($j) {
				case 0:
					$select->where(
						$read->quoteInto(" (dest_country_id=? ", $this->_request->getDestCountryId()).
							$read->quoteInto(" AND dest_region_id=? ", $this->_request->getDestRegionId()).
							$read->quoteInto(" AND STRCMP(LOWER(dest_city),LOWER(?)) = 0  ", $this->_request->getDestCity()).
							$this->_zipSearchString
						);
					break;
				case 1:
					$select->where(
						$read->quoteInto(" (dest_country_id=? ", $this->_request->getDestCountryId()).
							$read->quoteInto(" AND dest_region_id=?  AND dest_city=''", $this->_request->getDestRegionId()).
							$this->_zipSearchString
						);
					break;
				case 2:
					$select->where(
						$read->quoteInto(" (dest_country_id=? ", $this->_request->getDestCountryId()).
							$read->quoteInto(" AND dest_region_id=? ", $this->_request->getDestRegionId()).
							$read->quoteInto(" AND STRCMP(LOWER(dest_city),LOWER(?)) = 0  AND dest_zip='')", $this->_request->getDestCity())
						);
					break;
				case 3:
					$select->where(
					   $read->quoteInto("  (dest_country_id=? ", $this->_request->getDestCountryId()).
							$read->quoteInto(" AND STRCMP(LOWER(dest_city),LOWER(?)) = 0  AND dest_region_id='0'", $this->_request->getDestCity()).
							$this->_zipSearchString
					   );
					break;
				case 4:
					$select->where(
					   $read->quoteInto("  (dest_country_id=? ", $this->_request->getDestCountryId()).
							$read->quoteInto(" AND STRCMP(LOWER(dest_city),LOWER(?)) = 0  AND dest_region_id='0' AND dest_zip='') ", $this->_request->getDestCity())
					   );
					break;
				case 5:
					$select->where(
						$read->quoteInto("  (dest_country_id=? AND dest_region_id='0' AND dest_city='' ", $this->_request->getDestCountryId()).
							$this->_zipSearchString
						);
					break;
				case 6:
					$select->where(
					   $read->quoteInto("  (dest_country_id=? ", $this->_request->getDestCountryId()).
							$read->quoteInto(" AND dest_region_id=? AND dest_city='' AND dest_zip='') ", $this->_request->getDestRegionId())
					   );
					break;

				case 7:
					$select->where(
					   $read->quoteInto("  (dest_country_id=? AND dest_region_id='0' AND dest_city='' AND dest_zip='') ", $this->_request->getDestCountryId())
					);
					break;

				case 8:
					$select->where(
							"  (dest_country_id='0' AND dest_region_id='0' AND dest_zip='')"
				);
					break;
			}
			

			
			if ($structuredItem['package_id']=='include_all' || $structuredItem['package_id']=='none') {
				$select->where('package_id=?','');
			} else {
				$select->where('package_id=?', $structuredItem['package_id']);
	
			}
            
			$select->where('weight_from_value<?', $structuredItem['weight']);
			$select->where('weight_to_value>=?', $structuredItem['weight']);
			$select->where('price_from_value<?', $structuredItem['price']);
			$select->where('price_to_value>=?', $structuredItem['price']);
			$select->where('item_from_value<?', $structuredItem['qty']);
			$select->where('item_to_value>=?', $structuredItem['qty']);
			
			$groupArr[0]="STRCMP(LOWER(customer_group),LOWER('".$this->_customerGroupCode."')) =0";
			$groupArr[1]="customer_group=''";
			$select->where(join(' OR ', $groupArr));

			$select->where('website_id=?', $this->_request->getWebsiteId());

			$select->order('notes ASC');
			$select->order('price ASC');
			$select->order('algorithm ASC');
			/*
			pdo has an issue. we cannot use bind
			*/
			
			$row = $read->fetchAll($select);
			if (!empty($row)) {
				$newdata=array();
				foreach ($row as $data) {
					if ($data['price']==-1) {
						
						$exclusionItem=array ( 'package_id' => $structuredItem['package_id'],
											   'delivery_type' => $data['delivery_type']);
						$this->_exclusionList[]=$exclusionItem;
						$this->_minusOne=true;
						continue;
					}
					$data['priority']=0;
					$data['multiprice']="";
					$data['additional_price']=0;
					$data['qty']=0;
					$data['max_price']=-1;
					$data['override']=false;
					if ($data['algorithm']!="") {
						$algorithm_array=explode("&",$data['algorithm']);  // Multi-formula extension
						reset($algorithm_array);
						$skipData=false;
						foreach ($algorithm_array as $algorithm_single) {
							$algorithm=explode("=",$algorithm_single,2);
							if (!empty($algorithm) && count($algorithm)==2) {
								if (strtolower($algorithm[0])=="w") {
									// weight based
									$weightIncrease=explode("@",$algorithm[1]);
									if (!empty($weightIncrease) && count($weightIncrease)==2 ) {
										$weightDifference=	$structuredItem['weight']-$data['weight_from_value'];
										$quotient=$weightDifference / $weightIncrease[0];
										$data['price']=$data['price']+$weightIncrease[1]*$quotient;
									}
								} else if (strtolower($algorithm[0])=="wc") {
									// weight based
									$weightIncrease=explode("@",$algorithm[1]);
									if (!empty($weightIncrease) && count($weightIncrease)==2 ) {
										$weightDifference=	$structuredItem['weight']-$data['weight_from_value'];
										$quotient=ceil($weightDifference / $weightIncrease[0]);
										$data['price']=$data['price']+$weightIncrease[1]*$quotient;
									}
								} else if (strtolower($algorithm[0])=="p" ) {
									$this->_prioritySet=true;
									$data['priority']=$algorithm[1];
								} else if (strtolower($algorithm[0])=="m" ) {
									$this->_maxPriceSet = true;
									$data['max_price']=$algorithm[1];
								} else if (strtolower($algorithm[0])=="i" ) {
									if ($conditionName=='per_package') {
										$data['price']+=$algorithm[1]*($structuredItem['qty']-$data['item_from_value']);									
									} else {
										$data['multiprice']=$algorithm[1];
										$data['qty']=$structuredItem['qty'];
									}
								}	else if (strtolower($algorithm[0])=="o" ) {
									$data['order']=$algorithm[1];
								} else if (strtolower($algorithm[0])=="%" ) {
									$perSplit=explode("+",$algorithm[1]);
									if (!empty($perSplit) && count($perSplit)==2) {
										$percentage = $perSplit[0];
										$flatAdd = $perSplit[1];
									} else {
										$percentage = $algorithm[1];
										$flatAdd = 0;
									}
									$percPrice=($structuredItem['price']*$percentage/100)+$flatAdd;
									if ($percPrice>$data['price']) {
										$data['price']=$percPrice;
									}
								}
							} else {
								if ($algorithm_single=="OVERRIDE") {
									$data['override']=true;
								}
							}
						}
					}
					
										
					if ($conditionName=='per_product') {
						// for each unique product in basket for this package id (e.g. product A&B of package id Z)
						$data['price']=$data['price']*$structuredItem['unique'];
					} else if ($conditionName=='per_item' || $conditionName=='per_item_bare' ) {
						// foreach item in basket for this package_id (e.g. 3*product A of package id Z)
							$data['price']=$data['price']*$structuredItem['qty'];
					} 
					$newdata[]=$data;
				}
				if (!empty($newdata)) {
    				return $newdata;
				} else {
					return;
				}
			}
		}
	}
	
	private function populateStructuredItems($items, $conditionName)
	{
		$this->_structuredItems=array();
		$configurableQty = 0;
		$configurablePrice = 0;
		$bundledIdUsed = Mage::getStoreConfig("carriers/productmatrix/bundle_id");
		$filterPrice = Mage::getStoreConfig("carriers/productmatrix/filter_price");
		
		foreach($items as $item) {
			if ($item->getFreeShipping() && !$item->getProduct()->isVirtual()  || 
				$item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE ) {   //TODO do we need isvirtual - dont think so
				continue;
			}
			$currentQty = $item->getQty();	
			$currentPrice = $item->getPrice();
			if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE ) {
				$configurableQty = $currentQty;
				$configurablePrice = $currentPrice;
				continue;
			} elseif ($configurableQty > 0) {
				$currentQty = $configurableQty;
				$configurableQty = 0;
			} 
			if ($configurablePrice > 0) {
				$currentPrice = $configurablePrice;
				$configurablePrice = 0;
			}
			
			$parentQty = 1;
			/*if ($item->getParentItem()!=null) {
				if ($item->getParentItem()->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
					if ($bundledIdUsed) { continue; }
					$parentQty = $item->getParentItem()->getQty();
				}
			} */	
			$qty=$currentQty * $parentQty;
			$price=$currentPrice;
			
			
			if ($item->getParentItem()!=null && 
				($item->getParentItem()->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE ||
				$item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) && 
				$bundledIdUsed) { 
				$product = Mage::getModel('catalog/product')->getResourceCollection()
				    ->addAttributeToSelect('package_id')
				    ->addAttributeToSelect('shipping_qty')
				    ->addAttributeToFilter('entity_id',$item->getParentItem()->getProductId());
			} else {
				$product = Mage::getModel('catalog/product')->getResourceCollection()
				    ->addAttributeToSelect('package_id')
				    ->addAttributeToSelect('shipping_qty')
				    ->addAttributeToFilter('entity_id',$item->getProductId());
			}

			// if find a surcharge check to see if it is on the item or the order
			// if on the order then add to the surcharge_order_price if > than previous
			// if on the item then multiple by qty and add to the surcharge_price
			foreach($product as $object) {
				continue;
			}
			
			$shipQty=$object->getData('shipping_qty');
			
			if ($shipQty=="" || !is_numeric($shipQty)) {
				$shipQty=1;
			} 

			$packageId = $object->getAttributeText('package_id');
			$found=false;
			
			if (empty($packageId)) { $packageId='none'; $this->_hasEmptyPackages=true; }
			
			if ($conditionName=='per_item_bare') {
				$prodArray=array( 'package_id'  => $packageId,
						  'qty' 		=> $qty,
						  'weight'		=> $item->getWeight(),
						  'price'		=> $price,
						  'unique'		=> 1);
				$this->_structuredItems[]=$prodArray;
				
			} else {
			
				foreach($this->_structuredItems as $key=>$structuredItem) {
					if ($structuredItem['package_id']==$packageId) {
						// have already got this package id
						$this->_structuredItems[$key]['qty']=$this->_structuredItems[$key]['qty']+$qty*$shipQty;
						$this->_structuredItems[$key]['weight']=$this->_structuredItems[$key]['weight']+ $item->getWeight()*$qty;
						$this->_structuredItems[$key]['price']=$this->_structuredItems[$key]['price']+ $price*$qty;
						$this->_structuredItems[$key]['unique']+=1;
						$found=true;
						break;
					}
				}
				
				if (!$found){
					$prodArray=array( 'package_id'  => $packageId,
							  'qty' 				=> $qty*$shipQty,
							  'weight'				=> $item->getWeight()*$qty,
							  'price'				=> $price*$qty,
							  'unique'				=> 1);
					$this->_structuredItems[]=$prodArray;
					
				}
			}
			// also add to include_all package Id
			if ($this->_starIncludeAll) {
				$found=false;
				foreach($this->_structuredItems as $key=>$structuredItem) {
					if ($structuredItem['package_id']=='include_all') {
						$this->_structuredItems[$key]['qty']=$this->_structuredItems[$key]['qty']+$qty*$shipQty;
						$this->_structuredItems[$key]['weight']=$this->_structuredItems[$key]['weight']+ $item->getWeight()*$qty;
						$this->_structuredItems[$key]['price']=$this->_structuredItems[$key]['price']+ $price*$qty;
						$this->_structuredItems[$key]['unique']+=1;
						$found=true;
						break;
					}
				} 
				if (!$found) {
					$prodArray=array( 'package_id'  => 'include_all',
					  'qty' 		=> $qty*$shipQty,
					  'weight'		=> $item->getWeight()*$qty,
					  'price'		=> $price*$qty,
					  'unique'		=> 1);
					$this->_structuredItems[]=$prodArray;
				}
			}
		}
		if ($filterPrice) {
			foreach($this->_structuredItems as $key=>$structuredItem) {
				$this->_structuredItems[$key]['price'] = $this->_request->getPackageValue();
			}
		}
		
		//Mage::log(print_r($this->_structuredItems,true));
	}
	
	
	/**
	 * CSV Import routine
	 * @param $object
	 * @return unknown_type
	 */
    public function uploadAndImport(Varien_Object $object)
    {
        $csvFile = $_FILES["groups"]["tmp_name"]["productmatrix"]["fields"]["import"]["value"];

        if (!empty($csvFile)) {

            $csv = trim(file_get_contents($csvFile));

            $table = Mage::getSingleton('core/resource')->getTableName('productmatrix_shipping/productmatrix');

            $websiteId = $object->getScopeId();
            $websiteModel = Mage::app()->getWebsite($websiteId);
            
            if (!empty($csv)) {
                $exceptions = array();
                $csvLines = explode("\n", $csv);
                $csvLine = array_shift($csvLines);
                $csvLine = $this->_getCsvValues($csvLine);
                if (count($csvLine) < 15) {
                    $exceptions[0] = Mage::helper('shipping')->__('Invalid Product Matrix File Format');
                }

                $countryCodes = array();
                $regionCodes = array();
                foreach ($csvLines as $k=>$csvLine) {
                    $csvLine = $this->_getCsvValues($csvLine);
                    if (count($csvLine) > 0 && count($csvLine) < 15) {
                        $exceptions[0] = Mage::helper('shipping')->__('Invalid Product Matrix File Format %s',$csvLine);
                    } else {
                        $splitCountries = explode(",", trim($csvLine[0]));
                    	$splitRegions = explode(",", trim($csvLine[1]));
                        foreach ($splitCountries as $country) {
                        	$countryCodes[] = trim($country);
                    	}
                    	foreach ($splitRegions as $region) {     
                        	$regionCodes[] = $region;
                    	}                    
                   	}
                }
                
                
                if (empty($exceptions)) {
                    $data = array();
                    $countryCodesToIds = array();
                    $regionCodesToIds = array();
                    $countryCodesIso2 = array();

                    $countryCollection = Mage::getResourceModel('directory/country_collection')->addCountryCodeFilter($countryCodes)->load();
                    foreach ($countryCollection->getItems() as $country) {
                        $countryCodesToIds[$country->getData('iso3_code')] = $country->getData('country_id');
                        $countryCodesToIds[$country->getData('iso2_code')] = $country->getData('country_id');
                        $countryCodesIso2[] = $country->getData('iso2_code');
                    }

                    $regionCollection = Mage::getResourceModel('directory/region_collection')
                        ->addRegionCodeFilter($regionCodes)
                        ->addCountryFilter($countryCodesIso2)
                        ->load();                    
                 
                        
                    foreach ($regionCollection->getItems() as $region) {
                        $regionCodesToIds[$countryCodesToIds[$region->getData('country_id')]][$region->getData('code')] = $region->getData('region_id');
                    }
                        
                    foreach ($csvLines as $k=>$csvLine) {
                        $csvLine = $this->_getCsvValues($csvLine);
                        $splitCountries = explode(",", trim($csvLine[0]));
                        
                        foreach ($splitCountries as $country) {
                        	$splitRegions = explode(",", trim($csvLine[1]));
                        	
                        	foreach ($splitRegions as $region) {
                        		
                        		$splitPostcodes = explode(",",trim($csvLine[3]));
                        		
                        		foreach ($splitPostcodes as $postcode) {
                        		
		                        	$country=trim($country);
		                        	
		                        	if (empty($countryCodesToIds) || !array_key_exists($country, $countryCodesToIds)) {
			                        	$countryId = '0';
			                            if ($country != '*' && $country != '') {
			                                $exceptions[] = Mage::helper('shipping')->__('Invalid Country "%s" in the Row #%s', $country, ($k+1));
			                            }
			                        } else {
			                            $countryId = $countryCodesToIds[$country];
			                        }
			
			                        if (!isset($countryCodesToIds[$country])
			                            || !isset($regionCodesToIds[$countryCodesToIds[$country]])
			                            || !array_key_exists($region, $regionCodesToIds[$countryCodesToIds[$country]])) {
			                            $regionId = '0';
				                        if ($region != '*' && $region != '') {
			                            	$exceptions[] = Mage::helper('shipping')->__('Invalid Region/State "%s" in the Row #%s', $region, ($k+1));
			                            }
			                        } else {
			                            $regionId = $regionCodesToIds[$countryCodesToIds[$country]][$region];
			                        }
			                        
									if ($csvLine[2] == '*' || $csvLine[2] == '') {
										$city = '';
									} else {
										$city = $csvLine[2];
									}
			
			
									//if ($csvLine[3] == '*' || $csvLine[3] == '') {
									if ($postcode == '*' || $postcode == '') {
										$zip = '';
									} else {
										//$zip = $csvLine[3];
										$zip = $postcode;
									}
			
			
									if ($csvLine[4] == '*' || $csvLine[4] == '') {
										$zip_to = '';
									} else {
										$zip_to = $csvLine[4];
									}
			
			
									if ($csvLine[5] == '*' || $csvLine[5] == '') {
										$package_id = '';
									} else {
										$package_id = $csvLine[5];
									}
			
			                    	if ( $csvLine[6] == '*' || $csvLine[6] == '') {
										$weight_from = -1;
									} else if (!$this->_isPositiveDecimalNumber($csvLine[6])) {
										$exceptions[] = Mage::helper('shipping')->__('Invalid weight From "%s" in the Row #%s', $csvLine[6], ($k+1));
			                    	} else {
										$weight_from = (float)$csvLine[6];
									}
			
									if ( $csvLine[7] == '*' || $csvLine[7] == '') {
										$weight_to = 10000000;
									} else if (!$this->_isPositiveDecimalNumber($csvLine[7])) {
										$exceptions[] = Mage::helper('shipping')->__('Invalid weight To "%s" in the Row #%s', $csvLine[7], ($k+1));
									}
									else {
										$weight_to = (float)$csvLine[7];
									}
			
									if ( $csvLine[8] == '*' || $csvLine[8] == '') {
										$price_from = -1;
									} else if (!$this->_isPositiveDecimalNumber($csvLine[8]) ) {
										$exceptions[] = Mage::helper('shipping')->__('Invalid price From "%s" in the Row #%s',  $csvLine[8], ($k+1));
									} else {
										$price_from = (float)$csvLine[8];
									}
			
									if ( $csvLine[9] == '*' || $csvLine[9] == '') {
										$price_to = 10000000;
									} else if (!$this->_isPositiveDecimalNumber($csvLine[9])) {
										$exceptions[] = Mage::helper('shipping')->__('Invalid price To "%s" in the Row #%s', $csvLine[9], ($k+1));
									} else {
										$price_to = (float)$csvLine[9];
									}
			
									if ( $csvLine[10] == '*' || $csvLine[10] == '') {
										$item_from = 0;
									} else if (!$this->_isPositiveDecimalNumber($csvLine[10]) ) {
										$exceptions[] = Mage::helper('shipping')->__('Invalid item From "%s" in the Row #%s',  $csvLine[10], ($k+1));
									} else {
										$item_from = (float)$csvLine[10];
									}
			
									if ( $csvLine[11] == '*' || $csvLine[11] == '') { 
										$item_to = 10000000;
									} else if (!$this->_isPositiveDecimalNumber($csvLine[11])) {
										$exceptions[] = Mage::helper('shipping')->__('Invalid item To "%s" in the Row #%s', $csvLine[11], ($k+1));
									} else {
										$item_to = (float)$csvLine[11];
									}
									
									
			
			                    	if ($csvLine[12] == '*' || $csvLine[12] == '') {
										$customer_group = '';
									} else {
										$customer_group = $csvLine[12];
									}
									
									if (count($csvLine)==15) {
										$data[] = array('website_id'=>$websiteId, 'dest_country_id'=>$countryId, 'dest_region_id'=>$regionId, 
											'dest_city'=>$city, 'dest_zip'=>$zip, 'dest_zip_to'=>$zip_to, 
											'package_id'=>$package_id,
											'weight_from_value'=>$weight_from,'weight_to_value'=>$weight_to,
											'price_from_value'=>$price_from,'price_to_value'=>$price_to, 
											'item_from_value'=>$item_from,'item_to_value'=>$item_to, 
											'customer_group'=>$customer_group, 
											'price'=>$csvLine[13], 'delivery_type'=>$csvLine[14]);
									} else {
										$data[] = array('website_id'=>$websiteId, 'dest_country_id'=>$countryId, 'dest_region_id'=>$regionId, 
										'dest_city'=>$city, 'dest_zip'=>$zip, 'dest_zip_to'=>$zip_to, 
										'package_id'=>$package_id,
										'weight_from_value'=>$weight_from,'weight_to_value'=>$weight_to,
										'price_from_value'=>$price_from,'price_to_value'=>$price_to, 
										'item_from_value'=>$item_from,'item_to_value'=>$item_to, 
										'customer_group'=>$customer_group, 
										'price'=>$csvLine[13], 'algorithm'=>$csvLine[14], 'delivery_type'=>$csvLine[15], 'notes'=>$csvLine[16]);
									}
			
									$dataDetails[] = array('country'=>$country, 'region'=>$region);
	                        	}
                        	}
                        }
                    }
                }
                if (empty($exceptions)) {
                    $connection = $this->_getWriteAdapter();

                     $condition = array(
                        $connection->quoteInto('website_id = ?', $websiteId),
                    );
                    $connection->delete($table, $condition);
                    

                    foreach($data as $k=>$dataLine) {
                        try {
                            $connection->insert($table, $dataLine);
                        } catch (Exception $e) {
                            $exceptions[] = Mage::helper('shipping')->__('Duplicate Row #%s (Country "%s", Region/State "%s", Zip "%s")', ($k+1), $dataDetails[$k]['country'], $dataDetails[$k]['region'], $dataLine['dest_zip']);
                            $exceptions[] = $e;
                       }
                    }
                }
                if (!empty($exceptions)) {
                    throw new Exception( "\n" . implode("\n", $exceptions) );
                }
            }
        }
    }

    private function _getCsvValues($string, $separator=",")
    {
        $elements = explode($separator, trim($string));
        for ($i = 0; $i < count($elements); $i++) {
            $nquotes = substr_count($elements[$i], '"');
            if ($nquotes %2 == 1) {
                for ($j = $i+1; $j < count($elements); $j++) {
                    if (substr_count($elements[$j], '"') > 0) {
                        // Put the quoted string's pieces back together again
                        array_splice($elements, $i, $j-$i+1, implode($separator, array_slice($elements, $i, $j-$i+1)));
                        break;
                    }
                }
            }
            if ($nquotes > 0) {
                // Remove first and last quotes, then merge pairs of quotes
                $qstr =& $elements[$i];
                $qstr = substr_replace($qstr, '', strpos($qstr, '"'), 1);
                $qstr = substr_replace($qstr, '', strrpos($qstr, '"'), 1);
                $qstr = str_replace('""', '"', $qstr);
            }
            $elements[$i] = trim($elements[$i]);
        }
        return $elements;
    }

    private function _isPositiveDecimalNumber($n)
    {
        return preg_match ("/^[0-9]+(\.[0-9]*)?$/", $n);
    }
    


}
