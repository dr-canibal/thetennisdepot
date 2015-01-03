<?php

/****************************************************************************************
* LiveZilla extern.php
* 
* Copyright 2001-2009 SPAUN Power GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors. It is strongly 
* recommended to desist from editing this file.
* 
***************************************************************************************/ 

if(!defined("IN_LIVEZILLA"))
	die();
	
require(LIVEZILLA_PATH . "_lib/objects.external.inc.php");
require(LIVEZILLA_PATH . "_lib/functions.external.inc.php");

if(isset($_POST[POST_EXTERN_SERVER_ACTION]))
{
	languageSelect($CONFIG["gl_default_language"]);
	getData(false,true,false,true);
	$externalUser = new UserExternal($_POST[POST_EXTERN_USER_USERID]);
	$externalUser->ExternalStatic = new ExternalStatic($externalUser->UserId);
	array_push($externalUser->Browsers,new ExternalChat($externalUser->UserId,$_POST[POST_EXTERN_USER_BROWSERID]));
	
	define("IS_FILTERED",$FILTERS->Match(getServerParam("REMOTE_ADDR"),formLanguages(((getServerParam("HTTP_ACCEPT_LANGUAGE") != null) ? getServerParam("HTTP_ACCEPT_LANGUAGE") : "")),$_POST[POST_EXTERN_USER_USERID]));
	define("IS_FLOOD",(!dataSetExists($externalUser->Browsers[0]->SessionFile) && isFlood()));
	define("CALL_ID",getId(7));
	
	if(dataSetExists($externalUser->Browsers[0]->SessionFile))
		$externalUser->Browsers[0]->Load();
		
	$externalUser->ExternalStatic->Language = (getServerParam("HTTP_ACCEPT_LANGUAGE") != null) ? getServerParam("HTTP_ACCEPT_LANGUAGE") : "";
	$externalUser->Browsers[0]->LoadChat($CONFIG,null);
	
	if($_POST[POST_EXTERN_SERVER_ACTION] == EXTERN_ACTION_LISTEN)
		$externalUser = listen($externalUser);
	else if($_POST[POST_EXTERN_SERVER_ACTION] == EXTERN_ACTION_MAIL)
	{
		getData(false,true,false,false);
		if($externalUser->SaveMail(AJAXDecode($_POST[POST_EXTERN_USER_GROUP])) && ($CONFIG["gl_scom"] != null || $CONFIG["gl_sgom"] != null))
			$externalUser->SendCopyOfMail(AJAXDecode($_POST[POST_EXTERN_USER_GROUP]),$CONFIG,$GROUPS);
	}
	else if($_POST[POST_EXTERN_SERVER_ACTION] == EXTERN_ACTION_RATE)
	{
		getData(true,false,false,false);
		$externalUser->SaveRate(AJAXDecode($_POST[POST_EXTERN_REQUESTED_INTERNID]));
	}
	else
	{
		if($externalUser->Browsers[0]->Chat != null)
		{
			$externalUser->Browsers[0]->DestroyChatFiles();
			$externalUser->Browsers[0]->Chat->ExternalDestroy();
		}
		
		$externalUser->Browsers[0]->Waiting = false;
		$externalUser->Browsers[0]->WaitingMessageDisplayed = null;
		if($_POST[POST_EXTERN_SERVER_ACTION] == EXTERN_ACTION_RELOAD_GROUPS)
		{
			$externalUser = reloadGroups($externalUser);
		}
	}

	if(!dataSetExists($externalUser->ExternalStatic->SessionFile) && isset($_POST[POST_EXTERN_RESOLUTION_WIDTH]))
		createStaticFile($externalUser,Array($_POST[POST_EXTERN_RESOLUTION_WIDTH],$_POST[POST_EXTERN_RESOLUTION_HEIGHT]),$_POST[POST_EXTERN_COLOR_DEPTH],$_POST[POST_EXTERN_TIMEZONE_OFFSET],@$_POST[GEO_LATITUDE],@$_POST[GEO_LONGITUDE],@$_POST[GEO_COUNTRY_ISO_2],@$_POST[GEO_CITY],@$_POST[GEO_REGION],@$_POST[GEO_TIMEZONE],null,0);

	if(isset($_POST[GET_TRACK_SPECIAL_AREA_CODE]))
		$externalUser->Browsers[0]->Code = $_POST[GET_TRACK_SPECIAL_AREA_CODE];
	if(IS_FILTERED)
		$externalUser->Browsers[0]->Destroy($CONFIG);
	else
		$externalUser->Browsers[0]->Save();
		
	$EXTERNSCRIPT = $externalUser->Response;
}
?>
