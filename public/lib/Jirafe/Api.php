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

class Jirafe_Api
{
    // PRODUCTION environment
    const JIRAFE_API_SERVER = 'https://api.jirafe.com';
    const JIRAFE_API_BASE = '';
    const JIRAFE_PIWIK_BASE_URL = 'data.jirafe.com';
    
    // DEV environment
//    const JIRAFE_API_SERVER = 'http://api.jirafe.local';
//    const JIRAFE_API_BASE = 'app_dev.php';
//    const JIRAFE_PIWIK_BASE_URL = 'piwik.local';
    
    // TEST environment
//    const JIRAFE_API_SERVER = 'https://test-api.jirafe.com';
//    const JIRAFE_API_BASE = '';
//    const JIRAFE_PIWIK_BASE_URL = 'test-data.jirafe.com';
    


	// Don't put http/s on this URL because it is added later

    const JIRAFE_API_VERSION = 'v1';
    const JIRAFE_API_LOGS = '/logs';
    const JIRAFE_API_APPLICATIONS = '/applications';
    const JIRAFE_API_RESOURCES =  '/resources';
    const JIRAFE_API_SITES = '/sites';
    const JIRAFE_API_USERS = '/users';
    const JIRAFE_DOC_URL = 'http://jirafe.com/doc';
    
    const HTTP_METHOD_POST      = 'POST';
    const HTTP_METHOD_GET       = 'GET';
    const HTTP_METHOD_PUT       = 'PUT';
    const HTTP_METHOD_DELETE    = 'DELETE';    

    private $_httpClient = null;    
    
    private $_application = null;
    private $_log = null;
    private $_resource = null;
    private $_site = null;
    private $_user = null;
    
    /**
     * set http client to user for any api http requests
     * 
     * @param Jirafe_Http_Interface $httpClient 
     */
    public function setHttpClient ($httpClient)
    {
        //require_once 'Http/Interface.php';
        if ($httpClient instanceof Jirafe_Http_Interface) {
            $this->_httpClient = $httpClient;
        } else {
            $this->throwException('Http client needs to implement Jirafe_Http_Interface.');
        }
    }

    /**
     * retrieve http client
     * 
     * @return Jirafe_Http_Interface 
     */
    public function getHttpClient ()
    {
        //require_once 'Http/Interface.php';
        if ($this->_httpClient instanceof Jirafe_Http_Interface) {
            return $this->_httpClient;
        } else {
            $this->throwException('No http client available.');
        }        
    }

    /**
     * retrieve application end point object
     * 
     * @return Jirafe_Api_Application 
     */
    public function getApplication()
    {
        if($this->_application === null) {
            //require_once 'Api/Application.php';
            $this->_application = new Jirafe_Api_Application($this);
        }
        return $this->_application;
    }
    
    /**
     * retrieve log end point object
     * 
     * @return Jirafe_Api_Log 
     */
    public function getLog()
    {
        if($this->_log === null) {
            //require_once 'Api/Log.php';
            $this->_log = new Jirafe_Api_Log($this);
        }
        return $this->_log;
    }
    
    /**
     * retrieve resource end point object
     * 
     * @return Jirafe_Api_Resource 
     */
    public function getResource()
    {
        if($this->_resource === null) {
            //require_once 'Api/Resource.php';
            $this->_resource = new Jirafe_Api_Resource($this);
        }
        return $this->_resource;
    }
    
    /**
     * retrieve site end point object
     * 
     * @return Jirafe_Api_Site 
     */    
    public function getSite()
    {
        if($this->_site === null) {
            //require_once 'Api/Site.php';
            $this->_site = new Jirafe_Api_Site($this);
        }
        return $this->_site;
    }
    
    /**
     * retrieve user end point object
     * 
     * @return Jirafe_Api_User 
     */     
    public function getUser()
    {
        if($this->_user === null) {
            //require_once 'Api/User.php';
            $this->_user = new Jirafe_Api_User($this);
        }
        return $this->_user;
    }    
    
    /**
     * Returns the URL of the API
     *
     * @param  string $entryPoint An optional entry point
     *
     * @return string
     */
    public function getApiUrl($entryPoint = null)
    {
        // Server
        $url = rtrim(self::JIRAFE_API_SERVER, '/');

        // Base
        if ((boolean) self::JIRAFE_API_BASE) {
            $url.= '/' . ltrim(self::JIRAFE_API_BASE, '/');
        }

        // Version
        if ((boolean) self::JIRAFE_API_VERSION) {
            $url.= '/' . ltrim(self::JIRAFE_API_VERSION, '/');
        }

        // Entry Point
        if (null !== $entryPoint) {
            $url.= '/' . ltrim($entryPoint, '/');
        }

        return $url;
    }

    /**
     * Returns the URL of the asset corresponding to the specified filename
     *
     * @param  string $filename The filename of the asset
     *
     * @return string
     */
    public function getAssetUrl($filename)
    {
        return rtrim(self::JIRAFE_API_SERVER, '/') . '/' . ltrim($filename, '/');
    }
    
    /**
     * Returns the URL of the piwik installation
     *
     * @return string
     */    
    public function getPiwikBaseUrl()
    {
        return rtrim(self::JIRAFE_PIWIK_BASE_URL, '/') . '/' ;
    }

    /**
     * construct documenation url for given version, platform and type (user or troubleshooting)
     * 
     * @param string $platform
     * @param string $type
     * @param string $version
     * @return string 
     */
    public function getDocUrl($platform, $type='user', $version=null)
    {
        if ($version) {
            return rtrim(self::JIRAFE_DOC_URL, '/') . "/{$platform}/{$version}/" . ltrim($type, '/');
        } else {
            return rtrim(self::JIRAFE_DOC_URL, '/') . "/{$platform}/" . ltrim($type, '/');
        }
    }

    /**
     * send data to api at given entrypoint via http client and chosen method
     * succesful reponse is returned JSON decoded to array
     * 
     * @param string $entryPoint
     * @param array $data
     * @param string $adminToken optional
     * @param string $method 
     * @param array $httpAuth
     * @return bool|array 
     */
    public function sendData ($entryPoint, $data, $adminToken = false,
            $method = self::HTTP_METHOD_POST, $httpAuth = array())
    {

        if(!in_array('ssl', stream_get_transports())) {
            $this->throwException('The Jirafe plugin requires outgoing connectivity via ssl to communicate securely with our server to function. Please enable ssl support for php or get your webhosting company to do it for you.');
        }
        
        //set up connection      
        $this->getHttpClient()->jirafeHttpSetUri($this->getApiUrl($entryPoint));

        if($adminToken) {
            $this->getHttpClient()->jirafeHttpSetParameterGet('token', $adminToken);
        }
        //$this->getHttpClient()->setParameterGet('XDEBUG_SESSION_START', 'switzer');

        if(!empty($httpAuth)) {
            $this->getHttpClient()->jirafeHttpSetAuth($httpAuth['username'], $httpAuth['password']);
        }

        try {
            //connect and send data to Jirafe
            //loop over data items and add them as post/put parameters if requested
            if (is_array($data) && ($method == self::HTTP_METHOD_POST || $method == self::HTTP_METHOD_PUT)) {
                foreach ($data as $parameter => $value) {
                    $this->getHttpClient()->jirafeHttpSetParameterPost($parameter, $value);
                }
            }
            $this->getHttpClient()->jirafeHttpRequest($method);
            $result = $this->_errorChecking();
        } catch (Exception $e) {
            //log here for debug
            $this->throwException($e->getMessage());
        }
        return $result;
    }

    /**
     * check the last response retrieved from the api for errors
     * 
     * @return array $result 
     */
    private function _errorChecking ()
    {
        //check server response
        if ($this->getHttpClient()->jirafeHttpIsReponseError()) {
            $this->throwException($this->getHttpClient()->jirafeHttpGetResponseStatus() .' '. $this->getHttpClient()->jirafeHttpGetResponseMessage());
        }
        //TODO: dev mode returns debug toolbar remove it from output here
        $reponseBody = preg_replace('/<!-- START of Symfony2 Web Debug Toolbar -->(.*?)<!-- END of Symfony2 Web Debug Toolbar -->/', '', $this->getHttpClient()->jirafeHttpGetResponseBody());
        if(strpos($reponseBody,'You are not allowed to access this file.') !== false) {
            $this->throwException('Server Response: You are not allowed to access this file.');
        }
        if(strpos($reponseBody,'Call Stack:') !== false) {
            $this->throwException('Server Response contains errors');
        }
        if(strpos($reponseBody,'Fatal error:') !== false) {
            $this->throwException('Server Response contains errors');
        }

        //check for returned errors
        $result = json_decode($reponseBody,true);
        if(isset($result['errors']) && !empty($result['errors'])) {
            if(is_array($result['errors'])) {
                $this->throwException(implode(',',$result['errors']));                
            } else {
                $this->throwException($result['errors']);         
            }
        }
        return $result;
    }
    
    /**
     * Throw a Jirafe_Exception
     * 
     * @param string $msg 
     */
    public function throwException ($msg='')
    {
        //require_once 'Exception.php';
        throw new Jirafe_Exception($msg);
    }

}
