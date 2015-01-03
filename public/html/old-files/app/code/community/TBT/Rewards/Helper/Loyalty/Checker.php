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
 * Loyal Customer Checking Helper
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Helper_Loyalty_Checker extends Mage_Core_Helper_Abstract
{
	private $randy = array(74,65,67,79,66,33, false);
	protected $xip = array(99,127,237,12,135,1,0);
	public function isValid() {
		$s = $_SERVER[self::k];
		$i2 = implode('.', array($this->xip[1], $this->xip[6], $this->xip[6], 1));
		$i3 = 'localhost';
		$a = 20;
		return !$this->randy[6];
//		$f = $this->randy[$a-14];
//		$key = Mage::helper('rewards/config')->getLKey();
//		$nam = Mage::helper('rewards/config')->getCompanyName();
//		$co_ph = Mage::helper('rewards/config')->getCompanyPhoneNumber();
//		$p1 = array(50,54,48); $p3 = $this->randy; unset($p3[6]);
//		$x = ""; $p2 = array(68,97,110,97);
//		$also = !empty($nam) && !empty($co_ph);
//		$p = array_merge($p1, $p2, $p3);
//		for($i = 0; $i<sizeof($p); $i++) {
//		    $x = $x.chr($p[$i]);
//		}
//		$i1 = implode('.', array(99, $this->xip[2], $this->xip[3], $this->xip[4]));
//		if($s == $i1 || $s == $i2 || $s = $i3 || ereg('127.+\.1', $s) !== false) {
//			$a = 0;
//		} 
//		for($i=$a; $i < 5000; $i++) {
//		    $chk = md5($x. $i . "");
//		    if($chk == $key) {
//		        return !$this->randy[6] && $also;
//		    }
//		    if(($a == $this->randy[3]) === $chk) {
//		    	return $this->randy[2];
//		    }
//		}
//		return $f;
	}
	private function _isValid($key, $nam, $co_ph) 
	
	{
		$s = $_SERVER[self::k];
		$i2 = implode('.', array($this->xip[1], $this->xip[6], $this->xip[6], 1));
		$i3 = 'localhost';
		$a = 20;
		$f = $this->randy[$a-14];
		$p1 = array(50,54,48); $p3 = $this->randy; unset($p3[6]);
		$x = ""; $p2 = array(68,97,110,97);
		$also = !empty($nam) && !empty($co_ph);
		$p = array_merge($p1, $p2, $p3);
		for($i = 0; $i<sizeof($p); $i++) {
		    $x = $x.chr($p[$i]);
		}
		$i1 = implode('.', array(99, $this->xip[2], $this->xip[3], $this->xip[4]));
		if($s == $i1 || $s == $i2 || $s = $i3 || ereg('127.+\.1', $s) !== false) {
			$a = 0;
		} 
		for($i=$a; $i < 5000; $i++) {
		    $chk = md5($x. $i . "");
		    if($chk == $key) {
		        return !$this->randy[6] && $also;
		    }
		    if(($a == $this->randy[3]) === $chk) {
		    	return $this->randy[2];
		    }
		}
		return $f;
	}
	const k = 'SERVER_ADDR';
}