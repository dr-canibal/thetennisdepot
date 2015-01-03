<?php

class Velite_Teaserbox_Model_Product_Attribute_Source_Images extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions()
    {
        $productId = Mage::app()->getRequest()->getParam('id');
        
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID); 
        
        $product = new Mage_Catalog_Model_Product();
        $product->load($productId);

        $options = array();        
        $options[] = array('value'=>'0','label'=>'Deactivated');        
        
        if (is_array($product->getMediaGallery('images'))) {
        	foreach ($product->getMediaGallery('images') AS $image) {
                $image['id'] = isset($image['value_id']) ? $image['value_id'] : null;
                $image['path'] = $product->getMediaConfig()->getMediaPath($image['file']);        		
        		
                if ($image['id'] != null) {
                	if ($image['label'] != '') {
		        		$label = $image['label'] . ' ('. basename($image['file']) .')';
        			} else {
        				$label = basename($image['file']);
        			}
        			
                    $options[] = array(
                		'value'=>$image['id'],
                		'label'=>$label 
            		);   
                }
        	}
        }
        
    	$this->_options = $options;
        return $this->_options;
    }
}