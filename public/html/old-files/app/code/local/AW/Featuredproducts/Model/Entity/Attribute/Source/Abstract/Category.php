<?php

/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/LICENSE-M1.txt
 *
 * @category   AW
 * @package    AW_Featuredproducts
 * @copyright  Copyright (c) 2008-2009 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/LICENSE-M1.txt
 */

class AW_Featuredproducts_Model_Entity_Attribute_Source_Abstract_Category extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Retrive all attribute options
     *
     * @return array
     */

	public static $options;
	public static $options_by_id;

    public function getAllOptions()
    {
		
    	if(!self::$options){
			self::$options_by_id = array();
			$_options = array(
				array('value' => 0, 'label' => '--- All ---'),
				array('value' => -1, 'label' => '--- Outside of categories ---')
			);

			$category = Mage::getModel('catalog/category');
			$tree = $category->getTreeModel();
			$tree->load();
			
			$ids = $tree->getCollection()->getAllIds();
			
			if ($ids){
				foreach ($ids as $id){
					$cat = Mage::getModel('catalog/category');
					$cat->load($id);
					$name = $cat->getName();
					if($id && $name){
						array_push($_options, array('value' => $id, 'label' => $name));
						self::$options_by_id[$id] = $name;
					}
				}
			}
			
			$this->array_key_multi_sort($_options, 'label');
			self::$options = $_options;
		
		}
		return self::$options;
    }
    
	public function getOptionText($value){
		$out = array();
		if(is_string($value)){
			$value = explode(',', $value);
		}
		$this->getAllOptions();
		if(is_array($value)){
			foreach($value as $key){
				$out[] = @self::$options_by_id[$key];
			}
		}
		return implode(',',$out) ;
	}
	
    function array_key_multi_sort(&$arr, $l , $f='strnatcasecmp') {
        return usort($arr, create_function('$a, $b', "return $f(\$a['$l'], \$b['$l']);"));
	}
}
