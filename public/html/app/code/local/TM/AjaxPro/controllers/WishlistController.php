<?php
require_once 'Mage/Wishlist/controllers/IndexController.php';
class TM_AjaxPro_WishlistController extends Mage_Wishlist_IndexController
{
    private function _sendJson(array $data = array())
    {
        // clear message stack
        $storageAreas[] = Mage::getSingleton('checkout/session');
        $storageAreas[] = Mage::getSingleton('catalog/session');
        foreach ($storageAreas as $storage) {
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

        $response['topLinkWishlist'] = $this->_getTopLinkWishlistContent();

        $response['wishlistSideBar'] = $this->_genarateBlock('wishlist_sidebar');

        $response['customerWishlist'] = $this->_genarateBlock('customer.wishlist');

        return $response;
    }

    private function _getTopLinkWishlistContent()
    {
        if (Mage::helper('wishlist')->isAllow()) {
            $count = Mage::helper('wishlist')->getItemCount();
            if( $count > 1 ) {
                $text = $this->__('My Wishlist (%d items)', $count);
            } elseif( $count == 1) {
                $text = $this->__('My Wishlist (%d item)', $count);
            } else {
                $text = $this->__('My Wishlist');
            }
        }
        return '<a class="top-link-wishlist" title="' . $text
            . '" href="' . Mage::getBaseUrl() . 'wishlist/">'
            . $text . '</a>';
    }

    private function _getTopCheckoutCartLinksContent()
    {
        $count = Mage::helper('checkout/cart')->getSummaryCount();

        if( $count == 1 ) {
            $text = $this->__('My Cart (%s item)', $count);
        } elseif( $count > 0 ) {
            $text = $this->__('My Cart (%s items)', $count);
        } else {
            $text = $this->__('My Cart');
        }

        return '<a class="top-link-cart" title="' . $text
            . '" href="' . Mage::getBaseUrl() . 'checkout/cart/">'
            . $text . '</a>';

        // return $this->_genarateBlock('top.links');
    }
    

    /**
     * Adding new item
     */
    public function addAction()
    {
        $isAjaxProRequest = $this->getRequest()->getParam('ajaxpro', false);
		if ((!$isAjaxProRequest && !$this->getRequest()->isXmlHttpRequest()) ||
			!$this->getRequest()->isXmlHttpRequest() ||
			!Mage::getStoreConfig('ajax_pro/general/enabled')) {
            return parent::addAction();
        }
        $session = Mage::getSingleton('customer/session');
        $wishlist = $this->_getWishlist();
        if (!$wishlist) {
            $this->_sendJson(array(
                'redirectUrl' => $this->_redirectReferer(),
                'message' => __('wishlist model error')
            ));
        }

        $productId = (int) $this->getRequest()->getParam('product');
        if (!$productId) {
            $this->_sendJson(array(
                'redirectUrl' => $this->_redirectReferer(),
                'message' => __('productId param not set')
            ));
        }

        $product = Mage::getModel('catalog/product')->load($productId);
        if (!$product->getId() || !$product->isVisibleInCatalog()) {
            $this->_sendJson(array(
                'redirectUrl' => $this->_redirectReferer(),
                'message' => __('Cannot specify product')
            ));
        }

        try {
            $wishlist->addNewItem($product->getId());
            Mage::dispatchEvent('wishlist_add_product', array('wishlist'=>$wishlist, 'product'=>$product));

            if ($referer = $session->getBeforeWishlistUrl()) {
                $session->setBeforeWishlistUrl(null);
            }
            else {
                $referer = $this->_getRefererUrl();
            }
            Mage::helper('wishlist')->calculate();
            $message = $this->__('%1$s was successfully added to your wishlist. Click <a href="%2$s">here</a> to continue shopping', $product->getName(), $referer);
        }
        catch (Mage_Core_Exception $e) {
            $message = $this->__('There was an error while adding item to wishlist: %s', $e->getMessage());
        }
        catch (Exception $e) {
            $message = $this->__('There was an error while adding item to wishlist.');
        }
        $this->_updateLayout($this->getRequest()->getParam('handles'));
        $this->_sendJson($this->_generateResponse($message));
    }

    /**
     * Remove item
     */
    public function removeAction()
    {
        $isAjaxProRequest = $this->getRequest()->getParam('ajaxpro', false);
		if ((!$isAjaxProRequest && !$this->getRequest()->isXmlHttpRequest()) ||
			!$this->getRequest()->isXmlHttpRequest() ||
			!Mage::getStoreConfig('ajax_pro/general/enabled')) {
			
            return parent::removeAction();
        }
        $wishlist = $this->_getWishlist();
        $id = (int) $this->getRequest()->getParam('item');
        $item = Mage::getModel('wishlist/item')->load($id);

        $message = '';
        if($item->getWishlistId()==$wishlist->getId()) {
            try {
                $item->delete();
                Mage::helper('wishlist')->calculate();
            }
            catch (Mage_Core_Exception $e) {
                $message = __('There was an error while deleting item from wishlist: %s', $e->getMessage());
            }
            catch(Exception $e) {
                $message = __('There was an error while deleting item from wishlist.');
            }
        }
        $this->_updateLayout($this->getRequest()->getParam('handles'));
        $this->_sendJson($this->_generateResponse($message));
    }

    /**
     * Add wishlist item to shopping cart
     */
    public function cartAction()
    {
        $isAjaxProRequest = $this->getRequest()->getParam('ajaxpro', false);
		if ((!$isAjaxProRequest && !$this->getRequest()->isXmlHttpRequest()) ||
			!$this->getRequest()->isXmlHttpRequest() ||
			!Mage::getStoreConfig('ajax_pro/general/enabled')) {
            return parent::cartAction();
        }
        $wishlist   = $this->_getWishlist();
        $id         = (int) $this->getRequest()->getParam('item');
        $item       = Mage::getModel('wishlist/item')->load($id);

        if($item->getWishlistId()==$wishlist->getId()) {
            try {
                $product = Mage::getModel('catalog/product')->load($item->getProductId())->setQty(1);
                $quote = Mage::getSingleton('checkout/cart')
                   ->addProduct($product)
                   ->save();
                $item->delete();

                $this->_updateLayout($this->getRequest()->getParam('handles'));
                $response = $this->_generateResponse();
                $response['topLinkCart'] = $this->_getTopCheckoutCartLinksContent();
                $response['checkoutCart'] = $this->_genarateBlock('checkout.cart');
                $response['miniCart'] = $this->_genarateBlock('cart_sidebar');
                Mage::helper('wishlist')->calculate();
                $this->_sendJson($response);
            }
            catch(Exception $e) {
                Mage::getSingleton('checkout/session')->addError($e->getMessage());
                $url = Mage::getSingleton('checkout/session')->getRedirectUrl(true);
                if ($url) {
                    $url = Mage::getModel('core/url')->getUrl('catalog/product/view', array(
                        'id'=>$item->getProductId(),
                        'wishlist_next'=>1
                    ));
                    Mage::getSingleton('checkout/session')->setSingleWishlistId($item->getId());
                }
                else {
                    $url = $this->_redirectReferer();
                }
                $this->_sendJson(array(
                    'redirectUrl' => $this->_redirectReferer(),
                    'message' => __($e->getMessage())
                ));
                return;
            }
        }
    }
}
