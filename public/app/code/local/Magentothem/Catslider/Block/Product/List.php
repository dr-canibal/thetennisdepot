<?php
class Magentothem_Catslider_Block_Product_List extends Mage_Catalog_Block_Product_List
{

  public function getToolbarHtml()
  {
    // remove Toolbar
  }

  public function getSliderCfg($cfg)
  {
    return Mage::helper('catslider')->getSliderCfg($cfg);
  }

  public function getProductCfg($cfg)
  {
    return Mage::helper('catslider')->getProductCfg($cfg);
  }

  public function getPlayDelay()
  {
    return $this->getProductCfg('play_delay');
  }

  public function getColumnCount()
  {
    // rewrite method set column list product
    return $this->getProductCfg('items_column');
  }

  public function useFlatCatalogProduct()
  {
    return Mage::getStoreConfig('catalog/frontend/flat_catalog_product');
  }

  public function getNumProduct()
  {
    return $this->getProductCfg('product_number');
  }

  public function getCategoryId()
  {
    $categoryId = (int) $this->getRequest()->getPost('category_id');
    return  $categoryId;
  } 



  protected function _getProductCollection()
  {

/*
    $categoryId = $this->getCategoryId();

    $category = Mage::getModel('catalog/category')->load($categoryId);
    // $collection = $category->getProductCollection()->addAttributeToSort('position'); 
    $collection = $category->getProductCollection();
    $collection->setPageSize($this->getNumProduct());
    $collection->addAttributeToSelect('*');
    //$collection->getItems();



    $attributes = Mage::getSingleton('catalog/config')->getProductAttributes();
   $collection->addAttributeToSelect($attributes) // Select attribute as image price ...
              ->addMinimalPrice()
              ->addUrlRewrite()
              ->addTaxPercents()
              ->addStoreFilter();

    Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);

    //  only display product_visibility
    Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);

    $this->_productCollection = $collection;
    return $this->_productCollection;
  */
  

    $productType = $this->getProductCfg('product_type');

    switch ($productType) {
      case 'bestseller':
        $Collection = $this->getBestsellerProducts();
        break;
      case 'featured':
        $Collection = $this->getFeaturedProducts();
        break;
      case 'mostviewed':
        $Collection = $this->getMostviewedProducts();
        break;
      case 'newproduct':
        $Collection = $this->getNewProducts();
        break;
      case 'random':
        $Collection = $this->getRandomProducts();
        break;
      case 'saleproduct':
        $Collection = $this->getSaleProducts();
        break;
      case 'specialproduct':
        $Collection = $this->getSpecialProducts();
        break;
      default:
        $Collection = $this->getMostviewedProducts();
        break;
    }
    
    return $Collection;
  }


    public function getBestsellerProducts(){

        // $collection = Mage::getResourceModel('reports/product_collection')
        //                     ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
        //                     ->addOrderedQty()
        //                     ->addMinimalPrice()
        //                     ->addTaxPercents()
        //                     ->addStoreFilter()
        //                     ->setOrder('ordered_qty', 'desc');
        // Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        // Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($collection);

        // // CategoryFilter
        // $Category = Mage::getModel('catalog/category')->load($this->getCategoryId());
        // $collection->addCategoryFilter($Category);

        // // getNumProduct
        // $collection->setPageSize($this->getNumProduct());
        //return $collection;


        // fix Display configuable vs bundle product
        $collection = Mage::getResourceModel('catslider/product_bestseller'); //new Magentothem_Catslider_Model_Resource_reports_Bestseller;
        
        $collection = $this->_addProductAttributesAndPrices($collection)
                    ->addOrderedQty()
                    ->addMinimalPrice()
                    ->setOrder('ordered_qty', 'desc');

        // CategoryFilter
        $Category = Mage::getModel('catalog/category')->load($this->getCategoryId());
        $collection->addCategoryFilter($Category);

        // getNumProduct
        $collection->setPageSize($this->getNumProduct()); // require before foreach

        if($this->useFlatCatalogProduct())
        {
            // fix error mat image vs name while Enable useFlatCatalogProduct
            foreach ($collection as $product) 
            {
                $productId = $product->_data['entity_id'];
                $_product = Mage::getModel('catalog/product')->load($productId); //Product ID
                $product->_data['name']        = $_product->getName();
                $product->_data['thumbnail']   = $_product->getThumbnail();
                $product->_data['small_image'] = $_product->getSmallImage();
            }            
        }

        return $collection;
    }

    public function getFeaturedProducts(){

        $collection = Mage::getModel('catalog/product')->getCollection()
                            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                            ->addAttributeToFilter('mage_featured_product', 1, 'left')
                            ->addMinimalPrice()
                            ->addTaxPercents()
                            ->addStoreFilter();
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($collection);

        // CategoryFilter
        $Category = Mage::getModel('catalog/category')->load($this->getCategoryId());
        $collection->addCategoryFilter($Category);

        // getNumProduct
        $collection->setPageSize($this->getNumProduct());
        return $collection; 
    }

    public function getMostviewedProducts(){
     //Magento get popular products by total number of views

        $collection = Mage::getResourceModel('reports/product_collection')
                            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                            ->addViewsCount()
                            ->addMinimalPrice()
                            ->addTaxPercents()
                            ->addStoreFilter(); 
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($collection);

        // CategoryFilter
        $Category = Mage::getModel('catalog/category')->load($this->getCategoryId());
        $collection->addCategoryFilter($Category);

        // getNumProduct
        $collection->setPageSize($this->getNumProduct()); // require before foreach

        if($this->useFlatCatalogProduct())
        {
            // fix error mat image vs name while Enable useFlatCatalogProduct
            foreach ($collection as $product) 
            {
                $productId = $product->_data['entity_id'];
                $_product = Mage::getModel('catalog/product')->load($productId); //Product ID
                $product->_data['name']        = $_product->getName();
                $product->_data['thumbnail']   = $_product->getThumbnail();
                $product->_data['small_image'] = $_product->getSmallImage();
            }            
        }

        return $collection;
    }

    public function getNewProducts() {

        $todayDate  = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        $collection = Mage::getResourceModel('catalog/product_collection')
                            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                            ->addAttributeToSelect('*') //Need this so products show up correctly in product listing
                            ->addAttributeToFilter('news_from_date', array('or'=> array(
                                0 => array('date' => true, 'to' => $todayDate),
                                1 => array('is' => new Zend_Db_Expr('null')))
                            ), 'left')
                            ->addAttributeToFilter('news_to_date', array('or'=> array(
                                0 => array('date' => true, 'from' => $todayDate),
                                1 => array('is' => new Zend_Db_Expr('null')))
                            ), 'left')
                            ->addAttributeToFilter(
                                array(
                                    array('attribute' => 'news_from_date', 'is'=>new Zend_Db_Expr('not null')),
                                    array('attribute' => 'news_to_date', 'is'=>new Zend_Db_Expr('not null'))
                                    )
                              )
                            ->addAttributeToSort('news_from_date', 'desc')
                            ->addMinimalPrice()
                            ->addTaxPercents()
                            ->addStoreFilter(); 
                            
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($collection);

        // CategoryFilter
        $Category = Mage::getModel('catalog/category')->load($this->getCategoryId());
        $collection->addCategoryFilter($Category);

        // getNumProduct
        $collection->setPageSize($this->getNumProduct());
        return $collection;
    }

    public function getRandomProducts() {

        $collection = Mage::getResourceModel('catalog/product_collection')
                            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                            ->addMinimalPrice()
                            ->addTaxPercents()
                            ->addStoreFilter(); 
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($collection);
        $collection->getSelect()->order('rand()');

        // CategoryFilter
        $Category = Mage::getModel('catalog/category')->load($this->getCategoryId());
        $collection->addCategoryFilter($Category);

        // getNumProduct
        $collection->setPageSize($this->getNumProduct());
        return $collection;
    }

    public function getSaleProducts(){

        $todayDate  = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        $collection = Mage::getResourceModel('catalog/product_collection')
                                ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                                ->addAttributeToFilter('special_from_date', array('or'=> array(
                                    0 => array('date' => true, 'to' => $todayDate),
                                    1 => array('is' => new Zend_Db_Expr('null')))
                                ), 'left')
                                ->addAttributeToFilter('special_to_date', array('or'=> array(
                                    0 => array('date' => true, 'from' => $todayDate),
                                    1 => array('is' => new Zend_Db_Expr('null')))
                                ), 'left')
                                ->addAttributeToFilter(
                                    array(
                                        array('attribute' => 'special_from_date', 'is'=>new Zend_Db_Expr('not null')),
                                        array('attribute' => 'special_to_date', 'is'=>new Zend_Db_Expr('not null'))
                                        )
                                  )
                                ->addAttributeToSort('special_to_date','desc')
                                ->addTaxPercents()
                                ->addStoreFilter(); 
            Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
            Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($collection);    

        // CategoryFilter
        $Category = Mage::getModel('catalog/category')->load($this->getCategoryId());
        $collection->addCategoryFilter($Category);

        // get Sale off
        foreach ($collection as $key => $product) {
            if($product->getSpecialPrice() == '') $collection->removeItemByKey($key); // remove product not set SpecialPrice
            if($product->getSpecialPrice() && $product->getSpecialPrice() >= $product->getPrice())
            {
               $collection->removeItemByKey($key); // remove product price increase
            }
        }
        // getNumProduct
        $collection->setPageSize($this->getNumProduct());
        return $collection;

    }

    public function getSpecialProducts() {

        $todayDate  = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        $collection = Mage::getResourceModel('catalog/product_collection')
                                ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                                ->addAttributeToFilter('special_from_date', array('or'=> array(
                                    0 => array('date' => true, 'to' => $todayDate),
                                    1 => array('is' => new Zend_Db_Expr('null')))
                                ), 'left')
                                ->addAttributeToFilter('special_to_date', array('or'=> array(
                                    0 => array('date' => true, 'from' => $todayDate),
                                    1 => array('is' => new Zend_Db_Expr('null')))
                                ), 'left')
                                ->addAttributeToFilter(
                                    array(
                                        array('attribute' => 'special_from_date', 'is'=>new Zend_Db_Expr('not null')),
                                        array('attribute' => 'special_to_date', 'is'=>new Zend_Db_Expr('not null'))
                                        )
                                  )
                                ->addAttributeToSort('special_to_date','desc')
                                ->addTaxPercents()
                                ->addStoreFilter(); 
            Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
            Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($collection);    

        // CategoryFilter
        $Category = Mage::getModel('catalog/category')->load($this->getCategoryId());
        $collection->addCategoryFilter($Category);

        // getNumProduct
        $collection->setPageSize($this->getNumProduct());
        return $collection;
    }

}