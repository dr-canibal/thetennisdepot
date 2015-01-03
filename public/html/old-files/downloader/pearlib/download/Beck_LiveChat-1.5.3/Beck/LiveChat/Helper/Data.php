<?php

class Beck_LiveChat_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function isSessionExpired(Beck_LiveChat_Model_Session $session)
	{
		//file_put_contents('log_livechat.txt', "function isSessionExpired()\n", FILE_APPEND);
		$result = true;		
		$visitors = Mage::getModel('log/visitor')
					->getCollection()
					->useOnlineFilter();
		
		if ($session->getId() > 0)
		{
			//file_put_contents('log_livechat.txt', "session \t\t\t".$session->getCustomer_session_id()."\n", FILE_APPEND);
			foreach ($visitors as $visitor)
			{
				//file_put_contents('log_livechat.txt', "visitor session \t".$visitor->getSession_id()."\n", FILE_APPEND);
				if ($visitor->getSession_id() == $session->getCustomer_session_id())
				{
					$result = false;
				}
			}
		}
		return ($result);
	}
}