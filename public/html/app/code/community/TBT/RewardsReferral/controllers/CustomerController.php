<?php
/**
 * WDCA - Sweet Tooth
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the WDCA SWEET TOOTH POINTS AND REWARDS 
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL: 
 *      http://www.wdca.ca/sweet_tooth/sweet_tooth_license.txt
 * The Open Software License is available at this URL: 
 *      http://opensource.org/licenses/osl-3.0.php
 * 
 * DISCLAIMER
 * 
 * By adding to, editing, or in any way modifying this code, WDCA is 
 * not held liable for any inconsistencies or abnormalities in the 
 * behaviour of this code. 
 * By adding to, editing, or in any way modifying this code, the Licensee
 * terminates any agreement of support offered by WDCA, outlined in the 
 * provided Sweet Tooth License. 
 * Upon discovery of modified code in the process of support, the Licensee 
 * is still held accountable for any and all billable time WDCA spent 
 * during the support process.
 * WDCA does not guarantee compatibility with any other framework extension. 
 * WDCA is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 * If you did not receive a copy of the license, please send an email to 
 * contact@wdca.ca or call 1-888-699-WDCA(9322), so we can send you a copy 
 * immediately.
 * 
 * @category   [TBT]
 * @package    [TBT_Rewards]
 * @copyright  Copyright (c) 2009 Web Development Canada (http://www.wdca.ca)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

/**
 * Customer Controller
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_RewardsReferral_CustomerController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {

        $this->loadLayout();
        
		$customer = Mage::getSingleton('rewards/session')->getRewardsCustomer();
		Mage::register('customer', $customer);
		
        $this->renderLayout();

        return $this;
    }

    public function inviteAction()
    {
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
            $session         = Mage::getSingleton('core/session');
            $email           = trim((string) strip_tags($this->getRequest()->getPost('email')));
            $name            = trim((string) strip_tags($this->getRequest()->getPost('name')));
            $msg             = trim((string) strip_tags($this->getRequest()->getPost('msg')));
            
            $customerSession = Mage::getSingleton('rewards/session');
            $sess_customer = $customerSession->getSessionCustomer();
            try {
                if (!Zend_Validate::is($email, 'EmailAddress')) {
                    Mage::throwException($this->__('Please enter a valid email address.'));
                }
                
                if ($name == ''){
                    Mage::throwException($this->__('Please enter your friend name.'));
                }
                $referralModel = Mage::getModel('rewardsref/referral');

                $customer = Mage::getModel('rewards/customer')
                                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                                ->loadByEmail($email);

                if ($referralModel->isSubscribed($email)) {
                    Mage::throwException($this->__('You or sombody else has already invited %s.', $email));
                } elseif($sess_customer->getEmail() == $email) {
                    Mage::throwException($this->__("%s is your own e-mail address.", $email));
                } elseif($customer->getEmail() == $email) {
                    Mage::throwException($this->__("%s is already signed up to the store.", $email));
                } else {
                    //if(!$sess_customer->getId()) $sess_customer = Mage::getSingleton('customer/session')->getCustomer();
                    $subscribe_result = $referralModel->subscribe($sess_customer, $email, $name, $msg);
                    if ($subscribe_result) {
                        $session->addSuccess($this->__('Thank you!  Your referral e-mail to %s has been sent.', $name));
                    } else {
                        $session->addException($this->__('There was a problem with the invitation.'));
                    }
                }
            }
            catch (Mage_Core_Exception $e) {
                $session->addException($e, $this->__('%s', $e->getMessage()));
                Mage::logException($e);
            }
            catch (Exception $e) {
                $session->addException($e, $this->__('There was a problem with the invitation.'));
                Mage::logException($e);
            }
        }
        
        $this->_redirect('*/*/');
        
        return $this;
    }


    

    public function preDispatch()
    {
        parent::preDispatch();

        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
        if (!Mage::helper('rewards/config')->getIsCustomerRewardsActive()) {
            $this->norouteAction();
            return;
        }
    }
}