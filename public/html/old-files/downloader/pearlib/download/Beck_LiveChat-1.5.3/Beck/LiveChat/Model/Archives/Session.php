<?php

class Beck_LiveChat_Model_Archives_Session extends Beck_LiveChat_Model_Session
{
	protected function _construct()
	{
		parent::_construct();
		$this->_init('livechat/archives_session');
	}
	
	
	public function saveMessage($autor, $message, $customer_message = '0')
	{
		if ($this->getId() > 0)
		{
			$message = eregi_replace("(http://)(([[:punct:]]|[[:alnum:]])*)", "<a href=\"\\0\">\\2</a>", $message);
			$sessionmessage = Mage::getModel('livechat/archives_message');
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
			$messages = Mage::getModel('livechat/archives_message')->getCollection()
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
			$messages = Mage::getModel('livechat/archives_message')->getCollection()
				->addFilter('livechat_session_id', $this->getId(), 'and')
				->addFilter('is_customer_message', '1', 'and')
				->setOrder('date', Varien_Data_Collection::SORT_ORDER_ASC);
			return ($messages);
		}
		return null;
	}
	
	public function archive(Beck_LiveChat_Model_Session $session)
	{
		if ($session->getId() > 0)
		{
			$this->setCustomer_name($session->getCustomer_name());
			$this->setDate_started($session->getDate_started());
			$this->setCustomer_session_id($session->getCustomer_session_id());
			$this->setDispatched($session->getDispatched());
			$this->setStore_id($session->getStore_id());
			$this->setOrder_placed($session->getOrder_placed());
			$this->setClose('1');
			$this->setCustomer_url($session->getCustomer_url());
			$this->save();
			$messages = $session->getMessages();
			foreach ($messages as $message)
			{
				$archived_message = Mage::getModel('livechat/archives_message');
				$archived_message->setLivechat_session_id($this->getId());
				$archived_message->setAutor_name($message->getAutor_name());
				$archived_message->setIs_customer_message($message->getIs_customer_message());
				$archived_message->setMessage($message->getMessage());
				$archived_message->setDate($message->getDate());
				$archived_message->save();
			}
			$session->destroy();
		}
		return $this;
	}
}