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
 */
class AW_FBIntegrator_Model_Observer
{
    public function updateWall($observer){

        if(Mage::helper('fbintegrator')->extEnabled() && Mage::helper('fbintegrator')->getWallEnabled()){

            $order = $observer->getEvent()->getOrder();
			
            if(Mage::helper('fbintegrator')->isRegisteredOrder($order->getId()))
                return;
            else
                Mage::helper('fbintegrator')->registerOrder($order->getId());

            $facebook = new AW_FBIntegrator_Model_Facebook_Api(Mage::helper('fbintegrator')->getAppConfig());
            $session = $facebook->getSession();

            if($session){
                try {
                    
                    $orderItems = $order->getAllVisibleItems();
                    $store = Mage::app()->getStore();
                    $urlConfig = array(
                        '_secure'=>Mage::helper('fbintegrator')->isSecure(),
                        '_use_rewrite'=>Mage::helper('fbintegrator')->useRewrite(),
                        '_store_to_url'=>Mage::helper('fbintegrator')->addCode(),
                    );
                    $storeLink = Mage::getUrl('',$urlConfig);

                    $message = Mage::helper('fbintegrator')->getWallMessage();

                    $messageParams = array(
                        'count' => array(
                            'template'  =>  '{items_count}',
                            'real'      =>  count($order->getAllVisibleItems()),
                            ),
                        'link' => array(
                            'template'  =>  '{store_link}',
                            'real'      =>  $storeLink
                            ),
                    );

                    foreach($messageParams as $param){
                        $message = str_replace($param['template'], $param['real'], $message);
                    }

                    $description = array();
                    $media = array();
                    $countToPost = Mage::helper('fbintegrator')->getWallCount();
                    $count = ($countToPost) ? $countToPost : count($orderItems);

                    for($i=0;$i<$count;$i++){
                        $product = $orderItems[$i];

                        $productLink = (Mage::helper('fbintegrator')->useRewrite()) ? Mage::helper('fbintegrator')->getProductRewriteUrl($product->getProductId()) :'catalog/product/view/id/'.$product->getProductId();
                        $productInfo = array(
                            'count' => array(
                                'template'  =>  '{items_count}',
                                'real'      =>  $product->getQtyOrdered()
                                ),
                            'name' => array(
                                'template'  =>  '{item_name}',
                                'real'      =>  $product->getName()
                                ),
                            'price' => array(
                                'template'  =>  '{item_price}',
                                'real'      =>  $store->convertPrice($product->getBasePrice(), true, false)
                                ),
                            'link' => array(
                                'template'  =>  '{item_link}',
                                'real'      =>  $storeLink . $productLink
                                ),
                            'store' => array(
                                'template'  =>  '{store_link}',
                                'real'      =>  $storeLink
                                ),
                        );
                        $row = Mage::helper('fbintegrator')->getWallTemplate();
                        foreach($productInfo as $param){
                            $row = str_replace($param['template'], $param['real'], $row);
                        }
                        $description[] = $row;
                        $description[] = '<center></center>';//facebook line break

                        if(Mage::helper('fbintegrator')->postImagesToWall()){
                            $media[] = array(
                                'type'  =>  'image',
                                'src'   =>  Mage::getModel('catalog/product')->load($product->getProductId())->getImageUrl(),
                                'href'  =>  $storeLink . $productLink,
                            );
                        }

                    }

                    $param = array(
                        'method'     => 'stream.publish',
                        'message'    =>  $message,
                        'attachment' =>  array(
                            'description'   =>  implode(' ',$description),
                            'media' => $media,

                        ),
                    );

                    $facebook->api($param);

                } catch (Exception $e) {
                    Mage::log($e->getMessage());
                }
            }
        }
    }

    public function deleteUser($observer){
        $customer = $observer->getCustomer();
        $fb = Mage::getModel('fbintegrator/users')->load($customer->getId(),'customer_id');
        if($fb->getId())
            $fb->delete();
    }
}