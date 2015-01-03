<?php
class AW_FBIntegrator_FacebookController extends Mage_Core_Controller_Front_Action{

    public function checkappAction(){
        return $this->getResponse()->setBody(Mage::helper('fbintegrator')->checkApp($this->getRequest()->getParam('app_id'),$this->getRequest()->getParam('app_secret')));
    }
    public function connectAction(){
        $me = Mage::helper('fbintegrator')->getMe();
        $fbUser = Mage::getModel('fbintegrator/users')->getUser($me['id']);
        $back = Mage::helper('fbintegrator')->getFbBackUrl();
        if($fbUser->getCustomerId()){
            $customer = Mage::getModel('customer/customer')->load($fbUser->getCustomerId());
            Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
            $this->getResponse()->setRedirect($back);
        }
        else{
            $customer = Mage::getModel('customer/customer');
            $customer->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
            $customer->loadByEmail($me['email']);

            if($customer->getId()){
                $session = Mage::getSingleton('customer/session');
                Mage::getModel('fbintegrator/users')->createUser($me['id'],$customer->getEmail(),$customer->getId());
                $session->setCustomerAsLoggedIn($customer);
                $this->getResponse()->setRedirect($back);
            }
            else{
                if(Mage::helper('fbintegrator')->getCountRequiredFields() && !$this->getRequest()->getParam('from-required-form'))
                    return $this->getResponse()->setRedirect(Mage::helper('fbintegrator')->getRequiredFormUrl());

                $pass = uniqid();
                $data = array(
                  'firstname'    =>  $me['first_name'],
                  'lastname'     =>  $me['last_name'],
                  'email'        =>  $me['email'],
                  'gender'       =>  ($me['gender'] == 'female')? 124:123,
                  'password'     =>  $pass,
                  'confirmation' =>  $pass,
                  'dob'          => date('Y-m-d',strtotime($me['birthday'])),
                  'prefix'       => $this->getRequest()->getParam('prefix'),
                  'suffix'       => $this->getRequest()->getParam('suffix'),
                  'taxvat'       => $this->getRequest()->getParam('taxvat'),
                );
		
		if(isset($me['email']) && $me['email'] !== '')
                	Mage::getModel('fbintegrator/customer')->registerCustomer($me['id'],$data);

                $this->getResponse()->setRedirect($back);
            }
        }
		$this->getResponse()->setRedirect(Mage::getBaseUrl());
    }

    public function formAction(){
        $this->loadLayout();
        $block = $this->getLayout()->createBlock('fbintegrator/fb','fb_required_form',array('template' => 'fbintegrator/fb_required_form.phtml'));
        $this->getLayout()->getBlock('content')->append($block);
        $this->renderLayout();
    }
}
