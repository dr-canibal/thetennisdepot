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
 * Special Validator
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Special_Validator extends Varien_Object 
{
     
/**
 * Returns all rules that apply to a given review
 *
 * @return array(TBT_rewards_Model_Special)
 */
    public function getApplicableRulesOnReview(){

    	$resultCollection = array();
        $ruleCollection = Mage::getModel('rewards/special')->getCollection();
        foreach($ruleCollection as $rule){
            if($this->isRuleValid($rule, TBT_Rewards_Model_Special_Action::ACTION_WRITE_REVIEW) 
            || $this->isRuleValid($rule, TBT_Rewards_Model_Special_Action::ACTION_RATING))
                $resultCollection[] = $rule;        
        }
        return $resultCollection;
    }
    
/**
 * Returns all rules that apply to a sendign a product to a friend
 *
 * @return array(TBT_rewards_Model_Special)
 */
    public function getApplicableRulesOnSendfriend(){
        return $this->getApplicableRules(TBT_Rewards_Model_Special_Action::ACTION_SENDFRIEND);
    }
    
   /**
    * Returns all rules that apply to a given Poll
    *
    * @return array(TBT_rewards_Model_Special)
    */
    public function getApplicableRulesOnPoll(){
        return $this->getApplicableRules(TBT_Rewards_Model_Special_Action::ACTION_POLL);
    }
    
   /**
    * Returns all rules that apply to a given Tag
    *
    * @return array(TBT_rewards_Model_Special)
    */
    public function getApplicableRulesOnTag(){
        return $this->getApplicableRules(TBT_Rewards_Model_Special_Action::ACTION_TAG);
    }
    
   /**
    * Returns all rules that apply wehn a customer signs up to a newsletter
    *
    * @return array(TBT_rewards_Model_Special)
    */
    public function getApplicableRulesOnNewsletter(){
        return $this->getApplicableRules(TBT_Rewards_Model_Special_Action::ACTION_NEWSLETTER);
    }
/**
 * Returns all rules that apply to a given Rating
 *
 * @return array(TBT_rewards_Model_Special)
 */
    public function getApplicableRulesOnRating(){
        return $this->getApplicableRules(TBT_Rewards_Model_Special_Action::ACTION_RATING);
    }
    
/**
 * Returns all rules that apply to a customer signing up
 *
 * @return array(TBT_rewards_Model_Special)
 */
    public function getApplicableRulesOnSignup(){
        return $this->getApplicableRules(TBT_Rewards_Model_Special_Action::ACTION_SIGN_UP);
    }
    
    private function getApplicableRules($action){
        $resultCollection = array();
        $ruleCollection = Mage::getModel('rewards/special')->getCollection();
        foreach($ruleCollection as $rule){
            if($this->isRuleValid($rule, $action))
                $resultCollection[] = $rule;        
        }
        return $resultCollection;
    }

    /**
     * True if the rule is valid
     *
     * @param TBT_Rewards_Model_Special $rule
     * @param unknown_type $actionType
     * @return unknown
     */
    private function isRuleValid($rule, $actionType){
        $flag = true;
    	Varien_Profiler::start("TBT_REWARDS: Special Rule Validate");
        $localDate = Mage::getModel('core/date')->gmtDate();   
        
        //Check to see if its active
        if(!$rule->getIsActive())
            $flag = false;   
            
        //Make sure its after the start date           
        if(strtotime($rule->getFromDate()) >= strtotime($localDate))
            $flag = false;
           
        //make sure the ending date is not empty
        if($rule->getToDate() != ""){
        	//if it isn't make sure its before the ending date
            if(strtotime($rule->getToDate()) + 86399 <= strtotime($localDate))
            	$flag = false;
        }
                                    
        //Make sure the customer is within the allowed group for the rule   
        $customer  = $this->_getRS()->getSessionCustomer();
        $customer_group_ids = explode(",", $rule->getCustomerGroupIds());
        if(!$this->isInGroup($customer, $customer_group_ids)) {
            $flag = false;
        }

        //Unhashes the coditions and checks to make sure the rule triggers on the 
        //customer performing the correct action
        $rule_conditions = $this->_getRH()->unhashIt($rule->getConditionsSerialized());
        if(is_array($rule_conditions)){
        	if(!in_array($actionType, $rule_conditions)){
        		$flag = false;
        	}       	
        }else{
	        if($rule_conditions !=  $actionType) {
	            $flag = false;
	        }
        }

    	Varien_Profiler::stop("TBT_REWARDS: Special Rule Validate");
        return $flag;
    }
    
    /**
     * Returns true if customerId is within the customer groups listed
     * @param TBT_Rewards_Model_Customer $customer	: current customer id
     * @param array $groupIds                   	: customer group ids array
     * 
     * @return boolean                          
     */
    private function isInGroup($customer, array $groupIds) {
    	$group_id = $customer->getGroupId();
        return array_search($group_id, $groupIds) !== false;
    }
    
    
    /**
     * Fetches the rewards session singleton
     *
     * @return TBT_Rewards_Model_Session
     */
    protected function _getRS() {
    	return Mage::getSingleton('rewards/session');
    }
    
      
    /**
     * Fetches the rewards helper singleton
     *
     * @return TBT_Rewards_Helper_Data
     */
    protected function _getRH() {
    	return Mage::helper('rewards');
    }
    
}
?>