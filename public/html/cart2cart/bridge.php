<?php

define('M1_C2C_BRIDGE_VERSION', '9');

require_once 'config.php';

function stripslashes_array($array) {
  return is_array($array) ? array_map('stripslashes_array', $array) : stripslashes($array);
}

if (!isset($_SERVER))
{
   $_GET      = &$HTTP_GET_VARS;
   $_POST     = &$HTTP_POST_VARS;
   $_ENV      = &$HTTP_ENV_VARS;
   $_SERVER   = &$HTTP_SERVER_VARS;
   $_COOKIE   = &$HTTP_COOKIE_VARS;
   $_REQUEST  = array_merge($_GET, $_POST, $_COOKIE);
}

if ( get_magic_quotes_gpc() ) {

  $_COOKIE  = stripslashes_array($_COOKIE);
  $_FILES   = stripslashes_array($_FILES);
  $_GET     = stripslashes_array($_GET);
  $_POST    = stripslashes_array($_POST);
  $_REQUEST = stripslashes_array($_REQUEST);
}

define("M1_C2C_STORE_BASE_DIR", preg_replace('/[^\/\\\]*[\/\\\][^\/\\\]*$/', '', $_SERVER['SCRIPT_FILENAME']));

require_once M1_C2C_STORE_BASE_DIR
              . "cart2cart" . DIRECTORY_SEPARATOR
              . "app"  . DIRECTORY_SEPARATOR
              . "class" . DIRECTORY_SEPARATOR
              . "config_adapter.class.php";
              
require_once M1_C2C_STORE_BASE_DIR
              . "cart2cart" . DIRECTORY_SEPARATOR
              . "app"  . DIRECTORY_SEPARATOR
              . "class" . DIRECTORY_SEPARATOR
              . "bridge.class.php";
              
$bridge = new M1_C2c_Bridge(new M1_C2c_Config_Adapter());
$bridge->run();
