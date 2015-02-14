<?php
/**
 * WDCA - Sweet Tooth
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the WDCA SWEET TOOTH POINTS AND REWARDS 
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL: 
 *      http://www.wdca.ca/sweet_tooth/sweet_tooth_license.txt
 * The Open Software License is available at this URL: 
 *      http://opensource.org/licenses/osl-3.0.php
 * 
 * DISCLAIMER
 * 
 * By adding to, editing, or in any way modifying this code, WDCA is 
 * not held liable for any inconsistencies or abnormalities in the 
 * behaviour of this code. 
 * By adding to, editing, or in any way modifying this code, the Licensee
 * terminates any agreement of support offered by WDCA, outlined in the 
 * provided Sweet Tooth License. 
 * Upon discovery of modified code in the process of support, the Licensee 
 * is still held accountable for any and all billable time WDCA spent 
 * during the support process.
 * WDCA does not guarantee compatibility with any other framework extension. 
 * WDCA is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 * If you did not receive a copy of the license, please send an email to 
 * contact@wdca.ca or call 1-888-699-WDCA(9322), so we can send you a copy 
 * immediately.
 * 
 * @category   [TBT]
 * @package    [TBT_Rewards]
 * @copyright  Copyright (c) 2009 Web Development Canada (http://www.wdca.ca)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

/**
 * Sitemap
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
require_once 'Mage/Sitemap/Model/Sitemap.php';

class TBT_Rewards_Model_Sitemap extends Mage_Sitemap_Model_Sitemap
{
    public function generateXml()
    {
        $io = new Varien_Io_File();
        $io->setAllowCreateFolders(true);
        $io->open(array('path' => $this->getPath()));
        $io->streamOpen($this->getSitemapFilename());

        $io->streamWrite('<?xml version="1.0" encoding="UTF-8"?>' . "\n");
        $io->streamWrite('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');

        $storeId = $this->getStoreId();
        $date    = Mage::getSingleton('core/date')->gmtDate('Y-m-d');
        $baseUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);

        /**
         * Generate categories sitemap
         */
        $changefreq = (string)Mage::getStoreConfig('sitemap/category/changefreq');
        $priority   = (string)Mage::getStoreConfig('sitemap/category/priority');
        $collection = Mage::getResourceModel('sitemap/catalog_category')->getCollection($storeId);
        foreach ($collection as $item) {
            $xml = sprintf('<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($baseUrl . $item->getUrl()),
                $date,
                $changefreq,
                $priority
            );
            $io->streamWrite($xml);
        }
        unset($collection);

        /**
         * Generate products sitemap
         */
        $changefreq = (string)Mage::getStoreConfig('sitemap/product/changefreq');
        $priority   = (string)Mage::getStoreConfig('sitemap/product/priority');
        $collection = Mage::getResourceModel('sitemap/catalog_product')->getCollection($storeId);
        foreach ($collection as $item) {
            $xml = sprintf('<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($baseUrl . $item->getUrl()),
                $date,
                $changefreq,
                $priority
            );
            $io->streamWrite($xml);
        }
        unset($collection);

        /**
         * Generate cms pages sitemap
         */
        $changefreq = (string)Mage::getStoreConfig('sitemap/page/changefreq');
        $priority   = (string)Mage::getStoreConfig('sitemap/page/priority');
        $collection = Mage::getResourceModel('sitemap/cms_page')->getCollection($storeId);
        foreach ($collection as $item) {
            $xml = sprintf('<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($baseUrl . $item->getUrl()),
                $date,
                $changefreq,
                $priority
            );
            $io->streamWrite($xml);
        }
        unset($collection);

        /**
         * Generate blog pages sitemap
         */
        $changefreq = (string)Mage::getStoreConfig('sitemap/blog/changefreq');
        $priority   = (string)Mage::getStoreConfig('sitemap/blog/priority');
        $collection = Mage::getModel('blog/blog')->getCollection($storeId);
		Mage::getSingleton('blog/status')->addEnabledFilterToCollection($collection);
		$route = Mage::getStoreConfig('blog/blog/route');
		if ($route == "") {
		   $route = "blog";
		}
        foreach ($collection as $item) {
            $xml = sprintf('<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars($baseUrl . $route . '/' . $item->getIdentifier()),
                $date,
                $changefreq,
                $priority
            );
            $io->streamWrite($xml);
        }
        unset($collection);

        $io->streamWrite('</urlset>');
        $io->streamClose();

        $this->setSitemapTime(Mage::getSingleton('core/date')->gmtDate('Y-m-d H:i:s'));
        $this->save();

        return $this;
    }
}
