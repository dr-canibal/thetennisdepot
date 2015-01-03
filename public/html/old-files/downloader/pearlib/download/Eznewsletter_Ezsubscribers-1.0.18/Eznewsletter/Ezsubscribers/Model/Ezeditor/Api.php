<?php
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

        $productCollection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('entity_id')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('type_id')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('price')
            ->addAttributeToSelect('visibility')
            ->addAttributeToSelect('status')
            ->addAttributeToSelect('websites');

        // build the product collection filter on the store ids
        if (!empty($storeIds))
        {
            $productCollection->addStoreFilter($storeIds);
        }

        foreach($productCollection as $product)
        {
            // id
            $retval .= $product->getData('entity_id') . $delimProduct;
            // name
            $retval .= $product->getData('name') . $delimProduct;
            // type
            $retval .= $product->getData('type_id') . $delimProduct;
            // attribute set
            $retval .= $product->getAttributeSetId() . $delimProduct;
            // sku
            $retval .= $product->getData('sku') . $delimProduct;
            // price
            $retval .= $product->getData('price') . $delimProduct;
            // quantity
            $retval .= Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty() . $delimProduct;
            // visibility
            $retval .= $product->getData('visibility') . $delimProduct;
            // status
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
            $retval .= $delimLine;
        }

        return base64_encode($retval);
    }
}

?>