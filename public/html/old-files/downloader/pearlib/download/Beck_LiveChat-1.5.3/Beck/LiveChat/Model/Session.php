<?php

class Beck_LiveChat_Model_Session extends Mage_Core_Model_Abstract
{
	protected function _construct()
	{
		parent::_construct();
		$this->_init('livechat/session');
	}
	
	public function loadBySessionId($session_id)
	{
		$this->_getResource()->load($this, $session_id, 'customer_session_id');
		return ($this);
	}
	
	public function Exist($session_id)
	{
		if ($this->getId() > 0)
		{
			return true;
		}
		else
		{
			$this->loadBySessionId($session_id);
			if ($this->getId() > 0)
			{
				return true;
			}
		}
		return false;
	}
	
	public function saveMessage($autor, $message, $customer_message = '0')
	{
		if ($this->getId() > 0)
		{
			$message = eregi_replace("(http://)(([[:punct:]]|[[:alnum:]])*)", "<a href=\"\\0\">\\2</a>", $message);
			$sessionmessage = Mage::getModel('livechat/message');
			$sessionmessage->setLivechat_session_id($this->getId())
					->setAutor_name($autor)
					->setMessage($message)
					->setIs_customer_message($customer_message)
					->setDate(now())
					->save();
			return ($sessionmessage);
		}
		return null;
	}
	
	public function getMessages()
	{
		if ($this->getId() > 0)
		{
			$messages = Mage::getModel('livechat/message')->getCollection()
				->addFilter('livechat_session_id', $this->getId(), 'and')
				->setOrder('date', Varien_Data_Collection::SORT_ORDER_ASC);
			return ($messages);
		}
		return null;
	}
	
	public function getCustomerMessages()
	{
		if ($this->getId() > 0)
		{
			$messages = Mage::getModel('livechat/message')->getCollection()
				->addFilter('livechat_session_id', $this->getId(), 'and')
				->addFilter('is_customer_message', '1', 'and')
				->setOrder('date', Varien_Data_Collection::SORT_ORDER_ASC);
			return ($messages);
		}
		return null;
	}
	
	public function Destroy()
	{
		if ($this->getId() > 0)
		{
			$messages = $this->getMessages();
			if ($messages != null)
			{
				foreach ($messages as $message)
				{
					$message->delete();
				}
			}
			$this->delete();
		}
	}
	
	public function Expired()
	{
		$this->Close();
		$archivate = Mage::getStoreConfig('livechatevents/onsessionexpire/archive', 0) == '1' ? true : false;
		if ($archivate)
		{
			$archive = Mage::getModel('livechat/archives_session');
			$archive->archive($this);
			return $archive;
		}
		return $this;
	}
	
	/*
	public function Expired(array $customerOnlines)
	{
		$result = true;
		if ($this->getId() > 0)
		{
			if (is_array($customerOnlines))
			{
	            foreach ($customerOnlines as $customerSessionID)
	            {
	            	if ($customerSessionID == $this->getCustomer_session_id())
	            	{
	            		$result = false;
	            	}
	            }
			}
			else
			{
				return false;
			}
		}
		return ($result);
	}
	*/

	public function getNewMessages($CurrentMessages)
	{
		$newMessages = array();
		if ($this->getId() > 0)
		{
			$messagesList = $this->getMessages();
			
			foreach ($messagesList as $message)
			{
				if (!in_array($message->getId(), $CurrentMessages, false))
				{
					$newMessages[] = $message->toArray();
				}
			}
		}
		return $newMessages;
	}
	
	public function Close()
	{
		if ($this->getId() > 0)
		{
			if ($this->getClose() == '0')
			{
				$sendclosemessage = Mage::getStoreConfig('livechatevents/onsessionclose/sendclosemessage', 0) == '1' ? true : false;
				if ($sendclosemessage == true)
				{
					$this->saveMessage(Mage::getStoreConfig('livechatconfiguration/display/systemname', $this->getStore_id()), Mage::Helper('livechat')->__('-- Session terminated --'));
				}
				$this->setClose('1')->save();
			}
		}
		return ($this);
	}
	
	public function Open()
	{
		if ($this->getId() > 0)
		{
			$this->setClose('0')->save();
			if ($this->getDispatched() > 0)
			{
				$operator = Mage::getModel('livechat/operator')->load($this->getDispatched());
				if ($operator->getIs_online() == '0')
				{
					$this->setDispatched(0)->save();
				}
			}
		}
		return ($this);
	}
	
	public function PlaceOrder($increment_id)
	{
		if ($this->getId() > 0)
		{
			$this->setOrder_placed($increment_id)->save();
		}
		return ($this);
	}
}