<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 * 
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Who_bought_this_also_bought
 * @copyright  Copyright (c) 2009-2010 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */
/**
 * Related products Block
 */
class AW_Relatedproducts_Block_Relatedproducts extends Mage_Catalog_Block_Product_Abstract
{
    const ONE_TIME_INSTALL_ORDERS_LIMIT = 3;

    protected $_itemCollection;
    protected $_relatedCollection;
	protected $_target;

    public function getProductId()
    {
        return Mage::registry('product')->getId();
    }

    public function getProduct()
    {    	
        return Mage::registry('product');
    }    
	
	public function setTarget( $value )
	{
		$this->_target = $value;
		return $this;
	}
	
	public function getTarget()
	{
		return $this->_target;
	}

    /**
     * Retrives table name for Model Entity Name
     * @param string $modelEntity
     * @return string
     */
	public function getTableName($modelEntity)
	{
		try {
			$table = Mage::getSingleton('core/resource')->getTableName($modelEntity);
		} catch (Exception $e){
			Mage::throwException($e->getMessage());
		}
		return $table;
	}
	

    /**
     * Index sales data for current product
     * @param int|string $productId
     * @return AW_Relatedproducts_Block_Relatedproducts
     */
    protected function _installForProduct( $productId )
    {
        $orders = Mage::getModel('sales/order')->getCollection();
        $orders->addAttributeToSelect('*')->addAttributeToFilter('status', 'complete');

        //if (Mage::helper('relatedproducts')->isEnterprise()){
            $itemTable = $this->getTableName('sales/order_item');
            $orders->getSelect()->join( array('item'=>$itemTable), "main_table.entity_id = item.order_id", array() )
                        ->join( array('item1'=>$itemTable), "main_table.entity_id = item1.order_id", array( 'i_count' => 'COUNT( item1.product_id )' ) )
                        ->where('item.product_id = ?', $productId)
                        ->group('main_table.entity_id')
                        ->order('i_count', 'DESC')
                        ->limit( self::ONE_TIME_INSTALL_ORDERS_LIMIT );            
        /*} else {
            $itemTable = $orders->getTable('sales_flat_order_item');            
            $orders->getSelect()->join( array('item'=>$itemTable), "e.entity_id = item.order_id", array() )
                        ->join( array('item1'=>$itemTable), "e.entity_id = item1.order_id", array( 'i_count' => 'COUNT( item1.product_id )' ) )
                        ->where('item.product_id = ?', $productId)
                        ->group('e.entity_id')
                        ->order('i_count', 'DESC')
                        ->limit( self::ONE_TIME_INSTALL_ORDERS_LIMIT );
        }*/
       
        $orders->load();

        $ids = array();
		
        foreach($orders as $order)
        {
                $order = Mage::getModel('sales/order')->load($order->getId());
                $items = $order->getAllItems();
                if(count($items) > 1)
                {
                        $ids = array();
                        foreach ($items as $itemId => $item)
                        {
                           $a = $item->toArray();
                           array_push($ids, $a['product_id']);
                        }
                }
                Mage::helper('relatedproducts')->updateRelations($ids);
        }
        return $this;
    }

    public function getCollection()
    {
        if ( !$this->_relatedCollection )
        {
            if( $productId = $this->getProductId() )
            {
                return $this->_relatedCollection = Mage::getModel('relatedproducts/relatedproducts')
                              ->getCollection()
                              ->addProductFilter( $productId )
                              ->load();                
            }
            else
            {
                return null;
            }            
        }
        else
        {
            return $this->_relatedCollection;
        }
    }

    public function getUpdatedCollection()
    {
        $this->_relatedCollection = null;
        return $this->getCollection();
    }

    public function getRelatedProductsCollection()
    {
        $items = array();
        if ( count( $this->getCollection() ) )
        {            
             $items = $this->getCollection()->getItems();
        }
        elseif ( count( $this->_installForProduct( $this->getProductId() )->getUpdatedCollection() ) )
        {
            $items = $this->getCollection();
        }
//        Mage::log($items);
        $related_ids = array();

        foreach($items as $item)
        { //actually runs only once max, for 1 collection element
            $related_items = unserialize($item->getData('related_array'));
            arsort($related_items, SORT_NUMERIC);//order by number of purchases
            $related_items = array_slice($related_items, 0, 3, true);

            foreach($related_items as $key => $value){
                    array_push($related_ids, $key);
            }
        }

        $this->_itemCollection = Mage::getModel('catalog/product')
                                ->setStoreId( Mage::app()->getStore()->getId() )
                                ->getCollection();

        Mage::getResourceSingleton('checkout/cart')->addExcludeProductFilter($this->_itemCollection,
                                            Mage::getSingleton('checkout/session')->getQuoteId() );

        $this->_itemCollection->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect( Mage::getSingleton('catalog/config')->getProductAttributes() );

        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection( $this->_itemCollection );
        $this->_itemCollection->addAttributeToFilter('entity_id', array('in'=>$related_ids));
        $this->_itemCollection->load();
        foreach ($this->_itemCollection as $product)
        {
            $product->setDoNotUseCategoryId(true);
        }

        return $this->_itemCollection;
    }
}