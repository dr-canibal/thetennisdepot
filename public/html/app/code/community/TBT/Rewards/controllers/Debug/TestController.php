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

class TBT_Rewards_Debug_TestController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
		die("This is the test controller that should be used for test purposes only!");
    }

    public function reportAction()
    {
    	global $t;
    	$t['action_loaded'] = time() + microtime();
//    	$time_to_begin = $t['action_loaded'] - $t['app_loaded'];
//    	echo "Began after $time_to_begin <BR/>"; flush();
    	
    	$m = Mage::getModel('rewards/currency')->load(1);
    	$t['simple_model_loaded'] = time() + microtime();
    	$time_to_load_model = $t['simple_model_loaded'] - $t['action_loaded'];
    	echo "Loaded the model after $time_to_load_model <BR />"; flush();
    	
    	$m = new TBT_Rewards_Model_Currency();
    	$m->load(1);
    	$t['simple_model_loaded1'] = time() + microtime();
    	$m = Mage::getModel('rewards/currency')->load(2);
    	$t['simple_model_loaded2'] = time() + microtime();
    	$m = Mage::getModel('rewards/currency')->load(3);
    	$t['simple_model_loaded3'] = time() + microtime();
    	$m = Mage::getModel('rewards/currency')->load(4);
    	$t['simple_model_loaded4'] = time() + microtime();
    	$m = Mage::getModel('rewards/currency')->load(5);
    	$t['simple_model_loaded5'] = time() + microtime();
    	$time_to_load_models = $t['simple_model_loaded5'] - $t['simple_model_loaded'];
    	$time_to_load_model_3 = $t['simple_model_loaded3'] - $t['simple_model_loaded2'];
    	echo "Loaded the model 3 after $time_to_load_model_3 <BR />"; flush();
    	echo "Loaded one of those models after $time_to_load_models <BR />"; flush();
    	
    	//$m->save(); // TODO don't save the currency model.
    	$t['simple_model_saved'] = time() + microtime();
    	$time_to_save_model = $t['simple_model_saved'] - $t['simple_model_loaded'];
    	echo "Saved that model after $time_to_save_model <br />"; flush();
    	
//    	$model_inst = $t['model_insantiations'];
//    	echo "Total time loading models = $model_inst <br />"; flush();
    	
    	
    	
    	
    	
    }
    
    public function testOptimizerAction() {
    	$this->_getProduct(12)->getRewardAdjustedPrice();
    }
    

    
    public function translateListingAction()
    {
    	echo "<PRE>";
    	$x = Mage::app()->getLocale()->getCountryTranslation("error");
    	print_r($x);
    	echo "</PRE>";
    }
    
    public function testCustomerAction() {
    	// customer id = 1 should be Jay
    	echo "Jay Has: ". $this->_getJay()->getPointsSummary() . " <BR/>";
    	echo "But more importantly Jay has: ". $this->_getJay()->getPointsSummaryFull() . " <BR/>";
    	
    	$this->testJayPayment(array(1 => 40));
    	$this->testJayPayment(array(2 => 40));
    	$this->testJayPayment(array(3 => 40));
    	
    }

    public function printRedemptionsAction() {
    	echo "<html><body>";
    	$this->printRedemptions();
    	echo "</body></html>";
    }

    public function testPriceAdjustersAction() {
    	$e1 = "-10%";
    	$e2 = "50%";
    	$e3 = "-10";
    	$e4 = "-3.50";
    	$e3 = "100.20";
    	
    	$u1 = 1;
    	$u2 = 2;
    	$u3 = 3;
    	
    	
    	$p = 120.4500;
    	
    	
    	echo "product price is=". $p . "<BR />";
    	echo "effect was e=$e1 now=". ($e11 = $this->_getHelp()->amplifyEffect($p, $e1, $u1)) . " after $u1 uses.<BR />";
    	echo "Using $e11 the effect on $p is ". $this->_getHelp()->priceAdjuster($p, $e11) ."<BR/><BR/>";
    	echo "effect was e=$e1 now=". ($e12 = $this->_getHelp()->amplifyEffect($p, $e1, $u2)) . " after $u2 uses.<BR />";
    	echo "Using $e12 the effect on $p is ". $this->_getHelp()->priceAdjuster($p, $e12) ."<BR/><BR/>";
    	echo "effect was e=$e1 now=". ($e13 = $this->_getHelp()->amplifyEffect($p, $e1, $u3)) . " after $u3 uses.<BR />";
    	echo "Using $e13 the effect on $p is ". $this->_getHelp()->priceAdjuster($p, $e13) ."<BR/><BR/>";
    	echo "effect was e=$e2 now=". ($e21 = $this->_getHelp()->amplifyEffect($p, $e2, $u1)) . " after $u1 uses.<BR />";
    	echo "Using $e21 the effect on $p is ". $this->_getHelp()->priceAdjuster($p, $e21) ."<BR/><BR/>";
    	echo "effect was e=$e2 now=". ($e22 = $this->_getHelp()->amplifyEffect($p, $e2, $u2)) . " after $u2 uses.<BR />";
    	echo "Using $e22 the effect on $p is ". $this->_getHelp()->priceAdjuster($p, $e22) ."<BR/><BR/>";
    	echo "effect was e=$e2 now=". ($e23 = $this->_getHelp()->amplifyEffect($p, $e2, $u3)) . " after $u3 uses.<BR />";
    	echo "Using $e23 the effect on $p is ". $this->_getHelp()->priceAdjuster($p, $e23) ."<BR/><BR/>";
    	echo "effect was e=$e3 now=". ($e31 = $this->_getHelp()->amplifyEffect($p, $e3, $u1)) . " after $u1 uses.<BR />";
    	echo "Using $e31 the effect on $p is ". $this->_getHelp()->priceAdjuster($p, $e31) ."<BR/><BR/>";
    	echo "effect was e=$e3 now=". ($e32 = $this->_getHelp()->amplifyEffect($p, $e3, $u2)) . " after $u2 uses.<BR />";
    	echo "Using $e32 the effect on $p is ". $this->_getHelp()->priceAdjuster($p, $e32) ."<BR/><BR/>";
    	echo "effect was e=$e3 now=". ($e33 = $this->_getHelp()->amplifyEffect($p, $e3, $u3)) . " after $u3 uses.<BR />";
    	echo "Using $e33 the effect on $p is ". $this->_getHelp()->priceAdjuster($p, $e33) ."<BR/><BR/>";
    	echo "effect was e=$e4 now=". ($e41 = $this->_getHelp()->amplifyEffect($p, $e4, $u1)) . " after $u1 uses.<BR />";
    	echo "Using $e41 the effect on $p is ". $this->_getHelp()->priceAdjuster($p, $e41) ."<BR/><BR/>";
    	echo "effect was e=$e4 now=". ($e42 = $this->_getHelp()->amplifyEffect($p, $e4, $u2)) . " after $u2 uses.<BR />";
    	echo "Using $e42 the effect on $p is ". $this->_getHelp()->priceAdjuster($p, $e42) ."<BR/><BR/>";
    	echo "effect was e=$e4 now=". ($e43 = $this->_getHelp()->amplifyEffect($p, $e4, $u3)) . " after $u3 uses.<BR />";
    	echo "Using $e43 the effect on $p is ". $this->_getHelp()->priceAdjuster($p, $e43) ."<BR/><BR/>";
    	
    	
    }
    
    public function resetRedemptionsAction() {
    	echo "<html><body>";
    	$items = $this->_getSess()->getCheckoutSession()->getQuote()->getAllItems();
    	foreach ($items as &$item) {
    		$item->setRedeemedPointsHash('')->save();
    	}
    	$this->printRedemptions();
    	echo "</body></html>";
    }
    
    protected function printRedemptions() {
    	$items = $this->_getSess()->getCheckoutSession()->getQuote()->getAllItems();
    	foreach ($items as $item) {
    		$name = $item->getName();
    		$qty = $item->getQty();
    		$redemptions = $this->_getHelp()->unhashIt($item->getRedeemedPointsHash());
    		echo "$name \t| qty=$qty \t| ";
    		print_r($redemptions);
    		echo "<BR /> \n";
    		flush();
    	}
    }
    
    protected function testJayPayment($points_to_spend) {
    	$customer = $this->_getJay();
    	$points_remain = $customer->predictPointsRemaining($points_to_spend);
    	
    	echo "<BR/>Jay Will Be Spending: ". Mage::helper('rewards/currency')->getFormattedCurrencyString($points_to_spend);
    	echo "<BR/>Jay Will Have left: ". Mage::helper('rewards/currency')->getFormattedCurrencyString($points_remain);
    	echo "<BR/>Can he afford the amount specified? ". ($this->_getJay()->canAfford($points_to_spend) ? "yes" : "no");
    	echo "<BR/>------ <BR/>\n";
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
