<?php

class Beck_LiveChat_Model_Event
{
	public function save_chat_session_order_placed($observer)
	{
		$event = $observer->getEvent();
		$order = $event->getOrder();
		$session_id = Mage::getSingleton('checkout/session')->getSessionId();
		$session = Mage::getModel('livechat/session')->loadBySessionId($session_id);
		if (is_object($session))
		{
			$session->PlaceOrder($order->getIncrementId());
		}
		return $this;
	}
	
	/*public function after_save($observer)
	{
		Mage::getModel('fianet/log')->log('after_save');
		$object = $observer->getEvent()->getObject();
		
		//Zend_Debug::dump($object);
	}*/
	
	public function on_customer_login($observer)
	{
		$event = $observer->getEvent();
		try
		{
			$customer_name = $event->getCustomer()->getName();
			$changechatcreator = Mage::getStoreConfig('livechatevents/oncustomerconnexion/changechatcreatorname', 0) == '1' ? true : false;
			$changemessagesautor = Mage::getStoreConfig('livechatevents/oncustomerconnexion/changeallcustomermessageautor', 0) == '1' ? true : false;
			$defaultcustomername = Mage::getStoreConfig('livechatconfiguration/display/defaultcustomername', Mage::app()->getStore(true)->getId());
			$session_id = Mage::getSingleton('checkout/session')->getSessionId();
			$session = Mage::getModel('livechat/session');
			if ($changechatcreator == true)
			{
				if ($session->Exist($session_id))
				{
					$session->setCustomer_name($customer_name)->save();
				}
			}
			if ($changemessagesautor == true)
			{
				if ($session->Exist($session_id))
				{
					$messages = $session->getCustomerMessages();
					foreach ($messages as $message)
					{
						$message->setAutor_name($customer_name)->save();
					}
				}
			}
		}
		catch(Exception $ex)
		{
		}
	}
}