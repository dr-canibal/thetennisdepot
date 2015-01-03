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

    public function jirafeHttpSetUri ($uri)
    {
        return parent::setUri($uri);
    }

    public function jirafeHttpSetParameterGet ($param, $value = null)
    {
        return parent::setParameterGet($param, $value);
    }

    public function jirafeHttpSetParameterPost ($param, $value = null)
    {
        return parent::setParameterPost($param, $value);
    }

    public function jirafeHttpSetAuth ($user, $password = '', $type = null)
    {
        return parent::setAuth($user, $password = '', $type = self::AUTH_BASIC);
    }

    public function jirafeHttpIsReponseError ()
    {
        return $this->getLastResponse()->isError();
    }

    public function jirafeHttpGetResponseStatus ()
    {
        return $this->getLastResponse()->getStatus();
    }

    public function jirafeHttpGetResponseMessage ()
    {
        return $this->getLastResponse()->getMessage();
    }

    public function jirafeHttpGetResponseBody ()
    {
        return $this->getLastResponse()->getBody();
    }

    public function jirafeHttpRequest ($method)
    {
        return parent::request($method);
    }

}