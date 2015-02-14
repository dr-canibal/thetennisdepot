<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @package     Fooman_Jirafe
 * @copyright   Copyright (c) 2010 Jirafe Inc (http://www.jirafe.com)
 * @copyright   Copyright (c) 2010 Fooman Limited (http://www.fooman.co.nz)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Fooman_Jirafe_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_FOOMANJIRAFE_SETTINGS = 'foomanjirafe/settings/';
    const JIRAFE_STATUS_NOT_INSTALLED = '0';
    const JIRAFE_STATUS_ERROR = '1';
    const JIRAFE_STATUS_APP_TOKEN_RECEIVED = '2';
    const JIRAFE_STATUS_SYNC_COMPLETED = '3';

    /**
     * Return store config value for key
     *
     * @param   string $key
     * @return  string
     */
    public function getStoreConfig ($key,
            $storeId = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
    {
        $path = self::XML_PATH_FOOMANJIRAFE_SETTINGS . $key;
        return Mage::getStoreConfig($path, $storeId);
    }

    /**
     * Return store config value for key directly from db
     *
     * @param   string $key
     * @return  string
     */
    public function getStoreConfigDirect ($key,
            $storeId = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID, $foomanjirafe=true, $websiteId=false)
    {
        if($foomanjirafe) {
            $path = self::XML_PATH_FOOMANJIRAFE_SETTINGS . $key;
        } else {
            $path = $key;
        }
        $collection = Mage::getModel('core/config_data')->getCollection()
                        ->addFieldToFilter('path', $path)
                        ->addFieldToFilter('scope_id', $storeId);
        if ($storeId != Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID) {
            $collection->addFieldToFilter('scope', Mage_Adminhtml_Block_System_Config_Form::SCOPE_STORES);
        }
        if ($collection->load()->getSize() == 1) {
            //value exists /-> update/ should only be one
            foreach ($collection as $existingConfigData) {
                return $existingConfigData->getValue();
            }
        }
        //fall back on website and default for non-jirafe settings
        if(!$foomanjirafe) {
            $collection = Mage::getModel('core/config_data')->getCollection()
                            ->addFieldToFilter('path', $path)
                            ->addFieldToFilter('scope_id',  $websiteId)
                            ->addFieldToFilter('scope', Mage_Adminhtml_Block_System_Config_Form::SCOPE_WEBSITES);
            if ($collection->load()->getSize() == 1) {
                //value exists /-> update/ should only be one
                foreach ($collection as $existingConfigData) {
                    return $existingConfigData->getValue();
                }
            }
            $collection = Mage::getModel('core/config_data')->getCollection()
                            ->addFieldToFilter('path', $path)
                            ->addFieldToFilter('scope_id',  Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);
            if ($collection->load()->getSize() == 1) {
                //value exists /-> update/ should only be one
                foreach ($collection as $existingConfigData) {
                    return $existingConfigData->getValue();
                }
            }
        }
    }

    /**
     * Save store config value for key
     *
     * @param string $key
     * @param string $value
     * @return <type>
     */
    public function setStoreConfig ($key, $value,
            $storeId = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
    {
        $path = self::XML_PATH_FOOMANJIRAFE_SETTINGS . $key;

        //save to db
        try {
            $configModel = Mage::getModel('core/config_data');
            $collection = $configModel->getCollection()
                            ->addFieldToFilter('path', $path)
                            ->addFieldToFilter('scope_id', $storeId);
            if ($storeId != Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID) {
                $collection->addFieldToFilter('scope', Mage_Adminhtml_Block_System_Config_Form::SCOPE_STORES);
            }

            if ($collection->load()->getSize() > 0) {
                //value already exists -> update
                foreach ($collection as $existingConfigData) {
                    $existingConfigData->setValue($value)->save();
                }
            } else {
                //new value
                $configModel
                        ->setPath($path)
                        ->setValue($value);
                if ($storeId != Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID) {
                    $configModel->setScopeId($storeId);
                    $configModel->setScope(Mage_Adminhtml_Block_System_Config_Form::SCOPE_STORES);
                }
                $configModel->save();
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
        Mage::app()->getConfig()->removeCache();
        //we also set it as a temporary item so we don't need to reload the config
        return Mage::app()->getStore($storeId)->load($storeId)->setConfig($path, $value);
    }

    public function isConfigured ($storeId = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
    {
        return ($this->getStoreConfig('app_id', $storeId) && $this->getStoreConfig('app_token', $storeId));
    }

    public function getStoreIds ()
    {
        return $this->getStores(true);
    }

    public function getStores ($idsOnly = false)
    {
        // Get a list of store IDs to send to the user
        $stores = Mage::getModel('core/store')->getCollection();
        $storearr = array();
        foreach ($stores as $store) {
            // Only continue if the store is active
            if ($store->getIsActive()) {
                if ($idsOnly) {
                    $storearr[] = $store->getId();
                } else {
                    $storearr[$store->getId()] = $store;
                }
            }
        }
        if ($idsOnly) {
            return implode(',', $storearr);
        } else {
            ksort($storearr);
            return $storearr;
        }
    }

    /**
     * return admin email addresses that we are emailing the reports to
     * either return a csv list of just emails or an array
     * with key = email address and value = report type
     *
     * @param int $storeId
     * @param boolean $asCsv
     * @return string|array
     */
    public function collectJirafeEmails ($asCsv = true, $containsOrders = true, $allUsers = false)
    {
        $adminUsers = Mage::getSingleton('admin/user')->getCollection();
        $emails = array();
        // loop over all admin users
        foreach ($adminUsers as $adminUser) {
            if ($adminUser->getIsActive() && ($adminUser->getJirafeSendEmail() || $allUsers)) {
                // If user wants to suppress emails with no revenue...
                $suppress = $adminUser->getJirafeEmailSuppress();
                // Only continue if the data contains orders, or if we are not suppressing
                if (($containsOrders || !$suppress) || $allUsers) {
                    if ($asCsv) {
                        $emails[] = $adminUser->getEmail();
                    } else {
                        $emails[$adminUser->getEmail()] = $adminUser->getJirafeEmailReportType();
                    }
                }
            }
        }
        // add users added via global config
        if(!$allUsers){
            foreach (explode(',', Mage::helper('foomanjirafe')->getStoreConfig('also_send_emails_to')) as $jirafeEmail) {
                if (!empty($jirafeEmail)) {
                    $emails[$jirafeEmail] = Mage::helper('foomanjirafe')->getStoreConfig('report_type');
                }
            }
        }
        if ($asCsv) {
            return implode(',', $emails);
        } else {
            return $emails;
        }
    }

    public function debug ($mesg)
    {
        if ($this->isDebug()) {
            Mage::log($mesg, null, 'jirafe.log');
        }
    }

    public function isDebug ()
    {
        return (bool) $this->getStoreConfig('is_debug');
    }

    public function getStoreDescription ($store)
    {
        return Mage::getModel('core/store_group')->load($store->getGroupId())->getName() . ' (' . $store->getData('name') . ')';
    }

    public function createJirafeUserId ($user)
    {
        return 'magento_' . substr($this->getStoreConfig('app_token'), 0, 5) . '_' . $user->getEmail();
    }

    public function createJirafeUserEmail ($user)
    {
        return $this->createJirafeUserId($user);
    }

    public function getUserEmail ($email)
    {
        /*
         * see createJirafeUserId
         */

        $array = explode('_', $email, 3);
        return $array[2];
    }

    public function getUnifiedStoreBaseUrl ($baseUrl)
    {
        return rtrim($baseUrl, '/');
    }
	
    public function isDashboardActive ()
    {
        // To check if the dashboard is active, you must check:
        // 1. If the plugin is active
        // 2. If the dashboard is active for this user
        // 3. If we have a valid app_id and app_token
        return (
        Mage::helper('foomanjirafe')->getStoreConfig('isActive') &&
        Mage::getSingleton('admin/session')->getUser()->getJirafeDashboardActive() &&
        $this->isConfigured()
        );
    }

    public function isEmailActive ()
    {
        return (Mage::helper('foomanjirafe')->getStoreConfig('isEmailActive') && Mage::helper('foomanjirafe')->getStoreConfig('isActive'));
    }

    public function isOk ()
    {
        return $this->isConfigured()
                && $this->getStatus() != Fooman_Jirafe_Helper_Data::JIRAFE_STATUS_NOT_INSTALLED
                && $this->getStatus() != Fooman_Jirafe_Helper_Data::JIRAFE_STATUS_ERROR;
    }

    public function getStatus ()
    {
        return $this->getStoreConfig('last_status');
    }
}