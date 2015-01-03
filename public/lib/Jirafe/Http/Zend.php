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

class Jirafe_Http_Zend extends Zend_Http_Client implements Jirafe_Http_Interface
{
    /**
     *  translate Jirafe_Http_Interface methods to Zend_Http_Client equivalents
     */

    /**
     *  set the url to connect to
     *  
     * @param string $uri
     */
    public function jirafeHttpSetUri ($uri)
    {
        return parent::setUri($uri);
    }

    /**
     *  set a GET parameter for the next request
     * 
     *  @param string $param
     *  @param string $value (optional)
     */
    public function jirafeHttpSetParameterGet ($param, $value = null)
    {
        return parent::setParameterGet($param, $value);
    }

    /**
     *  set a POST parameter for the next request
     * 
     *  @param string $param
     *  @param string $value (optional)
     */
    public function jirafeHttpSetParameterPost ($param, $value = null)
    {
        return parent::setParameterPost($param, $value);
    }

    /**
     *  set http authentification parameters for the next request
     * 
     *  @param string $param
     *  @param string $value (optional)
     *  @param string $type (optional)
     */
    public function jirafeHttpSetAuth ($user, $password = '', $type = null)
    {
        return parent::setAuth($user, $password = '', $type = self::AUTH_BASIC);
    }

    /**
     *  is last response an error?
     * 
     *  @return bool
     */
    public function jirafeHttpIsReponseError ()
    {
        return $this->getLastResponse()->isError();
    }

    /**
     *  return last response status (200,404,501 etc)
     * 
     *  @return int
     */
    public function jirafeHttpGetResponseStatus ()
    {
        return $this->getLastResponse()->getStatus();
    }

    /**
     *  return last response message
     * 
     *  @return string
     */
    public function jirafeHttpGetResponseMessage ()
    {
        return $this->getLastResponse()->getMessage();
    }

    /**
     *  return the body of the last response 
     * 
     *  @return string
     */
    public function jirafeHttpGetResponseBody ()
    {
        return $this->getLastResponse()->getBody();
    }

    /**
     *  send the request to the api
     * 
     *  @param string $method
     *  @see Jirafe_Api for methods
     * 
     *  @return string
     */
    public function jirafeHttpRequest ($method)
    {
        return parent::request($method);
    }

}