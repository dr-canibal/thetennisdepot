<?php

class Beck_LiveChat_Block_Operator_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
		parent::__construct();
		
		$this->_objectId = 'id';
		
		$this->_blockGroup = 'livechat';
		$this->_controller = 'operator';
		$this->_mode = 'edit';
		
		$this->_updateButton('save', 'label', $this->__('Save'));		
	}
	
	public function getHeaderText()
	{
		return Mage::helper('livechat')->__('Edit operator');
	}
}