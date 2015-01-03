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
 * Manage Customer Points Grid
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Manage_Customer_Points_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $columnsAreSet = false;

    public function __construct()
    {
        parent::__construct();
        $this->setId('customerGrid');
        //$this->setUseAjax(true);
        $this->setDefaultSort('name');
		$this->setDefaultDir('ASC');
    }

    protected function _prepareCollection()
    {
  	    if($this->_collection == null) {
		  $collection = Mage::getResourceModel('customer/customer_collection');
		}
        $collection->addNameToSelect()
            ->addAttributeToSelect('email')
            ->addAttributeToSelect('created_at')
            ->addAttributeToSelect('group_id')
            ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
            ->joinAttribute('billing_region', 'customer_address/region', 'default_billing', null, 'left')
            ->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left');

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }
	protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }
    

    protected function _prepareColumns()
    {
	   if($this->columnsAreSet) return parent::_prepareColumns();
	   else                      $this->columnsAreSet = true;
		
        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('customer')->__('ID'),
            'width'     => '50px',
            'index'     => 'entity_id',
            'type'  	=> 'number',
        ));
        /*$this->addColumn('firstname', array(
            'header'    => Mage::helper('customer')->__('First Name'),
            'index'     => 'firstname'
        ));
        $this->addColumn('lastname', array(
            'header'    => Mage::helper('customer')->__('Last Name'),
            'index'     => 'lastname'
        ));*/
        $this->addColumn('name', array(
            'header'    => Mage::helper('customer')->__('Name'),
            'index'     => 'name'
        ));
        $this->addColumn('email', array(
            'header'    => Mage::helper('customer')->__('Email'),
            'width'     => '150',
            'index'     => 'email'
        ));

        $groups = Mage::getResourceModel('customer/group_collection')
            ->addFieldToFilter('customer_group_id', array('gt'=> 0))
            ->load()
            ->toOptionHash();

        $this->addColumn('group', array(
            'header'    =>  Mage::helper('customer')->__('Group'),
            'width'     =>  '100',
            'index'     =>  'group_id',
            'type'      =>  'options',
            'options'   =>  $groups,
        ));

        $this->addColumn('billing_country_id', array(
            'header'    => Mage::helper('customer')->__('Country'),
            'width'     => '100',
            'type'      => 'country',
            'index'     => 'billing_country_id',
        ));

        $this->addColumn('billing_region', array(
            'header'    => Mage::helper('customer')->__('State/Province'),
            'width'     => '100',
            'index'     => 'billing_region',
        ));

        $this->addColumn('customer_since', array(
            'header'    => Mage::helper('customer')->__('Customer Since'),
            'type'      => 'datetime',
            'align'     => 'center',
            'index'     => 'created_at',
            'gmtoffset' => true
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $websites = Mage::getSingleton('adminhtml/system_store')->getWebsiteValuesForGridFilter(true, true);

            $this->addColumn('website_id', array(
                'header'    => Mage::helper('customer')->__('Website'),
                'align'     => 'center',
                'width'     => '80px',
                'type'      => 'options',
                'options'   => $websites,
                'index'     => 'website_id',
            ));
        }

        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('customer')->__('Action'),
                'width'     => '120px',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('rewards')->__('Make Transfer'),
                        'url'       => array('base'=> '*/manage_transfer/new/controller/manage_customer_points'),
                        'field'     => 'customer_id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
        
        $this->addColumn('points', array(
            'header'    =>Mage::helper('rewards')->__('Points'),
            'width'     =>'220px',
            'renderer' => 'rewards/manage_grid_renderer_points',
        	'sortable' => false,
        	'filter'	=> false,
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('customer')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('customer')->__('XML'));
        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('customer');

//        $this->getMassactionBlock()->addItem('delete', array(
//             'label'    => Mage::helper('customer')->__('Delete'),
//             'url'      => $this->getUrl('*/*/massDelete'),
//             'confirm'  => Mage::helper('customer')->__('Are you sure?')
//        ));
//
//        $this->getMassactionBlock()->addItem('newsletter_subscribe', array(
//             'label'    => Mage::helper('customer')->__('Subscribe to newsletter'),
//             'url'      => $this->getUrl('*/*/massSubscribe')
//        ));
//
//        $this->getMassactionBlock()->addItem('newsletter_unsubscribe', array(
//             'label'    => Mage::helper('customer')->__('Unsubscribe from newsletter'),
//             'url'      => $this->getUrl('*/*/massUnsubscribe')
//        ));
//
		$currencies = Mage::helper('rewards/currency')->getAvailCurrencyOptions();
        array_unshift($currencies, array('label'=> '', 'value'=> ''));
        $this->getMassactionBlock()->addItem('mass_distribution', array(
             'label'        => Mage::helper('rewards')->__('Give Points'),
             'url'          => $this->getUrl('*/*/massTransferPoints', array('is_deduction' => 0)),
             'additional'   => array(
                'currency'    => array(
                     'name'     => 'currency',
                     'type'     => 'select',
                     'class'    => 'required-entry',
                     'label'    => Mage::helper('customer')->__('Currency'),
                     'values'   => $currencies
                 ),
                'points'    => array(
                     'name'     => 'quantity',
                     'type'     => 'text',
                     'class'    => 'required-entry',
                     'label'    => Mage::helper('customer')->__('Quantity'),
                 ),
            ),
            'confirm'  => Mage::helper('customer')->__('Are you sure you want to create the transfer for all selected customers?')
        ));
        $this->getMassactionBlock()->addItem('mass_redemption', array(
             'label'        => Mage::helper('rewards')->__('Deduct Points'),
             'url'          => $this->getUrl('*/*/massTransferPoints', array('is_deduction' => 1)),
             'additional'   => array(
                'currency'    => array(
                     'name'     => 'currency',
                     'type'     => 'select',
                     'class'    => 'required-entry',
                     'label'    => Mage::helper('customer')->__('Currency'),
                     'values'   => $currencies
                 ),
                'points'    => array(
                     'name'     => 'quantity',
                     'type'     => 'text',
                     'class'    => 'required-entry',
                     'label'    => Mage::helper('customer')->__('Quantity'),
                 ),
            ),
            'confirm'  => Mage::helper('customer')->__('Are you sure you want to create the transfer for all selected customers?')
        ));

        return $this;
    }


}
