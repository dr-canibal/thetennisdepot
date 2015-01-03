<?php

class Beck_LiveChat_TestController extends Mage_Core_Controller_Front_Action
{
	public function indexAction()
	{
		$api = Mage::getModel('livechat/api');
		$result = $api->refreshmessages('8-31,32,33,34');
		Zend_Debug::dump($result);
	}
}