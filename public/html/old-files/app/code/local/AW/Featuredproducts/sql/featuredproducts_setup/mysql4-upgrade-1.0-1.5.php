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

$installer = $this;

/* $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');


$setup->addAttribute('catalog_product', 'aw_fp_category', array(
        'backend'       => '',
        'source'        => 'featuredproducts/entity_attribute_source_abstract_category',
        'group'			=> 'Featured',
        'label'         => 'Category',
        'input'         => 'select',
        'class'         => '',
        'global'        => true,
        'visible'       => true,
        'required'      => false,
        'user_defined'  => false,
        'default'       => '0',
        'visible_on_front' => false
    ));    
    
//Set the category attribute to provide migration facility
$_productCollection = Mage::getModel('catalog/product')
    ->getCollection()
    ->addAttributeToFilter('aw_fp_enabled', 1)
    ->load();
    
    
foreach($_productCollection as $_product){
	if(!$_product->getAw_fp_category())
		$_product->setAw_fp_category('0');
	$_product->save();
}

$installer->endSetup();
