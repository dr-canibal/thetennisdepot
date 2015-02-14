<?php

class Camiloo_Stockupload_StockuploadController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('stockupload/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

	public function downloadAction() {
			
			$collectionProducts = Mage::getResourceModel('catalog/product_collection');
		  
			$currentstocks = array();
			
			if(isset($collectionProducts)){	// if there are products on the system
					foreach($collectionProducts as $_item){ // for each product
							$currentsku = $_item->getData('sku');
							$currentstock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_item)->getData('qty');
							
							$currentstocks["$currentsku"]['stock'] = $currentstock;
							$currentstocks["$currentsku"]['name'] = $_item->getName();
					}
			}
			
			
			ksort($currentstocks);
			
			
			header("Content-Disposition: attachment; filename=\"".time()."-stock.csv\";" );
			header("Content-Type:text/csv");
		
			$csvfile = "Item SKU,Item Name,Stock Before Update,Please enter:,Stock after update,---------OR--------,Add to stock level,---------OR--------,Remove from stock level\n";
			
			foreach($currentstocks as $key=>$value){
				$csvfile .= "$key,".$value['name'].",------------------,,---------OR--------,,---------OR--------,,\n";
			}
			
			echo $csvfile;
			
	

	}
 
	public function uploadAction() {
			ob_start();
			$fileName   = $_FILES['csvfile']['tmp_name'];
			$csvObject  = new Varien_File_Csv();
			$csvObject->setDelimiter(",");
			$csvData = $csvObject->getData($fileName);
			
				$count = sizeof($csvData) - 1;	
			while (list($key, $value) = each($csvData)) {
				if($key > 0){
				
					// we're into our data... let's process it.
					
					$productsku = $value[0];
					
					$product = Mage::getModel('Catalog/Product')->loadByAttribute('sku',$productsku);
					
					if(is_object($product)){
					$stockitem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
	
					$currentstock = $stockitem->getData('qty');
								
					if($value[4] != ""){
						// column index 3 is the stock after update value - this basically changes the stock to the amount given.	
						$stockitem->setData('qty',$value[3]);
						
					}else if($value[6] != ""){
						// column index 5 is the add to stock value. This will be easier to process...
						$stockitem->setData('qty',$currentstock + $value[5]);
				
					}else if(isset($value[8])){
						if($value[8] != ""){
							// column index 7 is the remove from stock value. This will be easier to process...
							$stockitem->setData('qty',$currentstock - $value[7]);
						}
						
					}
					
						// New feature in v1.1 - is_in_stock setting.
						
							// step one: get the manage_stock_config and min_qty_config setting for the stockitem
							$iscfgminqty = $stockitem->getData('use_config_min_qty');
							$iscfgmanage = $stockitem->getData('use_config_manage_stock');
							
							if($iscfgminqty > 0){
								$minqty = Mage::getStoreConfig('cataloginventory/item_options/min_qty');
							}else{
								$minqty = $stockitem->getData('min_qty');	
							}
							
							if($iscfgmanage > 0){
								$manage = Mage::getStoreConfig('cataloginventory/item_options/manage_stock');
							}else{
								$manage = $stockitem->getData('manage_stock');	
							}
						
						$stockitem->save();
	
						$newqty = $stockitem->getData('qty');
						if($manage == 1){
							if($newqty > $minqty){
								$stockitem->setData('is_in_stock','1');
							}else if($newqty < $minqty+1){
								$stockitem->setData('is_in_stock','0');
							}
						}
						
						$stockitem->save();
					
					}
					
					
					
					$progresscomplete = number_format(($key / $count) * 100 * 4,2);
					$paddingleft = number_format(($key / $count) * 100 * 2 - 15,2);
					echo "<div style=\"width:".$progresscomplete."px;height:50px;padding-top:10px;font-family:Arial;color:#FFFFFF;font-weight:bold;padding-left:".$paddingleft."px;position:absolute;top:0px;left:0px;z-index:$key;background-color:green;\">".($progresscomplete / 4)."%</div>";
					usleep(1000);
					ob_flush();
				}
				
			}
	}
 
	
}