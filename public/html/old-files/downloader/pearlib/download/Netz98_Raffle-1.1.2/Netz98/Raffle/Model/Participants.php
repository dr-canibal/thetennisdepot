<?php
/**
 * @category   Netz98
 * @package    Netz98_Raffle
 * @author     Daniel Nitz <d.nitz@netz98.de>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Netz98_Raffle_Model_Participants extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('raffle/participants');
    }
    
	/**
     * Load data from resource model by email
     *
     * @param int $email
     */
    public function loadByEmail($email)
    {
        $this->addData($this->getResource()->loadByEmail($email));
        return $this;
    }
}