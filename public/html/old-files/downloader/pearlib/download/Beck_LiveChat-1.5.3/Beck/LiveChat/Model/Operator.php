<?php

class Beck_LiveChat_Model_Operator extends Mage_Core_Model_Abstract 
{
	protected function _construct()
	{
		parent::_construct();
		$this->_init('livechat/operator');
	}
	
	public function IsOperatorAffectedToStore($store_id)
	{
		$data = $this->getStore_allowed();
		if ($data != null)
		{
			$AffectedStore = explode('-', $data);
			
			foreach ($AffectedStore as $store)
			{
				if ($store == $store_id)
				{
					return true;
				}
			}
		}
		return false;
	}
	
	public function loadByName($name)
	{
		$this->_getResource()->load($this, $name, 'name');
		return ($this);
	}
	
	public function Exist($name)
	{
		$this->loadByName($name);
		if ($this->getId() > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public function getSessionsAvailable()
	{
		$result = array();
		if ($this->getId() > 0)
		{
			$sessions = Mage::getModel('livechat/session')->getCollection()
						->addFilter('close', '0', 'and')
						->setOrder('date_started', Varien_Data_Collection::SORT_ORDER_ASC);
			
			foreach ($sessions as $session)
			{
				if ($this->IsOperatorAffectedToStore($session->getStore_id()))
				{
					$result[] = $session;
				}
			}
		}
		return ($result);
	}
	
	public function getDispatchedSessions()
	{
		$sessions = array();
		if ($this->getid() > 0)
		{
			$sessions = Mage::getModel('livechat/session')->getCollection()
						->addFilter('dispatched', $this->getId(), 'and');
		}
		return $sessions;
	}
	
	protected function UndispatchSessions()
	{
		if ($this->getId() > 0)
		{
			$sessions = $this->getDispatchedSessions();
			foreach ($sessions as $session)
			{
				if ($session->getClose() == '0')
				{
					$session->setDispatched(0)->save();
				}
			}
		}
		return $this;
	}
	
	protected function CloseSessions()
	{
		if ($this->getId() > 0)
		{
			$sessions = $this->getDispatchedSessions();
			foreach ($sessions as $session)
			{
				$session->close();
			}
		}
		return $this;
	}
	
	public function Connected()
	{
		if ($this->getId() > 0)
		{
			$this->setIs_online('1')->save();
		}
		return $this;
	}
	
	public function Disconnected()
	{
		if ($this->getId() > 0)
		{
			$this->setIs_online('0')->save();
			$undispatch_sessions = Mage::getStoreConfig('livechatevents/onoperatordisconnect/undispatchsession', 0) == '1' ? true : false;
			$close_sessions = Mage::getStoreConfig('livechatevents/onoperatordisconnect/closesession', 0) == '1' ? true : false;
			if ($close_sessions)
			{
				$this->CloseSessions();
			}
			if ($undispatch_sessions == true)
			{
				$this->UndispatchSessions();
			}		
		}
		return $this;
	}
}