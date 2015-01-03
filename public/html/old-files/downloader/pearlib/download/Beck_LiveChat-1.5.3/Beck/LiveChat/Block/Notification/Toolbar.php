<?php

class Beck_LiveChat_Block_Notification_Toolbar extends Mage_Adminhtml_Block_Template
{
	protected function _construct()
    {
	}
	
	protected function _getHelper()
    {
        return Mage::helper('livechat');
    }
	
	public function isShow()
    {
		if ($this->getConfigData('livechatconfiguration/general/active') != '1')
		{
			return false;
		}
		return $this->getConfigData('livechatconfiguration/general/displayadmintab') != '1' ? false : true;
    }
	
	protected function getConfigData($path)
	{
		return (Mage::getStoreConfig($path));
	}
}