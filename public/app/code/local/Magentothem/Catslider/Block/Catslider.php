<?php
class Magentothem_Catslider_Block_Catslider extends Mage_Core_Block_Template
{

    public function getSliderCfg($cfg)
    {
        return Mage::helper('catslider')->getSliderCfg($cfg);
    }

    public function getProductCfg($cfg)
    {
        return Mage::helper('catslider')->getProductCfg($cfg);
    }

    public function getProductTypeTitle()
    {
        $types = Mage::getSingleton("catslider/system_config_type");//new Magentothem_Catslider_Model_System_Config_Type;
        $typeProduct = $types->toOptionArray();
        foreach ($typeProduct as $type) {
            if($type['value'] == $this->getProductCfg('product_type')) echo $type['label'];
        }
    }

    public function sortCategories()
    {
        //return Mage::getStoreConfig('catslider/catslider_config/sort_name');
        return $this->getSliderCfg('sort_name');
    }

	public function getCategories()
	{
		$_categories = Mage::getModel('catalog/category')->getCollection()
		->addAttributeToSelect('name')
		->addAttributeToSelect('url_key')
		->addAttributeToSelect('thumbnail')
		->addAttributeToSelect('hover_thumb')	
		//->setLoadProductCount(true)
		->addAttributeToFilter('is_active',array('eq'=>true))
		->addAttributeToFilter('catslider',array('eq'=>true))
		->addAttributeToSort('name', $this->sortCategories())
		->load();
		return $_categories;
	}


	public function getCatResizedSlider($cat ,$width, $height = null, $quality = 100) {
		if (! $cat->getThumbnail())
			return false;

		$imageUrl = Mage::getBaseDir ( 'media' ) . DS . "catalog" . DS . "category" . DS . $cat->getThumbnail();
		if (! is_file ( $imageUrl ))
			return false;

        $imageResized = Mage::getBaseDir ( 'media' ) . DS . "catalog" . DS . "category" . DS . "cache" . DS . "cat_resized" . DS . $cat->getThumbnail();// Because clean Image cache function works in this folder only
        if (! file_exists ( $imageResized ) && file_exists ( $imageUrl ) || file_exists($imageUrl) && filemtime($imageUrl) > filemtime($imageResized)) :
        	$imageObj = new Varien_Image ( $imageUrl );
        $imageObj->constrainOnly ( true );
        $imageObj->keepAspectRatio ( true );
        $imageObj->keepFrame ( true ); // ep
        $imageObj->quality ( $quality );
        $imageObj->keepTransparency(true);  // png
        $imageObj->backgroundColor(array(255,255,255));
        $imageObj->resize ( $width, $height );
        $imageObj->save ( $imageResized );
        endif;
        
        if(file_exists($imageResized)){
        	return Mage::getBaseUrl ( 'media' ) ."/catalog/category/cache/cat_resized/" . $cat->getThumbnail();
        }else{
        	return $this->getImageUrl();
        }

    }

	public function getCatResizedSliderHover($cat ,$width, $height = null, $quality = 100) {
		if (! $cat->getHoverThumb())
			return false;

		$imageUrl = Mage::getBaseDir ( 'media' ) . DS . "catalog" . DS . "category" . DS . $cat->getHoverThumb();
		if (! is_file ( $imageUrl ))
			return false;

        $imageResized = Mage::getBaseDir ( 'media' ) . DS . "catalog" . DS . "category" . DS . "cache" . DS . "catslider_resized" . DS . $cat->getHoverThumb();// Because clean Image cache function works in this folder only
        if (! file_exists ( $imageResized ) && file_exists ( $imageUrl ) || file_exists($imageUrl) && filemtime($imageUrl) > filemtime($imageResized)) :
        	$imageObj = new Varien_Image ( $imageUrl );
        $imageObj->constrainOnly ( true );
        $imageObj->keepAspectRatio ( true );
        $imageObj->keepFrame ( true ); // ep
        $imageObj->quality ( $quality );
        $imageObj->keepTransparency(true);  // png
        $imageObj->backgroundColor(array(255,255,255));
        $imageObj->resize ( $width, $height );
        $imageObj->save ( $imageResized );
        endif;
        
        if(file_exists($imageResized)){
        	return Mage::getBaseUrl ( 'media' ) ."/catalog/category/cache/catslider_resized/" . $cat->getHoverThumb();
        }else{
        	return $this->getImageUrl();
        }

    }

}