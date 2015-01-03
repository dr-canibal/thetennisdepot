<?php
/**
 * @category   Netz98
 * @package    Netz98_Raffle
 * @author     Daniel Nitz <d.nitz@netz98.de>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Netz98_Raffle_Block_Form extends Mage_Core_Block_Template
{
	protected $_campagin = 'none';
	protected $_redirect = '/';
	
	public function setCampaign($name)
	{
		$this->_campaign = $name;
	}
	
	public function getCampaign()
	{
		return $this->_campaign;
	}
	
	public function setRedirect($url)
	{
		$this->_redirect = $url;
	}
	
	public function getRedirect()
	{
		return $this->_redirect;
	}
	
    public function getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }
}