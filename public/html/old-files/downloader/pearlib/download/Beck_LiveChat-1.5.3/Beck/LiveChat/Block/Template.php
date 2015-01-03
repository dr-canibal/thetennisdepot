<?php

class Beck_LiveChat_Block_Template extends Mage_Core_Block_Template
{
	public $isActive = false;
	public $refreshfrequency = 15;
	public $refreshdecay = 1;
	public $chatlabel = 'Need help ? Ask live !';
	public $imageStyle = '3';
	public $unavailablelabel = 'Sorry';
	public $titlelabel = 'LIVECHAT';
	public $nb_message_max = 10;
	
	protected function _construct()
	{
		parent::_construct();
		$this->isActive				= $this->getConfigData('livechatconfiguration/general/active') == '1' ? true : false;
		
		$limited_to_registered_user = $this->getConfigData('livechatconfiguration/display/limitregisteredusers') == '1' ? true : false;
		if ($limited_to_registered_user == true)
		{
			$this->isActive = Mage::Helper('customer/data')->isLoggedIn();
		}
		
		if ($this->isActive)
		{
			$this->refreshfrequency		= $this->getConfigData('livechatconfiguration/general/refreshfrequency');
			$this->refreshdecay			= $this->getConfigData('livechatconfiguration/general/refreshdecay');
			$this->chatlabel			= $this->getConfigData('livechatconfiguration/display/chatlabel');
			$this->imageStyle			= $this->getConfigData('livechatconfiguration/display/imagestyle');
			$this->unavailablelabel		= $this->getConfigData('livechatconfiguration/display/unavailablelabel');
			$this->titlelabel			= $this->getConfigData('livechatconfiguration/display/titlelabel');
			$this->nb_message_max 		= $this->getConfigData('livechatconfiguration/general/nbmaxlines');
			$session_id = Mage::getSingleton('checkout/session')->getSessionId();
			//$session_id = Mage::getSingleton('checkout/session')->getEncryptedSessionId();
			$session = Mage::getModel('livechat/session');
			if ($session->Exist($session_id))
			{
				$url = 'http://' . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
				if ($session->getCustomer_url() != $url && $session->getClose() == '0')
				{
					$session->setCustomer_url($url)->save();
				}
			}
			else
			{
				$sessionCreation = $this->getConfigData('livechatconfiguration/general/sessioncreation');
				if ($sessionCreation != '0')
				{
					if (Mage::Helper('customer/data')->isLoggedIn())
					{
						$customer_name = Mage::getSingleton('customer/session')->getCustomer()->getName();
					}
					else
					{
						$customer_name = Mage::getStoreConfig('livechatconfiguration/display/defaultcustomername', $this->getStoreId());
					}
					$message = trim(Mage::getStoreConfig('livechatconfiguration/display/systemautosessionmessage', $this->getStoreId()));
					if ($sessionCreation == '1')
					{
						$session->setCustomer_name($customer_name)
							->setCustomer_session_id($session_id)
							->setDate_started(now())
							->setStore_id($this->getStoreId())
							->save();
						if ($message != '')
						{
							$session->saveMessage(Mage::getStoreConfig('livechatconfiguration/display/systemname', $this->getStoreId()), $message);
						}
					}
					elseif ($sessionCreation == '2' && Mage::Helper('customer/data')->isLoggedIn())
					{
						$session->setCustomer_name($customer_name)
							->setCustomer_session_id($session_id)
							->setDate_started(now())
							->setStore_id($this->getStoreId())
							->save();
						if ($message != '')
						{
							$session->saveMessage(Mage::getStoreConfig('livechatconfiguration/display/systemname', $this->getStoreId()), $message);
						}
					}
				}
			}
		}
	}
	
	protected function getConfigData($path)
	{
		return (Mage::getStoreConfig($path, $this->getStoreId()));
	}
	
	public function ChatSessionStarted()
	{
		$session_id = Mage::getSingleton('checkout/session')->getSessionId();
		return (Mage::getModel('livechat/session')->Exist($session_id));
	}
	
	public function returnChatImage()
	{
		$url =$this->getSkinUrl('images/livechat/livechat_icon_'.$this->imageStyle.'_offline.gif');
		if ($this->AreOperatorOnline())
		{
			$url = $this->getSkinUrl('images/livechat/livechat_icon_'.$this->imageStyle.'_online.gif');
		}
		$img = '<img src="'.$url.'" alt="'.$this->chatlabel.'" class="livechat-image">';
		return $img;
	}
	
	public function AreOperatorOnline()
	{
		$operatorCollection = Mage::getModel('livechat/operator')->getCollection();
		$nb  = 0;
		foreach ($operatorCollection as $operator)
		{
			if ($operator->getIs_online() == '1')
			{
				if ($operator->IsOperatorAffectedToStore($this->getStoreId()))
				{
					$nb++;
				}
			}
		}
		if ($nb > 0)
		{
			return true;
		}
		return false;
	}
	
	public function getStoreId()
	{
		return (Mage::app()->getStore(true)->getId());
	}
	
	public function getMessages()
	{
		$session_id = Mage::getSingleton('checkout/session')->getSessionId();
		$session = Mage::getModel('livechat/session');
		if ($session->Exist($session_id))
		{
			
			$messages = $session->getMessages()->limit($this->nb_message_max);
			return ($messages);
			//Zend_Debug::dump($messages);
		}
		return (null);
	}
}