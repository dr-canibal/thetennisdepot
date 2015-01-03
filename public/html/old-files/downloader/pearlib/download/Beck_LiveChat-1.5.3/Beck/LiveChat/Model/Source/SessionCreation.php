<?php

class Beck_LiveChat_Model_Source_SessionCreation
{
	public static function toOptionArray()
	{
		$list = array(
					0 => Mage::Helper('livechat')->__('When customer send his first message'), 
					1 => Mage::Helper('livechat')->__('Automatically create a new chatsession for everyone'),
					2 => Mage::Helper('livechat')->__('Automatically create a new chat session for every customer logged in')
					);
		
		return ($list);
	}
}