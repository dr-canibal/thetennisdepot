<?php
/**
 * @category   Netz98
 * @package    Netz98_Raffle
 * @author     Daniel Nitz <d.nitz@netz98.de>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Netz98_Raffle_Model_Mysql4_Participants extends Mage_Core_Model_Mysql4_Abstract
{
	/**
     * DB read connection
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_read;
	
    public function _construct()
    {    
    	$this->_read = Mage::getSingleton('core/resource')->getConnection('core_read');    	
    	$this->_participantsTable = Mage::getSingleton('core/resource')->getTableName('raffle/participants');
    	
        $this->_init('raffle/participants', 'participant_id');
    }
    
	/**
     * Load participant from DB by email
     *
     * @param string $participantEmail
     * @return array
     */
    public function loadByEmail($participantEmail)
    {
        $select = $this->_read->select()
            ->from($this->_participantsTable)
            ->where('email_address = ?', $participantEmail);

        $result = $this->_read->fetchRow($select);
        if (!$result) {
            return array();
        }

        return $result;
    }
}