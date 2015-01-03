<?php 
class Beck_LiveChat_Block_Session extends Mage_Adminhtml_Block_Template
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('livechat/session.phtml');
    }

    protected function _prepareLayout()
    {
		$type = $this->getRequest()->getParam('type', 'standard');
		if ($type == 'standard')
		{
			$this->setChild('add_new_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('livechat')->__('View archived sessions'),
                    'onclick'   => "setLocation('".$this->getUrl('livechat/session_list/index/type/archive')."')",
                    'class'   => 'scalable back'
                    ))
                );
		}
		elseif ($type == 'archive')
		{
			$this->setChild('add_new_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('livechat')->__('View live sessions'),
                    'onclick'   => "setLocation('".$this->getUrl('livechat/session_list/index/')."')",
                    'class'   => 'scalable back'
                    ))
                );
		}
        

        $this->setChild('grid', $this->getLayout()->createBlock('livechat/session_grid', 'session.grid'));
        return parent::_prepareLayout();
    }

    public function getAddNewButtonHtml()
    {
        return $this->getChildHtml('add_new_button');
    }

    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }

}