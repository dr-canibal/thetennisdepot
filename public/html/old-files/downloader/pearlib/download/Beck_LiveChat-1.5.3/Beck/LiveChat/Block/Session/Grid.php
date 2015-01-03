<?php

class Beck_LiveChat_Block_Session_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
    {
        parent::__construct();
        $this->setId('id');
        $this->setDefaultSort('date_started');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('session_filter');

    }
	
	protected function _prepareCollection()
    {
		$type = $this->getRequest()->getParam('type', 'standard');
		if ($type == 'standard')
		{
			$collection = Mage::getModel('livechat/session')->getCollection();
			$this->setCollection($collection);
		}
		elseif ($type == 'archive')
		{
			$collection = Mage::getModel('livechat/archives_session')->getCollection();
			$this->setCollection($collection);
		}
		
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('session_id',
            array(
                'header'=> Mage::helper('catalog')->__('ID'),
                'width' => '50px',
                'type'  => 'number',
                'index' => 'id',
        ));
        $this->addColumn('customername',
            array(
                'header'=> Mage::helper('livechat')->__('Customer name'),
                'index' => 'customer_name',
        ));
		 $this->addColumn('customerurl',
            array(
                'header'=> Mage::helper('livechat')->__('Customer url'),
                'index' => 'customer_url',
        ));
		$this->addColumn('date_started',
            array(
                'header'=> Mage::helper('livechat')->__('Started at'),
                'type'  => 'datetime',
                'index' => 'date_started'
        ));
		$this->addColumn('dispatched',
            array(
                'header'=> Mage::helper('livechat')->__('Dispatched to'),
                'index' => 'dispatched',
				'type' => 'dispatched',
				'renderer'=>'livechat/widget_grid_column_renderer_dispatched',
				'filter'=>'livechat/widget_grid_column_filter_dispatched',
				'options'=>Mage::getModel('livechat/session_list')->getOperatorFilterListToArray()
        ));
		$this->addColumn('store',
            array(
                'header'=> Mage::helper('livechat')->__('Store'),
				'type' =>'store',
                'index' => 'store_id',
        ));
		$this->addColumn('state',
            array(
                'header'=> Mage::helper('livechat')->__('State'),
				'type'=>'state',
				'renderer'=>'livechat/widget_grid_column_renderer_state',
				'filter'=>'livechat/widget_grid_column_filter_state',
                'index' => 'close',
				'options'=>Mage::getModel('livechat/session_list')->getStateFilterListToArray()
        ));
		$this->addColumn('order',
            array(
                'header'=> Mage::helper('livechat')->__('Order placed'),
				'type'=>'text',
				//'renderer'=>'livechat/widget_grid_column_renderer_orderplaced',
				//'filter'=>'livechat/widget_grid_column_filter_orderplaced',
                'index' => 'order_placed'
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
		$this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('session');
		$type = $this->getRequest()->getParam('type', 'standard');
		if ($type == 'standard')
		{
			$this->getMassactionBlock()->addItem('open', array(
             'label'=> Mage::helper('livechat')->__('Open'),
             'url'  => $this->getUrl('*/*/massOpen'),
             'confirm' => Mage::helper('livechat')->__('Are you sure ?')
			));
	        $this->getMassactionBlock()->addItem('close', array(
	             'label'=> Mage::helper('livechat')->__('Close'),
	             'url'  => $this->getUrl('*/*/massClose'),
	             'confirm' => Mage::helper('livechat')->__('Are you sure ?')
	        ));
			$this->getMassactionBlock()->addItem('archive', array(
	             'label'=> Mage::helper('livechat')->__('Archive'),
	             'url'  => $this->getUrl('*/*/massArchivate'),
	             'confirm' => Mage::helper('livechat')->__('Are you sure ?')
	        ));
		}
		elseif ($type == 'archive')
		{
			$this->getMassactionBlock()->addItem('delete', array(
             'label'=> Mage::helper('livechat')->__('Delete'),
             'url'  => $this->getUrl('*/*/massDelete'),
             'confirm' => Mage::helper('livechat')->__('Are you sure ?')
			));
		}
        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
	
	public function getRowUrl($row)
	{
		$type = $this->getRequest()->getParam('type', 'standard');
		return $this->getUrl('*/*/detail', array('sessionId' => $row->getId(), 'type' => $type));
	}
}