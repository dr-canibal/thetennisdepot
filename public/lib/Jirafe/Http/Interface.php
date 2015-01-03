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

interface Jirafe_Http_Interface
{
    /**
     *  interface required for using the Jirafe api class
     */

    /**
     *  set the url to connect to
     *  
     * @param string $uri
     */
    public function jirafeHttpSetUri ($uri);

    /**
     *  set a GET parameter for the next request
     * 
     *  @param string $param
     *  @param string $value (optional)
     */
    public function jirafeHttpSetParameterGet ($param, $value = null);

    /**
     *  set a POST parameter for the next request
     * 
     *  @param string $param
     *  @param string $value (optional)
     */
    public function jirafeHttpSetParameterPost ($param, $value = null);

    /**
     *  set http authentification parameters for the next request
     * 
     *  @param string $param
     *  @param string $value (optional)
     *  @param string $type (optional)
     */
    public function jirafeHttpSetAuth ($user, $pwd ='', $type = null);

    /**
     *  is last response an error?
     * 
     *  @return bool
     */
    public function jirafeHttpIsReponseError ();

    /**
     *  return last response status (200,404,501 etc)
     * 
     *  @return int
     */
    public function jirafeHttpGetResponseStatus ();

    /**
     *  return last response message
     * 
     *  @return string
     */
    public function jirafeHttpGetResponseMessage ();

    /**
     *  return the body of the last response 
     * 
     *  @return string
     */
    public function jirafeHttpGetResponseBody ();

    /**
     *  send the request to the api
     * 
     *  @param string $method
     *  @see Jirafe_Api for methods
     * 
     *  @return string
     */
    public function jirafeHttpRequest ($method);
}