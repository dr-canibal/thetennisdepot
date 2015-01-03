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
 * @copyright  Copyright (c) 2009-2010 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */
class AW_FBIntegrator_Block_Facebook extends Mage_Core_Block_Template
{
    public function isEnabled()
    {
        return Mage::getStoreConfigFlag('fbintegrator/facebook/enabled');
    }

    public function isNewsletterEnabled()
    {
        return !Mage::getStoreConfigFlag('advanced/modules_disable_output/Mage_Newsletter');
    }

    public function getApiKey()
    {
        return Mage::getStoreConfig('fbintegrator/facebook/api_key');
    }

    public function getXdUrl()
    {
        return $this->getUrl('', array('_secure' => true)) . 'xd_receiver.htm';
    }

    public function getAjaxUrl()
    {
        return $this->getUrl('fbintegrator/facebook/', array('_secure' => true));
    }

    public function getNonGrantedPermissions()
    {
        $facebook = Mage::getSingleton('fbintegrator/facebook');
        return $facebook->getNonGrantedPermissions();
    }

    protected function _toHtml()
    {
        if ($this->isEnabled()){
            return parent::_toHtml();
        }
        return '';
    }

	public function getLoginFormUrl()
	{
		return $this->getUrl('fbintegrator/facebook/loginForm');
	}

}