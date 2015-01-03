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
    
    public function jirafeHttpSetUri($uri);
    public function jirafeHttpSetParameterGet($param, $value = null);
    public function jirafeHttpSetParameterPost($param, $value = null); 
    public function jirafeHttpSetAuth($user, $pwd ='',$type = null);
    public function jirafeHttpIsReponseError();
    public function jirafeHttpGetResponseStatus();
    public function jirafeHttpGetResponseMessage();
    public function jirafeHttpGetResponseBody();
    public function jirafeHttpRequest($method);
    
}