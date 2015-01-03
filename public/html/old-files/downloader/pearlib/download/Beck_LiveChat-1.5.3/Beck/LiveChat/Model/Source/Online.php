<?php

class Beck_LiveChat_Model_Source_Online
{
	public static function toOptionsArray()
	{
		$list = array(
					0 => Mage::Helper('livechat')->__('Disconnected'), 
					1 => Mage::Helper('livechat')->__('Online')
					);
		
		return ($list);
	}
}