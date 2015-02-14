<?php

class AW_Featuredproducts_Block_Featuredproducts extends Mage_Catalog_Block_Product_Abstract
{
	public function __construct(){
		$this->setTemplate('featuredproducts/featuredproducts.phtml');
		$this->addPriceBlockType('bundle', 'bundle/catalog_product_price', 'bundle/catalog/product/price.phtml');
		//parent::__construct();
	}
}



?>
