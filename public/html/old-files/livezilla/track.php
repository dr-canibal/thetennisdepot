<?php

/****************************************************************************************
* LiveZilla track.php
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

require(LIVEZILLA_PATH . "_lib/functions.tracking.inc.php");

if(!getAvailability())
	die();

if(isset($_GET[GET_TRACK_USERID]) && !isnull($_GET[GET_TRACK_USERID]))
{
	define("CALLER_BROWSER_ID",$_GET[GET_TRACK_BROWSERID]);
	define("CALLER_USER_ID",$_GET[GET_TRACK_USERID]);

	if(getCookieValue("userid") == null || (getCookieValue("userid") != null && getCookieValue("userid") != CALLER_USER_ID))
		setCookieValue("userid",CALLER_USER_ID);
}
else if(!isnull(getCookieValue("userid")))
{
	define("CALLER_BROWSER_ID",getId(USER_ID_LENGTH));
	define("CALLER_USER_ID",getCookieValue("userid"));
}
if(!defined("CALLER_USER_ID"))
{
	define("CALLER_USER_ID",getId(USER_ID_LENGTH));
	define("CALLER_BROWSER_ID",getId(USER_ID_LENGTH));
	define("CALLER_FILE_ID",CALLER_BROWSER_ID .".". EX_BROWSER_SESSION);
	if(getCookieValue("userid") == null || (getCookieValue("userid") != null && getCookieValue("userid") != CALLER_USER_ID))
		setCookieValue("userid",CALLER_USER_ID);
}

$externalUser = new UserExternal(CALLER_USER_ID);
$externalUser->LoadStaticInformation();
if(isset($_GET[GET_TRACK_OUTPUT_TYPE]) && ($_GET[GET_TRACK_OUTPUT_TYPE] == "jscript" || $_GET[GET_TRACK_OUTPUT_TYPE] == "jcrpt"))
{
	if(!isset($_GET[GET_TRACK_NO_SEARCH_ENGINE]))
	{
		header("Location: http://www.livezilla.net");
		exit(getFile(TEMPLATE_HTML_SUPPORT));
	}

	$browser = getWebBrowser();
	if(!$browser["allowed"])
		exit();

	$TRACKINGSCRIPT = getFile(TEMPLATE_SCRIPT_GLOBAL) . getFile(TEMPLATE_SCRIPT_TRACK) . getFile(TEMPLATE_SCRIPT_BOX);
	$TRACKINGSCRIPT = str_replace("<!--server-->",LIVEZILLA_URL,$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--area_code-->",(getParam(GET_TRACK_SPECIAL_AREA_CODE) != null) ? getParam(GET_TRACK_SPECIAL_AREA_CODE) : "",$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--browser_id-->",CALLER_BROWSER_ID,$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--user_id-->",CALLER_USER_ID,$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--poll_frequency-->",$CONFIG["poll_frequency_tracking"],$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--timeout-->",$CONFIG["timeout_track"],$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--height-->",$CONFIG["wcl_window_height"],$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--width-->",$CONFIG["wcl_window_width"],$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--geo_url-->",CONFIG_LIVEZILLA_GEO . "?aid=" . $CONFIG["wcl_geo_tracking"],$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--geo_resolute-->",parseBool(!isSSpanFile() && !dataSetExists($externalUser->ExternalStatic->SessionFile) && !isnull($CONFIG["wcl_geo_tracking"]) && (isnull(getCookieValue("geo_data")) || (!isnull(getCookieValue("geo_data")) && getCookieValue("geo_data") < time()-2592000))),$TRACKINGSCRIPT);
	$TRACKINGSCRIPT = str_replace("<!--alert_html-->",base64_encode(str_replace("<!--server-->",LIVEZILLA_URL,getFile(TEMPLATE_SCRIPT_ALERT))),$TRACKINGSCRIPT);
	
	if(!isnull(getCookieValue("login_name")) && !isnull(getParam(GET_EXTERN_USER_NAME)))
		setCookieValue("login_name",getParam(GET_EXTERN_USER_NAME));
	$TRACKINGSCRIPT = str_replace("<!--user_name-->",getParam(GET_EXTERN_USER_NAME),$TRACKINGSCRIPT);
	
	if(!isnull(getCookieValue("login_email")) && !isnull(getParam(GET_EXTERN_USER_EMAIL)))
		setCookieValue("login_email",getParam(GET_EXTERN_USER_EMAIL));
	$TRACKINGSCRIPT = str_replace("<!--user_email-->",getParam(GET_EXTERN_USER_EMAIL),$TRACKINGSCRIPT);
	
	if(!isnull(getCookieValue("login_company")) && !isnull(getParam(GET_EXTERN_USER_COMPANY)))
		setCookieValue("login_company",getParam(GET_EXTERN_USER_COMPANY));
	$TRACKINGSCRIPT = str_replace("<!--user_company-->",getParam(GET_EXTERN_USER_COMPANY),$TRACKINGSCRIPT);
}
else
{
	$TRACKINGSCRIPT = "lz_tracking_set_sessid(\"".CALLER_USER_ID."\",\"".CALLER_BROWSER_ID."\");";
	if(isset($_GET[GET_TRACK_BROWSERID]) && isset($_GET[GET_TRACK_START]) && isset($_GET[GET_TRACK_URL]))
	{
		$BROWSER = new ExternalBrowser(CALLER_BROWSER_ID,CALLER_USER_ID);
		
		if(!isnull(getParam(GET_EXTERN_USER_NAME)))
			$BROWSER->Fullname = utf8_encode(substr(utf8_decode(getParam(GET_EXTERN_USER_NAME)),0,32));
		
		if(!isnull(getParam(GET_EXTERN_USER_EMAIL)))
			$BROWSER->Email = utf8_encode(substr(utf8_decode(getParam(GET_EXTERN_USER_EMAIL)),0,50));
			
		if(!isnull(getParam(GET_EXTERN_USER_COMPANY)))
			$BROWSER->Company = utf8_encode(substr(utf8_decode(getParam(GET_EXTERN_USER_COMPANY)),0,50));
			
		getData(false,false,(rand(0,1)==10),true);
		define("IS_FILTERED",$FILTERS->Match(getServerParam("REMOTE_ADDR"),formLanguages(((getServerParam("HTTP_ACCEPT_LANGUAGE") != null) ? getServerParam("HTTP_ACCEPT_LANGUAGE") : "")),CALLER_USER_ID));
		define("IS_FLOOD",(!dataSetExists($BROWSER->SessionFile) && isFlood()));

		if(!getAvailability() || IS_FILTERED || IS_FLOOD)
		{
			$BROWSER->Destroy();
			exit("lz_tracking_stop_tracking();");
		}
		if(dataSetExists($BROWSER->SessionFile))
			$BROWSER->Load();

		$count = count($BROWSER->History);
		if(!dataSetExists($externalUser->ExternalStatic->SessionFile))
			createStaticFile($externalUser,Array(@$_GET[GET_TRACK_RESOLUTION_WIDTH],@$_GET[GET_TRACK_RESOLUTION_HEIGHT]),@$_GET[GET_TRACK_COLOR_DEPTH],@$_GET[GET_TRACK_TIMEZONE_OFFSET],@$_GET[GEO_LATITUDE],@$_GET[GEO_LONGITUDE],@$_GET[GEO_COUNTRY_ISO_2],@$_GET[GEO_CITY],@$_GET[GEO_REGION],@$_GET[GEO_TIMEZONE],@$_GET[GEO_SSPAN],@$_GET[GEO_RESULT_ID]);

		$TRACKINGSCRIPT .= processActions($BROWSER);
		$TRACKINGSCRIPT .= "lz_tracking_callback(" . $CONFIG["poll_frequency_tracking"] . ",'" . $_GET[GET_TRACK_START] . "');";

		$BROWSER->LastActive = time();
		if($BROWSER->FirstActive == null)
			$BROWSER->FirstActive = time();
			
		$BROWSER->Referrer = (!isset($BROWSER->Referrer)) ? isset($_GET[GET_TRACK_REFERRER]) ? (isBase64($_GET[GET_TRACK_REFERRER])) ? trim(slashesStrip(base64_decode($_GET[GET_TRACK_REFERRER]))) : trim(slashesStrip($_GET[GET_TRACK_REFERRER])) : "" : $BROWSER->Referrer;
		$url = (isset($_GET[GET_TRACK_URL])) ? (isBase64($_GET[GET_TRACK_URL])) ? substr(base64_decode($_GET[GET_TRACK_URL]),0,1024) : substr($_GET[GET_TRACK_URL],0,1024) : "";
		
		if(!isset($BROWSER->History))
			$BROWSER->History = array();
		if(count($BROWSER->History) == 0 || (count($BROWSER->History) > 0 && $BROWSER->History[count($BROWSER->History)-1][1] != $url))
			$BROWSER->History[] = array(time() ,$url ,((isset($_GET[GET_TRACK_SPECIAL_AREA_CODE])) ? utf8_encode(substr(utf8_decode($_GET[GET_TRACK_SPECIAL_AREA_CODE]),0,512)) : ""),false);

		if(count($BROWSER->History) > DATA_URL_STORAGE_AMOUNT)
		{
			array_shift($BROWSER->History);
			define("ARRAY_MAX_SIZE",true);
		}
		if($count != count($BROWSER->History) || defined("ARRAY_MAX_SIZE"))
			$BROWSER->Save();
		else
			$BROWSER->KeepAlive();

		if(isset($CONFIG["gl_hide_inactive"]) && $CONFIG["gl_hide_inactive"] && $BROWSER->History[count($BROWSER->History)-1][0] < (time()-ACTIVE_TIME))
			exit("lz_tracking_stop_tracking();");
	}
}
?>
