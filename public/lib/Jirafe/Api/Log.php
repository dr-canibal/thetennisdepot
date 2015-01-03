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

class Jirafe_Api_Log 
{

    private $_api = null;
    
    public function __construct($api)
    {
        $this->_api = $api;
    }    
    
    /**
     * Update application information
     *
     * @param $appId
     * @param $adminToken
     * @param $data
     */
    public function sendLog ($adminToken, $data=array())
    {
        if(empty($adminToken)) {
            $this->_api->throwException('Admin token can\'t be empty');
        }
        return $this->_api->sendData(Jirafe_Api::JIRAFE_API_LOGS, $data, $adminToken, Jirafe_Api::HTTP_METHOD_POST);
    }

       
}