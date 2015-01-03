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
 * @package    AW_Who_bought_this_also_bought
 * @copyright  Copyright (c) 2009-2010 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */

class AW_Relatedproducts_Helper_Data extends Mage_Core_Helper_Abstract
{
	
	/*
	 *	Take $relatedIds array and establish relations to each other
	 */
	function updateRelations($relatedIds){
		$model = Mage::getResourceModel('relatedproducts/relatedproducts');
		
		$arr = array();
		foreach($relatedIds as $id){
			//fetch relations for each of the ID's
			$model = Mage::getModel('relatedproducts/relatedproducts');
			
			$coll = $model->getCollection()
				->addProductFilter($id)
				->load();
			if(sizeof($coll) == 0){
				foreach($relatedIds as $i){
					if($i != $id) //not the product for itself
						$arr[$i] = 1; //set relation rate to 1 for all
				}
				$arr = serialize($arr);
				$model
					->setProductId($id)
					->setRelatedArray($arr)
					->save();
			}
			else{
				foreach($coll as $c){
					$incrementalId = $c->getId();
					//take current related products
					$arr = unserialize($c->getData('related_array'));
					foreach($relatedIds as $i){
						if($i != $id){ //not the product for itself
							if(!empty($arr[$i]))
								$arr[$i] += 1; //increment the relation counter
							else
								$arr[$i] = 1;
						}
					}
				}
				$arr = serialize($arr);
				$model
					->setId($incrementalId)
					->setProductId($id)
					->setRelatedArray($arr)
					->save();
			}
			$arr = array();
		}
	}
	
	public function isEnterprise()
	{
		return (Mage::getVersion() >= '1.6.0.0');	
	}		
}


