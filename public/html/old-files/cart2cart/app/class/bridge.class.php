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
# $Id: bridge.class.php 245 2009-05-28 17:23:40Z peter $
#

class M1_C2c_Bridge
{
	var $_link = null;
  var $_res = null;
  var $_tblPrefix = "";
  var $config = null;
  
  /**
   * Bridge constructor
   *
   * @param M1_C2c_Config_Adapter $config
   * @return M1_C2c_Bridge
   */
  function M1_C2c_Bridge($config)
  {
    $this->config = $config;
    $triesCount = 10;
    while (!$this->_link) {
      if (!$triesCount--) {
        break;
      }
      $this->_link = @mysql_connect($config->Host.":".$config->Port, $config->Username, $config->Password);
      if  (!$this->_link) {
        sleep(5);
      }
    }
    $this->_tblPrefix = $config->TblPrefix;
    @mysql_select_db($config->Dbname, $this->_link);
  }

	function getTablesPrefix()
	{
		return $this->_tblPrefix;
	}
	
	function getLink()
	{
		return $this->_link;
	}
	
	function query($sql, $fetchMode) 
	{
	  if (!$this->_link) {
	   return '[ERROR] MySQL Query Error: Can not connect to DB';
	  }
	  
	  if (isset($_GET['disable_checks'])) {
      mysql_query('SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0', $this->_link);
      mysql_query("SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO'", $this->_link);
	  }
	  
		$fetch_mode = MYSQL_ASSOC;
		switch ($fetchMode) {
			case 3:
				$fetch_mode = MYSQL_BOTH;
			break;
				
			case 2:
				$fetch_mode = MYSQL_NUM;
			break;	
			
			case 1:
				$fetch_mode = MYSQL_ASSOC;
			default:
		  break;
		}
		
	  $this->_res = mysql_query($sql, $this->_link);
	  
	  $triesCount = 10;
	  while (mysql_errno($this->_link) == 2013) {
	    if (!$triesCount--) {
        break;
	    }
	    // reconnect
	    $this->_link = @mysql_connect($config->Host.":".$config->Port, $config->Username, $config->Password);
	    if ($this->_link) {
	       @mysql_select_db($this->Dbname, $this->_link);
	       
	       // excute query once again
	       $this->_res = mysql_query($sql, $this->_link);
	    }
	  }
	  
	  if( ($errno = mysql_errno($this->_link)) != 0 ) {
	  	return "[ERROR] Mysql Query Error: $errno, " . mysql_error($this->_link);
	  }
	  
	  if(!is_resource($this->_res)) {
	  	return $this->_res;
	  }
	  
	  $result = array();
	  while( ($row = mysql_fetch_array($this->_res, $fetch_mode)) !== false) {
	    $result[] = $row;
	  }
	
	  mysql_free_result($this->_res);

	  if (isset($_GET['disable_checks'])) {
      mysql_query("SET SQL_MODE=IFNULL(@OLD_SQL_MODE,'')", $this->_link);
      mysql_query("SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS,0)", $this->_link);
    }
	  
	  return $result;
	}
	
	function run()
	{
		if(isset($_GET['action'])) {
			$action = str_replace(".", "", $_GET['action']);
		}
    
    $this->_selfTest();

    if($action == "checkbridge") {
      return;
    }
		
		define("M1_C2C_BRIDGE_ACTION_CLASS_PATH", M1_C2C_STORE_BASE_DIR
                                                . "cart2cart" . DIRECTORY_SEPARATOR 
																			          . "app" . DIRECTORY_SEPARATOR
																			          . "actions" . DIRECTORY_SEPARATOR
																			          . $action . "_action.class.php" );
		require_once M1_C2C_BRIDGE_ACTION_CLASS_PATH;
																			                
	  $clas_name =  "M1_C2c_Bridge_Action_" . ucfirst($action);
	  $action_obj = new $clas_name();
	  $action_obj->perform($this);
	  $this->_destroy();
	}
	
	function isWritable($dir)
	{
	  if (!@is_dir($dir)) {
	    return false;
	  }

    $dh = opendir($dir);

    while( ($entry = readdir($dh)) !== false ) {
      if($entry == "." || $entry == ".." || !@is_dir($dir . DIRECTORY_SEPARATOR . $entry)) {
        continue;
      }

      if( !$this->isWritable($dir . DIRECTORY_SEPARATOR . $entry) ) {
        return false;
      }
    }

    if (!is_writable($dir)) {
	    return false;
	  }
    
	  return true;
	}
	
	function _destroy()
	{
		mysql_close($this->_link);
	}

  function _selfTest()
  {
    if( !isset($_GET['ver']) || $_GET['ver'] != M1_C2C_BRIDGE_VERSION ) {
      die ('ERROR_BRIDGE_VERSION_NOT_SUPPORTED');
    }
    
    if (isset($_GET['token']) && $_GET['token'] == M1_C2C_TOKEN) {
			// good :)
		} else {
			die('ERROR_INVALID_TOKEN');
		}

		if (!file_exists(M1_C2C_STORE_BASE_DIR . 'cart2cart/dump') || !is_writable(M1_C2C_STORE_BASE_DIR . 'cart2cart/dump')) {
		  die('ERROR_NO_DUMP_DIR');
		}

    if (!file_exists(CART_IMAGES_DIR) && is_writable(M1_C2C_STORE_BASE_DIR)) {
      if (!mkdir(CART_IMAGES_DIR, 0777, true)) {
        die('ERROR_TRIED_TO_CREATE_IMAGE_DIR');
      }
    }
		
		if (!$this->isWritable(CART_IMAGES_DIR)) {
		  die('ERROR_NO_IMAGES_DIR');
		}
  }
}