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
require_once dirname(dirname(__FILE__)).'/Api.php';

class Jirafe_Api_User
{

    private $_api = null;
    
    public function __construct($api)
    {
        $this->_api = $api;
    }
    
    /**
     * Create user account (does not require authentication)
     * @param $username - Desired username
     * @param $password - The password
     * @param $passwordConfirm - Repeat your password
     * @param $email - Your email
     * @param $firstName - Your first name
     * @param $lastName - Your last name
     * @param $mobilePhone - Your mobile phone number
     */
    public function create ($username, $password, $passwordConfirm, $email, $firstName=null, $lastName=null, $mobilePhone=null)
    {
        $data = array();
        $data['username'] = $username;
        $data['password'] = $password;
        $data['password_confirm'] = $passwordConfirm;
        $data['email'] = $email;
        $data['first_name'] = $firstName;
        $data['last_name'] = $lastName;
        $data['mobile_phone'] = $mobilePhone;
        return $this->_api->sendData(Jirafe_Api::JIRAFE_API_USERS, $data, Jirafe_Api::HTTP_METHOD_POST);
    }

    /**
     * Get user account information
     *
     * @param $username
     */
    public function getInfo ($username)
    {
        return $this->_api->sendData(Jirafe_Api::JIRAFE_API_USERS.'/'.$username, false, Jirafe_Api::HTTP_METHOD_GET);
    }

    /**
     * Update user account information
     *
     * @param $username
     * @param $email
     */
    public function update ($username, $email)
    {
        $data = array();
        $data['email'] = $email;
        return $this->_api->sendData(Jirafe_Api::JIRAFE_API_SITES.'/'.$username, $data, Jirafe_Api::HTTP_METHOD_PUT);
    }

}