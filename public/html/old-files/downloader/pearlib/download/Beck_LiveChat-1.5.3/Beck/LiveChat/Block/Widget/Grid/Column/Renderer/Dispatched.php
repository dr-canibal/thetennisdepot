<?php

class Beck_LiveChat_Block_Widget_Grid_Column_Renderer_Dispatched extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{	
	public function render(Varien_Object $row)
	{
		$html = '';
		$operatorid = (int)$row->dispatched;
		if ($operatorid <= 0)
		{
			$html .= Mage::Helper('livechat')->__('Not dispatched');
		}
		else
		{
			$html .= Mage::getModel('livechat/operator')->load($operatorid)->getName();
		}
		
		return ($html);
	}
}