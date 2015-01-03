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
class AW_FBIntegrator_Helper_Data extends Mage_Core_Helper_Abstract
{
	/*
	 * Compare param $version with magento version
	 */
	public function checkVersion($version)
	{
		return version_compare(Mage::getVersion(), $version, '>=');
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

    public function extEnabled(){
        return Mage::getStoreConfigFlag('fbintegrator/general/enabled', Mage::app()->getStore()->getId());
    }

    public function getAppKey(){
        return Mage::getStoreConfig('fbintegrator/app/api_key', Mage::app()->getStore()->getId());
    }

    public function getAppSecret(){
        return Mage::getStoreConfig('fbintegrator/app/secret', Mage::app()->getStore()->getId());
    }

    public function getWallEnabled(){
        return Mage::getStoreConfigFlag('fbintegrator/wall/enabled', Mage::app()->getStore()->getId());
    }
    public function getWallMessage(){
        return Mage::getStoreConfig('fbintegrator/wall/post_message', Mage::app()->getStore()->getId());
    }

    public function getWallTemplate(){
        return Mage::getStoreConfig('fbintegrator/wall/post_link_template', Mage::app()->getStore()->getId());
    }

    public function getWallCount(){
        return Mage::getStoreConfig('fbintegrator/wall/items_count', Mage::app()->getStore()->getId());
    }
    public function postImagesToWall(){
        return Mage::getStoreConfigFlag('fbintegrator/wall/picture', Mage::app()->getStore()->getId());
    }

    public function getProductRewriteUrl($productId){
        $collection = Mage::getModel('core/url_rewrite')->getCollection();
        $collection->getSelect()
                ->where('product_id = ?', $productId)
                ->where('store_id = ?',Mage::app()->getStore()->getId())
                ;

        if(count($collection)){
            $path = $collection->getColumnValues('request_path');
            return reset($path);
        }
        else
            return 'catalog/product/view/id/'.$productId;
    }

    public function isSecure(){
        return Mage::getStoreConfig('web/secure/use_in_frontend',Mage::app()->getStore()->getId());
    }

    public function addCode(){
        return Mage::getStoreConfig('web/url/use_store',Mage::app()->getStore()->getId());
    }

    public function useRewrite(){
        return Mage::getStoreConfig('web/seo/use_rewrites',Mage::app()->getStore()->getId());
    }

    public function checkApp($appId = null,$appSecret = null){
        $config = array(
            'appId'  => ($appId) ? $appId : Mage::helper('fbintegrator')->getAppKey(),
            'secret' => ($appSecret) ? $appSecret :Mage::helper('fbintegrator')->getAppSecret(),
            'cookie' => true,
        );

        $facebook = new AW_FBIntegrator_Model_Facebook_Api($config);
        try {
            $session = $facebook->api('/kozhemyako');
            return true;
        } catch (Exception $exc) {
            return false;
        }
    }

    public function likeEnabled(){
        return Mage::getStoreConfigFlag('fbintegrator/like/enabled', Mage::app()->getStore()->getId());
    }
    public function likePosition(){
        return Mage::getStoreConfig('fbintegrator/like/position', Mage::app()->getStore()->getId());
    }
    public function likeStyle(){
        return array(
            'laystyle'  => Mage::getStoreConfig('fbintegrator/like/laystyle', Mage::app()->getStore()->getId()),
            'face'      => Mage::getStoreConfig('fbintegrator/like/face', Mage::app()->getStore()->getId()),
            'width'     => Mage::getStoreConfig('fbintegrator/like/width', Mage::app()->getStore()->getId()),
            'verb'      => Mage::getStoreConfig('fbintegrator/like/verb', Mage::app()->getStore()->getId()),
            'font'      => Mage::getStoreConfig('fbintegrator/like/font', Mage::app()->getStore()->getId()),
            'color'     => Mage::getStoreConfig('fbintegrator/like/color', Mage::app()->getStore()->getId()),
        );
    }

    public function getAppConfig(){
        return array(
            'appId'  => $this->getAppKey(),
            'secret' => $this->getAppSecret(),
            'cookie' => true,
        );
    }

    public function getMe(){
        $facebook = new AW_FBIntegrator_Model_Facebook_Api($this->getAppConfig());
        $session = $facebook->getSession();
        if($session){

            try {
                return $facebook->api('/me');
            } catch (Exception $exc) {
                return null;
            }
        }
        else
            return null;
    }

    public function getRequiredFields(){

        $attribues = array();
        //$codes = array('prefix','suffix','dob','taxvat','gender');
        $codes = array('prefix','suffix','taxvat');
        foreach($codes as $code){
            $att = Mage::getSingleton('eav/config')->getAttribute('customer', $code);

            $attribues[$code] = array(
                  'code'    =>  $code,
                  'name'    =>  $att->getFrontendLabel(),
                  //'required'=>  $att->getIsRequired(),//works at 1400 and above
                  'required'=>  Mage::getStoreConfig('customer/address/'.$code.'_show') == 'req', //works at 1330 and above
                  'values'  =>  explode(';',Mage::helper('customer/address')->getConfig($code . '_options')),
            );
        }

        return $attribues;
    }

    public function getCountRequiredFields(){
        $array = $this->getRequiredFields();
        $count = 0;
        foreach($array as $field){
            if($field['required'])
                $count++;
        }
        return $count;
    }

    public function getRequiredFormUrl(){
        return Mage::getUrl('fbintegrator/facebook/form');
    }

    public function setFbBackUrl($url){
        $session = Mage::getSingleton('core/session', array('name'=>'frontend'))->start();
        $session->setFbBackUrl($url);
        return $this;
    }
    public function getFbBackUrl(){
        $session = Mage::getSingleton('core/session', array('name'=>'frontend'))->start();
        if(Mage::getStoreConfigFlag('customer/startup/redirect_dashboard', Mage::app()->getStore()->getId()))
            return Mage::getUrl('customer/account');
        else
            return $session->getFbBackUrl();
    }

    public function getStoreLogo(){
        return Mage::getDesign()->getSkinUrl(Mage::getStoreConfig('design/header/logo_src', Mage::app()->getStore()->getId()));
    }
}