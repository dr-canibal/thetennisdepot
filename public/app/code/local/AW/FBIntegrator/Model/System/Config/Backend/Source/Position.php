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
 * @package    AW_Facebooklink
 * @copyright  Copyright (c) 2010-2011 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */?>
<?php
class AW_FBIntegrator_Model_System_Config_Backend_Source_Position
{
    const PRODUCT_PAGE_POSITION = 'product';
    const CMS_PAGE_POSITION     = 'cms';

    public function toOptionArray()
    {
        return array(
            array('value'=> 'product', 'label'=>Mage::helper('fbintegrator')->__('Product page')),
            array('value'=> 'cms', 'label'=>Mage::helper('fbintegrator')->__('Custom')),
        );
    }
}