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
class AW_Featuredproducts_Model_Entity_Attribute_Source_Abstract_Image extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Retrive all attribute options
     *
     * @return array
     */

    public function getAllOptions()
    {
    	
		$_options = array(
			array('value' => 0, 'label' => '--- Default ---'),
			
		);
		if($product = Mage::registry('product')){
			$images = ($product->getMediaGallery('images'));
			if(count($images)){
				foreach($images as $image){
					$_options[] = array(
						'value' => $image['file'],
						'label' => ($image['label'] ? "{$image['label']} | " : "")
										.basename($image['file'])
					);
				}
			}
		}
		return $_options;
        
    }
    
    function array_key_multi_sort(&$arr, $l , $f='strnatcasecmp') {
        return usort($arr, create_function('$a, $b', "return $f(\$a['$l'], \$b['$l']);"));
	}
}
