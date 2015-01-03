<?php

class Beck_LiveChat_Block_Session_Detail_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	private $idSession = 0;

	public function __construct()
    {
        parent::__construct();
        $this->setId('id');
        $this->setDefaultSort('id');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('sessiondetail_filter');
		$this->sessionId = $this->getRequest()->getParam('sessionId', 0);
    }
	
	protected function _prepareCollection()
    {
		$type = $this->getRequest()->getParam('type', 'standard');
		if ($type == 'standard')
		{
			$session = Mage::getModel('livechat/session')->load($this->sessionId);
			$collection = $session->getMessages();
			$this->setCollection($collection);
		}
		elseif ($type == 'archive')
		{
			$session = Mage::getModel('livechat/archives_session')->load($this->sessionId);
			$collection = $session->getMessages();
			$this->setCollection($collection);
		}
		
		$this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('message_id',
            array(
                'header'=> Mage::helper('catalog')->__('ID'),
                'width' => '50px',
                'type'  => 'number',
                'index' => 'id',
        ));
		$this->addColumn('date',
            array(
                'header'=> Mage::helper('livechat')->__('Placed at'),
                'index' => 'date',
				'type'=> 'datetime'
        ));
        $this->addColumn('autorname',
            array(
                'header'=> Mage::helper('livechat')->__('Autor'),
                'index' => 'autor_name',
        ));	
		$this->addColumn('message',
            array(
                'header'=> Mage::helper('livechat')->__('Message'),
                'index' => 'message',
        ));
		
        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
		$this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('message');
		$type = $this->getRequest()->getParam('type', 'standard');
        $this->getMassactionBlock()->addItem('delete', array(
             'label'=> Mage::helper('livechat')->__('Delete'),
             'url'  => $this->getUrl('*/*/massMessageDelete', array('sessionId' => $this->sessionId, 'type' => $type)),
             'confirm' => Mage::helper('livechat')->__('Are you sure ?')
        ));
        
        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/detailGrid', array('_current'=>true));
    }
}