<?php

class Velite_Teaserbox_Block_Xml extends Velite_Teaserbox_Block_Xml_Abstract
{	
	
	protected $_extName = 'Teaserbox';
	protected $_version = '1.0';
	
	protected function _getConfigPaths()
	{
		$paths = array(
						'stageColor'  => array( 
								'path' 		=> 'stageColor',
								'prefix' 	=> '0x'),
						'enableTimer' 	=> 'enableTimer',
						'timer' 		=> 'timer',
						'direction' 	=> 'direction',
						'containerSpeed'=> 'containerSpeed',
						'showButtons' 	=> 'showButtons',
						'buttonTransparency' 	=> 'buttonTransparency' );
		return $paths;
	}
	
	protected function _toHtml() 
	{
		$storeId = $this->getRequest()->getParam('storeid', 1);
		$store = Mage::app()->getStore($storeId);
		
		$configXml = $this->getConfig(
								'teaserbox/general/',
								$this->_getConfigPaths(),
								array (
									'version' => $this->_extName .' '.$this->_version
								),
								$storeId );
	
		$out = "<teaserbox>\n";
		$out .= $configXml;

		$products = Mage::getResourceModel('catalog/product_collection')
		                  ->addFieldToFilter('teaserboxadd', array(array('from' => 1)))
		                  ->addAttributeToSelect('*')
		                  ->setStore($store)
		                  ->setOrder('teaserboxprio', 'asc');
		
		$out .= "<products>\n";
		
		foreach ($products AS $product) {
			$productFull = new Mage_Catalog_Model_Product();
			$productFull->load($product['entity_id']);
			
			$imgPath = '';
			  
		    if (is_array($productFull->getMediaGallery('images'))) {
        		foreach ($productFull->getMediaGallery('images') AS $image) {
                	$image['id'] = isset($image['value_id']) ? $image['value_id'] : null;
                	
                	if ($image['id'] != null && 
                		$image['id'] == $productFull->getTeaserboxadd()) {
   						$imgPath = $productFull->getMediaConfig()->getMediaUrl($image['file']);
                	}
        		}
        	}
		
		    if (empty($imgPath)) {
		    	continue;
		    }

			$productName = (string)$productFull->getTeaserboxalttext();
			  
			if (strlen($productName) == 0) {
				$productName = $productFull->name;
			}

			$out .= "<product>\n";
			$out .= $this->createXmlTag('productName', $productName);
			$out .= $this->createXmlTag('imagePath', $imgPath);
			$out .= $this->createXmlTag('targetURL', $productFull->getProductUrl());
			$out .= $this->createXmlTag('prio', $productFull->getTeaserboxprio());
		    $out .= "</product>\n";
		}
        
		$out .= "</products>\n";
		$out .= "</teaserbox>\n";		                  
		                  
		return $out;
	}

}