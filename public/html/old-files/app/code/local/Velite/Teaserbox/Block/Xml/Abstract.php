<?php

class Velite_Teaserbox_Block_Xml_Abstract extends Mage_Core_Block_Template
{
	public function getConfig($namespace, $paths, $data = null, $storeId = null)
	{
		$out = "<config>\n";
		foreach($paths AS $tagName => $path) {
			if (is_array($path)) {
				$val = Mage::getStoreConfig($namespace . $path['path'], $storeId);
				$out .= $this->createXmlTag($tagName, $path['prefix'] . $val);
			} else {
				$val = Mage::getStoreConfig($namespace . $path, $storeId);
				$out .= $this->createXmlTag($tagName, $val);
			}
		}
		
		if (is_array($data)) {
			foreach($data AS $tagName => $val) {
				$out .= $this->createXmlTag($tagName, $val);
			}
		}
		$out .= "</config>\n";
		return $out;
	}

	public function createXmlTag ($tagName, $tagValue)
	{
		return "<{$tagName}><![CDATA[{$tagValue}]]></{$tagName}>\n";
	}
}