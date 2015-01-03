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
class AW_FBIntegrator_Model_Facebook extends AW_FBIntegrator_Model_Facebook_Api
{
    public function __construct()
    {
        $this->setApiKey(Mage::getStoreConfig('fbintegrator/facebook/api_key'));
        $this->setSecret(Mage::getStoreConfig('fbintegrator/facebook/secret'));
    }

    public function getCurrentUid()
    {
        return Mage::app()->getRequest()->getCookie($this->getApiKey().'_user');
    }

    public function getSessionKey()
    {
        return Mage::app()->getRequest()->getCookie($this->getApiKey().'_session_key');
    }

    public function getPermissions()
    {
        $permissions = array(
            'publish_stream',
            //'status_update',
            //'photo_upload',
            //'share_item'
        );
        return $permissions;
    }

    public function getGrantedPermissions($facebookId = null)
    {
        if (!$facebookId){
            $facebookId = $this->getCurrentUid();
        }
        $collection = Mage::getResourceModel('customer/customer_collection');
        $collection->addAttributeToFilter('facebook_id', array('eq' => $facebookId));
        $collection->addAttributeToSelect('facebook_permissions');
        $publishType = new Mage_Customer_Model_Config_Share();
        if ($publishType->isWebsiteScope()){
            $siteId = Mage::app()->getWebsite()->getId();
            $collection->addAttributeToFilter('website_id', array('eq' => $siteId));
        }

        $customer = $collection->getFirstItem();
        if ($customer->getId()){
            return (array) explode(',', $customer->getFacebookPermissions());
        }
        return array();
    }

    public function getNonGrantedPermissions($facebookId = null)
    {
        $permissions = $this->getPermissions();
        $grantedPermissions = $this->getGrantedPermissions();
        return array_diff($permissions, $grantedPermissions);
    }
}