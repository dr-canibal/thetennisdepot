<?php

/****************************************************************************************
* LiveZilla image.php
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
	define("LIVEZILLA_PATH","./");
	
@set_time_limit(30);

require(LIVEZILLA_PATH . "_definitions/definitions.inc.php");
require(LIVEZILLA_PATH . "_definitions/definitions.files.inc.php");
require(LIVEZILLA_PATH . "_lib/objects.global.users.inc.php");
require(LIVEZILLA_PATH . "_config/config.inc.php");
require(LIVEZILLA_PATH . "_definitions/definitions.dynamic.inc.php");
require(LIVEZILLA_PATH . "_lib/functions.global.inc.php");

@set_error_handler("handleError");
@error_reporting(E_ALL);

header("Connection: close");
header("Pragma: no-cache");
header("Cache-Control: no-cache, must-revalidate");

setDataProvider();
if(isset($_GET["id"]) && strpos($_GET["id"],"..") === false)
{
	$id = $_GET["id"];
	$exclude = (isset($_GET["hg"])) ? explode("?",base64_decode($_GET["hg"])) : null;
	header("Content-Type: image/png;");
	if(operatorsAvailable(0,$exclude) > 0)
		exit(readfile("./banner/livezi