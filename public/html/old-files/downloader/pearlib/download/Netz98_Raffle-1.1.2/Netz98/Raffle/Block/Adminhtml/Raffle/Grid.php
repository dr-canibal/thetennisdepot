<?php
/**
 * @category   Netz98
 * @package    Netz98_Raffle
 * @author     Daniel Nitz <d.nitz@netz98.de>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Netz98_Raffle_Block_Adminhtml_Raffle_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('raffleGrid');
        $this->setDefaultSort('participant_id', 'desc');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('raffle/participants')->getCollection()
        	->showNewsletterSubscriberInfo()
        	->addParticipantTypeField();
        $this->setCollection($collection);
        
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('participant_id', array(
        	'header'    => Mage::helper('raffle')->__('ID'),
        	'align'     =>'right',
        	'width'     => '50px',
        	'index'     => 'participant_id',
        ));

        $this->addColumn('firstname', array(
			'header'    => Mage::helper('raffle')->__('Firstname'),
			'align'     =>'left',
			'index'     => 'firstname',
        ));
      
        $this->addColumn('lastname', array(
			'header'    => Mage::helper('raffle')->__('Lastname'),
			'align'     =>'left',
			'index'     => 'lastname',
        ));
      
        $this->addColumn('email_address', array(
			'header'    => Mage::helper('raffle')->__('E-Mail Address'),
            'align'     =>'left',
          	'index'     => 'email_address',
        ));
        
        $this->addColumn('newsletter_status', array(
            'header'    => Mage::helper('raffle')->__('Newsletter Status'),
            'index'     => 'subscriber_status',
            'type'      => 'options',
            'options'   => array(
                Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE   => Mage::helper('newsletter')->__('Not activated'),
                Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED   => Mage::helper('newsletter')->__('Subscribed'),
                Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED => Mage::helper('newsletter')->__('Unsubscribed'),
            )
        ));
        
        $this->addColumn('type', array(
            'header'    => Mage::helper('raffle')->__('Type'),
            'index'     => 'type',
            'type'      => 'options',
            'options'   => array(
                1  => Mage::helper('newsletter')->__('Guest'),
                2  => Mage::helper('newsletter')->__('Customer')
            )
        ));
      
        $this->addColumn('campaign', array(
        	'header'    => Mage::helper('raffle')->__('Campaign'),
        	'align'     =>'left',
          	'index'     => 'campaign',
        ));
        
        $this->addExportType('*/*/exportCsv', Mage::helper('raffle')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('raffle')->__('XML'));
        
        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('participant_id');
        $this->getMassactionBlock()->setFormFieldName('raffle');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('raffle')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('raffle')->__('Are you sure?')
        ));

        return $this;
    }
}