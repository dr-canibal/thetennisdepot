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
require_once dirname(dirname(__FILE__)) . '/Api.php';

require_once 'Buzz/ClassLoader.php';
Buzz\ClassLoader::register();

class Jirafe_Http_Buzz extends Browser implements Jirafe_Http_Interface
{

    protected $url;
    protected $postParameters = array();
    protected $getParameters = array();

    public function jirafeHttpSetUri ($uri)
    {
        $this->url = $uri;
    }

    public function jirafeHttpSetParameterGet ($param, $value = null)
    {
        $this->getParameters[$param] = $value;
    }

    public function jirafeHttpSetParameterPost ($param, $value = null)
    {
        $this->postParameters[$param] = $value;
    }

    public function jirafeHttpSetAuth ($user, $password = '', $type = null)
    {
        //TODO
    }

    public function jirafeHttpIsReponseError ()
    {
        return $this->getJournal()->getLastResponse()->getStatusCode() > 400;
    }

    public function jirafeHttpGetResponseStatus ()
    {
        return $this->getJournal()->getLastResponse()->getStatusCode();
    }

    public function jirafeHttpGetResponseMessage ()
    {
        return $this->getJournal()->getLastResponse()->getReasonPhrase();
    }

    public function jirafeHttpGetResponseBody ()
    {
        return $this->getJournal()->getLastResponse()->getContent();
    }

    public function jirafeHttpRequest ($method)
    {
        switch ($method) {
            case Jirafe_Api::HTTP_METHOD_POST:
                return $this->post($this->_getUrl(),
                        $this->_getHeaders(Jirafe_Api::HTTP_METHOD_POST),
                        $this->_getContent(Jirafe_Api::HTTP_METHOD_POST));
                break;
            case Jirafe_Api::HTTP_METHOD_GET:
                return $this->get($this->_getUrl(),
                        $this->_getHeaders(Jirafe_Api::HTTP_METHOD_GET));
                break;
            case Jirafe_Api::HTTP_METHOD_PUT;
                return $this->put($this->_getUrl(),
                        $this->_getHeaders(Jirafe_Api::HTTP_METHOD_PUT),
                        $this->_getContent(Jirafe_Api::HTTP_METHOD_PUT));
                break;
            case Jirafe_Api::HTTP_METHOD_DELETE;
                return $this->delete($this->_getUrl(),
                        $this->_getHeaders(Jirafe_Api::HTTP_METHOD_DELETE),
                        $this->_getContent(Jirafe_Api::HTTP_METHOD_DELETE));
                break;
        }
    }

    protected function _getUrl ()
    {
        //TODO check for uris which already have ?
        $getParameters = '';
        if (!empty($this->getParameters)) {
            $getParameters = '?' . implode('&', $this->getParameters);
        }
        return $this->url . $getParameters;
    }

    protected function _getHeaders ($method)
    {
        if ($method == Jirafe_Api::HTTP_METHOD_GET) {
            return array();
        } else {
            //TODO do we need add this for Buzz?
            $headers = array();
            $headers[] = "Content-Type: application/x-www-form-urlencoded";
            $headers[] = "Content-Length: " . strlen(http_build_query($this->postParameters,
                                    '', '&'));
            return $headers;
        }
    }

    protected function _getContent ()
    {
        return http_build_query($this->postParameters, '', '&');
    }

}