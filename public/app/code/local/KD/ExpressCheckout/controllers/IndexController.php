<?php

class KD_ExpressCheckout_IndexController extends Mage_Checkout_Controller_Action
{
    protected $_sectionUpdateFunctions = array(
        'payment-method'  => '_getPaymentMethodsHtml',
        'shipping-method' => '_getShippingMethodsHtml',
        'review'          => '_getReviewHtml',
    );

    private $_loadedLayout = null;

    /**
     * @return Mage_Checkout_OnepageController
     */
    public function preDispatch()
    {
        parent::preDispatch();
        $this->_preDispatchValidateCustomer();
        return $this;
    }

    protected function _ajaxRedirectResponse()
    {
        $this->getResponse()
            ->setHeader('HTTP/1.1', '403 Session Expired')
            ->setHeader('Login-Required', 'true')
            ->sendResponse();
        return $this;
    }

    /**
     * Validate ajax request and redirect on failure
     *
     * @return bool
     */
    protected function _expireAjax()
    {
        if (!$this->getCheckout()->getQuote()->hasItems()
            || $this->getCheckout()->getQuote()->getHasError()
            || $this->getCheckout()->getQuote()->getIsMultiShipping()) {
            $this->_ajaxRedirectResponse();
            return true;
        }
        $action = $this->getRequest()->getActionName();
        if (Mage::getSingleton('checkout/session')->getCartWasUpdated(true)
            && !in_array($action, array('index', 'progress'))) {
            $this->_ajaxRedirectResponse();
            return true;
        }

        return false;
    }

    protected function _getLoadedUpdateCheckoutLayout()
    {
        if (null === $this->_loadedLayout) {
            $layout = $this->getLayout();
            $update = $layout->getUpdate();
            $update->load('expresscheckout_index_updatecheckout');
            $layout->generateXml();
            $layout->generateBlocks();
            $this->_loadedLayout = $layout;
        }
        return $this->_loadedLayout;
    }

    /**
     * Get shipping method html
     *
     * @return string
     */
    protected function _getShippingMethodsHtml()
    {
        return $this->_getLoadedUpdateCheckoutLayout()->getBlock('checkout.shipping.method')->toHtml();
    }

    /**
     * Get payment method html
     *
     * @return string
     */
    protected function _getPaymentMethodsHtml()
    {
        return $this->_getLoadedUpdateCheckoutLayout()->getBlock('checkout.payment.method')->toHtml();
    }

    /**
     * Get order review html
     *
     * @return string
     */
    protected function _getReviewHtml()
    {
        return $this->_getLoadedUpdateCheckoutLayout()->getBlock('checkout.review')->toHtml();
    }

    /**
     * Get express checkout model
     *
     * @return KD_ExpressCheckout_Model_Type_Standard
     */
    public function getCheckout()
    {
        return Mage::getSingleton('expresscheckout/type_standard');
    }

    public function forgotpasswordAction()
    {
        $session = Mage::getSingleton('customer/session');

        if ($this->_expireAjax() || $session->isLoggedIn()) {
            return;
        }

        $email = $this->getRequest()->getPost('email');
        $result = array(
            'success' => false
        );
        if ($email) {
            if (!Zend_Validate::is($email, 'EmailAddress')) {
                $session->setForgottenEmail($email);
                $result['error'] = Mage::helper('checkout')->__('Invalid email address.');
            } else {
                $customer = Mage::getModel('customer/customer')
                    ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                    ->loadByEmail($email);

                if ($customer->getId()) {
                    try {
                        $newPassword = $customer->generatePassword();
                        $customer->changePassword($newPassword, false);
                        $customer->sendPasswordReminderEmail();
                        $result['success'] = true;
                        $result['message'] = Mage::helper('customer')->__('A new password has been sent.');
                    } catch (Exception $e){
                        $result['error'] = $e->getMessage();
                    }
                } else {
                    $result['error'] = Mage::helper('customer')->__('This email address was not found in our records.');
                    $session->setForgottenEmail($email);
                }
            }
        } else {
            $result['error'] = Mage::helper('customer')->__('Please enter your email.');
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function loginAction()
    {
        $session = Mage::getSingleton('customer/session');

        if ($this->_expireAjax() || $session->isLoggedIn()) {
            return;
        }

        $result = array(
            'success' => false
        );

        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost('login');
            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $session->login($login['username'], $login['password']);
                    $result['redirect'] = Mage::getUrl('*/*/index');
                    $result['success'] = true;
                } catch (Mage_Core_Exception $e) {
                    switch ($e->getCode()) {
                        case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                            $message = Mage::helper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', Mage::helper('customer')->getEmailConfirmationUrl($login['username']));
                            break;
                        case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                            $message = $e->getMessage();
                            break;
                        default:
                            $message = $e->getMessage();
                    }
                    $result['error'] = $message;
                    $session->setUsername($login['username']);
                } catch (Exception $e) {
                    // Mage::logException($e); // PA DSS violation: this exception log can disclose customer password
                }
            } else {
                $result['error'] = Mage::helper('customer')->__('Login and password are required.');
            }
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Checkout page
     */
    public function indexAction()
    {
        if (!Mage::helper('expresscheckout')->canExpressCheckout()) {
            Mage::getSingleton('checkout/session')->addError($this->__('The express checkout is disabled.'));
            $this->_redirect('checkout/cart');
            return;
        }
        $quote = $this->getCheckout()->getQuote();
        if (!$quote->hasItems() || $quote->getHasError()) {
            $this->_redirect('checkout/cart');
            return;
        }
        if (!$quote->validateMinimumAmount()) {
            $error = Mage::getStoreConfig('sales/minimum_order/error_message');
            Mage::getSingleton('checkout/session')->addError($error);
            $this->_redirect('checkout/cart');
            return;
        }
        Mage::getSingleton('checkout/session')->setCartWasUpdated(false);
        Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('*/*/*', array('_secure'=>true)));

        $this->getCheckout()->applyDefaults()->initCheckout();
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->getLayout()->getBlock('head')->setTitle(Mage::getStoreConfig('expresscheckout/general/title'));
        $this->renderLayout();
    }

    /**
     * Order success action
     */
    public function successAction()
    {
        $session = $this->getCheckout()->getCheckout();
        if (!$session->getLastSuccessQuoteId()) {
            $this->_redirect('checkout/cart');
            return;
        }

        $lastQuoteId = $session->getLastQuoteId();
        $lastOrderId = $session->getLastOrderId();
        $lastRecurringProfiles = $session->getLastRecurringProfileIds();
        if (!$lastQuoteId || (!$lastOrderId && empty($lastRecurringProfiles))) {
            $this->_redirect('checkout/cart');
            return;
        }

        $session->clear();
        $this->loadLayout();
        $this->_initLayoutMessages('checkout/session');
        Mage::dispatchEvent('checkout_onepage_controller_success_action');
        $this->renderLayout();
    }

    public function failureAction()
    {
        $lastQuoteId = $this->getCheckout()->getCheckout()->getLastQuoteId();
        $lastOrderId = $this->getCheckout()->getCheckout()->getLastOrderId();

        if (!$lastQuoteId || !$lastOrderId) {
            $this->_redirect('checkout/cart');
            return;
        }

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Address JSON
     */
    public function getAddressAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        $addressId = $this->getRequest()->getParam('address', false);
        if ($addressId) {
            $address = $this->getCheckout()->getAddress($addressId);

            if (Mage::getSingleton('customer/session')->getCustomer()->getId() == $address->getCustomerId()) {
                $this->getResponse()->setHeader('Content-type', 'application/x-json');
                $this->getResponse()->setBody($address->toJson());
            } else {
                $this->getResponse()->setHeader('HTTP/1.1','403 Forbidden');
            }
        }
    }

    /**
     * Checks is the region_id or postcode or country was changed
     */
    protected function _isAddressChanged($type = 'Billing', $data, $addressId = false)
    {
        $address = $this->getCheckout()->getQuote()->{"get{$type}Address"}();

        if ($addressId) {
            return $address->getCustomerAddressId() != $addressId;
        } else {
            return $data['region_id']    != $address->getRegionId()
                || $data['postcode']     != $address->getPostcode()
                || $data['country_id']   != $address->getCountryId();
        }
    }

    /**
     * Update checkout sections
     */
    public function updateCheckoutAction()
    {
        if ($this->_expireAjax() || !$this->getRequest()->isPost()) {
            return;
        }

        $billingData = $this->getRequest()->getPost('billing', array());
        $billingData = $this->_filterPostData($billingData);
        $billingAddressId = $this->getRequest()->getPost('billing_address_id', false);
        $result = array();
        $shippingUpdated = false;

        // billing section
        if ($this->getRequest()->getPost('payment-method', false)
            || $this->_isAddressChanged('Billing', $billingData, $billingAddressId)) {

            if (isset($billingData['email'])) {
                $billingData['email'] = trim($billingData['email']);
            }
            $billingResult = $this->getCheckout()->saveBilling($billingData, $billingAddressId, false);

            if (!isset($billingResult['error'])) {
                $paymentData = $this->getRequest()->getPost('payment', array());
                $this->getCheckout()->applyPaymentMethod(isset($paymentData['method']) ? $paymentData['method'] : null);

                $result['update_section']['payment-method'] = $this->_getPaymentMethodsHtml();

                if (!$this->getCheckout()->getQuote()->isVirtual()
                    && isset($billingData['use_for_shipping']) && $billingData['use_for_shipping'] == 1) {

                    $shippingUpdated = true;
                    $result['update_section']['shipping-method'] = $this->_getShippingMethodsHtml();
                    $result['duplicateBillingInfo'] = 'true';
                }
            } else {
                $result['error_messages'] = $billingResult['message'];
            }
        }

        $shippingData = $this->getRequest()->getPost('shipping', array());
        $shippingAddressId = $this->getRequest()->getPost('shipping_address_id', false);
        // shipping section
        if (($this->getRequest()->getPost('shipping-method', false)
                || $this->_isAddressChanged('Shipping', $shippingData, $shippingAddressId))
            && !$shippingUpdated
            && !$this->getCheckout()->getQuote()->isVirtual()) {

            $shippingResult = $this->getCheckout()->saveShipping($shippingData, $shippingAddressId, false);

            if (!isset($shippingResult['error'])) {
                $result['update_section']['shipping-method'] = $this->_getShippingMethodsHtml();
            }
        }

        $this->getCheckout()->applyShippingMethod($this->getRequest()->getPost('shipping_method', false));

        $this->getCheckout()->getQuote()->collectTotals()->save();

        $result['update_section']['review'] = $this->_getReviewHtml();

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Create order action
     */
    public function saveOrderAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        $result = array();
        try {
            $billing = $this->_filterPostData($this->getRequest()->getPost('billing', array()));
            $result = $this->getCheckout()->saveBilling(
                $billing,
                $this->getRequest()->getPost('billing_address_id', false)
            );
            if ($result) {
                $result['success'] = false;
                $result['error'] = true;
                $result['error_messages'] = $result['message'];
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                return;
            }

            if ((!isset($billing['use_for_shipping']) || !$billing['use_for_shipping'])
                && !$this->getCheckout()->getQuote()->isVirtual()) {

                $result = $this->getCheckout()->saveShipping(
                    $this->_filterPostData($this->getRequest()->getPost('shipping', array())),
                    $this->getRequest()->getPost('shipping_address_id', false)
                );
                if ($result) {
                    $result['success'] = false;
                    $result['error'] = true;
                    $result['error_messages'] = $result['message'];
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                    return;
                }
            }

            if ($requiredAgreements = Mage::helper('expresscheckout')->getRequiredAgreementIds()) {
                $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
                if ($diff = array_diff($requiredAgreements, $postedAgreements)) {
                    $result['success'] = false;
                    $result['error'] = true;
                    $result['error_messages'] = Mage::helper('checkout')->__('Please agree to all the terms and conditions before placing the order.');
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                    return;
                }
            }

            $result = $this->_savePayment();
            if ($result && !isset($result['redirect'])) {
                $result['error_messages'] = $result['error'];
            }

            if (!isset($result['error'])) {
                Mage::dispatchEvent('checkout_controller_onepage_save_shipping_method', array('request'=>$this->getRequest(), 'quote'=>$this->getCheckout()->getQuote()));
                $this->_subscribeToNewsletter();
            }

            Mage::getSingleton('customer/session')->setOrderCustomerComment(
                $this->getRequest()->getPost('order-comment')
            );
            // didn't use quote because of its not working with paypal express.
//            $this->getCheckout()->getQuote()->setCustomerComment($this->getRequest()->getPost('order-comment'));

            if (!isset($result['redirect']) && !isset($result['error'])) {
                if ($data = $this->getRequest()->getPost('payment', false)) {
                    $this->getCheckout()->getQuote()->getPayment()->importData($data);
                }
				$this->getCheckout()->getQuote()->setCustomerNote($this->getRequest()->getPost('order-comment'));
                $this->getCheckout()->saveOrder();
                $redirectUrl = $this->getCheckout()->getCheckout()->getRedirectUrl();
                $result['success'] = true;
                $result['order_created'] = true;
                $result['error']   = false;
            }
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getCheckout()->getQuote(), $e->getMessage());
            $result['success'] = false;
            $result['error'] = true;
            $result['error_messages'] = $e->getMessage();

            if ($gotoSection = $this->getCheckout()->getCheckout()->getGotoSection()) {
                $result['goto_section'] = $gotoSection;
                $this->getCheckout()->getCheckout()->setGotoSection(null);
            }

            if ($updateSection = $this->getCheckout()->getCheckout()->getUpdateSection()) {
                if (isset($this->_sectionUpdateFunctions[$updateSection])) {

                    $layout = $this->_getLoadedUpdateCheckoutLayout();

                    $updateSectionFunction = $this->_sectionUpdateFunctions[$updateSection];
                    $result['update_section'] = array(
                        'name' => $updateSection,
                        'html' => $this->$updateSectionFunction()
                    );
                }
                $this->getCheckout()->getCheckout()->setUpdateSection(null);
            }

            $this->getCheckout()->getQuote()->save();
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getCheckout()->getQuote(), $e->getMessage());
            $result['success']  = false;
            $result['error']    = true;
            $result['error_messages'] = Mage::helper('checkout')->__('There was an error processing your order. Please contact us or try again later.');
            $this->getCheckout()->getQuote()->save();
        }

        /**
         * when there is redirect to third party, we don't want to save order yet.
         * we will save the order in return action.
         */
        if (isset($redirectUrl)) {
            $result['redirect'] = $redirectUrl;
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Save payment
     */
    protected function _savePayment()
    {
        try {
            $result = array();
            $data = $this->getRequest()->getPost('payment', array());
            $result = $this->getCheckout()->savePayment($data);

            $redirectUrl = $this->getCheckout()->getQuote()->getPayment()->getCheckoutRedirectUrl();
            if ($redirectUrl) {
                $result['redirect'] = $redirectUrl;
            }
        } catch (Mage_Payment_Exception $e) {
            if ($e->getFields()) {
                $result['fields'] = $e->getFields();
            }
            $result['error'] = $e->getMessage();
        } catch (Mage_Core_Exception $e) {
            $result['error'] = $e->getMessage();
        } catch (Exception $e) {
            Mage::logException($e);
            $result['error'] = Mage::helper('checkout')->__('Unable to set Payment Method.');
        }
        return $result;
    }

    /**
     * Subsribe payer to newsletterr.
     * All notices and error messages are not shown,
     * to not confuse payer during checkout (Only checkout messages can be showed).
     *
     * @return void
     */
    protected function _subscribeToNewsletter()
    {
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('newsletter')) {
            //$session            = Mage::getSingleton('core/session');
            $customerSession    = Mage::getSingleton('customer/session');
            $billingData        = $this->getRequest()->getPost('billing');
            $email              = $customerSession->isLoggedIn() ?
                $customerSession->getCustomer()->getEmail() : $billingData['email'];

            try {
                if (Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG) != 1 &&
                    !$customerSession->isLoggedIn()) {
                    Mage::throwException(Mage::helper('newsletter')->__('Sorry, but administrator denied subscription for guests. Please <a href="%s">register</a>.', Mage::getUrl('customer/account/create/')));
                }

                $ownerId = Mage::getModel('customer/customer')
                        ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                        ->loadByEmail($email)
                        ->getId();
                if ($ownerId !== null && $ownerId != $customerSession->getId()) {
                    Mage::throwException(Mage::helper('newsletter')->__('Sorry, but your can not subscribe email adress assigned to another user.'));
                }

                $status = Mage::getModel('newsletter/subscriber')->subscribe($email);
                /*if ($status == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE) {
                    $session->addSuccess(Mage::helper('newsletter')->__('Confirmation request has been sent.'));
                } else {
                    $session->addSuccess(Mage::helper('newsletter')->__('Thank you for your subscription.'));
                }*/
            } catch (Mage_Core_Exception $e) {
                //$session->addException($e, Mage::helper('newsletter')->__('There was a problem with the subscription: %s', $e->getMessage()));
            } catch (Exception $e) {
                //$session->addException($e, Mage::helper('newsletter')->__('There was a problem with the subscription.'));
            }
        }
    }

    /**
     * Filtering posted data. Converting localized data if needed
     *
     * @param array
     * @return array
     */
    protected function _filterPostData($data)
    {
        $data = $this->_filterDates($data, array('dob'));
        return $data;
    }
}
