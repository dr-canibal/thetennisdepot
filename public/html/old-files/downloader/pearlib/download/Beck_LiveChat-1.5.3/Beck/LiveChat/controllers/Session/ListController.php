<?php

class Beck_LiveChat_Session_ListController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
	{
		$this->loadLayout();
		
		$this->getOnlineCustomers();
		
		$this->renderLayout();
	}
	
	protected function getOnlineCustomers()
	{
		$customerOnline = Mage::getResourceSingleton('log/visitor_collection')
						->useOnlineFilter();
		$list = array();
		foreach ($customerOnline as $customer)
		{
			$list[] = $customer->getSession_id();
		}
		$session = Mage::getSingleton('livechat/adminSession');
		$session->setData('customerOnline', $list);
	}
	
	public function gridAction()
    {
        $this->loadLayout();
		$this->getOnlineCustomers();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('livechat/session_grid')->toHtml()
        );
    }
	
	public function detailAction()
	{
		$this->loadLayout();
		$this->renderLayout();
	}
	
	public function detailGridAction()
	{
		$this->loadLayout();
		$this->getResponse()->setBody(
            $this->getLayout()->createBlock('livechat/session_detail')->toHtml()
        );
	}
	
	public function massDeleteAction()
    {
		$sessionIds = $this->getRequest()->getParam('session');
        if (!is_array($sessionIds))
		{
            $this->_getSession()->addError($this->__('Please select chat(s)'));
        }
        else
		{
            try
			{
                foreach ($sessionIds as $sessionId)
				{
                    $session = Mage::getSingleton('livechat/archives_session')->load($sessionId);
                    Mage::dispatchEvent('livechat_controller_session_delete', array('session' => $session));
                    $session->Destroy();
                }
                $this->_getSession()->addSuccess($this->__('Total of %d record(s) were successfully deleted', count($sessionIds)));
            }
			catch (Exception $e)
			{
                $this->_getSession()->addError($e->getMessage());
            }
        }
		$this->_redirect('*/*', array('type'=>'archive'));
	}
	
	public function massCloseAction()
    {
		$sessionIds = $this->getRequest()->getParam('session');
        if (!is_array($sessionIds))
		{
            $this->_getSession()->addError($this->__('Please select chat(s)'));
        }
        else
		{
            try
			{
                foreach ($sessionIds as $sessionId)
				{
                    $session = Mage::getSingleton('livechat/session')->load($sessionId);
                    Mage::dispatchEvent('livechat_controller_session_close', array('session' => $session));
                    $session->Close();
                }
                $this->_getSession()->addSuccess($this->__('Total of %d record(s) were successfully closed', count($sessionIds)));
            }
			catch (Exception $e)
			{
                $this->_getSession()->addError($e->getMessage());
            }
        }
		$this->_redirect('*/*');
	}
	
	public function massOpenAction()
    {
		$sessionIds = $this->getRequest()->getParam('session');
        if (!is_array($sessionIds))
		{
            $this->_getSession()->addError($this->__('Please select chat(s)'));
        }
        else
		{
            try
			{
                foreach ($sessionIds as $sessionId)
				{
                    $session = Mage::getSingleton('livechat/session')->load($sessionId);
                    Mage::dispatchEvent('livechat_controller_session_open', array('session' => $session));
                    $session->Open();
                }
                $this->_getSession()->addSuccess($this->__('Total of %d record(s) were successfully opened', count($sessionIds)));
            }
			catch (Exception $e)
			{
                $this->_getSession()->addError($e->getMessage());
            }
        }
		$this->_redirect('*/*');
	}
	
	public function massMessageDeleteAction()
    {
		$messageIds = $this->getRequest()->getParam('message');
		$sessionId = $this->getRequest()->getParam('sessionId', 0);
		$type = $this->getRequest()->getParam('type', 'standard');
        if (!is_array($messageIds))
		{
            $this->_getSession()->addError($this->__('Please select message(s)'));
        }
        else
		{
            try
			{
                foreach ($messageIds as $messageId)
				{
					if ($type == 'standard')
					{
						$message = Mage::getSingleton('livechat/message')->load($messageId);
						Mage::dispatchEvent('livechat_controller_message_delete', array('message' => $message));
						$message->delete();
					}
					elseif ($type == 'archive')
					{
						$message = Mage::getSingleton('livechat/archives_message')->load($messageId);
						Mage::dispatchEvent('livechat_controller_message_delete', array('message' => $message));
						$message->delete();
					}
                }
                $this->_getSession()->addSuccess($this->__('Total of %d record(s) were successfully deleted', count($messageIds)));
            }
			catch (Exception $e)
			{
                $this->_getSession()->addError($e->getMessage());
            }
        }
		$this->_redirect('*/*/detail', array('sessionId'=>$sessionId, 'type'=>$type));
	}
	
	public function  massArchivateAction()
	{
		$sessionIds = $this->getRequest()->getParam('session');
        if (!is_array($sessionIds))
		{
            $this->_getSession()->addError($this->__('Please select chat(s)'));
        }
        else
		{
            try
			{
                foreach ($sessionIds as $sessionId)
				{
                    $session = Mage::getSingleton('livechat/session')->load($sessionId);
					$archive = Mage::getModel('livechat/archives_session')->archive($session);
                    Mage::dispatchEvent('livechat_controller_session_archived', array('session' => $session));
					
				}
                $this->_getSession()->addSuccess($this->__('Total of %d record(s) were successfully archived', count($sessionIds)));
            }
			catch (Exception $e)
			{
                $this->_getSession()->addError($e->getMessage());
            }
        }
		$this->_redirect('*/*');
	}
}