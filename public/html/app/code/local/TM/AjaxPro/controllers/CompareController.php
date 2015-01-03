<?php

require_once 'Mage/Catalog/controllers/Product/CompareController.php';
class TM_AjaxPro_CompareController extends Mage_Catalog_Product_CompareController
{

    private function _sendJson(array $data = array())
    {
        // clear message stack
        $storageAreas[] = Mage::getSingleton('checkout/session');
        $storageAreas[] = Mage::getSingleton('catalog/session');
        foreach ($storageAreas as $storage){
            $storage->getMessages(true);
        }

        header('Content-type: application/json');
        echo json_encode($data);
        exit();
    }

    /**
     *
     * @param json string array $handles
     */
    private function _updateLayout($handles)
    {
        $handles = Zend_Json::decode($handles);
        foreach ($handles as $handle) {
            $this->getLayout()->getUpdate()->addHandle($handle);
        }
        $this->loadLayout(false, false);
        $this->_layoutXml = $this->getLayout()->getUpdate()->asSimplexml();
    }

    private function _genarateBlock($blockName)
    {
        $nodes = $this->_layoutXml->xpath("//block[@name='" . $blockName . "']");
        if (0 == count($nodes)) {
            return '';
        }
        //hardcoded
        foreach ($nodes as &$node) {
            $node['parent'] = 'root';
        }
        $this->getLayout()->generateBlocks($nodes);

        $block = $this->getLayout()->getBlock($blockName);

        if (!$block) {
            return '';
        }
        return $block->toHtml();
    }

    protected function _generateResponse($message = '')
    {
        $response = array();
        if (!empty($message)) {
            $response['message'] = $message;
        }

        $response['compareSideBar'] =
            $this->_genarateBlock('catalog.compare.sidebar');
        $response['rightReportsProductCompared'] =
            $this->_genarateBlock('right.reports.product.compared');
        return $response;
    }

    public function addAction()
    {
        $isAjaxProRequest = $this->getRequest()->getParam('ajaxpro', false);
		if ((!$isAjaxProRequest && !$this->getRequest()->isXmlHttpRequest()) ||
			!$this->getRequest()->isXmlHttpRequest() ||
			!Mage::getStoreConfig('ajax_pro/general/enabled')) {
			
            return parent::addAction();
        }
        $message = '';
        if ($productId = (int) $this->getRequest()->getParam('product')) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);

            if ($product->getId()/* && !$product->isSuper()*/) {
                Mage::getSingleton('catalog/product_compare_list')->addProduct($product);
                $message = __('Product %s successfully added to compare list', $product->getName());
                Mage::dispatchEvent('catalog_product_compare_add_product', array('product'=>$product));
            }

            Mage::helper('catalog/product_compare')->calculate();
        }
        $this->_updateLayout($this->getRequest()->getParam('handles'));

        $this->_sendJson($this->_generateResponse($message));
    }

    /**
     * Remove item from compare list
     */
    public function removeAction()
    {
        $isAjaxProRequest = $this->getRequest()->getParam('ajaxpro', false);
		if ((!$isAjaxProRequest && !$this->getRequest()->isXmlHttpRequest()) ||
			!$this->getRequest()->isXmlHttpRequest() ||
			!Mage::getStoreConfig('ajax_pro/general/enabled')) {
            return parent::removeAction();
        }
        if ($productId = (int) $this->getRequest()->getParam('product')) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);

            if($product->getId()) {
                $item = Mage::getModel('catalog/product_compare_item');
                if(Mage::getSingleton('customer/session')->isLoggedIn()) {
                    $item->addCustomerData(Mage::getSingleton('customer/session')->getCustomer());
                } else {
                    $item->addVisitorId(Mage::getSingleton('log/visitor')->getId());
                }

                $item->loadByProduct($product);

                if($item->getId()) {
                    $item->delete();

                    $message = $this->__('Product %s successfully removed from compare list', $product->getName());
                    Mage::dispatchEvent('catalog_product_compare_remove_product', array('product'=>$item));
                    Mage::helper('catalog/product_compare')->calculate();

                }
            }
        }
        $this->_updateLayout($this->getRequest()->getParam('handles'));

        $this->_sendJson($this->_generateResponse($message));
    }

    public function clearAction()
    {
        $isAjaxProRequest = $this->getRequest()->getParam('ajaxpro', false);
        if ((!$isAjaxProRequest && !$this->getRequest()->isXmlHttpRequest()) ||
			!$this->getRequest()->isXmlHttpRequest() ||
			!Mage::getStoreConfig('ajax_pro/general/enabled')) {
			
            return parent::clearAction();
        }
        $items = Mage::getResourceModel('catalog/product_compare_item_collection')
            //->useProductItem(true)
            //->setStoreId(Mage::app()->getStore()->getId())
            ;

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $items->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId());
        }
        else {
            $items->setVisitorId(Mage::getSingleton('log/visitor')->getId());
        }

        try {
            $items->clear();
            Mage::helper('catalog/product_compare')->calculate();

        }
        catch (Mage_Core_Exception $e) {
            $this->_sendJson(array(
                'redirectUrl' => $this->_redirectReferer(),
                'message' => $e->getMessage()
            ));
        }
        catch (Exception $e) {
            $this->_sendJson(array(
                'redirectUrl' => $this->_redirectReferer(),
                'message' => $this->__('There was an error while cleared compare list')
            ));
        }

        $this->_updateLayout($this->getRequest()->getParam('handles'));

        $this->_sendJson($this->_generateResponse(
            $this->__('Compare list successfully cleared')
        ));
    }
}