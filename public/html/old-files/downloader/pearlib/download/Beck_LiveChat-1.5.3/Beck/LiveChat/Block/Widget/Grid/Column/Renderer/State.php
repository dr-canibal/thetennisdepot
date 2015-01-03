<?php

class Beck_LiveChat_Block_Widget_Grid_Column_Renderer_State extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{	
	public function render(Varien_Object $row)
	{
		$html = '';
		$state = (int)$row->close;
		
				
		$session = Mage::getModel('livechat/session')->load($row->id);
		if (Mage::Helper('livechat')->isSessionExpired($session))
		{
			$session->Expired();
			$state = 1;
		}
		
		if ($state == 1)
		{
			$html .= Mage::Helper('livechat')->__('Closed');
		}
		else
		{
			$html .= Mage::Helper('livechat')->__('Open');
		}
		
		return ($html);
	}
}