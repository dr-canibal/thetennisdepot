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
 * Points model for handling point quantities
 * TODO: implement array iterator interface
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Points extends Varien_Object
{  
	
	protected $points = array();
	/**
     * Adds points to be displayed
     * 
     * @param integer||array|| any type of rewards or Mage rule $param1 
     * 			: - if a rule is passed in, the points effect of the rule is added
     * 			: - If an integer is passed in the value is assumed to be the currency id and the second
     * 				parameter is assumed to be the points amount
     * 			: - if an array is passed in the value is assumed to be in the format 
     * 				array( $currency_id => $points_amount )
     * @param integer|null $param2=null	:	if not null the function will assume the 
     * 										format function($currency_id, $points_amount)
     * @return TBT_Rewards_Block_Points
     * 
     * TODO: Should we ignore attempts to add "0" point balances?          
     */
    public function add($param1, $param2 = null) {
    	if($param2 != null) { // params are function($currency_id, $points_amount)
    		$currency_id = intval($param1);
    		$points_amount = intval($param2);
    		if(!isset($this->points[$currency_id])) {
    			$this->points[$currency_id] = 0;
    		}
	    	$this->points[$currency_id] += $points_amount;
    	} elseif(is_array($param1)) { // parameters are function( array($currency_id=>$points_amount, ...) )
    		$points = &$param1;
	    	foreach($points as $currency_id => $points_amount) {
	    		$this->add($currency_id, $points_amount);
	    	}
    	} elseif($param1 instanceof TBT_Rewards_Model_Catalogrule_Rule
    			|| $param1 instanceof TBT_Rewards_Model_Salesrule_Rule
    			|| $param1 instanceof Mage_CatalogRule_Model_Rule
    			|| $param1 instanceof Mage_SalesRule_Model_Rule 
                || $param1 instanceof Varien_Object) { // params are function($rule)
    		$rule = &$param1;
    		if($rule->getPointsCurrencyId()) {
    			$this->add($rule->getPointsCurrencyId(), $rule->getPointsAmount());
    		}
    	} else {
			// Do nothing since the parameters entered were incorrect...    		
    	}
    	return $this;
    }
    
   	/**
     * Sets the points for this points model.  Any previous points put into this model will be cleared.
     * 
     * @param integer||array|| any type of rewards or Mage rule $param1 
     * 			: - if a rule is passed in, the points effect of the rule is added
     * 			: - If an integer is passed in the value is assumed to be the currency id and the second
     * 				parameter is assumed to be the points amount
     * 			: - if an array is passed in the value is assumed to be in the format 
     * 				array( $currency_id => $points_amount )
     * @param integer|null $param2=null	:	if not null the function will assume the 
     * 										format function($currency_id, $points_amount)
     * @return TBT_Rewards_Block_Points
     * 
     * TODO: Should we ignore attempts to add "0" point balances?          
     */
    public function setPoints($param1, $param2 = null)  {
    	$this->clear();
    	return $this->add($param1, $param2);
    }
    
   	/**
     * Sets the points for this points model.  Any previous points put into this model will be cleared.
     *  @alias for setPoints()
     * @param integer||array|| any type of rewards or Mage rule $param1 
     * 			: - if a rule is passed in, the points effect of the rule is added
     * 			: - If an integer is passed in the value is assumed to be the currency id and the second
     * 				parameter is assumed to be the points amount
     * 			: - if an array is passed in the value is assumed to be in the format 
     * 				array( $currency_id => $points_amount )
     * @param integer|null $param2=null	:	if not null the function will assume the 
     * 										format function($currency_id, $points_amount)
     * @return TBT_Rewards_Block_Points
     * 
     * TODO: Should we ignore attempts to add "0" point balances?          
     */
    public function set($param1, $param2 = null)  {
    	return $this->setPoints($param1, $param2);
    }
    
    /**
     * Clears out all points information stored by this model
     *
     */
    public function clear() {
    	$this->points = array();
    }
    
    
    /**
     * Fetches the raw points data for this points model
     *
     * @return array : in the format array($currency_id=>$points_amount, ...)
     */
    public function getPoints() {
    	return $this->points;
    }

	/**
	 * True if the block contains points to be displayed, otherwise false
	 *
	 * @return boolean
	 */
    public function hasPoints() {
    	return sizeof($this->points) > 0;    
    }
    
    /**
     * Alias for hasPoints();
     *
     * @return boolean
     */
    public function isEmpty() {
    	return !$this->hasPoints();
    }

    /**
     * Instantiates and returns a rendering block of this points model
     *
     * @return TBT_Rewards_Block_Points
     */
    public function getRendering() {
    	$block = Mage::getBlockSingleton('rewards/points');
    	$block->setDataModel($this);
    	return $block;
    }
    
    public function __toString(array $arrAttributes = array(), $valueSeparator=',') {
    	return $this->getRendering()->setDataFromModel($this->getData())->__toString();
    }
    
    public function getSimpleAmount() {
        if($this->isEmpty()) return 0;
        return array_pop($this->points);
    }
}