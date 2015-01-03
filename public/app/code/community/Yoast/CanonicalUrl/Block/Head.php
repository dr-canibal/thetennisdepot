<?php
/**
 * Yoast
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 *
 * @category   Yoast
 * @package    Yoast_CanonicalUrl
 * @copyright  Copyright (c) 2009 Yoast (http://yoast.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Yoast_CanonicalUrl_Block_Head extends Fooman_Speedster_Block_Page_Html_Head
{

	public function getHeadUrl()
	{
		if (empty($this->_data['urlKey'])) {
			$host = parse_url(Mage::helper('core/url')->getCurrentUrl(),PHP_URL_HOST);
			$path = parse_url(Mage::helper('core/url')->getCurrentUrl(),PHP_URL_PATH);
			$headUrl = "http://$host$path";
			
			if (Mage::getStoreConfig('web/seo/trailingslash')) {

				if (!preg_match('/\\.(rss|html|htm|xml|php?)$/', strtolower($headUrl)) && substr($headUrl, -1) != '/') {
				$headUrl .= '/';
				}
			}
        return $headUrl;
   
            $this->_data['urlKey'] =$headUrl;
        }
		
		return $this->_data['urlKey'];
	}

	public function getHeadProductUrl()
        {           
			$storeId = $this->getStoreId();
	        $baseUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
		    $collection = Mage::getResourceModel('sitemap/catalog_product')->getCollection($storeId);
			$product_id = $this->getRequest()->getParam('id');
			$headUrl = "";

			if (empty($this->_data['urlKey']))
				{
					foreach ($collection as $item)
						{
							if ($item->getId() == $product_id)
								{
					                $headUrl = $baseUrl . $item->getUrl();
									if (Mage::getStoreConfig('web/seo/trailingslash')) 
										{
										if (!preg_match('/\\.(rss|html|htm|xml|php?)$/', strtolower($headUrl)) && substr($headUrl, -1) != '/') 
											{	$headUrl .= '/';	}
										}
									
									
									break;
								}
							
						}
						
				}
				
            $this->_data['urlKey'] =$headUrl; 
			   
			return $this->_data['urlKey'];
        } 
}