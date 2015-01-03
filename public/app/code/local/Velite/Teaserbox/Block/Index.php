<?php

class Velite_Teaserbox_Block_Index extends Mage_Core_Block_Template
{	
	public function getTeaserboxXmlUrl() 
	{
		return Mage::getBaseUrl() . 'teaserbox/xml' . $this->getTeaserboxXmlParamString();
	}

	public function getTeaserboxXmlParamString()
	{
		$storeId = Mage::app()->getStore()->getId();	
		return '?storeid='.$storeId;
	}

	public function getTeaserboxDiv()
	{
		$baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
		return "<div id='teaserboxBox'><a href='http://velite.de'>Magento ".(strlen($baseUrl)%2?'Templates':'Extensions')."</a> by velite&trade;</div>";
	}
	
}