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
 * Observer Sales Convert Quote To Order
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Observer_Sales_Convert_Quotetoorder 
		implements TBT_Rewards_Model_Customer_Listener
{
	/**
	 * Quotation model by reference for quoteToOrder conversions event
	 *
	 * @var TBT_Rewards_Model_Sales_Quote
	 */
	protected $quote = null;
	
	public function __construct() { }	
	
    /**
    * Applies the special price percentage discount
    * @param   Varien_Event_Observer $observer
    * @return  Xyz_Catalog_Model_Price_Observer
    */
    public function prepareCatalogPointsTransfers($observer)
    {
        $event = $observer->getEvent();
        $this->quote = $event->getQuote();
	    
    	if(Mage::helper('rewards')->isMultishipMode($this->quote)) {
            return $this;
    	}
        if (!$this->quote ) {
            return $this;
        }
        
        if($this->quote->getCheckoutMethod() == Mage_Sales_Model_Quote::CHECKOUT_METHOD_REGISTER) {
        	$this->_getRewardsSession()->addCustomerListener($this);
        } else  {
			$this->quote->collectQuoteToOrderTransfers();
        }
    
        
        
        return $this;
    }
    
    /**
     * Fetches the rewards session
     *
     * @return TBT_Rewards_Model_Session
     */
    protected function _getRewardsSession() {
 		return Mage::getSingleton('rewards/session');   	
    	
    }
    
    
	/**
	 * Triggered when customer model is created
	 *
	 * @param TBT_Rewards_Model_Customer $customer
	 * @return TBT_Rewards_Model_Customer_Listener
	 */
	public function onNewCustomerCreate(&$customer) {
		$this->quote->collectQuoteToOrderTransfers();
		return $this;
	}
}