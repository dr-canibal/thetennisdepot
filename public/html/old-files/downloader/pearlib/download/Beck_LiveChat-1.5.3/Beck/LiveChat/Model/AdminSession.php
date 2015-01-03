<?php

class Beck_LiveChat_Model_AdminSession extends Mage_Core_Model_Session_Abstract
{
	public function __construct()
    {
        $this->init('livechat');
    }
}