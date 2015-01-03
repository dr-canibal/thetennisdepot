<?php
/**
 * @category   Netz98
 * @package    Netz98_Raffle
 * @author     Daniel Nitz <d.nitz@netz98.de>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Netz98_Raffle_ParticipantController extends Mage_Core_Controller_Front_Action
{
	public function addAction()
	{
        $session = Mage::getSingleton('core/session');
        $post = $this->getRequest()->getPost();
        
        if ($post) {
        	try {
        		$firstname = trim($post['firstname']);
        		$lastname  = trim($post['lastname']);
        		$email     = trim($post['email']);
        		$campaign  = trim($post['campaign']);
        		$redirect  = trim($post['redirect']);
        		
	            if (!Zend_Validate::is($firstname, 'Alpha')) {
	            	Mage::throwException($this->__('Please enter a valid firstname'));
	            }
	            
				if (!Zend_Validate::is($lastname, 'Alpha')) {
	            	Mage::throwException($this->__('Please enter a valid lastname'));
	            }
	            
	            if (!Zend_Validate::is($email, 'EmailAddress')) {
	            	Mage::throwException($this->__('Please enter a valid email address'));
	            } 
	            
				$newsletter = Mage::getModel('newsletter/subscriber');
			    $newsletter->loadByEmail($email);
			    
			    if (!$newsletter->isSubscribed()) {
			    	$newsletter->subscribe($email);
			    } 
			    
			    $raffle = Mage::getModel('raffle/participants');
			    $raffle->loadByEmail($email);
        		
			    if ($raffle->getId() === null && $raffle->getCampaign() != $campaign) {
			    	$raffle->setData('newsletter_subscriber_id', $newsletter->getId());
			    	$raffle->setData('firstname', $firstname);
			    	$raffle->setData('lastname', $lastname);
			    	$raffle->setData('campaign', $campaign);
			    	$raffle->setData('email_address', $email);
			    	$raffle->save();
		    	} else {
		    		Mage::throwException($this->__('You are already participating in this raffle'));
		    	}
			}
			catch (Exception $e) {
				$session->addException($e, $this->__('There was a problem with the entry: %s', $e->getMessage()));
				return $this->_redirectUrl($this->_getRefererUrl());
			}
			
			$session->addSuccess($this->__('Your raffle entry was successfull'));

        } 
		return $this->_redirect($redirect);
	}
	
}