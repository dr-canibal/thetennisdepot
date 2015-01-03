<?php

class Beck_LiveChat_Model_Mysql4_Operator extends Mage_Core_Model_Mysql4_Abstract 
{
	protected function _construct()
	{
		$this->_init('livechat/livechat_operators', 'id');
	}
}