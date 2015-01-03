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
class AW_FBIntegrator_Model_Observer
{
    public function facebookPublishOrder($observer)
    {
        $isEnabled = Mage::getStoreConfigFlag('fbintegrator/facebook/enabled');
        $facebook = Mage::getSingleton('fbintegrator/facebook');
        if (!$isEnabled || !$facebook->getCurrentUid()){
            return;
        }
        $order = $observer->getEvent()->getOrder();
        if (Mage_Sales_Model_Order::STATE_NEW == $order->getState()){
			$orderId = $order->getId();
			# fix for wall stream doubling
			if ( Mage::helper('fbintegrator')->isRegisteredOrder( $orderId ) ){ return; }
			Mage::helper('fbintegrator')->registerOrder( $orderId );
			# end fix
            $facebook = Mage::getSingleton('fbintegrator/facebook');
            $template = Mage::helper('fbintegrator/template');
            list($message, $actionLinks) = $template->process($order);
            try {
                $facebook->streamPublish(
                    $facebook->getGrantedPermissions(),
                    $facebook->getSessionKey(),
                    $facebook->getCurrentUid(),
                    $message,
                    $actionLinks
                );
            } catch (Exception $e){}
        }
    }
}