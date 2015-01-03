<?php

class Beck_LiveChat_Block_Widget_Sessions extends Mage_Adminhtml_Block_Widget
{	
	protected $urlUpdater = '';
	protected $urlSendMessage = '';
	protected $urlOperatorCloseSession = '';

	public function __construct()
    {
		parent::__construct();
        $this->setTemplate('livechat'.DS.'widget'.DS.'sessionslive.phtml');
		$this->urlUpdater = $this->getUrl('*/*/updater');
		$this->urlSendMessage = $this->getUrl('*/*/sendMessage');
		$this->urlOperatorCloseSession = $this->getUrl('*/*/closeSession');
    }
	
	protected function _toHtml()
	{
	    $html = parent::_toHtml();
		$session = Mage::getSingleton('livechat/adminSession');
		if ($session->getData('OperatorName') != null)
		{
			foreach ($this->getSortedChildren() as $name)
			{
				$block = $this->getLayout()->getBlock($name);
				if (!$block)
				{
					Mage::throwException(Mage::helper('core')->__('Invalid block: %s', $name));
				}
				$html .= $block->toHtml();
			}
		}
	    return $html;
	}
	
	
	public function AddChatSession(Beck_LiveChat_Model_Session $session)
	{
		//$block = $this->getLayout()->createBlock('livechat/widget_chat', 'chat_'.$session->getId());
		///Zend_Debug::dump('AddChatSession '. $session->getId());
		$block = $this->getLayout()->createBlock('livechat/widget_chat', 'chat_'.$session->getId());
		$block->SetSession($session);
		$this->append($block);
	}

}