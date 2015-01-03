<?php

class Beck_LiveChat_Model_Mysql4_Message_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract 
{
	protected function _construct()
	{
		parent::_construct();
		$this->_init('livechat/message');
	}
	
	public function limit($limit)
	{
		$limit = (int) $limit;
		if ($limit > 0)
		{
			if ($this->count() > $limit)
			{
				$i = 0;
				$i_max = $this->count() - $limit;
				foreach ($this->_items as $key => $val)
				{
					if ($i  < $i_max)
					{
						$this->removeItemByKey($key);
					}
					$i++;
				}
			}
		}
		return ($this);
	}
}