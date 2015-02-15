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
//require_once dirname(dirname(__FILE__)).'/Api.php';

class Jirafe_Api_Application
{
 
    private $_api = null;
    
    public function __construct($api)
    {
        $this->_api = $api;
    }
    
    /**
     *
     * @param $name - The name of Application
     * @param $url - The URL of the application, which will be the admin URL for the Magento instance
     * @param $callbackUrl - The URL that will be redirected to after an account login
     */
    public function create ($name, $url)
    {
        if(empty($name) || empty($url)) {
            $this->_api->throwException('Application name and application url can\'t be empty');
        }
        $data = array();
        $data['name'] = $name;
        $data['url'] = $url;
        return $this->_api->sendData(Jirafe_Api::JIRAFE_API_APPLICATIONS, $data, false, Jirafe_Api::HTTP_METHOD_POST);
    }

    /**
     * Get application information for application ID
     *
     * @param $appId
     * @param $adminToken
     */
    public function getInfo ($appId, $adminToken)
    {
        if(empty($appId) || empty($adminToken)) {
            $this->_api->throwException('Application id and admin token can\'t be empty');
        }
        return $this->_api->sendData(Jirafe_Api::JIRAFE_API_APPLICATIONS.'/'.$appId, false, $adminToken, Jirafe_Api::HTTP_METHOD_GET);
    }

    /**
     * Get linked sites for application ID
     *
     * @param $appId
     * @param $adminToken
     */
    public function getLinkedSites ($appId, $adminToken)
    {
        if(empty($appId) || empty($adminToken)) {
            $this->_api->throwException('Application id and admin token can\'t be empty');
        }
        return $this->_api->sendData(Jirafe_Api::JIRAFE_API_APPLICATIONS.'/'.$appId.Jirafe_Api::JIRAFE_API_SITES, false, $adminToken, Jirafe_Api::HTTP_METHOD_GET);
    }

    /**
     * Update application information
     *
     * @param $appId
     * @param $adminToken
     */
    public function update ($appId, $adminToken, $url)
    {
        if(empty($appId) || empty($adminToken) || empty($url)) {
            $this->_api->throwException('Application id, admin token and url can\'t be empty');
        }
        $data = array();
        $data['url'] = $url;
        return $this->_api->sendData(Jirafe_Api::JIRAFE_API_APPLICATIONS.'/'.$appId, $data, $adminToken, Jirafe_Api::HTTP_METHOD_PUT);
    }

    /**
     * Delete application (Caution! This operation can't be reverted and will delete all dependent data)
     *
     * @param $appId
     * @param $adminToken
     */
    public function delete ($appId, $adminToken)
    {
        if(empty($appId) || empty($adminToken)) {
            $this->_api->throwException('Application id and admin token can\'t be empty');
        }
        return $this->_api->sendData(Jirafe_Api::JIRAFE_API_APPLICATIONS.'/'.$appId, false, $adminToken, Jirafe_Api::HTTP_METHOD_DELETE);
    }
       
}