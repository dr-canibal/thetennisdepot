<?php

class Beck_LiveChat_OperatorController extends Mage_Adminhtml_Controller_Action
{
	
	public function indexAction()
	{
		$this->loadLayout();
		$this->renderLayout();
	}
	
	public function createAction()
	{
		$this->loadLayout();
		$this->renderLayout();
	}
	
	public function editAction()
	{
		$this->loadLayout();
		$this->renderLayout();
	}
	
	public function saveAction()
	{
		$post = $this->getRequest()->getPost();
		//Zend_Debug::dump($post);
		try
		{
			if (empty($post))
			{
				Mage::throwException($this->__('Invalid form data.'));
			}
			
			if (is_array($post))
			{
				//Zend_Debug::dump($post);
				$operator = Mage::getModel('livechat/operator')->load($post['id']);
				if ($post['password'] != null && trim($post['password']) != '')
				{
					$operator->setPassword(md5($post['password']));
				}
				$operator->setName($post['name'])
					->setIs_active($post['active'])
					->save();
				if ($operator->getId() != null)
				{
					Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Operator %s has been successfully modified.', $post['name']));
				}
			}
		}
		catch (Exception $e)
		{
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		}
		$this->_redirect('adminhtml/system_config/edit', array('section'=>'livechatoperators'));
	}
	/*
	public function deleteAction()
	{
		$OperatorId = $this->getRequest()->getParam('id', 0);
		$Operator = Mage::getModel('livechat/operator')->load($OperatorId);
		$Operator->delete();
		Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Operator %s has been successfully deleted.', $Operator->getName()));
		$this->_redirect('adminhtml/system_config/edit', array('section'=>'livechatoperators'));
	}*/
}