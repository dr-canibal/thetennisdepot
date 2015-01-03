<?php
/****************************************************************************************
* LiveZilla functions.intern.inc.php
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

function validate()
{
	global $INTERNAL,$GROUPS,$RESPONSE,$CONFIG;
	if(!(DATA_SOURCE == DATA_SOURCE_DATABASE && !DB_CONNECTION) || SERVERSETUP)
	{
		if(isset($_POST[POST_INTERN_AUTHENTICATION_USERID]) && isset($_POST[POST_INTERN_AUTHENTICATION_PASSWORD]))
		{
			foreach($INTERNAL as $sysId => $internuser)
			{
				if(strtolower($internuser->UserId) == strtolower($_POST[POST_INTERN_AUTHENTICATION_USERID]))
				{
					$internuser->SetStaticCaller();
					if(isset($_POST[POST_INTERN_NEW_PASSWORD]))
						$INTERNAL[CALLER_SYSTEM_ID]->ChangePassword($_POST[POST_INTERN_NEW_PASSWORD]);
					if(LOGIN || SERVERSETUP)
					{
						$INTERNAL[CALLER_SYSTEM_ID]->LoadPassword();
						if(isset($_POST[POST_INTERN_VERSION]) && VERSION != $_POST[POST_INTERN_VERSION])
							define("INVALID_CLIENT_VERSION",true);
					}
					if(isset($INTERNAL[CALLER_SYSTEM_ID]->Password) && $INTERNAL[CALLER_SYSTEM_ID]->Password == md5($_POST[POST_INTERN_AUTHENTICATION_PASSWORD]))
					{
						if(LOGIN && !SERVERSETUP && $INTERNAL[CALLER_SYSTEM_ID]->LastActive != null && $INTERNAL[CALLER_SYSTEM_ID]->LastActive > (time()-$CONFIG["timeout_clients"]))
						{
							if(isset($INTERNAL[CALLER_SYSTEM_ID]->LoginId) && $_POST[POST_INTERN_AUTHENTICATION_LOGINID] != $INTERNAL[CALLER_SYSTEM_ID]->LoginId)
							{
								define("AUTH_RESULT",LOGIN_REPLY_ALREADY_ONLINE);
								break;
							}
						}
						if(!SERVERSETUP && LOGIN && $INTERNAL[CALLER_SYSTEM_ID]->IsPasswordChangeNeeded())
						{
							define("AUTH_RESULT",LOGIN_REPLY_CHANGE_PASS);
							break;
						}
						
						if(SERVERSETUP && $INTERNAL[CALLER_SYSTEM_ID]->Level != USER_LEVEL_ADMIN)
						{
							define("AUTH_RESULT",LOGIN_REPLY_NOADMIN);
							break;
						}
						define("VALIDATED",true);
						if(!LOGOFF && isset($_POST[POST_INTERN_AUTHENTICATION_LOGINID]))
							$INTERNAL[CALLER_SYSTEM_ID]->LoginId = $_POST[POST_INTERN_AUTHENTICATION_LOGINID];
						elseif(LOGOFF)
							$INTERNAL[CALLER_SYSTEM_ID]->LoginId = null;
						
						define("AUTH_RESULT",LOGIN_REPLY_SUCCEEDED);
	
						if(isset($_POST[POST_GLOBAL_TYPING]) && $_POST[POST_INTERN_SERVER_ACTION] == INTERN_ACTION_LISTEN)
							$INTERNAL[CALLER_SYSTEM_ID]->Typing = $_POST[POST_GLOBAL_TYPING];
						break;
					}
					else
					{
						if($INTERNAL[CALLER_SYSTEM_ID]->LastActive < (time()-$CONFIG["timeout_clients"]))
							$INTERNAL[CALLER_SYSTEM_ID]->Destroy();
						break;
					}
				}
				$INTERNAL[$sysId] = $internuser;
			}
		}
	}
	else
		define("AUTH_RESULT",LOGIN_REPLY_DB);
	
	if(defined("VALIDATED") && LOGIN)
	{
		$INTERNAL[CALLER_SYSTEM_ID]->IP = getServerParam("REMOTE_ADDR");
		$INTERNAL[CALLER_SYSTEM_ID]->FirstActive = time();
		$INTERNAL[CALLER_SYSTEM_ID]->VisitorFileSizes = array();
		$INTERNAL[CALLER_SYSTEM_ID]->VisitorStaticReload = array();
		$RESPONSE->Login = $INTERNAL[CALLER_SYSTEM_ID]->GetLoginReply($GROUPS[$INTERNAL[CALLER_SYSTEM_ID]->Groups[0]]["gr_extern"],getTimeDifference($_POST[POST_INTERN_CLIENT_TIME]));
	}
	if(!defined("AUTH_RESULT"))
		define("AUTH_RESULT",LOGIN_REPLY_BAD_COMBINATION);
}

function receiveFile($id = FILE_ACTION_NONE)
{
	global $RESPONSE;
	if(isset($_POST[POST_INTERN_FILE_TYPE]) && $_POST[POST_INTERN_FILE_TYPE] == FILE_TYPE_USERFILE)
	{
		if(!@is_dir(PATH_UPLOADS_INTERNAL.$_POST[POST_INTERN_AUTHENTICATION_USERID]."/"))
		{
			if(!mkdir(PATH_UPLOADS_INTERNAL.($_POST[POST_INTERN_AUTHENTICATION_USERID])))
				$id = FILE_ACTION_ERROR;
			else
				createFile(PATH_UPLOADS_INTERNAL.($_POST[POST_INTERN_AUTHENTICATION_USERID])."/".FILE_INDEX,"",true);
		}
		if(move_uploaded_file($_FILES["file"]["tmp_name"], (PATH_UPLOADS_INTERNAL.$_POST[POST_INTERN_AUTHENTICATION_USERID])."/".$_FILES["file"]["name"]))
			$id = FILE_ACTION_SUCCEEDED;
		else
			$id = FILE_ACTION_ERROR;
	}
	$RESPONSE->SetStandardResponse($id,"");
}

function removeFile($id = FILE_ACTION_NONE)
{
	global $RESPONSE;
	if(SERVERSETUP && isset($_POST[POST_INTERN_FILE_TYPE]) && $_POST[POST_INTERN_FILE_TYPE] == FILE_TYPE_ADMIN_BANNER)
	{
		$files = explode(";",$_POST[POST_INTERN_UPLOAD_VALUE]);
		foreach($files as $file)
			if(file_exists(PATH_BANNER . base64_decode($file)))
				if(unlink(PATH_BANNER . base64_decode($file)))
					$id = FILE_ACTION_SUCCEEDED;
				else
					$id = FILE_ACTION_ERROR;
	}
	else if(isset($_POST[POST_INTERN_FILE_TYPE]) && $_POST[POST_INTERN_FILE_TYPE] == FILE_TYPE_CARRIERLOGO)
	{
		if(file_exists(FILE_CARRIERLOGO))
		{
			if(unlink(FILE_CARRIERLOGO))
				$id = FILE_ACTION_SUCCEEDED;
			else
				$id = FILE_ACTION_ERROR;
		}
	}
	$RESPONSE->SetStandardResponse($id,"");
}

function processActions()
{
	global $CONFIG;
	require(LIVEZILLA_PATH . "_lib/functions.internal.process.inc.php");
	processAcceptedConversations();
	processAuthentications();
	processStatus();
	processDeleteFiles();
	processClosures();
	processRequests();
	processForwards();
	processGuides();
	processFilters();
	processVisitcards();
	processProfilePictures();
	processWebcamPictures();
	processAlerts();
	processPermissions();
	processClosedTickets();
	processExternalReloads();
	
	if(SERVERSETUP)
		processBannerPictures();
}

function buildSystem()
{
	global $RESPONSE,$INTERNAL,$GROUPS;
	require_once(LIVEZILLA_PATH . "_lib/functions.internal.build.inc.php");
	$INTERNAL[CALLER_SYSTEM_ID]->GetExternalObjects();
	removeChats();
	buildIntern();
	buildExtern();
	buildFiles();
	buildFilters();
	
	if(!LOGIN && !SERVERSETUP)
	{
		buildNewPosts();
		if(!isset($_POST[POST_GLOBAL_SHOUT]))
		{
			$external = $INTERNAL[CALLER_SYSTEM_ID]->IsExternal($GROUPS);
			buildRatings($external);
			buildMessages($external);
			buildArchive($external);
			buildResources();
		}
	}
}

function listenXML($runs = 1)
{
	global $CONFIG,$RESPONSE,$INTERNAL;
	processActions();
	
	if(!SERVERSETUP && !LOGIN && $INTERNAL[CALLER_SYSTEM_ID]->Status == USER_STATUS_OFFLINE)
		return;
		
	$start = time();

	while(time() < $start + getLongPollRuntime() || $runs == 1)
	{
		if($runs > 1)
		{
			getDataSets();
			getData(true,true,true,true);
		}
		$RESPONSE->XML = "<listen disabled=\"".base64_encode(((getAvailability()) ?  "0" : "1" ))."\" h=\"<!--gl_all-->\" ".((isset($_POST[POST_INTERN_XMLCLIP_HASH_EXECUTION_TIME])) ? "ex_time=\"<!--execution_time-->\"" : "").">\r\n";
		$RESPONSE->Typing = "";
		if($RESPONSE->Login != null)
			$RESPONSE->XML .= $RESPONSE->Login;
			
		buildSystem();
		
		if($runs++ == 1)
			processPosts();
		
		if(($hash = substr(md5($RESPONSE->Typing),0,5)) != @$_POST[POST_INTERN_XMLCLIP_HASH_TYPING] && strlen($RESPONSE->Typing) > 0)
			$RESPONSE->XML .= "<gl_typ h=\"".base64_encode($hash)."\">\r\n" . $RESPONSE->Typing . "</gl_typ>\r\n";
		if(($hash = substr(md5($RESPONSE->Exceptions),0,5)) != @$_POST[POST_INTERN_XMLCLIP_HASH_ERRORS] && strlen($RESPONSE->Exceptions) > 0)
			$RESPONSE->XML .= "<gl_e h=\"".base64_encode($hash)."\">\r\n" . $RESPONSE->Exceptions . "</gl_e>\r\n";
		if(($hash = substr(md5($RESPONSE->Internals),0,5)) != @$_POST[POST_INTERN_XMLCLIP_HASH_INTERN] && strlen($RESPONSE->Internals) > 0)
			$RESPONSE->XML .= "<int_r h=\"".base64_encode($hash)."\">\r\n" . $RESPONSE->Internals . "</int_r>\r\n";
		if(($hash = substr(md5($RESPONSE->Groups),0,5)) != @$_POST[POST_INTERN_XMLCLIP_HASH_GROUPS] && strlen($RESPONSE->Groups) > 0)
			$RESPONSE->XML .= "<int_d h=\"".base64_encode($hash)."\">\r\n" . $RESPONSE->Groups . "</int_d>\r\n";
		if(($hash = substr(md5($RESPONSE->InternalFiles),0,5)) != @$_POST[POST_INTERN_XMLCLIP_HASH_FILES])
			$RESPONSE->XML .= "<int_f h=\"".base64_encode($hash)."\">\r\n" . $RESPONSE->InternalFiles . "</int_f>\r\n";
		if(($hash = substr(md5($RESPONSE->InternalVcards),0,5)) != @$_POST[POST_INTERN_XMLCLIP_HASH_VISITCARDS])
			$RESPONSE->XML .= "<int_v h=\"".base64_encode($hash)."\">\r\n" . $RESPONSE->InternalVcards . "</int_v>\r\n";
		if(($hash = substr(md5($RESPONSE->InternalProfilePictures),0,5)) != @$_POST[POST_INTERN_XMLCLIP_HASH_PICTURES_PROFILE])
			$RESPONSE->XML .= "<int_pp h=\"".base64_encode($hash)."\">\r\n" . $RESPONSE->InternalProfilePictures . "</int_pp>\r\n";
		if(($hash = substr(md5($RESPONSE->InternalWebcamPictures),0,5)) != @$_POST[POST_INTERN_XMLCLIP_HASH_PICTURES_WEBCAM])
			$RESPONSE->XML .= "<int_wp h=\"".base64_encode($hash)."\">\r\n" . $RESPONSE->InternalWebcamPictures . "</int_wp>\r\n";
		if(($hash = substr(md5($RESPONSE->Filter),0,5)) != @$_POST[POST_INTERN_XMLCLIP_HASH_FILTERED])
			$RESPONSE->XML .= "<ext_b h=\"".base64_encode($hash)."\">\r\n" . $RESPONSE->Filter . "</ext_b>\r\n";
		if(($hash = substr(md5($RESPONSE->Tracking),0,5)) != @$_POST[POST_INTERN_XMLCLIP_HASH_EXTERN])
			$RESPONSE->XML .= "<ext_u h=\"".base64_encode($hash)."\">\r\n" . $RESPONSE->Tracking . "</ext_u>\r\n";
		if($RESPONSE->Archive != null)
			$RESPONSE->XML .= "<ext_c>\r\n" . $RESPONSE->Archive . "</ext_c>\r\n";
		if($RESPONSE->Resources != null)
			$RESPONSE->XML .= "<ext_res>\r\n" . $RESPONSE->Resources . "</ext_res>\r\n";
		if($RESPONSE->Ratings != null)
			$RESPONSE->XML .= "<ext_r>\r\n" . $RESPONSE->Ratings . "</ext_r>\r\n";
		if($RESPONSE->Messages != null)
			$RESPONSE->XML .= "<ext_m>\r\n" . $RESPONSE->Messages . "</ext_m>\r\n";
		if(strlen($RESPONSE->Authentications) > 0)
			$RESPONSE->XML .= "<gl_auths>\r\n" . $RESPONSE->Authentications . "\r\n</gl_auths>\r\n";
		if(strlen($RESPONSE->Posts)>0)
			$RESPONSE->XML .=  "<usr_p>\r\n" . $RESPONSE->Posts . "</usr_p>\r\n";
		if(isset($_POST[POST_INTERN_ACCESSTEST]))
			$RESPONSE->XML .= "<permission>" . base64_encode(getFolderPermissions()) . "</permission>";
	
		if(SERVERSETUP || LOGIN || $INTERNAL[CALLER_SYSTEM_ID]->LastActive <= @filemtime(FILE_CONFIG))
			$RESPONSE->XML .= getConfig();
			
		$RESPONSE->XML .= "</listen>";
		
		if(substr_count($RESPONSE->XML,"<") > 4 || $INTERNAL[CALLER_SYSTEM_ID]->Status == USER_STATUS_OFFLINE || isset($_POST[POST_GLOBAL_NO_LONG_POLL]))
		{
			break;
		}
		else
		{
			if(isset($_POST[POST_GLOBAL_SHOUT]))
				break;
			$wait = max($CONFIG["poll_frequency_clients"]-1,1);
			if(time()+$wait <= $start + getLongPollRuntime())
			{
				sleep($wait);
			}
			else
				break;
		}
	}
}

function getConfig()
{
	global $CONFIG;
	$xml = "<gl_c h=\"".base64_encode(substr(md5file(FILE_CONFIG),0,5))."\">\r\n";
	foreach($CONFIG as $key => $val)
	{
		if(is_array($val))$val = implode(";",$val);
		$xml .= "<conf value=\"".base64_encode($val)."\" />\r\n";
	}
	
	if(SERVERSETUP && file_exists(FILE_CARRIERLOGO))
		$xml .= "<carrier_logo content=\"".fileToBase64(FILE_CARRIERLOGO)."\" />\r\n";
	if(SERVERSETUP && file_exists(FILE_INVITATIONLOGO))
		$xml .= "<invitation_logo content=\"".fileToBase64(FILE_INVITATIONLOGO)."\" />\r\n";
		
	$xml .= "<php_cfg_vars post_max_size=\"".base64_encode(cfgFileSizeToBytes((@get_cfg_var("post_max_size")!=NULL)?get_cfg_var("post_max_size"):MAX_POST_SIZE_SAFE_MODE))."\" upload_max_filesize=\"".base64_encode(cfgFileSizeToBytes((@get_cfg_var("upload_max_filesize")!=NULL)?get_cfg_var("upload_max_filesize"):MAX_UPLOAD_SIZE_SAFE_MODE))."\" />\r\n";
	$xml .= "</gl_c>\r\n";
	return $xml;
}

function removeChats()
{
	global $INTERNAL,$CONFIG;
	foreach($INTERNAL[CALLER_SYSTEM_ID]->ExternalChats as $chat)
	{
		if(!dataSetExists($chat->ExternalUser->SessionFile) || $chat->Closed && getDataSetTime($chat->ExternalUser->SessionFile) < (time()-$CONFIG["timeout_clients"]))
		{
			$chat->InternalDestroy();
			$chat->ExternalUser->Destroy($CONFIG);
		}
	}
}

function getFolderPermissions($message=null)
{
	$directories = Array(PATH_DATA,PATH_UPLOADS_INTERNAL,PATH_INTERN_IMAGES,PATH_BANNER,PATH_VISITCARDS,PATH_CONFIG,PATH_USERS,PATH_GROUPS,PATH_MESSAGES,PATH_RATINGS);
	foreach($directories as $key => $dir)
	{
		$result = testDirectory($dir);
			if(!$result)
				return 0;
	}
	return 1;
}
?>
