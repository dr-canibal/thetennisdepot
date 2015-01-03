<?php
/**
 * @category   Netz98
 * @package    Netz98_Raffle
 * @author     Daniel Nitz <d.nitz@netz98.de>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Netz98_Raffle_Model_Mysql4_Participants_Collection extends Varien_Data_Collection_Db
{
	protected $_subscriberTable;
	
    public function __construct()
    {
        parent::__construct(Mage::getSingleton('core/resource')->getConnection('raffle_read'));
        $this->_participantsTable = Mage::getSingleton('core/resource')->getTableName('raffle/participants');
        $this->_subscriberTable = Mage::getSingleton('core/resource')->getTableName('newsletter/subscriber');
        $this->_select->from(array('main_table' => $this->_participantsTable));
        $this->setItemObjectClass(Mage::getConfig()->getModelClassName('raffle/participants'));
    }
    
    public function showNewsletterSubscriberInfo()
    {
        $this->getSelect()
             ->joinLeft(
            	$this->_subscriberTable,
            	'main_table.newsletter_subscriber_id = subscriber_id',
            	'*'
             );

    	return $this;
    }
    
    public function addParticipantTypeField()
    {
    	$this->getSelect()
    		 ->from(null, array('type' => new Zend_Db_Expr('IF('
    		 	.$this->_subscriberTable
    		 	.'.customer_id = 0, 1, 2)'))
    		 );
    	
    	return $this;
    }
    
    public function getAllIds()
    {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(Zend_Db_Select::ORDER);
        $idsSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $idsSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $idsSelect->reset(Zend_Db_Select::COLUMNS);
        $idsSelect->from(null,
            $this->_subscriberTable.'.subscriber_id'
        );
        
        return $this->getConnection()->fetchCol($idsSelect);
    }
    
	public function addFieldToFilter($field, $condition=null)
    {
        if(!is_null($condition)) {
            $this->_select->where($this->_getConditionSql($this->_getFieldTableAlias($field), $condition));
            $this->_countFilterPart[] = $this->_getConditionSql($this->_getFieldTableAlias($field), $condition);
        }
        
        return $this;
    }

    public function _getFieldTableAlias($field)
    {
        if($field=='type') {
            return new Zend_Db_Expr('IF('.$this->_subscriberTable.'.customer_id = 0, 1, 2)');
        }

        return 'main_table.' . $field;
    }
}