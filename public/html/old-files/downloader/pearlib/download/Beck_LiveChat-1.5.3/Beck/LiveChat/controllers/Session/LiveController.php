<?php

class Beck_LiveChat_Session_LiveController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
	{
		$this->loadLayout();
		
		$session = Mage::getSingleton('livechat/adminSession');
		$operatorName = $session->getData('OperatorName');
		$operator = Mage::getModel('livechat/operator');
		
		if ($operator->Exist($operatorName))
		{
			$sessions = $operator->getSessionsAvailable();
			if (count($sessions) > 0)
			{
				foreach ($sessions as $session)
				{
					if (!Mage::Helper('livechat')->isSessionExpired($session))
					{
						if ($session->getDispatched() == 0 || $session->getDispatched() == $operator->getId())
						{
							$session->setDispatched($operator->getId())
								->save();
							$this->AddChatSession($session);
						}
					}
					else
					{
						$session->Expired();
					}
				}
			}
		}
		else
		{
			$session->setData('OperatorName', null);
		}
		
		$this->renderLayout();
	}
	
	public function updaterAction()
	{
		$post = $this->getRequest()->getPost();
		if (isset($post['sessionId']))
		{
			$session = Mage::getModel('livechat/session')->load($post['sessionId']);
			$messages = $session->getMessages();
			$customerUrl = $session->getCustomer_url();
			foreach ($messages as $message)
			{
				if ($message->getIs_customer_message() == '1')
				{
					echo $message->renderLine($customerUrl);
				}
				else
				{
					echo $message->renderLine();
				}
			}
			if ($session->getClose() == '1')
			{
				echo "<br />".$this->__('This session is closed, you cannot send message.');
				echo "<br /><button class=\"form-button-alt delete\" onclick=\"javascript:CloseChatBox_".$post['sessionId']."();\">";
				echo "<span>".$this->__('Close')."</span></button>";
			}
		}
	}
	
	public function sendMessageAction()
	{
		$post = $this->getRequest()->getPost();
		if (isset($post['sessionId']))
		{
			$message = $post['message'];
			if (trim($message) != '')
			{
				$session = Mage::getModel('livechat/session')->load($post['sessionId']);
				if ($session->getClose() == '0')
				{
					$adminsession = Mage::getSingleton('livechat/adminSession');
					$name = $adminsession->getData('OperatorName');
					$message = $session->saveMessage($name, $message);
					echo $message->renderLine();
				}
			}
		}
	}
	
	protected function checkUserPermission($login, $key)
	{
		$res = false;
		$user = new Mage_Api_Model_User();
		if ($user->authenticate($login, $key))
		{
			$user->loadByUsername($login);
			$roles = $user->getRoles();
			foreach ($roles as $rid)
			{
				//echo 'Role #' . $rid.'<br>';
				$rules_set = Mage::getResourceModel('api/rules_collection')->getByRoles($rid)->load();
				foreach ($rules_set as $rule)
				{
					//echo 'Rule #'.$rule->getId() . '('.$rule->getResource_id().')<br>';
					if ($rule->getResource_id() == 'all' && $rule->getPermission() == 'allow')
					{
						$res = true;
					}
					if ($rule->getResource_id() == 'livechat' && $rule->getPermission() == 'allow')
					{
						$res = true;
					}
				}
			}
		}
		return $res;
	}
	
	public function loginAction()
	{
		$post = $this->getRequest()->getPost();
		if (isset($post['Login']) && isset($post['Key']))
		{
			$res = $this->checkUserPermission($post['Login'], $post['Key']);
			$session = Mage::getSingleton('livechat/adminSession');
			if ($res)
			{
				$operator = Mage::getModel('livechat/operator');
				if ($operator->Exist($post['Login']))
				{
					
					$session->setData('OperatorName', $post['Login']);
					$operator->Connected();
				}
				else
				{
					$operator->setName($post['Login'])->save();
					$session->setData('OperatorName', $post['Login']);
					$url = $this->getUrl('livechat/operator_list/edit', array('id'=>$operator->getId()));
					$session->addSuccess(Mage::Helper('livechat')->__('Operator %s has just been created configure the store allowed for him <a href=\'%s\'>here</a>.', $post['Login'], $url));
					$operator->Connected();
				}
			}
			else
			{
				$session->addError($this->__('Access denied for %s', $post['Login']));
			}
		}
		$this->_redirect('*/*/index');
	}
	
	public function logoutAction()
	{
		$post = $this->getRequest()->getPost();
		$session = Mage::getSingleton('livechat/adminSession');
		$operatorName = $session->getData('OperatorName');
		$operator = Mage::getModel('livechat/operator');
		$session->setData('OperatorName', null);
		if ($operator->Exist($operatorName))
		{
			$operator->Disconnected();
			$session->addSuccess($this->__('Operator %s is now disconnected and no longer appear online.', $operatorName));
		}
		$this->_redirect('*/*/index');
	}
	
	public function AddChatSession(Beck_LiveChat_Model_Session $session)
	{
		$this->getLayout()->getBlock('livechat.sessions.live')->AddChatSession($session);
	}
	
	public function closeSessionAction()
	{
		$post = $this->getRequest()->getPost();
		if (isset($post['sessionId']))
		{
			$session = Mage::getModel('livechat/session')->load($post['sessionId']);
			$session->Close();
		}
	}
}