<?php

class Beck_LiveChat_Model_Api extends Mage_Api_Model_Resource_Abstract
{
	public function closesession($session_id)
	{
		$session_id = (int)$session_id;
		$session = Mage::getModel('livechat/session')->load($session_id);
		$session->Close();
	}

	public function getsessions($operatorName)
	{
		$result = array();
		$operator = Mage::getModel('livechat/operator');

		if ($operator->Exist($operatorName))
		{
			$sessions = $operator->getSessionsAvailable();
			
			foreach ($sessions as $session)
			{
				if (!Mage::Helper('livechat')->isSessionExpired($session))
				{
					if ($session->getDispatched() == 0 || $session->getDispatched() == $operator->getId())
					{
						$session->setDispatched($operator->getId())
							->save();
						$result[] = $session->toArray();
					}
				}
				else
				{
					$session->Expired();
				}
			}
		}
		return ($result);
	}
	
	public function getmessages($session_id)
	{
		$session_id = (int)$session_id;
		$result = array();
		$session = Mage::getModel('livechat/session');
		if ($session->Exist($session_id))
		{
			$messages = $session->getMessages();
			foreach ($messages as $message)
			{
				$result[] = $message->toArray();
			}
		}
		return ($result);
	}
	
	public function sendmessage($session_id, $autor, $message)
	{
		$session_id = $session_id;
		$session = Mage::getModel('livechat/session');
		if ($session->Exist($session_id))
		{
			$session->saveMessage($autor, $message);
			//return true;
		}
		//return false;
	}
	
	public function updatesessions($session_list)
	{
		$session_list = explode('-', $session_list);
		$res = array();
		foreach ($session_list as $session_id)
		{
			$session_id = (int)$session_id;
			$session = Mage::getModel('livechat/session')->load($session_id);
			if (Mage::Helper('livechat')->isSessionExpired($session))
			{
				$session->Expired();
			}
			$index  = count($res);
			if ($session->getId() === null)
			{
				$session = Mage::getModel('livechat/archives_session')->load($session_id);
			}
			if ($session->getId() !== null)
			{
				$res[$index]['id'] = $session_id;
				$res[$index]['customer_url'] = $session->getCustomer_url();
				$res[$index]['close'] = $session->getClose();
				$res[$index]['customer_name'] = $session->getCustomer_name();
			}
		}
		return ($res);
	}
	
	public function operatorconnect($name)
	{
		$operator = Mage::getModel('livechat/operator');
		if (!$operator->Exist($name))
		{
			$operator->setName($name)
			->save();
		}
		$operator->Connected();
	}
	
	public function operatordisconnect($name)
	{
		$operator = Mage::getModel('livechat/operator');
		if ($operator->Exist($name))
		{
			$operator->Disconnected();
		}
	}
	
	public function refreshmessages($current_messages)
	{
		$data = $this->FormatCurrentData($current_messages);
		$session = Mage::getModel('livechat/session');
		$newMessages = array();
		foreach ($data['sessions'] as $sessionData)
		{
			$session->load($sessionData['id']);
			$newMessages = array_merge($session->getNewMessages($sessionData['messages']), $newMessages);
		}
		return ($newMessages);
	}
	
	public function refreshsessions($operatorName, $current_sessions)
	{
		$current_sessions = explode('-', $current_sessions);
		$sessions = $this->getsessions($operatorName);
		
		$result = array();
		//Zend_Debug::dump($sessions, 'sessions');
		//Zend_Debug::dump($current_sessions, 'current_sessions');
		foreach ($sessions as $session)
		{
			if (!in_array($session['id'], $current_sessions, false))
			{
				$result[] = $session;
			}
		}
		return ($result);
	}
	
	public function getAllowedStores($operatorName)
	{
		//IsOperatorAffectedToStore
		$result = array();
		$operator = Mage::getModel('livechat/operator');
		if ($operator->Exist($operatorName))
		{
			$storeList = $this->getStoreList();
			foreach ($storeList['websites'] as $website)
			{
				foreach ($website['groups'] as $group)
				{
					foreach ($group['stores'] as $store)
					{
						if ($operator->IsOperatorAffectedToStore($store['id']))
						{
							$index = count($result);
							$result[$index]['websitename'] = $website['name'];
							$result[$index]['websiteid'] = (int)$website['id'];
							$result[$index]['groupname'] = $group['name'];
							$result[$index]['groupid'] = (int)$group['id'];
							$result[$index]['storename'] = $store['name'];
							$result[$index]['storecode'] = $store['code'];
							$result[$index]['storeid'] = (int)$store['id'];
						}
					}
				}
			}
		}
		//Zend_Debug::dump($result);
		return $result;
	}
	
	private function getStoreList()
	{
		$values['websites'] = array();
		$websiteCollection = Mage::getModel('core/website')->getResourceCollection();
		foreach ($websiteCollection as $website)
		{
			$values['websites'][$website->getId()]['id'] = $website->getId();
			$values['websites'][$website->getId()]['name'] = $website->getName();
			$values['websites'][$website->getId()]['code'] = $website->getCode();
			$values['websites'][$website->getId()]['groups'] = array();
			$groupCollection = $website->getGroupCollection();
			foreach ($groupCollection as $group)
			{
				$values['websites'][$website->getId()]['groups'][$group->getId()]['id'] = $group->getId();
				$values['websites'][$website->getId()]['groups'][$group->getId()]['name'] = $group->getName();
				$values['websites'][$website->getId()]['groups'][$group->getId()]['code'] = $group->getCode();
				$values['websites'][$website->getId()]['groups'][$group->getId()]['stores'] = array();
				$storeCollection = $group->getStoreCollection();
				foreach ($storeCollection as $store)
				{
					$values['websites'][$website->getId()]['groups'][$group->getId()]['stores'][$store->getId()]['id'] = $store->getId();
					$values['websites'][$website->getId()]['groups'][$group->getId()]['stores'][$store->getId()]['name'] = $store->getName();
					$values['websites'][$website->getId()]['groups'][$group->getId()]['stores'][$store->getId()]['code'] = $store->getCode();
				}
			}
		}
		return $values;
	}
	
	private function FormatCurrentData($current_Data)
	{
		$data = array();
		$sessions = explode('#', $current_Data);
		foreach ($sessions as $session)
		{
			eregi("^([0-9]+)-", $session, $regs);
			$session_id = $regs[0];
			$session = str_ireplace($session_id, '', $session);
			$session_id = str_ireplace('-', '', $session_id);
			$messages = explode(',', $session);
			$data['sessions'][$session_id]['id'] = $session_id;
			foreach ($messages as $message)
			{
				$data['sessions'][$session_id]['messages'][$message] = $message;
			}
		}
		return ($data);
	}
}