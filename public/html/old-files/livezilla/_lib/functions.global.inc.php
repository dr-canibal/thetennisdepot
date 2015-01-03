<?php

/****************************************************************************************
* LiveZilla functions.global.inc.php
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
	
function handleError($_errno, $_errstr, $_errfile, $_errline)
{
	if(error_reporting()!=0)
		errorLog(date("d.m.y H:i") . " ERR# " . $_errno." ".$_errstr." ".$_errfile." IN LINE ".$_errline."\r");
}

function getAvailability()
{
	return (file_exists(FILE_SERVER_DISABLED)) ? false : true;
}

function slashesStrip($_value)
{
	if (@get_magic_quotes_gpc() == 1)
        return stripslashes($_value);
    return $_value; 
}

function getIdle()
{
	if(file_exists(FILE_SERVER_IDLE) && @filemtime(FILE_SERVER_IDLE) < (time()-15))
		@unlink(FILE_SERVER_IDLE);
	return file_exists(FILE_SERVER_IDLE);
}

function getTimeDifference($_time)
{
	global $CONFIG;
	$_time = (time() - $_time);
	if(abs($_time) <= 5)
		$_time = 0;
	return $_time;
}

function isnull($_var)
{
	return ($_var == null || $_var == "");
}

function parseBool($_value)
{
	return ($_value) ? "true" : "false";
}

function namebase($_path)
{
	$file = basename($_path);
	if (strpos($file,'\\') !== false)
	{
		$tmp = preg_split("[\\\]",$file);
		$file = $tmp[count($tmp) - 1];
		return $file;
	}
	else
		return $file;
}

function getScheme()
{
	$scheme = SCHEME_HTTP;
	if(getServerParam("HTTPS") != null && getServerParam("HTTPS") == "on")
		$scheme = SCHEME_HTTP_SECURE;
	else if(getServerParam("SERVER_PORT") != null && getServerParam("SERVER_PORT") == 443)
		$scheme = SCHEME_HTTP_SECURE;
	return $scheme;
}

function doReplacements($_toReplace)
{
	global $CONFIG,$LZLANG;
	if(!isset($LZLANG))
		languageSelect($CONFIG["gl_default_language"]);
	
	$to_replace_nam = Array("lang","config");
	$to_replace_con = Array("lang"=>$LZLANG,"config"=>$CONFIG);
	foreach ($to_replace_nam as $nam_e)
		foreach($to_replace_con[$nam_e] as $short => $value)
			if(!is_array($value))
				$_toReplace = str_replace("<!--".$nam_e."_".$short."-->",$value,$_toReplace);
			else
				foreach($value as $subKey => $subValue)
					$_toReplace = str_replace("<!--".$nam_e."_".$subKey."-->",$subValue,$_toReplace);
	return $_toReplace;
}

function processHeaderValues()
{
	if(getServerParam("HTTP_INTERN_AUTHENTICATION_USERID") != null)
	{
		$_POST[POST_INTERN_AUTHENTICATION_USERID] = base64_decode(getServerParam("HTTP_INTERN_AUTHENTICATION_USERID"));
		$_POST[POST_INTERN_AUTHENTICATION_PASSWORD] = base64_decode(getServerParam("HTTP_INTERN_AUTHENTICATION_PASSWORD"));
		$_POST[POST_INTERN_FILE_TYPE] = getServerParam("HTTP_INTERN_FILE_TYPE");
		$_POST[POST_SERVER_REQUEST_TYPE] = getServerParam("HTTP_SERVER_REQUEST_TYPE");
		$_POST[POST_INTERN_SERVER_ACTION] = getServerParam("HTTP_INTERN_SERVER_ACTION");
	}
	if(getServerParam("HTTP_ADMINISTRATE") != null)
		$_POST[POST_INTERN_ADMINISTRATE] = getServerParam("HTTP_ADMINISTRATE");
}

function isBase64($_value)
{
    return strpos($_value,":") === false && strpos($_value,".") === false && preg_match("/[A-Za-z=\/\+]+/",$_value);
}


function getServerAddLink($_scheme)
{
	global $CONFIG;
	return PROTOCOL . "://" . base64_encode($_scheme . getServerParam("HTTP_HOST") . str_replace("index.php","",getServerParam("PHP_SELF"))) . "|" . base64_encode($CONFIG["gl_site_name"] . " (" . getServerParam("HTTP_HOST") .")");
}

function getInternSessIdByUserId($_userId)
{
	global $INTERNAL;
	foreach($INTERNAL as $sysId => $intern)
	{
		if($intern->UserId == $_userId)
			return $sysId;
	}
	return null;
}

function md5file($_file)
{
	global $RESPONSE;
	$md5file = @md5_file($_file);
	if(gettype($md5file) != 'boolean' && $md5file != false)
	return $md5file;
}

function getFile($_file,$data="")
{
	if(file_exists($_file))
	{
		$handle = @fopen($_file,"r");
		if($handle)
		{
		   	$data = @fread($handle,@filesize($_file));
			@fclose ($handle);
		}
		return $data;
	}
}

function removeXSS($_val)
{
   $_val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $_val);
   $search = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#$%^&*()~`";:?+/={}[]-_|\'\\';
   for ($i = 0; $i < strlen($search); $i++) 
   {
      $_val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $_val);
      $_val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $_val);
   }
   $tags = Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
   $events = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
   $objects = array_merge($tags, $events);
   $found = true;
   while ($found == true)
   {
      $_val_before = $_val;
      for ($i = 0; $i < sizeof($objects); $i++) 
	  {
         $pattern = '/';
         for ($j = 0; $j < strlen($objects[$i]); $j++)
		 {
            if ($j > 0) 
			{
               $pattern .= '(';
               $pattern .= '(&#[xX]0{0,8}([9ab]);)';
               $pattern .= '|';
               $pattern .= '|(&#0{0,8}([9|10|13]);)';
               $pattern .= ')*';
            }
            $pattern .= $objects[$i][$j];
         }
         $pattern .= '/i';
         $replacement = substr($objects[$i], 0, 2).'<x>'.substr($objects[$i], 2);
         $_val = preg_replace($pattern, $replacement, $_val);
         if($_val_before == $_val)
            $found = false;
      }
   }
   return $_val;
}

function getParam($_getParam)
{
	global $GETVALUES;
	if(isset($GETVALUES[$_getParam]))
		return $GETVALUES[$_getParam];
	if(isset($_GET[$_getParam]))
	{
		$val = removeXSS($_GET[$_getParam]);
		$GETVALUES[$_getParam] = $val;
		return $val;
	}
	return null;
}

function getParams($_getParams="")
{
	foreach($_GET as $key => $value)
		if($key != "template")
			$_getParams.=((strlen($_getParams) == 0) ? $_getParams : "&") . urlencode($key) ."=" . urlencode($value);
	return $_getParams;
}

function getServerParam($_serverParam)
{
	global $SERVERVALUES;
	if(isset($SERVERVALUES[$_serverParam]))
		return $SERVERVALUES[$_serverParam];
	if(isset($_SERVER[$_serverParam]))
	{
		$val = removeXSS($_SERVER[$_serverParam]);
		$SERVERVALUES[$_serverParam] = $val;
		return $val;
	}
	return null;
}

function cfgFileSizeToBytes($_configValue) 
{
   $_configValue = trim($_configValue);
   $last = strtolower($_configValue{strlen($_configValue)-1});
   switch($last) 
   {
       case 'g':
           $_configValue *= 1024;
       case 'm':
           $_configValue *= 1024;
       case 'k':
           $_configValue *= 1024;
   }
   return floor($_configValue);
}

function AJAXDecode($value)
{
    return slashesStrip(trim(preg_replace('/%([0-9a-f]{2})/ie', 'chr(hexdec($1))',utf8_decode($value))));
}

function createFolder($_folder)
{
	$folders = explode("/",$_folder);
	$path = ".";
	foreach($folders as $folder)
	{
		if($folder === "" || $folder === ".")
			continue;
			
		$path.="/".$folder;
		if(!@is_dir($path))
			@mkdir($path);
	}
}

function createFile($_filename,$_content,$_recreate)
{
	if(strpos($_filename,"..") === false)
	{
		if(file_exists($_filename))
		{
			if($_recreate)
				@unlink($_filename);
			else
				return 0;
		}
		$handle = @fopen($_filename,"w");
		if(strlen($_content)>0)
			@fputs($handle,$_content);
		@fclose($handle);
		return 1;
	}
	return 0;
}

function base64ToFile($_filename,$_content)
{
	if(file_exists($_filename))
		@unlink($_filename);
	$handle = @fopen($_filename,"wb");
	@fputs($handle,base64_decode($_content));
	@fclose($handle);
}

function fileToBase64($_filename)
{
	if(@filesize($_filename) == 0)
		return "";
	$handle = @fopen($_filename,"rb");
	$content = @fread($handle,@filesize($_filename));
	@fclose($handle);
	return base64_encode($content);
}

function getData($_int,$_gr,$_vi,$_ft)
{
	if($_int)buildInternUsers();
	if($_gr)buildGroups();
	if($_vi)buildTracking();
	if($_ft)buildFilter();
}

function buildFilter()
{
	global $FILTERS;
	$FILTERS = new FilterSystem();
	$FILTERS->Populate();
}

function buildInternUsers()
{
	global $CONFIG,$INTERNAL;
	require(PATH_USERS . "intern.inc.php");
	foreach($INTERN as $sysId => $internaluser)
	{
		$INTERNAL[$sysId] = new UserInternal($sysId,$internaluser["in_id"]);
		$INTERNAL[$sysId]->Email = $internaluser["in_email"];
		$INTERNAL[$sysId]->Webspace = $internaluser["in_websp"];
		$INTERNAL[$sysId]->Level = $internaluser["in_level"];
		$INTERNAL[$sysId]->Description = $internaluser["in_desc"];
		$INTERNAL[$sysId]->Fullname = $internaluser["in_name"];
		$INTERNAL[$sysId]->Groups = unserialize(base64_decode($internaluser["in_groups"]));
		
		array_walk($INTERNAL[$sysId]->Groups,"b64dcode");

		$INTERNAL[$sysId]->GroupsArray = $internaluser["in_groups"];
		$INTERNAL[$sysId]->PermissionSet = $internaluser["in_perms"];
		$INTERNAL[$sysId]->Status = USER_STATUS_OFFLINE;
		
		if(DATA_SOURCE == DATA_SOURCE_FILE)
		{
			if(!@is_dir(PATH_DATA_INTERNAL))
				mkdir(PATH_DATA_INTERNAL);
		
			if(!@is_dir($INTERNAL[$sysId]->Folder))
				mkdir($INTERNAL[$sysId]->Folder);
		
			if(!@is_dir($INTERNAL[$sysId]->FolderInbox))
				mkdir($INTERNAL[$sysId]->FolderInbox);
		
			if(dataSetExists($INTERNAL[$sysId]->SessionFile) && getDataSetTime($INTERNAL[$sysId]->SessionFile) > time()-$CONFIG["timeout_clients"])
			{
				$INTERNAL[$sysId]->LastActive = getDataSetTime($INTERNAL[$sysId]->SessionFile);
				$INTERNAL[$sysId]->Load();
				if($INTERNAL[$sysId]->FileError != null)
					$INTERNAL[$sysId]->Load();
			}
		}
		else
		{
			if(getDataSetTime($INTERNAL[$sysId]->SessionFile) > time()-$CONFIG["timeout_clients"])
			{
				$INTERNAL[$sysId]->LastActive = getDataSetTime($INTERNAL[$sysId]->SessionFile);
				$INTERNAL[$sysId]->Load();
			}
		}
		
		if(file_exists($INTERNAL[$sysId]->WebcamFile) && (@filemtime($INTERNAL[$sysId]->WebcamFile) < time() - ($CONFIG["poll_frequency_clients"]+5)))
			@unlink($INTERNAL[$sysId]->WebcamFile);
	}
}

function b64dcode(&$_a,$_b)
{
	$_a = base64_decode($_a);
}

function buildGroups()
{
	global $GROUPS,$CONFIG;
	require(PATH_GROUPS . "groups.inc.php");
	foreach($GROUPS as $id => $group)
	{
		$GROUPS[$id]["gr_desc_array"] = $GROUPS[$id]["gr_desc"];
		$descriptions = unserialize(base64_decode($group["gr_desc"]));
		if(defined("DEFAULT_BROWSER_LANGUAGE") && isset($descriptions[strtoupper(DEFAULT_BROWSER_LANGUAGE)]))
			$GROUPS[$id]["gr_desc"] = base64_decode($descriptions[strtoupper(DEFAULT_BROWSER_LANGUAGE)]);
		else if(isset($descriptions[strtoupper($CONFIG["gl_default_language"])]))
			$GROUPS[$id]["gr_desc"] = base64_decode($descriptions[strtoupper($CONFIG["gl_default_language"])]);
		else if(isset($descriptions["EN"]))
			$GROUPS[$id]["gr_desc"] = base64_decode($descriptions["EN"]);
		else
			$GROUPS[$id]["gr_desc"] =  base64_decode(current($descriptions));
			
		$GROUPS[$id]["gr_extern"] = ($GROUPS[$id]["gr_extern"] == 1);
	}
}

function operatorsAvailable($_amount = 0, $_exclude=null)
{
	global $CONFIG,$INTERNAL,$GROUPS;
	getData(true,true,false,false);
	foreach($INTERNAL as $sysId => $internaluser)
		if($internaluser->IsExternal($GROUPS, $_exclude) && $internaluser->Status < USER_STATUS_OFFLINE)
			$_amount++;
	return $_amount;
}

function getOperatorList()
{
	global $CONFIG,$INTERNAL,$GROUPS;
	$array = array();
	getData(true,true,false,false);
	foreach($INTERNAL as $sysId => $internaluser)
		if($internaluser->IsExternal($GROUPS))
			$array[utf8_decode($internaluser->Fullname)] = $internaluser->Status;
	return $array;
}

function isValidUploadFile($_filename)
{
	global $CONFIG;
	$extensions = explode(",",str_replace("*.","",$CONFIG["wcl_upload_blocked_ext"]));
	foreach($extensions as $ext)
	{
		if(strlen($_filename) > strlen($ext) && substr($_filename,strlen($_filename)-strlen($ext),strlen($ext)) == $ext)
			return false;
	}
	return true;
}

function languageSelect($_default)
{
	global $LZLANG;
	if(getServerParam("HTTP_ACCEPT_LANGUAGE") == null || (getServerParam("HTTP_ACCEPT_LANGUAGE") != null && strpos(getServerParam("HTTP_ACCEPT_LANGUAGE"),"..") === false))
	{
		define("DEFAULT_BROWSER_LANGUAGE",$s_browser_language = ((getServerParam("HTTP_ACCEPT_LANGUAGE") != null) ? strtolower(substr(getServerParam("HTTP_ACCEPT_LANGUAGE"),0,2)) : $_default));
		if(getServerParam("HTTP_ACCEPT_LANGUAGE") != null && strlen(getServerParam("HTTP_ACCEPT_LANGUAGE")) >= 5 && substr(getServerParam("HTTP_ACCEPT_LANGUAGE"),2,1) == "-" && file_exists(LIVEZILLA_PATH . "_language/lang". strtolower(substr(getServerParam("HTTP_ACCEPT_LANGUAGE"),0,5)) .".php"))
			require(LIVEZILLA_PATH . "_language/lang".strtolower(substr(getServerParam("HTTP_ACCEPT_LANGUAGE"),0,5)).".php");
		else if(file_exists(LIVEZILLA_PATH . "_language/lang".$s_browser_language.".php"))
			require(LIVEZILLA_PATH . "_language/lang".$s_browser_language.".php");
		else if(file_exists(LIVEZILLA_PATH . "_language/lang".$_default.".php"))
			require(LIVEZILLA_PATH . "_language/lang".$_default.".php");
	}
	if(!defined("DEFAULT_BROWSER_LANGUAGE"))
	{
		define("DEFAULT_BROWSER_LANGUAGE","en");
		require(LIVEZILLA_PATH . "_language/langen.php");
	}
}

function getLongPollRuntime()
{
	global $CONFIG;
	if(SAFE_MODE)
		$value = 10;
	else
	{
		$value = $CONFIG["timeout_clients"] - $CONFIG["poll_frequency_clients"] - 55;
		if(($ini = @ini_get('max_execution_time')) != null && $ini > $CONFIG["poll_frequency_clients"] && $ini < $value)
			$value = $ini-$CONFIG["poll_frequency_clients"];
		if($value > 20)
			$value = 20;
		if($value < 1)
			$value = 1;
	}
	return $value;
}

function checkPhpVersion($_ist,$_ond,$_ird)
{
	$array = explode(".",phpversion());
	if($array[0] >= $_ist)
	{
		if($array[0] == 5)
			return true;
		else
		{
			if($array[1] > $_ond || ($array[1] == $_ond && $array[2] >= $_ird))
				return true;
		}
		return false;
	}
	return false;
}

function formLanguages($_lang)
{
	if(strlen($_lang) == 0)
		return "";
	$array_lang = explode(",",$_lang);
	foreach($array_lang as $key => $lang)
		if($key == 0)
		{
			$_lang = strtoupper(substr(trim($lang),0,2));
			break;
		}
	return (strlen($_lang) > 0) ? $_lang : "";
}

function logit($_id,$_file=null)
{
	if(isnull($_file))
		$_file = LIVEZILLA_PATH . "_log/debug.txt";
		
	$handle = @fopen ($_file,"a+");
	@fputs($handle,$_id."\r\n");
	@fclose($handle);
}

function errorLog($_message)
{
	global $RESPONSE;
	if(defined("FILE_ERROR_LOG"))
	{
		if(file_exists(FILE_ERROR_LOG) && @filesize(FILE_ERROR_LOG) > 100000)
			@unlink(FILE_ERROR_LOG);
		$handle = @fopen (FILE_ERROR_LOG,"a+");
		if($handle)
		{
			@fputs($handle,$_message . "\r");
			@fclose($handle);
		}
		if(!isset($RESPONSE->Exceptions))
			$RESPONSE->Exceptions = "";
		$RESPONSE->Exceptions .= "<val err=\"".base64_encode(trim($_message))."\" />";
	}
	else
		$RESPONSE->Exceptions = "";
}

function getId($_length,$start=0)
{
	$id = md5(uniqid(rand(),1));
	if($_length != 32)
		$start = rand(0,(31-$_length));
	$id = substr($id,$start,$_length);
	return $id;
}

function createFloodFilter($_user)
{
	global $FILTERS;
	foreach($FILTERS->Filters as $currentFilter)
		if($currentFilter->IP == $_user->ExternalStatic->IP && $currentFilter->Activeipaddress == 1 && $currentFilter->Activestate == 1)
			return;
	
	$filter = new Filter(md5(uniqid(rand())));
	$filter->Creator = "SYSTEM";
	$filter->Created = time();
	$filter->Editor = "SYSTEM";
	$filter->Edited = time();
	$filter->IP = $_user->ExternalStatic->IP;
	$filter->Expiredate = 172800;
	$filter->Userid = $_user->UserId;
	$filter->Reason = "Anti Flood Protection active. Your IP-Address will be saved: " . $_user->ExternalStatic->IP;
	$filter->Filtername = "AUTO FLOOD FILTER";
	$filter->Activestate = 1;
	$filter->Exertion = 0;
	$filter->Languages = "";
	$filter->Activeipaddress = 1;
	$filter->Activeuserid = 0;
	$filter->Activelanguage = 0;
	$filter->Save();
}

function isFlood()
{
	global $VISITOR,$FILTERS,$CONFIG;
	if($CONFIG["gl_atflt"] == null)
		return false;
	if(!isset($VISITOR))
		getData(false,false,true,false);
	$myurls="";
	$count = 0;
	$files = getDirectory(PATH_DATA,".");
	foreach($VISITOR as $visitor)
	{
		if($visitor->ExternalStatic == null)
		{
			$visitor->LoadStaticInformation();
			$visitor->ExternalStatic->Load();
			if($visitor->ExternalStatic->IP == getServerParam("REMOTE_ADDR"))
			{
				foreach($visitor->Browsers as $browser)
				{
					$browser->Load();
					if(!is_array($browser->History))
						continue;
					foreach($browser->History as $key => $value)
						$myurls.="\r\n(" .$browser->FirstActive. ") " . $value[1]."\r";
					if($browser->FirstActive > (time() - FLOOD_PROTECTION_TIME))
					{
						if(++$count >= FLOOD_PROTECTION_SESSIONS)
						{
							createFloodFilter($visitor);
							return true;
						}
					}
				}
			}
		}
	}
	return false;
}

function createStaticFile($_externalUser,$_resolution,$_color,$_timezone,$_lat,$_long,$_countryiso2,$_city,$_region,$_geotimezone,$_geosspan,$_grid)
{
	global $CONFIG;
	if(getCookieValue("userid") == null)
		setCookieValue("visits",$_externalUser->ExternalStatic->Visits = 1);
	else if(getCookieValue("userid") != null)
		setCookieValue("visits",$_externalUser->ExternalStatic->Visits = getCookieValue("visits")+1);
	
	$_externalUser->ExternalStatic->IP = getServerParam("REMOTE_ADDR");
	$_externalUser->ExternalStatic->Host = @gethostbyaddr(getServerParam("REMOTE_ADDR"));
	$_externalUser->ExternalStatic->SystemInfo = ((($userAgent = getServerParam("HTTP_USER_AGENT")) != null) ? $userAgent : "");
	$_externalUser->ExternalStatic->Name = (getCookieValue("login_name") != null) ? getCookieValue("login_name") : "";
	$_externalUser->ExternalStatic->Language = ((($acceptLanguage = getServerParam("HTTP_ACCEPT_LANGUAGE")) != null) ? $acceptLanguage : "");
	$_externalUser->ExternalStatic->Resolution = (is_array($_resolution) && count($_resolution) == 2) ? $_resolution[0] . " x " . $_resolution[1] : "";
	$_externalUser->ExternalStatic->Resolution .= (!isnull($_color)) ? " (" . $_color . " Bit)" : "";
	$_externalUser->ExternalStatic->GeoTimezoneOffset = getLocalTimezone($_timezone);
	
	if(!isnull($CONFIG["wcl_geo_tracking"]))
	{
		if(!isnull($_lat) && base64_decode($_lat) > -180)
		{
			setCookieValue(GEO_LATITUDE,$_externalUser->ExternalStatic->GeoLatitude = base64_decode($_lat));
			setCookieValue(GEO_LONGITUDE,$_externalUser->ExternalStatic->GeoLongitude = base64_decode($_long));
			setCookieValue(GEO_COUNTRY_ISO_2,$_externalUser->ExternalStatic->GeoCountryISO2 = base64_decode($_countryiso2));
			setCookieValue(GEO_CITY,$_externalUser->ExternalStatic->GeoCity = base64_decode($_city));
			setCookieValue(GEO_REGION,$_externalUser->ExternalStatic->GeoRegion = base64_decode($_region));
			setCookieValue(GEO_TIMEZONE,$_externalUser->ExternalStatic->GeoTimezoneOffset = base64_decode($_geotimezone));
			setCookieValue("geo_data",time());
		}
		else if(isset($_lat))
		{
			$_externalUser->ExternalStatic->GeoLatitude = base64_decode($_lat);
			$_externalUser->ExternalStatic->GeoLongitude = base64_decode($_long);
		}
		else if(!isnull(getCookieValue("geo_data")))
		{
			$_externalUser->ExternalStatic->GeoLatitude = getCookieValue(GEO_LATITUDE);
			$_externalUser->ExternalStatic->GeoLongitude = getCookieValue(GEO_LONGITUDE);
			$_externalUser->ExternalStatic->GeoCountryISO2 = getCookieValue(GEO_COUNTRY_ISO_2);
			$_externalUser->ExternalStatic->GeoCity = getCookieValue(GEO_CITY);
			$_externalUser->ExternalStatic->GeoRegion = getCookieValue(GEO_REGION);
			$_externalUser->ExternalStatic->GeoTimezoneOffset = getCookieValue(GEO_TIMEZONE);
		}
		
		if($_grid == 0 && isSSpanFile())
		{
			if(@filemtime(FILE_SERVER_GEO_SSPAN) <= (time()-60))
				$_externalUser->ExternalStatic->GeoResultId = 4;
			else
				$_externalUser->ExternalStatic->GeoResultId = 5;
		}
		else
			$_externalUser->ExternalStatic->GeoResultId = $_grid;
		if(strlen($_externalUser->ExternalStatic->Language) == 2 && !isnull(GEO_COUNTRY_ISO_2))
			$_externalUser->ExternalStatic->Language .= "-" . $_externalUser->ExternalStatic->GeoCountryISO2;
	}
	$_externalUser->ExternalStatic->Save();
	if(!isnull($_geosspan))
		createSSpanFile($_geosspan);
	else
		removeSSpanFile();
}

function removeSSpanFile()
{
	if(@file_exists(FILE_SERVER_GEO_SSPAN) && @filemtime(FILE_SERVER_GEO_SSPAN) <= (time()-86400))
		@unlink(FILE_SERVER_GEO_SSPAN);
}

function isSSpanFile()
{
	return @file_exists(FILE_SERVER_GEO_SSPAN);
}

function createSSpanFile($_sspan)
{
	if(!@file_exists(FILE_SERVER_GEO_SSPAN))
	{
		createFile(FILE_SERVER_GEO_SSPAN,"",false);
		@touch(FILE_SERVER_GEO_SSPAN,time()-(86400-$_sspan));
	}
}

function getLocalTimezone($_timezone,$ltz=0)
{
	$template = "%s%s%s:%s%s";
	if(isset($_timezone) && !isnull($_timezone))
	{
		$ltz = $_timezone;
		if($ltz == ceil($ltz))
		{
			if($ltz >= 0 && $ltz < 10)
				$ltz = sprintf($template,"+","0",$ltz,"0","0");
			else if($ltz < 0 && $ltz > -10)
				$ltz = sprintf($template,"-","0",$ltz*-1,"0","0");
			else if($ltz >= 10)
				$ltz = sprintf($template,"+",$ltz,"","0","0");
			else if($ltz <= -10)
				$ltz = sprintf($template,"",$ltz,"","0","0");
		}
		else
		{
			$split = explode(".",$ltz);
			$split[1] = (60 * $split[1]) / 100;
			if($ltz >= 0 && $ltz < 10)
				$ltz = sprintf($template,"+","0",$split[0],$split[1],"0");
			else if($ltz < 0 && $ltz > -10)
				$ltz = sprintf($template,"","0",$split[0],$split[1],"0");
				
			else if($ltz >= 10)
				$ltz = sprintf($template,"+",$split[0],"",$split[1],"0");
			
			else if($ltz <= -10)
				$ltz = sprintf($template,"",$split[0],"",$split[1],"0");
		}
	}
	return $ltz;
}

function isValidEmail($_email)
{
	return eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $_email);
}

function setCookieValue($_key,$_value)
{
	if(!isset($_COOKIE["livezilla"]))
		$c_array = Array();
	else
		$c_array = @unserialize(@base64_decode($_COOKIE["livezilla"]));
	if(!isset($c_array[$_key]) || (isset($c_array[$_key]) && $c_array[$_key] != $_value))
	{	
		$c_array[$_key] = $_value;
		setcookie("livezilla",($_COOKIE["livezilla"] = base64_encode(serialize($c_array))),time()+6307200);
	}
}

function getCookieValue($_key)
{
	if(isset($_COOKIE["livezilla"]))
		$c_array = @unserialize(@base64_decode($_COOKIE["livezilla"]));
	if(isset($c_array[$_key]))
		return $c_array[$_key];
	else
		return null;
}

function getCookies()
{
	if(isset($_COOKIE["livezilla"]))
		return @unserialize(base64_decode($_COOKIE["livezilla"]));
	else
		return Array();
}

function hashFile($_file)
{
	$enfile = md5(base64_encode(file_get_contents($_file)));
	return $enfile;
}

function bigTime($_time=0)
{
	$time = str_replace(".","",microtime());
	$time = explode(" " , $time);
	if($_time==0)
		return $time[1] . $time[0];
	else
		return $_time . $time[0];
}

function microtimeFloat($_microtime)
{
   list($usec, $sec) = explode(" ", $_microtime);
   return ((float)$usec + (float)$sec);
}

function testDirectory($_dir)
{	
	global $LZLANG,$ERRORS;
	if(!@is_dir($_dir))
		@mkdir($_dir);
	
	if(@is_dir($_dir))
	{
		$fileid = md5(uniqid(rand()));
		$handle = @fopen ($_dir . $fileid ,"a");
		@fputs($handle,$_id."\r\n");
		@fclose($handle);
		
		if(!file_exists($_dir . $fileid))
			return false;
			
		@unlink($_dir . $fileid);
		if(file_exists($_dir . $fileid))
			return false;
			
		return true;
	}
	else
		return false;
}

function sendMail($_receiver,$_sender,$_replyto,$_text,$_subject)
{
	global $CONFIG;
	if(strpos($_receiver,",") === false)
	{
		$message  = $_text;
		$headers  = "From: ".$_sender. "\n";
	    $headers .= "Reply-To: ".$_replyto. "\n";
		$headers .= "MIME-Version: 1.0\n";
		$headers .= "Content-Type: text/plain; charset=UTF-8; format=flowed\n";
		$headers .= "Content-Transfer-Encoding: 8bit\n";
    	$headers .= "X-Mailer: LiveZilla.net/" . VERSION . "\n";
			
		if($CONFIG["gl_smtpauth"] != null)
			authMail($CONFIG["gl_smtphost"], $CONFIG["gl_smtpport"], $_receiver, $_subject, $_text, $headers, $_sender, $CONFIG["gl_smtppass"], $CONFIG["gl_smtpuser"], ($CONFIG["gl_smtpssl"] != null));
		else
			@mail($_receiver, $_subject, $_text, $headers);
	}
	else
	{
		$emails = explode(",",$_receiver);
		foreach($emails as $mail)
			if($mail != null)
				sendMail($mail, $_sender, $_text, $_subject);
	}
}

function authMail($_server, $_port, $_receiver, $_subject, $_text, $_header, $_from, $_password, $_account, $_secure)
{
	$break = "\r\n";
	$_text = preg_replace("/^\./","..",explode($break,$_text));

	$smtp = array(
	array("EHLO localhost".$break,"220,250"),
	array("AUTH LOGIN".$break,"334"),
	array(base64_encode($_account).$break,"334"),
	array(base64_encode($_password).$break,"235")
	);
	
	$smtp[] = array("MAIL FROM: <".$_from.">".$break,"250");
	$smtp[] = array("RCPT TO: <".$_receiver.">".$break,"250");
	$smtp[] = array("DATA".$break,"354");
	$smtp[] = array("Subject: ".$_subject.$break,"");
	$smtp[] = array("To: ".$_receiver.$break,"");
	
	$_header = explode($break,$_header);
	foreach($_header as $value) 
		$smtp[] = array($value.$break,"");

	$smtp[] = array($break,"");
	
	foreach($_text as $line) 
		$smtp[] = array($line.$break,"");

	$smtp[] = array(".".$break,"250");
	$smtp[] = array("QUIT".$break,"221");
	
	$secure = ($_secure) ? "ssl://" : "";

	$fp = @fsockopen($secure . $_server, $_port);
	$banner = @fgets($fp, 1024);

	foreach($smtp as $req)
	{
		@fputs($fp, $req[0]);
		if($req[1])
			while($result = @fgets($fp, 1024))
				if(substr($result,3,1) == " ") 
					break;
	}
	@fclose($fp);
}

function setDataProvider()
{
	global $CONFIG,$DATASETS;
	$DATASETS = array();
	define("DATA_SOURCE",$CONFIG["gl_datprov"]);
	if(DATA_SOURCE == DATA_SOURCE_DATABASE)
	{
		require(LIVEZILLA_PATH . "_lib/functions.data.db.inc.php");
		define("DB_PREFIX",$CONFIG["gl_db_prefix"]);
		createDBConnector();
	}
	else
	{
		$CONFIG["gl_datprov"] = DATA_SOURCE_FILE;
		require(LIVEZILLA_PATH . "_lib/functions.data.file.inc.php");
	}
}
?>
