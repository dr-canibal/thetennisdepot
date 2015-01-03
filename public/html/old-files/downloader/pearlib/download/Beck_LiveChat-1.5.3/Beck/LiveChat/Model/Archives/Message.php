<?php

class Beck_LiveChat_Model_Archives_Message extends Beck_LiveChat_Model_Message
{
	protected function _construct()
	{
		parent::_construct();
		$this->_init('livechat/archives_message');
	}
}