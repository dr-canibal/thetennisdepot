<?php
/*-----------------------------------------------------------------------------+
| MagneticOne                                                                  |
| Copyright (c) 2008 MagneticOne.com <contact@magneticone.com>                 |
| All rights reserved                                                          |
+------------------------------------------------------------------------------+
| PLEASE READ  THE FULL TEXT OF SOFTWARE LICENSE AGREEMENT IN THE "license.txt"|
| FILE PROVIDED WITH THIS DISTRIBUTION. THE AGREEMENT TEXT IS ALSO AVAILABLE   |
| AT THE FOLLOWING URL: http://www.magneticone.com/store/license.php           |
|                                                                              |
| THIS  AGREEMENT  EXPRESSES  THE  TERMS  AND CONDITIONS ON WHICH YOU MAY USE  |
| THIS SOFTWARE   PROGRAM   AND  ASSOCIATED  DOCUMENTATION   THAT  MAGNETICONE |
| (hereinafter  referred to as "THE AUTHOR") IS FURNISHING  OR MAKING          |
| AVAILABLE TO YOU WITH  THIS  AGREEMENT  (COLLECTIVELY,  THE  "SOFTWARE").    |
| PLEASE   REVIEW   THE  TERMS  AND   CONDITIONS  OF  THIS  LICENSE AGREEMENT  |
| CAREFULLY   BEFORE   INSTALLING   OR  USING  THE  SOFTWARE.  BY INSTALLING,  |
| COPYING   OR   OTHERWISE   USING   THE   SOFTWARE,  YOU  AND  YOUR  COMPANY  |
| (COLLECTIVELY,  "YOU")  ARE  ACCEPTING  AND AGREEING  TO  THE TERMS OF THIS  |
| LICENSE   AGREEMENT.   IF  YOU    ARE  NOT  WILLING   TO  BE  BOUND BY THIS  |
| AGREEMENT, DO  NOT INSTALL OR USE THE SOFTWARE.  VARIOUS   COPYRIGHTS   AND  |
| OTHER   INTELLECTUAL   PROPERTY   RIGHTS    PROTECT   THE   SOFTWARE.  THIS  |
| AGREEMENT IS A LICENSE AGREEMENT THAT GIVES  YOU  LIMITED  RIGHTS   TO  USE  |
| THE  SOFTWARE   AND  NOT  AN  AGREEMENT  FOR SALE OR FOR  TRANSFER OF TITLE. |
| THE AUTHOR RETAINS ALL RIGHTS NOT EXPRESSLY GRANTED BY THIS AGREEMENT.       |
|                                                                              |
| The Developer of the Code is MagneticOne,                                    |
| Copyright (C) 2006 - 2008 All Rights Reserved.                               |
+-----------------------------------------------------------------------------*/

#
# $Id: config_adapter.class.php 243 2009-05-22 19:08:37Z peter $
#

/**
 * @todo Refactor this class
 * need to do it abstract with factory
 * to provide specific config classes for each
 * shopping cart type
 *
 */

class M1_C2c_Config_Adapter
{
	var $Host      = "localhost";
	var $Port      = "3306";
	var $Username  = "root";
	var $Password  = "";
	var $Dbname    = "";
	var $TblPrefix = "";
	
	function M1_C2c_Config_Adapter()
	{
		
		if(file_exists(M1_C2C_STORE_BASE_DIR
                . "includes" . DIRECTORY_SEPARATOR
                . "configure.php")) {
                	
			require_once M1_C2C_STORE_BASE_DIR
			            . "includes" . DIRECTORY_SEPARATOR
			            . "configure.php";
			            
			define('CART_IMAGES_DIR', M1_C2C_STORE_BASE_DIR . DIR_WS_IMAGES);
				
			$this->Host = DB_SERVER;
			$this->Username = DB_SERVER_USERNAME;
			$this->Password = DB_SERVER_PASSWORD;
			$this->Dbname = DB_DATABASE;
			
    } elseif(file_exists(M1_C2C_STORE_BASE_DIR . "config.php")) {
    	
    	define('XCART_START', 1);
    	
    	define('CART_IMAGES_DIR', M1_C2C_STORE_BASE_DIR . 'images/'); // xcart starting from 4.1.x hardcodes images location
    	
    	$xcart_dir = M1_C2C_STORE_BASE_DIR;

    	require_once M1_C2C_STORE_BASE_DIR . "config.php";

    	$this->Host     = $sql_host;
    	$this->Username = $sql_user;
    	$this->Password = $sql_password;
    	$this->Dbname   = $sql_db;
      
    } elseif (file_exists(M1_C2C_STORE_BASE_DIR . 'content/engine/engine_config.php')) {
      include_once M1_C2C_STORE_BASE_DIR . 'content/engine/engine_config.php';
      
      define('CART_IMAGES_DIR', M1_C2C_STORE_BASE_DIR . 'images/');
      
      $this->Host = DB_HOST;
      $this->Dbname = DB_NAME;
      $this->Username = DB_USER;
      $this->Password = DB_PASSWORD;
      
    } elseif (file_exists(M1_C2C_STORE_BASE_DIR . 'app/etc/local.xml')) { // Magento, we can be sure that PHP is >= 5.2.0
      /**
       * @var SimpleXMLElement
       */
      $config = simplexml_load_file(M1_C2C_STORE_BASE_DIR . 'app/etc/local.xml');
      
      $this->Host = $config->global->resources->default_setup->connection->host;
      $this->Username = $config->global->resources->default_setup->connection->username;
      $this->Dbname = $config->global->resources->default_setup->connection->dbname;
      $this->Password = $config->global->resources->default_setup->connection->password;
      
      define('CART_IMAGES_DIR', M1_C2C_STORE_BASE_DIR . 'media/');
      @unlink(M1_C2C_STORE_BASE_DIR . 'app/etc/use_cache.ser');
    } elseif (file_exists(M1_C2C_STORE_BASE_DIR . 'includes/global.inc.php')) {
      // CubeCart 3.x
      include_once(M1_C2C_STORE_BASE_DIR . 'includes/global.inc.php');
      
      $this->Host = $glob['dbhost'];
      $this->Dbname = $glob['dbdatabase'];
      $this->Username = $glob['dbusername'];
      $this->Password = $glob['dbpassword'];
      
      define('CART_IMAGES_DIR', M1_C2C_STORE_BASE_DIR . 'images/uploads');
    }
	}
}