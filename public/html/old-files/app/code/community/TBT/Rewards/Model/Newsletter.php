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
 * Newsletter
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Newsletter extends Mage_Newsletter_Model_Subscriber
{
 
    /**
     * Processing object after save data
     *
     * @return Mage_Core_Model_Abstract
     */
    public function save()
    {
    	$was_subscribed = $this->isSubscribed();
        $return = parent::save();
        if($this->isSubscribed() && !$was_subscribed){
            $this->rewardForNewSubscription();
        }
        return $return;
    }
    
    /**
     * Loops through each Special rule. If it applies, create a new pending transfer.
     */
    private function rewardForNewSubscription(){
    	try {
	        $ruleCollection = $this->_getSpecialValidator()->getApplicableRulesOnNewsletter();
	        
	        $customer = $this->getRewardsCustomer();
	        $newsletter_id = $this->getNewsletterId();
	        
	        if(!$customer->hasPointsForNewsletter($newsletter_id)){	   
		        foreach($ruleCollection as $rule){
		        	if (!$rule->getId()) {
		        		continue;
		        	}
		        	
		            try {
		                $is_transfer_successful = $this->transferNewsletterPoints( $rule );
		               
		                if($is_transfer_successful) {
				            Mage::getSingleton('core/session')->addSuccess(
				            	Mage::helper('rewards')->__('You received %s for signing up to a newsletter',
				            	Mage::getModel('rewards/points')->set($rule))
				            );
		                }
		            } catch (Exception $ex) {
		            	Mage::getSingleton('core/session')->addError($ex->getMessage());
		            }
	                              
		        }
	        } else {
				Mage::getSingleton('core/session')->addNotice(
					Mage::helper('rewards')->__("You've already received points for signing up to this newsletter in the past, so you won't get any this time.")
				);
	        }
    	} catch (Exception $e) {
    		Mage::getSingleton('core/session')->addError(Mage::helper('rewards')->__('Could not interface with customer rewards system.'));
    	}
    }    
    
    /**
     * Fetches the rewards customer trying to subscribe
     *
     * @return TBT_Rewards_Model_Customer
     */
    protected function getRewardsCustomer() {
    	return Mage::getModel('rewards/customer')->load($this->getCustomerId());
    }
    
    /**
     * Fetches the transfer helper
     *
     * @return TBT_Rewards_Helper_Transfer
     */
    protected function _getTransferHelper() {
    	return Mage::helper('rewards/transfer');
    }
    	
    /**
     * Fetches the rewards special validator singleton
     *
     * @return TBT_Rewards_Model_Special_Validator
     */
    protected function _getSpecialValidator() {
    	return Mage::getSingleton('rewards/special_validator');
    }
    
    /**
     * Pseudo newsletter ID since Magento only has one newsletter for the time being.
     *
     * @return integer
     */
    public function getNewsletterId() {
    	return 1;	
    }
       
   /**
     * Creates a customer point-transfer of any amount or currency.
     *
     * @param  $rule    : Special Rule
     * @return boolean            : whether or not the point-transfer succeeded
     */
    public function transferNewsletterPoints($rule)
    {

    	$num_points = $rule->getPointsAmount();
    	$currency_id = $rule->getPointsCurrencyId();
    	$rule_id = $rule->getId();
        $transfer = $this->_getTransferHelper()->initTransfer($num_points, $currency_id, $rule_id);
        

        if (!$transfer) {
            return false;
        }
    
        // get the default starting status - usually Pending
        if (!$transfer->setStatus(null, Mage::helper('rewards/config')->getInitialTransferStatusAfterNewsletter())) {
            // we tried to use an invalid status... is getInitialTransferStatusAfterReview() improper ??
            return false;
        }
        
        $comments = Mage::helper('rewards')->__(Mage::getStoreConfig('rewards/transferComments/newsletterEarned'));
        $customer_id = $this->getCustomerId();
        
        $transfer->setNewsletterId($this->getNewsletterId())
            ->setComments($comments)
            ->setCustomerId($customer_id)
            ->save();
        return true;
    }
}
    
?>