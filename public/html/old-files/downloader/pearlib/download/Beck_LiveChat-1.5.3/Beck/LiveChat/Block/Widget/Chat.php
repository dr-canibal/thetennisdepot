<?php

class Beck_LiveChat_Block_Widget_Chat extends Mage_Adminhtml_Block_Widget
{
	protected $_session = null;
	
	public function __construct()
    {
		parent::__construct();
        $this->setTemplate('livechat'.DS.'widget'.DS.'chatlive.phtml');
    }
	
	public function SetSession(Beck_LiveChat_Model_Session $session)
	{
		$this->_session = $session;
	}
}