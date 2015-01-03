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

class TBT_Rewards_Debug_SpecialController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
		die("This is the test controller that should be used for test purposes only!");
    }
    
    public function newsPointsTestAction() {
    	echo " <pre> ";
    	
    	$customer = $this->_getJay();
    	echo "Initiating newsletter points check for " . $customer->getName() . " with e-mail " . $customer->getEmail() . " and id=" . $customer->getId() . " \n";
    	
    	$newsletter_id = 1;
    	$has_newsletter_points = $customer->hasPointsForNewsletter($newsletter_id);
    	
    	echo ($has_newsletter_points ? "You have received points for newsletter #$newsletter_id!" : "You have not received points for newsletter #$newsletter_id yet.\n");
    	
    	$newsletter_xfers = $customer->getNewsletterTransfers($newsletter_id)->getItems();
    	
    	print_r($newsletter_xfers);
		
    	echo " </pre> ";
		die();
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

}