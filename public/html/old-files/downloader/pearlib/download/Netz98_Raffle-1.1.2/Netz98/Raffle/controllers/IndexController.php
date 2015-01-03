<?php
/**
 * @category   Netz98
 * @package    Netz98_Raffle
 * @author     Daniel Nitz <d.nitz@netz98.de>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Netz98_Raffle_IndexController extends Mage_Core_Controller_Front_Action
{
	public function indexAction()
	{
		$this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Enter our great Raffle!'));
        $this->renderLayout();
	}
}