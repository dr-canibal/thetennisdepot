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
 * @package    AW_Featuredproducts
 * @copyright  Copyright (c) 2009-2010 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */

class AW_Featuredproducts_Helper_Data extends Mage_Core_Helper_Abstract
{
	function getFeaturedProducts(){
		

	//	$model = Mage::getModel('catalog/product_flat')->updateAttribute('aw_fp_category');
		
		
		$db = Mage::getSingleton('core/resource')->getConnection('core_write');

		$currentCategoryId = '0';
		if(Mage::registry('current_category'))
			$currentCategoryId = Mage::registry('current_category')->getId();
		$showLast   		= Mage::getStoreConfig('featuredproducts/automationoptions/showlast');
		$showRandom 		= Mage::getStoreConfig('featuredproducts/automationoptions/showrandom');
		$considerCategory 	= Mage::getStoreConfig('featuredproducts/automationoptions/considercategory');
		$showItems 			= $showRandom > 0 ? $showRandom : $showLast; //show either last X either random X products
        $_productCollection = Mage::getModel('catalog/product')
								->setStoreId(Mage::app()->getStore()->getId())
								->getCollection();
        
		
		
	    if($considerCategory > 0 && $currentCategoryId > 0)
	    	$_productCollection->addCategoryFilter(Mage::getSingleton('catalog/category')->setId($currentCategoryId));

	    if($showRandom > 0){
	    	Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($_productCollection);
	    	$_productCollection->getSelect()->order('rand()');
	    	
	    }
	    elseif($showLast){
	    	$_productCollection->addAttributeToSort('created_at','desc');
	    }else{
			
			$orAttrs= array(
						array('attribute' => 'aw_fp_category', 'or' => ''),
						array('attribute' => 'aw_fp_category', 'or' => '0'),
						array('attribute' => 'aw_fp_category', 'or' => $currentCategoryId),
						array('attribute' => 'aw_fp_category', 'like' => "%,$currentCategoryId"),
						array('attribute' => 'aw_fp_category', 'like' => "$currentCategoryId,%"),
						array('attribute' => 'aw_fp_category', 'like' => "%,$currentCategoryId,%")
					);
			if(!$currentCategoryId){
				$orAttrs[] = array('attribute' => 'aw_fp_category', 'or' => '-1');
			}		
			
	    	$_productCollection
				
				->addAttributeToFilter('aw_fp_enabled', 1)
				->addAttributeToFilter($orAttrs);		
		}
		
		$_productCollection
			->setVisibility(array(2,3,4));
		$_productCollection->addUrlRewrite();

		if($showItems > 0){
			$_productCollection
				->setPageSize($showItems)
				->setCurPage(1); //otherwise just use as many as defined 'featured'   
		}
	    $_productCollection->groupByAttribute('entity_id');
	    $_productCollection->load();
	    
		$_res = array();

		
		foreach($_productCollection as $_product){


			$_product->load($_product->getId());
			$_title = ltrim(rtrim($_product->getName()));
			$_url = $_product->getProductUrl();

			$_description = nl2br(ltrim(rtrim($_product->getData('aw_fp_description'))));
			$_description = $_description ? $_description : $_product->getShortDescription();
			

		
			if($_product->isSalable()){
			
			
				$blk = Mage::getSingleton('core/layout')
					->createBlock('catalog/product')
					->setProduct($_product);
			 
				
			
				if($_product->getTypeId() == 'bundle'){
					//$blk->setPrice
					list($_minimalPrice, $_maximalPrice) = $_product->getPriceModel()->getPrices($_product); 
					$_price = $_minimalPrice;
					
				}else{
					$_price = $_product->getPrice();
				} 
				
				
				$_price = Mage::helper('core')->formatCurrency($_price);
				array_push($_res, array(
					'title'			=> $_title,
					'url'			=> $_url,
					//'image'			=> Mage::helper('catalog/image')->init($_product, 'image')->resize(400, 400),
					'image'			=> (string)Mage::helper('catalog/image')->init($_product, 'image', 
						$_product->getAwFpImage()
					)->resize(Mage::getStoreConfig('featuredproducts/displayoptions/imagewidth')),
					'price'			=> $_price,
					'priceHTML'		=> $_product->getPriceHtml($_product, true),
					'description' 	=> $_description,
					'product'		=> $_product
					));
			}		
		}
	    return $_res;
	}

	function getDescriptionHtml($tpl, $p){
		
		$out = "";
		$pr = $p["product"];
		
		/* Move all to phtml */
        if (preg_match('/^1.8/', Mage::getVersion()))
        {$spanBefore = '<span>'; $spanAfter = '</span>';}
        else
        {$spanBefore = $spanAfter = '';}

        if(Mage::getStoreConfig('featuredproducts/displayoptions/title') == 'description'){
			$out .= "<ins><h4>{$pr->getName()}</h4></ins>";
		}
		$out .= "<span class=\"description\"".
					(!Mage::getStoreConfig('featuredproducts/displayoptions/description') ?
						"style=\"display:none;\""
					:
						""
					)
					.">{$p['description']}</span>";
		$out .= "<ins><div>".
						(Mage::getStoreConfig('featuredproducts/displayoptions/price') ?
							"{$tpl->getPriceHtml($pr, true, 'awfp')}"
						:
							""
						)		
					
					."".
						(Mage::getStoreConfig('featuredproducts/displayoptions/bnb') ?
							"<button class=\"form-button button\" onclick=\"setLocation('{$p["url"]}')\"><span>$spanBefore{$tpl->__('Buy Now')}$spanAfter</span></button>"
						:
							"")
					."
				</div></ins>";
		return $out;		
	}


	function getSettings(){
		return array(
			'displayType' 		=> Mage::getStoreConfig('featuredproducts/displayoptions/displaytype'),
			'containerWidth'	=> abs(Mage::getStoreConfig('featuredproducts/displayoptions/containerwidth')),
			'SSDelay' 			=> abs(Mage::getStoreConfig('featuredproducts/displayoptions/ssdelay')),
			'imageWidth'		=> abs(Mage::getStoreConfig('featuredproducts/displayoptions/imagewidth')),
			'imageHeight'		=> abs(Mage::getStoreConfig('featuredproducts/displayoptions/imageheight')),
			'isHint'			=> Mage::getStoreConfig('featuredproducts/displayoptions/istooltip'),
			'isThumbs'			=> Mage::getStoreConfig('featuredproducts/displayoptions/isthumbs')
		);
	}
	
	
	
	function __construct(){
		$this->_default = array(
			"displaytype" => "slider",
			"arrows" => 1,
			"switchertype" => "none",
			"title" => "top",
			"details" => "integrate",
			"istooltip" => 1,
			"interval" => 0,
			"align" => "left",
			"image" => "left",
			"bnb" => 0,
			"description" => 1,
			"price" => 1,
			"imagewidth" => 120,
			"thumbwidth" => 32,
			"thumbzoom" => 1.5,
			"containerwidth" => 475,
			"containerheight" => 200,
			"blockwidth" => 395,
			"blockheight" => 200,
			"ssdelay" => 30
		);
	}
	
	function getDisplay($name){
		$val = Mage::getStoreConfig('featuredproducts/displayoptions/'.$name);
		if(!count($val) && !is_numeric($val)){
			return @$this->_default[$name];
		}else
			return $val;
	}
}


