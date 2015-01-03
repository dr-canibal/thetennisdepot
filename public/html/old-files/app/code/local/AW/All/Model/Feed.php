<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/LICENSE-M1.txt
 *
 * @category   AW
 * @package    AW_All
 * @copyright  Copyright (c) 2003-2009 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/LICENSE-M1.txt
 */ 

class AW_All_Model_Feed extends Mage_AdminNotification_Model_Feed
{
    const XML_USE_HTTPS_PATH    = 'awall/feed/use_https';
    const XML_FEED_URL_PATH     = 'awall/feed/url';
    const XML_FREQUENCY_PATH    = 'awall/feed/frequency';
	const XML_FREQUENCY_ENABLE    = 'awall/feed/enable';
    const XML_LAST_UPDATE_PATH  = 'awall/feed/last_update';

	
	public static function check(){
		if(!Mage::getStoreConfig(self::XML_FREQUENCY_ENABLE)){		
			return;
		}
		return Mage::getModel('awall/feed')->checkUpdate();
	}
	
    public function getFrequency()
    {
        return Mage::getStoreConfig(self::XML_FREQUENCY_PATH) * 3600;
    }

    public function getLastUpdate()
    {
        return Mage::app()->loadCache('awall_notifications_lastcheck');

    }

    public function setLastUpdate()
    {
        Mage::app()->saveCache(time(), 'awall_notifications_lastcheck');
        return $this;
    }

}
