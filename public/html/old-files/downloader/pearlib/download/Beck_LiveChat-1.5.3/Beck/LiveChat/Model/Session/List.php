<?php

class Beck_LiveChat_Model_Session_List
{
	public function getOperatorFilterListToArray()
	{
		$list = array(0 => Mage::Helper('livechat')->__('Not dispatched'));
		$operators = Mage::getModel('livechat/operator')->getCollection();
		foreach ($operators as $operator)
		{
			$list[$operator->getId()] = $operator->getName();
		}
		
		return $list;
	}
	
	public function getStateFilterListToArray()
	{
		$list = array(0 => Mage::Helper('livechat')->__('Open'),
					  1 => Mage::Helper('livechat')->__('Closed'));

		
		return $list;
	}
}