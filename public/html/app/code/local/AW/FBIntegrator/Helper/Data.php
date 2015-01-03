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
class AW_FBIntegrator_Helper_Data extends Mage_Core_Helper_Abstract
{
	/*
	 * Compare param $version with magento version
	 */
	public function checkVersion($version)
	{
		return version_compare(Mage::getVersion(), $version, '>=');
	}	
	
	public function setLoginError($value)
	{
        $session = Mage::getSingleton('core/session', array('name'=>'frontend'))->start();
        $session->setAwLoginError( $value );		
		return $this;
	}

	public function getLoginError()
	{
        $session = Mage::getSingleton('core/session', array('name'=>'frontend'))->start();
        $error = $session->getAwLoginError();
        $session->setAwLoginError( null );
        return $error;
	}

	# fix for wall stream doubling
	public function registerOrder( $orderId )
	{
        $session = Mage::getSingleton('customer/session', array('name'=>'frontend'))->start();
        $orders = $session->getFaceBookPublishedOrders();

        if ( count($orders) )
        {
            $orders[] = $orderId;
        }
        else
        {
            $orders = array($orderId);
        }
        $session->setFaceBookPublishedOrders($orders);
		return $this;
	}

	public function isRegisteredOrder( $orderId )
	{
        $session = Mage::getSingleton('customer/session', array('name'=>'frontend'))->start();
        $orders = $session->getFaceBookPublishedOrders();
        if ( isset($orders) )
        {
            return in_array($orderId, $orders);
        }
        else
        {
            return false;
        }
	}
	# end fix

}