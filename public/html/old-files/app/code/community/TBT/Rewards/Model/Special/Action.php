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
 * Special Action
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Special_Action extends Varien_Object
{   
    // status values less than 1 means that transfer is ignored in
    // customer point calculations.
    const ACTION_WRITE_REVIEW = 'customer_writes_review';
    const ACTION_RATING    = 'customer_rating';
    const ACTION_SIGN_UP   = 'customer_sign_up';
    const ACTION_POLL  = 'customer_poll';
    const ACTION_SENDFRIEND  = 'customer_send_friend';
    const ACTION_NEWSLETTER  = 'customer_newsletter';
    const ACTION_TAG = 'customer_tag';
    
    
    public function _construct()
    {
        parent::_construct();
    }
 
    public function getActionArray()
    {
        return array(
            self::ACTION_WRITE_REVIEW  => Mage::helper('rewards')->__('Writes review'),
            self::ACTION_RATING        => Mage::helper('rewards')->__('Rates a product'), //Rating a product happens at the same time as making a review
            self::ACTION_SIGN_UP       => Mage::helper('rewards')->__('Signs up'),
            self::ACTION_POLL          => Mage::helper('rewards')->__('Votes in poll'),
            self::ACTION_SENDFRIEND    => Mage::helper('rewards')->__('Sends product to friend'),
            self::ACTION_NEWSLETTER    => Mage::helper('rewards')->__('Signs up for a newsletter'),
            self::ACTION_TAG           => Mage::helper('rewards')->__('Makes a new tag'),
            
        );
    } 
    public function getOptionsArray()
    {
        return array(
            ''                         => '',     //include the null option so the user can pick nothing
            self::ACTION_WRITE_REVIEW  => Mage::helper('rewards')->__('Writes review'),
            self::ACTION_RATING        => Mage::helper('rewards')->__('Rates a product'), //Rating a product happens at the same time as making a review
            self::ACTION_SIGN_UP       => Mage::helper('rewards')->__('Signs up'),
            self::ACTION_POLL          => Mage::helper('rewards')->__('Votes in poll'),
            self::ACTION_SENDFRIEND    => Mage::helper('rewards')->__('Sends product to friend'),
            self::ACTION_NEWSLETTER    => Mage::helper('rewards')->__('Signs up for a newsletter'),
            self::ACTION_TAG           => Mage::helper('rewards')->__('Makes a new tag'),
   
        );
    }
}

?>