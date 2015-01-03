<?php

class Beck_LiveChat_Block_Operator_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form(array(
					'id' => 'edit_form',
					'action' => $this->getUrl('*/*/save'),
					'method' => 'post',
					'enctype' => 'multipart/form-data'
					)
			);
		
		$OperatorId = $this->getRequest()->getParam('id', 0);
		$Operator = Mage::getModel('livechat/operator')->load($OperatorId);
		$data = $Operator->getStore_allowed();
		$data = explode('-', $data);
		$form->setUseContainer(true);
		$this->setForm($form);
		
		$fieldset = $form->addFieldset('livechat_form', array('legend'=>$this->__('Operator\'s data')));
		
		$fieldset->addField('id', 'hidden', array(
					'class'     => 'required-entry',
					'required'  => true,
					'name'      => 'id',
					'value'		=> $Operator->getId(),
					));
		
		$field = $fieldset->addField('stores', 'multiselect',
				array(
					'name'          => 'stores',
					'label'         => $Operator->getName(),
					'value'         => $data,
					'values'		=> $this->_getValues(),
					));
		
		
		return parent::_prepareForm();
	}
	
	protected function _getValues()
	{
		$values = array();
		$websiteCollection = Mage::getModel('core/website')->getResourceCollection();
		foreach ($websiteCollection as $website)
		{
			$groupCollection = $website->getGroupCollection();
			foreach ($groupCollection as $group)
			{
				$storeCollection = $group->getStoreCollection();
				$values[$group->getName()]['value'] = array();
				$values[$group->getName()]['label'] = $group->getName();
				foreach ($storeCollection as $store)
				{
					$index = count($values[$group->getName()]['value']);
					$values[$group->getName()]['value'][$index]['value'] = $store->getId();
					$values[$group->getName()]['value'][$index]['label'] = $store->getName();
					$values[$group->getName()]['value'][$index]['title'] = $store->getName();
				}
			}
		}
		//Zend_Debug::dump($this->_values);
		return $values;
	}
	
}