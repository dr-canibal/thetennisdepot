<?php

class Beck_LiveChat_Block_Widget_Grid_Column_Renderer_Online extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{	
	public function render(Varien_Object $row)
	{
		$html = '';
		$status = (int)$row->is_online;
		if ($status == 1)
		{
			$html .= Mage::Helper('livechat')->__('Online');
		}
		else
		{
			$html .= Mage::Helper('livechat')->__('Disconnected');
		}
		return $html;
	}
}