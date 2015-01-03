<?php
/**
 * WDCA - Sweet Tooth
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the WDCA SWEET TOOTH POINTS AND REWARDS 
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL: 
 *      http://www.wdca.ca/solutions_page_sweettooth/Sweet_Tooth_License.php
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
 * Tax calulcator rewrite from the original magento
 * tax total calculator 
 * This needed to be done because of the strange way that Magento
 * handles taxes.  
 * Thsi is the same as TBT_Rewards_Model_Sales_Quote_Address_Total_Tax but it is written for Magento 1.4 instead of 1.3 
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Sales_Quote_Address_Total_Tax2 extends Mage_Tax_Model_Sales_Total_Quote_Tax
{


    /**
     * Collect tax totals for quote address
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_Tax_Model_Sales_Total_Quote
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        Varien_Profiler::start("TBT_Rewards:: Recalculating tax for points redemption purposes.");
        $store = $address->getQuote()->getStore();
        $customer = $address->getQuote()->getCustomer();
        $shippingTaxClass   = Mage::helper('tax')->getShippingTaxClass($store);
        foreach ($address->getAllItems() as $item) {
            Mage::getSingleton('rewards/redeem')->refactorRedemptions($item, false);
        }
        Varien_Profiler::stop("TBT_Rewards:: Recalculating tax for points redemption purposes.");
        parent::collect($address);
        Varien_Profiler::start("TBT_Rewards:: Recalculating tax for points redemption purposes.");
        if(!$shippingTaxClass) {
            // Reset shipping tax amount
            $address->setTaxAmount($address->getTaxAmount() - $address->getShippingTaxAmount());
            $address->setBaseTaxAmount($address->getBaseTaxAmount() - $address->getBaseShippingTaxAmount());
            $address->setShippingTaxAmount(null);
            $address->setBaseShippingTaxAmount(null);
            
            $request = $this->_calculator->getRateRequest(
                $address,
                $address->getQuote()->getBillingAddress(),
                $address->getQuote()->getCustomerTaxClassId(),
                $store
            );
            
            $address->setTotalAmount('tax', max(0, $address->getTaxAmount()));
            $address->setBaseTotalAmount('tax', max(0, $address->getBaseTaxAmount()));
            
            /**
             * Subtract taxes from subtotal amount if prices include tax
             */
            if ($this->_usePriceIncludeTax($store)) {
                $subtotal       = $address->getSubtotalInclTax() - $address->getTotalAmount('tax');
                $baseSubtotal   = $address->getBaseSubtotalInclTax() - $address->getBaseTotalAmount('tax');
                $address->setTotalAmount('subtotal', $subtotal);
                $address->setBaseTotalAmount('subtotal', $baseSubtotal);
            }
            
            // Recalculate shipping tax amounts 
            $this->_calculateShippingTax($address, $request);
        }
        
        Varien_Profiler::stop("TBT_Rewards:: Recalculating tax for points redemption purposes.");
        return $this;
    }


}
