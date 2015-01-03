<?php

//ini_set('display_errors', 1);
//ini_set('xmlrpc_errors', 1);
//ini_set("memory_limit","1024M");

class Eznewsletter_Ezsubscribers_Model_Ezeditor_Api extends Mage_Api_Model_Resource_Abstract
{
    public function getTest()
    {
        $block = Mage::getSingleton('core/layout')
            ->createBlock("catalog/product_list")
            ->setTemplate("catalog/product/list.phtml")
            ->setCategoryId(8);

        echo $block->toHtml();
    }

    public function getCategoryLayout($categoryId, $block, $template, $option)
    {
        $block = Mage::getSingleton('core/layout')
        ->createBlock($block, $option, array('category_id'=>$categoryId))
        ->setTemplate($template);

        return $block->toHtml();
    }

    public function getCatalog($storeIds, $delimProduct, $delimSubProduct, $delimLine)
    {
        $retval = "";
        
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');
        $websiteTableName = $resource->getTableName('core/website');
        $storeTableName = $resource->getTableName('core/store');

        // split the store id's into array and filter product collection on each one
        $storeIdsArr = explode(",", $storeIds);
        $cnt = 0;
//        foreach ($storeIdsArr as $storeId)
//        {
//            // make sure store id exists
//            $readresult = $read->query("SELECT code FROM $storeTableName WHERE store_id = $storeId");
//            if (!$readresult->fetch())
//            {
//                continue;
//            }

            //VISIBILITY_BOTH = 4
            //VISIBILITY_IN_CATALOG = 2
            //VISIBILITY_IN_SEARCH = 3
            //VISIBILITY_NOT_VISIBLE = 1 
            $visibility = array(
                     Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
                     Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH,
                     Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG
                 );
            $productCollection = Mage::getModel('catalog/product')->getCollection()
//                ->setStoreId(1)
                ->addAttributeToSelect('entity_id')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('type_id')
                ->addAttributeToSelect('sku')
                ->addAttributeToSelect('price')
                ->addAttributeToSelect('visibility')
                ->addAttributeToSelect('status')
                ->addAttributeToSelect('websites')
                ->addAttributeToSelect('model')
                ->addAttributeToSelect('weight')
                ->addAttributeToSelect('meta_title')
                ->addAttributeToSelect('meta_keywords')
                ->addAttributeToSelect('description')
                ->addAttributeToSelect('in_depth')
                ->addAttributeToSelect('short_description')
                ->addAttributeToSelect('thumbnail')
                ->addAttributeToSelect('thumbnail_label')
                ->addAttributeToSelect('small_image')
                ->addAttributeToSelect('small_image_label')
                ->addAttributeToSelect('image')
                ->addAttributeToSelect('image_label')
                ->addAttributeToSelect('url_path')
                ->addAttributeToSelect('special_price')
                ->addAttributeToSelect('special_from_date')
                ->addAttributeToSelect('special_to_date')
                ->addAttributeToFilter('visibility', $visibility);

            foreach($productCollection as $product)
            {
                // standard info
                $retval .= $product->getData('entity_id') . $delimProduct;
                $retval .= $product->getData('name') . $delimProduct;
                $retval .= $product->getData('type_id') . $delimProduct;
                $retval .= $product->getAttributeSetId() . $delimProduct;
                $retval .= $product->getData('sku') . $delimProduct;
                $retval .= $product->getData('price') . $delimProduct;
                $retval .= Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty() . $delimProduct;
                $retval .= $product->getData('visibility') . $delimProduct;
                $retval .= $product->getData('status') . $delimProduct;

                // website names
                foreach ($product->getWebsiteIds() as $websiteId)
                {
                    $readresult = $read->query("SELECT name FROM $websiteTableName WHERE website_id = $websiteId");
                    while ($row = $readresult->fetch())
                    {
                        $retval .= $row["name"] . $delimSubProduct;
                    }
                }
                $retval .= $delimProduct;

                // categories
                foreach ($product->getCategoryIds() as $categoryId)
                {
                    $category = Mage::getModel('catalog/category')->load($categoryId);
                    $retval .= $category->getData('name') . $delimSubProduct;
                }
                $retval .= $delimProduct;

                // more standard info
                $retval .= $product->getData('model') . $delimProduct;
                $retval .= $product->getData('weight') . $delimProduct;
                //$retval .= $product->getData('meta_title') . $delimProduct;
                $retval .= $delimProduct;
                //$retval .= $product->getData('meta_keywords') . $delimProduct;
                $retval .= $delimProduct;
                //$retval .= $product->getData('description') . $delimProduct;
                $retval .= $delimProduct;
                //$retval .= $product->getData('in_depth') . $delimProduct;
                $retval .= $delimProduct;
                $retval .= $product->getData('short_description') . $delimProduct;

                // images
                $retval .= Mage::helper('catalog/image')->init($product,'image')->resize() . $delimProduct;
                $retval .= $product->getData('image_label') . $delimProduct;
                $retval .= Mage::helper('catalog/image')->init($product,'thumbnail')->resize() . $delimProduct;
                $retval .= $product->getData('thumbnail_label') . $delimProduct;
                $retval .= Mage::helper('catalog/image')->init($product,'small_image')->resize() . $delimProduct;
                $retval .= $product->getData('small_image_label') . $delimProduct;

                // links
                $retval .= Mage::getUrl('checkout/cart/add', array('product'=>$product->getData('entity_id'))) . $delimProduct;
                $retval .= $product->getProductUrl() . $delimProduct;
                $retval .= Mage::getUrl('catalog/product_compare/add', array('product'=>$product->getData('entity_id'))) . $delimProduct;
                $retval .= Mage::getUrl('wishlist/index/add', array('product'=>$product->getData('entity_id'))) . $delimProduct;
                $review = Mage::getSingleton('review/review')->getCollection()
                            ->addStatusFilter('approved')
                            ->addEntityFilter('product', $product->getData('entity_id'));
                $retval .= $review->count() . $delimProduct;
                $retval .= Mage::getUrl('review/product/list', array('id'=>$product->getData('entity_id'))) . $delimProduct;

                // image for the reorder display
                $retval .= Mage::helper('catalog/image')->init($product,'image')->resize(50, 50) . $delimProduct;

                // image for the grid display
                $retval .= Mage::helper('catalog/image')->init($product,'image')->resize(35, 35) . $delimProduct;

                // special info
                $retval .= $product->getData('special_price') . $delimProduct;
                $retval .= $product->getData('special_from_date') . $delimProduct;
                $retval .= $product->getData('special_to_date');

                $retval .= $delimLine;
            }
//        }

        return base64_encode($retval);
    }
}

?>