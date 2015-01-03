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
 * Test Controller used for testing purposes ONLY!
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 *
*/

class TBT_Rewards_Debug_Sales_OrderController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
		die("This is the test controller that should be used for test purposes only!");
    }

    
    /**
     * gets a product
     *
     * @param integer $id
     * @return TBT_Rewards_Model_Catalog_Product
     */
    public function _getProduct($id) {
    	return Mage::getModel('rewards/catalog_product')->load($id);
    }
    
    
    
    /**
     * Fetches an order model
     *
     * @return TBT_Rewards_Model_Sales_Order
     */
    public function _getOrder($id=167) {
    	return Mage::getModel('rewards/sales_order')->load($id);
    }
    
    
    
    /**
     * Fetches the Jay rewards customer model.
     *
     * @return TBT_Rewards_Model_Customer
     */
    public function _getJay() {
    	return Mage::getModel('rewards/customer')->load(1);
    }
    

    /**
     * Fetches the rewards session
     *
     * @return TBT_Rewards_Model_Session
     */
    public function _getSess() {
    	return Mage::getSingleton('rewards/session');
    }
    
    /**
     * Gets the default rewards helper
     *
     * @return TBT_Rewards_Helper_Data
     */
    public function _getHelp() {
    	return Mage::helper('rewards');
    }
    
    public function orderPointsAction() {
    	echo "<html><body>";
    	echo "<PRE>";
    	echo "1 TRANSFER for order id 167: \n";
    	$this->printOrderPoints($this->_getOrder(167));
    	echo "\n\n0 TRANSFERS for order id 164: \n";
    	$this->printOrderPoints($this->_getOrder(164));
    	echo "\n\nMANY TRANSFERS for order id 160: \n";
    	$this->printOrderPoints($this->_getOrder(160));
    	echo "\n\n ONLY EARNED TRANSFERS for order id 157: \n";
    	$this->printOrderPoints($this->_getOrder(157));
    	echo "</PRE>";
    	echo "</body></html>";
    }
    
    public function orderAssocTransfersAction() {
    	echo "<html><body>";
    	echo "<PRE>";
    	echo "1 TRANSFER: \n";
    	$this->printOrderTransferData($this->_getOrder(167));
    	echo "\n\n0 TRANSFERS: \n";
    	$this->printOrderTransferData($this->_getOrder(164));
    	echo "\n\nMANY TRANSFERS: \n";
    	$this->printOrderTransferData($this->_getOrder(160));
    	echo "</PRE>";
    	echo "</body></html>";
    }

    public function orderPointsSpentAction() {
    	echo "<html><body>";
    	echo "<PRE>";
    	echo "TODO";
    	echo "</PRE>";
    	echo "</body></html>";
    }
    
    /**
     * prints order transfer data
     *
     * @param TBT_Rewards_Model_Sales_Order $order
     */
    protected function printOrderTransferData($order) {
    	$transfers = $order->getAssociatedTransfers();
    	foreach ($transfers as $transfer) {
    		print_r($transfer->getData());
    	}
    	
    }
    
    
    /**
     * prints order point sum data
     *
     * @param TBT_Rewards_Model_Sales_Order $order
     */
    protected function printOrderPoints($order) {
    	$points_earned = $order->getTotalEarnedPoints();
    	$earned_str = $this->_getHelp()->getPointsString($points_earned);
    	echo $order->hasPointsEarning() ? "Has points earning..." : "no points earning :(...";
    	echo "Points earned: ". $earned_str . "\n";
    	
    	$points_spent = $order->getTotalSpentPoints();
    	$spent_str = $this->_getHelp()->getPointsString($points_spent);
    	echo $order->hasPointsSpending() ? "Has points spending..." : "no points spending :(...";
    	echo "Points spent: ". $spent_str . "\n";
    	
    }
    
    

}