<?php
/****************************************************************************************
* LiveZilla api.php
* 
* Copyright 2001-2009 SPAUN Power GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors. It is strongly 
* recommended to desist from editing this file.
* 
***************************************************************************************/ 

define("IN_LIVEZILLA",true);
if(!defined("LIVEZILLA_PATH"))
	exit("Error: 'LIVEZILLA_PATH' is not defined. Please define the constant 'LIVEZILLA_PATH' before accessing the API.");

require(LIVEZILLA_PATH . "_definitions/definitions.inc.php");
require(LIVEZILLA_PATH . "_definitions/definitions.files.inc.php");
require(LIVEZILLA_PATH . "_lib/objects.global.users.inc.php");
require(LIVEZILLA_PATH . "_config/config.inc.php");
require(LIVEZILLA_PATH . "_definitions/definitions.dynamic.inc.php");
require(LIVEZILLA_PATH . "_lib/functions.global.inc.php");

class LiveZillaAPI
{
	function LiveZillaAPI()
	{
		@register_shutdown_function('unloadDataProvider');
		setDataProvider();
	}
	
	function IsOperatorAvailable()
	{
		return (operatorsAvailable() > 0);
	}
	
	function GetOperatorList()
	{
		return getOperatorList();
	}
}
?>