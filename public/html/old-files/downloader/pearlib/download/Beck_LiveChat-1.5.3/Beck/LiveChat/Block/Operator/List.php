<?php

class Beck_LiveChat_Block_Operator_List extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
    {
        parent::__construct();
        $this->setId('id');
        $this->setDefaultSort('id');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('operator_filter');

    }
	
	protected function _prepareCollection()
    {
		$collection = Mage::getModel('livechat/operator')->getCollection();
		$this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }
	
	protected function _prepareMassaction()
    {
		$this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('operators');

		$this->getMassactionBlock()->addItem('disconnect', array(
			 'label'=> Mage::helper('livechat')->__('Disconnect'),
			 'url'  => $this->getUrl('*/*/massDisconnect'),
			 'confirm' => Mage::helper('livechat')->__('Are you sure ?')
		));

        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('operator_id',
            array(
                'header'=> Mage::helper('catalog')->__('ID'),
                'width' => '50px',
                'type'  => 'number',
                'index' => 'id',
        ));
        $this->addColumn('operatorname',
            array(
                'header'=> Mage::helper('livechat')->__('Name'),
                'index' => 'name',
        ));
		$this->addColumn('is_online',
            array(
                'header'=> Mage::helper('livechat')->__('Status'),
                'type'  => 'text',
                'index' => 'is_online',
				'type' => 'select',
				'renderer'=>'livechat/widget_grid_column_renderer_online',
				'filter'=>'livechat/widget_grid_column_filter_online',
				'options'=>Beck_LiveChat_Model_Source_Online::toOptionsArray()
        ));
		
        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
	
	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/edit', array('id'=>$row->getId()));
	}
}