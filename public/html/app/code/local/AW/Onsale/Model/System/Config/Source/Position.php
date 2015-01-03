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
 * @package    AW_Onsale
 * @copyright  Copyright (c) 2009-2010 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */?>
<?php

class AW_Onsale_Model_System_Config_Source_Position {
	public function toOptionArray() {
		return array(
			array(
				'value' => 'TL',
				'label' => Mage::helper('adminhtml')->__('Top-Left')
			),
			array(
				'value' => 'TC',
				'label' => Mage::helper('adminhtml')->__('Top-Center')
			),
			array(
				'value' => 'TR',
				'label' => Mage::helper('adminhtml')->__('Top-Right')
			),
			array(
				'value' => 'BL',
				'label' => Mage::helper('adminhtml')->__('Bottom-Left')
			),
			array(
				'value' => 'BC',
				'label' => Mage::helper('adminhtml')->__('Bottom-Center')
			),
			array(
				'value' => 'BR',
				'label' => Mage::helper('adminhtml')->__('Bottom-Right')
			)
		);
	}
}