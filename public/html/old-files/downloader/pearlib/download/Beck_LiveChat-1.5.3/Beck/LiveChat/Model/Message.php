<?php

class Beck_LiveChat_Model_Message extends Mage_Core_Model_Abstract
{
	protected function _construct()
	{
		parent::_construct();
		$this->_init('livechat/message');
	}
	
	public function RenderLine($customerUrl = '')
	{
		$render = '';
		if ($this->getId() > 0)
		{
			if ($customerUrl == '')
			{
				$render .= '<b>'.$this->getAutor_name() . '</b> : ' . $this->getMessage() . '<br />';
			}
			else
			{
				$render .= '<b><a href="'.$customerUrl.'" title="'.$customerUrl.'">'.$this->getAutor_name() . '</a></b> : ' . $this->getMessage() . '<br />';
			}
		}
		return $render;
	}
}