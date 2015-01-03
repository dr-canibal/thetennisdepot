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
		exit(readfile("./banner/livezilla_".$id."_1.png"));
	else
		exit(readfile("./banner/livezilla_".$id."_0.png"));
	
}
else if(isset($_GET["v"]))
{
	$parts = explode("<!>",base64_decode(str_replace(" ","+",$_GET["v"])));
	if(strlen($parts[3]) > 0)
		$parts[0] = str_replace("<!--class-->","class=\\\"".$parts[3]."\\\"",$parts[0]);
	else
		$parts[0] = str_replace("<!--class-->","",$parts[0]);
		
	if(operatorsAvailable() > 0)
		$html = str_replace("<!--text-->",$parts[1],$parts[0]);
	else
		$html = str_replace("<!--text-->",$parts[2],$parts[0]);
	exit("document.write(\"".$html."\");");
}
unloadDataProvider();
?>