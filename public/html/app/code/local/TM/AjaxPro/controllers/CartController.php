<?php
/* <!-- AjaxPro --> */
/**
 * Shopping cart controller
 */
require_once 'Mage/Checkout/controllers/CartController.php';
class TM_AjaxPro_CartController extends Mage_Checkout_CartController
{
    private function _sendJson(array $data = array())
    {
        // clear message stack
        $storageAreas[] = Mage::getSingleton('checkout/session');
        $storageAreas[] = Mage::getSingleton('catalog/session');
        foreach ($storageAreas as $storage){
            $storage->getMessages(true);
        }

        @header('Content-type: application/json');
        echo json_encode($data);
        exit();
    }

    private function _updateLayout($handles)
    {
        $handles = Zend_Json::decode($handles);
        foreach ($handles as $handle) {
            $this->getLayout()->getUpdate()->addHandle($handle);
        }
        $this->loadLayout(false, false);
        $this->_layoutXml = $this->getLayout()->getUpdate()->asSimplexml();
    }

    private function _addReferenceBlocks( Mage_Core_Model_Layout_Element $node)
    {
        if (!function_exists('xml2array')) {
            function xml2array($data)
            {
              if (is_object($data)) $data = get_object_vars($data);
              return (is_array($data)) ? array_map(__FUNCTION__, $data) : $data;
            }
        }
        
        $xmlBlocks = array();
        foreach ($node->block as $block) {

            $references = $this->_layoutXml->xpath("//reference[@name='" . $block['name'] . "']");
            
            foreach ($references as $reference) {
                $referenceBlock = $reference->block;
                if ($referenceBlock) {
                    $referenceBlock = $this->_addReferenceBlocks($referenceBlock);
                }
                $child = $block->addChild('block', $reference->block);
                $attributes = xml2array($reference->block);
                if (!isset($attributes['@attributes'])) {
                    continue;
                }
                
                $attributes = $attributes['@attributes'];
                foreach ($attributes as $key => $value) {
                    $child->addAttribute($key, $value);
                }

            }
        }
        return $node;
    }

    private function _genarateBlock($blockName)
    {
        $nodes = $this->_layoutXml->xpath("//block[@name='" . $blockName . "']");

        if (!count($nodes)) {
            return '';
        }
        //hardcoded
        foreach ($nodes as &$node) {
            $node['parent'] = 'root';
            $node = $this->_addReferenceBlocks($node);
        }//print_r($nodes);die();

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
        $response['topLinkCart'] = $this->_getTopCheckoutCartLinksContent();
        $response['checkoutCart'] = $this->_genarateBlock('checkout.cart');
        $response['miniCart'] = $this->_genarateBlock('cart_sidebar');

        
        return $response;
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
     * Add product to shopping cart action
     */
    public function addAction()
    {
        $isAjaxProRequest = $this->getRequest()->getParam('ajaxpro', false);
        if ((!$isAjaxProRequest && !$this->getRequest()->isXmlHttpRequest()) ||
			!$this->getRequest()->isXmlHttpRequest() ||
			!Mage::getStoreConfig('ajax_pro/general/enabled')) {
			
			return parent::addAction();
        }
        
        $cart   = $this->_getCart();
        $params = $this->getRequest()->getParams();

        $product= $this->_initProduct();
        $related= $this->getRequest()->getParam('related_product');

        /**
         * Check product availability
         */
        if (!$product) {
            $this->_sendJson(array(
                'redirectUrl' => $this->_getRefererUrl(),
                'message' => __('Product not available')
            ));
        }

        try {
            
            $cart->addProduct($product, $params);
            
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }
            
            $cart->save();

            $this->_getSession()->setCartWasUpdated(true);

            /**
             * @todo remove wishlist observer processAddToCart
             */
            Mage::dispatchEvent('checkout_cart_add_product_complete',
                array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );
            $message = $this->__('%s was successfully added to your shopping cart.', $product->getName());

            $this->_updateLayout($this->getRequest()->getParam('handles'));

            $this->_sendJson($this->_generateResponse(__($message)));
        }
        catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $this->_getSession()->addNotice($e->getMessage());
                $allMessages = $e->getMessage();
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                $allMessages = '';
                foreach ($messages as $message) {
                    $this->_getSession()->addError($message);
                    $allMessages .= $message . "\n";
                }
            }

            $url = $this->_getSession()->getRedirectUrl(true);
            if (!$url) {
                $url = Mage::helper('checkout/cart')->getCartUrl();
            }

            $this->_sendJson(array(
                'redirectUrl' => $url,
                'message' => __($allMessages)
            ));
        }
        catch (Exception $e) {
            $message = 'Can not add item to shopping cart' . $e->getMessage();
            $this->_getSession()->addException($e, __($message));
            
            $this->_sendJson(array(
                'redirectUrl' => $this->_getRefererUrl(),
                'message' => __($message)
            ));
        }
    }

    /**
     * Update shoping cart data action
     */
    public function updatePostAction()
    {

        $isAjaxProRequest = $this->getRequest()->getParam('ajaxpro', false);
        if ((!$isAjaxProRequest && !$this->getRequest()->isXmlHttpRequest()) ||
			!$this->getRequest()->isXmlHttpRequest() ||
			!Mage::getStoreConfig('ajax_pro/general/enabled')) {
			
            return  parent::updatePostAction();
        }

        try {
            
            $this->_updateLayout($this->getRequest()->getParam('handles'));
            
            $cartData = $this->getRequest()->getParam('cart');
            if (is_array($cartData)) {
                $cart = $this->_getCart();
                $cart->updateItems($cartData)
                    ->save();
            }
            $this->_getSession()->setCartWasUpdated(true);


            if ($this->_getSession()->getQuote()->getHasError()) {
                $this->_getSession()->getQuote()->setMessages(array());
                $this->_getSession()->getQuote()->setHasError(false);
                return; //reload shopping cart page
            }
            $this->_getSession()->getQuote()->setMessages(array());
            return $this->_sendJson($this->_generateResponse(__('Update successful')));
        }
        catch (Exception $e) {
            $message = $e->getMessage();
        }
        $this->_sendJson(array(
            //'redirectUrl' => $this->_getRefererUrl(),
            'message' => __($message)
        ));
    }


    /**
     * Delete shoping cart item action
     */
    public function deleteAction()
    {
        $isAjaxProRequest = $this->getRequest()->getParam('ajaxpro', false);
        if ((!$isAjaxProRequest && !$this->getRequest()->isXmlHttpRequest()) ||
			!$this->getRequest()->isXmlHttpRequest() ||
			!Mage::getStoreConfig('ajax_pro/general/enabled')) {
			
            return parent::deleteAction();
        }
        
        $id = (int) $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $this->_getCart()->removeItem($id)
                  ->save();
                $this->_updateLayout($this->getRequest()->getParam('handles'));

                $this->_sendJson($this->_generateResponse(
                    __('Shopping cart item(s) remove')
                ));

            } catch (Exception $e) {
                //$this->_getSession()->addError(__('Cannot remove item'));
                $this->_sendJson(array(
                    'redirectUrl' => Mage::getUrl('*/*'),
                    'message' => __('Cannot remove item')
                ));
            }
        }
        $this->_sendJson(array(
            'redirectUrl' => Mage::getUrl('*/*'),
            'message' => __('set id')
        ));
    }


    
}