<?php

/****************************************************************************************
* LiveZilla functions.external.inc.php
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

function listen($_user)
{
	global $CONFIG,$GROUPS,$INTERNAL,$USER,$INTLIST;
	$USER = $_user;
	if(!IS_FILTERED)
	{
		if(isnull($USER->Browsers[0]->Chat))
		{
			getData(true,false,false,false);
			$USER->Browsers[0]->SetCookieGroup();
			
			if(isset($_POST[POST_EXTERN_USER_GROUP]))
			{
				$USER->Browsers[0]->DesiredChatGroup = utf8_decode($_POST[POST_EXTERN_USER_GROUP]);
				$USER->Browsers[0]->SetCookieGroup();
			}
			$response = getIntern();
			login();
			if(count($INTLIST) > 0 && count($USER->Browsers) > 0)
			{
				if($USER->Browsers[0]->Waiting)
				{
					displayWaitingMessage();
				}
				else
				{
					$USER->Browsers[0]->DestroyChatFiles();
					if($CONFIG["gl_alloc_mode"] != ALLOCATION_MODE_ALL || !isnull($USER->Browsers[0]->DesiredChatPartner))
					{
						$USER->Browsers[0]->CreateChat($INTERNAL[$USER->Browsers[0]->DesiredChatPartner]);
						$USER->AddFunctionCall($response,false);
					}
					else
					{
						$USER->AddFunctionCall($response,false);
						foreach($INTLIST as $intid => $am)
						{
							$USER->Browsers[0]->CreateChat($INTERNAL[$intid]);
						}
					}
				}
			}
			if(DATA_SOURCE != DATA_SOURCE_FILE)
				closeOpenChatLog($USER->Browsers[0]->DesiredChatPartner,$USER->Browsers[0]->SystemId);
		}
		else
		{
			activeListen();
		}
	}
	else
		displayFiltered();
	return $USER;
}

function activeListen($runs=1,$picUpdate=false,$isPost=false)
{
	global $CONFIG,$GROUPS,$INTERNAL,$USER,$ISFORWARD;
	$start = time();
	$USER->Browsers[0]->Typing = isset($_POST[POST_EXTERN_TYPING]);
	while($runs == 1)
	{
		isForward();
		processForward();

		if($runs > 1)
		{
			getDataSets();
			getData(false,false,false,true);
			$USER->Browsers[0]->LoadChat($CONFIG,null);
			if(isnull($USER->Browsers[0]->Chat))
			{
				break;
			}
		}
		if($USER->Browsers[0]->Chat->Closed)
		{
			displayQuit();
			return $USER;
		}
		else if($USER->Browsers[0]->Chat->Declined)
		{
			displayDeclined();
			return $USER;
		}
		else if($USER->Browsers[0]->Chat->Activated == CHAT_STATUS_WAITING && !$ISFORWARD)
		{
			$USER->Browsers[0]->WaitingMessageDisplayed = -1;
			beginnConversation();
		}
		else if($USER->Browsers[0]->Chat->Activated != CHAT_STATUS_ACTIVE && !$ISFORWARD)
		{
			displayWaitingMessage();
		}
		if($USER->Browsers[0]->Chat->Activated >= CHAT_STATUS_WAITING && !$ISFORWARD)
		{
			$picUpdate = refreshPicture();
			processTyping();
		}
		
		if($runs == 1 && isset($_POST[POST_EXTERN_USER_FILE_UPLOAD_NAME]) && !isset($_POST[POST_EXTERN_USER_FILE_UPLOAD_ERROR]) && !$ISFORWARD)
			$USER = $USER->Browsers[0]->RequestFileUpload($USER,$_POST[POST_EXTERN_USER_FILE_UPLOAD_NAME]);
		else if($runs == 1 && isset($_POST[POST_EXTERN_USER_FILE_UPLOAD_NAME]) && isset($_POST[POST_EXTERN_USER_FILE_UPLOAD_ERROR]))
			$USER->Browsers[0]->AbortFileUpload($USER,namebase($_POST[POST_EXTERN_USER_FILE_UPLOAD_NAME]),$_POST[POST_EXTERN_USER_FILE_UPLOAD_ERROR]);
	
		if($runs++ == 1 && isset($_POST[POST_GLOBAL_SHOUT]))
			processPosts();
			
		if($USER->Browsers[0]->Chat->Activated == CHAT_STATUS_ACTIVE)
			 $isPost = getPosts();
			 
		if(isset($_POST[POST_GLOBAL_SHOUT]) || isset($_POST[POST_GLOBAL_NO_LONG_POLL]) || $isPost || $ISFORWARD)
		{
			break;
		}
		else if(md5($USER->Response) != $_POST[POST_GLOBAL_XMLCLIP_HASH_ALL] || $picUpdate)
		{
			$_POST[POST_GLOBAL_XMLCLIP_HASH_ALL] = md5($USER->Response);
			$USER->AddFunctionCall("lz_chat_listen_hash('". md5($USER->Response) . "','".getId(5)."');",false);
			break;
		}
		else
		{
			$USER->Response = "";
			$wait = max($CONFIG["poll_frequency_clients"]-1,1);
			if(time()+$wait < $start+getLongPollRuntime())
				sleep($wait);
			else
				break;
		}
	}
}

function isForward()
{
	global $USER,$CONFIG,$ISFORWARD,$ISFORWARDPROCESSED;
	$USER->Browsers[0]->GetForwards();
	$ISFORWARD = (!isnull($USER->Browsers[0]->Forward) && !$USER->Browsers[0]->Forward->Processed);
	$ISFORWARDPROCESSED = (!isnull($USER->Browsers[0]->Forward) && $USER->Browsers[0]->Forward->Processed);
}

function processForward()
{
	global $USER,$CONFIG,$ISFORWARD;
	if($ISFORWARD && !isnull($USER->Browsers[0]->Forward->TargetGroupId))
	{
		$USER->AddFunctionCall("lz_chat_set_intern('','','',false,false);",false);
		$USER->AddFunctionCall("lz_chat_set_group('".base64_encode($USER->Browsers[0]->Forward->TargetGroupId)."');",false);
		$USER->AddFunctionCall("lz_chat_file_stop();",false);
		$USER->AddFunctionCall("lz_chat_add_system_text(0,null);",false);
		$USER->AddFunctionCall("lz_chat_set_intern_image(false,false,false,false,'','');",false);

		$USER->Browsers[0]->Chat->ExternalDestroy();
		$USER->Browsers[0]->Chat->InternalDestroy();
		$USER->Browsers[0]->DestroyChatFiles();
		$USER->Browsers[0]->DesiredChatGroup = $USER->Browsers[0]->Forward->TargetGroupId;
		$USER->Browsers[0]->DesiredChatPartner = $USER->Browsers[0]->Forward->TargetSessId;
		$USER->Browsers[0]->SetCookieGroup();
		$USER->Browsers[0]->Forward->Processed = true;
		$USER->Browsers[0]->Forward->Save();
		$USER->Browsers[0]->WaitingMessageDisplayed = time();
	}
	else
	{
		if(!isnull($USER->Browsers[0]->Forward) && !isnull($USER->Browsers[0]->Chat) && $USER->Browsers[0]->Chat->Activated == CHAT_STATUS_ACTIVE)
			if($USER->Browsers[0]->Forward->SenderSessId != $USER->Browsers[0]->DesiredChatPartner)
				$USER->Browsers[0]->Forward->Destroy();
	}
}

function getPosts()
{
	global $USER;
	$isPost = false;
	foreach($USER->Browsers[0]->GetPosts() as $posts)
		foreach($posts as $post)
		{
			if($USER->Browsers[0]->DesiredChatPartner == $post->Sender)
			{
				$USER->AddFunctionCall($post->GetCommand(),false);
				$isPost = true;
			}
		}
	return $isPost;
}

function processPosts()
{
	global $USER,$CONFIG;
	if(isset($_POST["p_p0"]))
	{
		$PostMan = new PostMan($USER->Browsers[0],null,null,$CONFIG);
		$counter = 0;
		while(isset($_POST["p_p" . $counter]))
		{
			$post = new Post($_POST["p_i" . $counter],$USER->Browsers[0],$USER->Browsers[0]->Chat->InternalUser->SystemId,AJAXDecode($_POST["p_p" . $counter]),null,null,time());
			if(strlen($_POST["p_p" . $counter]) > 0)
				$PostMan->AddPost($post);
			$counter++;
		}
		$PostMan->Save();
	}
	$USER->AddFunctionCall("lz_chat_release_posts();",false);
}

function login()
{
	global $INTERNAL,$USER;
	if($_POST[POST_EXTERN_USER_NAME] == null && getCookieValue("login_name") != null)
		$USER->Browsers[0]->Fullname = getCookieValue("login_name");
	else
		$USER->Browsers[0]->Fullname = AJAXDecode($_POST[POST_EXTERN_USER_NAME]);
	
	if($_POST[POST_EXTERN_USER_EMAIL] == null && getCookieValue("login_email") != null)
		$USER->Browsers[0]->Email = getCookieValue("login_email");
	else
		$USER->Browsers[0]->Email = AJAXDecode($_POST[POST_EXTERN_USER_EMAIL]);
	if($_POST[POST_EXTERN_USER_COMPANY] == null && getCookieValue("login_company") != null)
		$USER->Browsers[0]->Company = getCookieValue("login_company");
	else
		$USER->Browsers[0]->Company = (trim($_POST[POST_EXTERN_USER_COMPANY]) != null) ? AJAXDecode($_POST[POST_EXTERN_USER_COMPANY]) : "";
	
	if(isset($_POST[POST_EXTERN_USER_NAME]) && !isnull($_POST[POST_EXTERN_USER_NAME]))
		setCookieValue("login_name",($USER->Browsers[0]->Fullname));
	if(isset($_POST[POST_EXTERN_USER_EMAIL]) && !isnull($_POST[POST_EXTERN_USER_EMAIL]))
		setCookieValue("login_email",($USER->Browsers[0]->Email));
	if(isset($_POST[POST_EXTERN_USER_COMPANY]) && !isnull($_POST[POST_EXTERN_USER_COMPANY]))
		setCookieValue("login_company",($USER->Browsers[0]->Company));

	$USER->AddFunctionCall("lz_chat_set_status(0);",false);
}

function refreshPicture()
{
	global $CONFIG,$USER;
	$update = (@filemtime($USER->Browsers[0]->Chat->InternalUser->PictureFile) >  time()-$CONFIG["poll_frequency_clients"]*2);
	if(isset($USER->Browsers[0]->Chat->InternalUser->UserId) && (file_exists($USER->Browsers[0]->Chat->InternalUser->WebcamFile) || file_exists($USER->Browsers[0]->Chat->InternalUser->PictureFile)))
		$USER->AddFunctionCall("lz_chat_set_intern_image(true," . parseBool($update) . "," . parseBool(file_exists($USER->Browsers[0]->Chat->InternalUser->WebcamFile)) . ",false,'".$USER->Browsers[0]->Chat->InternalUser->WebcamFile."','".$USER->Browsers[0]->Chat->InternalUser->PictureFile."');",false);
	else
		$USER->AddFunctionCall("lz_chat_set_intern_image(false,false,false,false,'','');",false);
	$USER->AddFunctionCall("lz_chat_set_config(".$CONFIG["timeout_clients"].",".$CONFIG["poll_frequency_clients"].");",false);
	return (file_exists($USER->Browsers[0]->Chat->InternalUser->WebcamFile) || $update);
}

function processTyping()
{
	global $CONFIG,$USER,$GROUPS;
	$groupname = addslashes($GROUPS[$USER->Browsers[0]->DesiredChatGroup]["gr_desc"]);
	$USER->AddFunctionCall("lz_chat_set_intern(\"".base64_encode($USER->Browsers[0]->Chat->InternalUser->UserId)."\",\"".base64_encode(addslashes($USER->Browsers[0]->Chat->InternalUser->Fullname))."\",\"". base64_encode($groupname)."\",".parseBool($USER->Browsers[0]->DesiredChatPartnerTyping).",".parseBool(file_exists(PATH_VISITCARDS.$USER->Browsers[0]->Chat->InternalUser->UserId.".vcf")).");",false);
}

function beginnConversation()
{
	global $USER,$CONFIG;
	$USER->Browsers[0]->Chat->ExternalActivate();
	if(!isnull($CONFIG["gl_save_op"]))
		setCookieValue("internal_user",$USER->Browsers[0]->Chat->InternalUser->UserId);
	$USER->Browsers[0]->DesiredChatPartner = $USER->Browsers[0]->Chat->InternalUser->SystemId;
	
	$USER->AddFunctionCall("lz_chat_add_system_text(1,'".base64_encode($USER->Browsers[0]->Chat->InternalUser->Fullname)."');",false);
	$USER->AddFunctionCall("lz_chat_set_status(1);",false);
	$USER->AddFunctionCall("lz_chat_shout();",false);
}

function displayFiltered()
{
	global $FILTERS,$USER;
	$USER->Browsers[0]->DestroyChatFiles();
	$USER->AddFunctionCall("lz_chat_set_intern('','','',false,false);",false);
	$USER->AddFunctionCall("lz_chat_set_status(2);",false);
	$USER->AddFunctionCall("lz_chat_add_system_text(2,'".base64_encode("&nbsp;<b>".$FILTERS->Filters[ACTIVE_FILTER_ID]->Reason."</b>")."');",false);
	$USER->AddFunctionCall("lz_chat_stop_system();",false);
	$USER->AddFunctionCall("lz_chat_set_intern_image(false,false,false,true,'','');",false);
}

function displayQuit()
{
	global $GROUPS,$USER;
	$USER->Browsers[0]->DestroyChatFiles();
	$USER->AddFunctionCall("lz_chat_set_intern('','','',false,false);",false);
	$USER->AddFunctionCall("lz_chat_set_status(2);",false);
	$USER->AddFunctionCall("lz_chat_add_system_text(3,null);",false);
	$USER->AddFunctionCall("lz_chat_stop_system();",false);
}

function displayDeclined()
{
	global $GROUPS,$USER;
	$USER->Browsers[0]->DestroyChatFiles();
	$USER->AddFunctionCall("lz_chat_set_intern('','','',false,false);",false);
	$USER->AddFunctionCall("lz_chat_set_status(2);",false);
	$USER->AddFunctionCall("lz_chat_add_system_text(4,null);",false);
	$USER->AddFunctionCall("lz_chat_stop_system();",false);
}

function displayWaitingMessage()
{
	global $GROUPS,$USER;
	if(isnull($USER->Browsers[0]->WaitingMessageDisplayed))
		$USER->Browsers[0]->WaitingMessageDisplayed = time();
	else if($USER->Browsers[0]->WaitingMessageDisplayed == -1)
		return;
		
	if($USER->Browsers[0]->WaitingMessageDisplayed < time()-60)
	{
		$USER->Browsers[0]->WaitingMessageDisplayed = -1;
		$USER->AddFunctionCall("lz_chat_add_system_text(5,null);",false);
	}
}

function buildLoginErrorField($error="",$addition = "")
{
	global $FILTERS,$LZLANG;
	if(!getAvailability())
		$error = $LZLANG["client_error_deactivated"];
		
	if(IS_FILTERED)
	{
		$error = $LZLANG["client_error_unavailable"];
		if(isset($FILTERS->Message) && strlen($FILTERS->Message) > 0)
			$addition = "<br><br>" . $FILTERS->Message;
	}
	return $error . $addition;
}

function reloadGroups($_user)
{
	global $CONFIG,$INTERNAL,$GROUPS;
	getData(true,false,false,true);
	$groupbuilder = new GroupBuilder($INTERNAL,$GROUPS,$CONFIG,$_POST);
	$groupbuilder->Generate();
	
	if(isset($_POST[POST_EXTERN_REQUESTED_INTERNID]) && !isnull($_POST[POST_EXTERN_REQUESTED_INTERNID]))
	{
		$_user->Browsers[0]->DesiredChatPartner = getInternSessIdByUserId(AJAXDecode($_POST[POST_EXTERN_REQUESTED_INTERNID]));
	}

	$_user->AddFunctionCall("top.lz_chat_set_groups(\"" . $groupbuilder->Result . "\" ,". $groupbuilder->ErrorHTML .");",true);
	$_user->AddFunctionCall("top.lz_chat_set_groups(\"" . $groupbuilder->Result . "\" ,". $groupbuilder->ErrorHTML .");",true);
	$_user->AddFunctionCall("lz_chat_release(".parseBool(($groupbuilder->GroupAvailable || (isset($_POST[GET_EXTERN_RESET]) && strlen($groupbuilder->ErrorHTML) <= 2))).",".$groupbuilder->ErrorHTML.");",false);
	return $_user;
}

function getIntern($response = "",$desired = "",$util = 0,$fromCookie = null)
{
	global $CONFIG,$INTERNAL,$GROUPS,$USER,$ISFORWARDPROCESSED,$INTLIST;
	$INTLIST = array();
	$backup_target = null;
	$fromDepartment = $fromDepartmentBusy = false;
	if(!isnull($USER->Browsers[0]->DesiredChatPartner) && isset($INTERNAL[$USER->Browsers[0]->DesiredChatPartner]) && $INTERNAL[$USER->Browsers[0]->DesiredChatPartner]->Status < USER_STATUS_OFFLINE)
		$desired = $USER->Browsers[0]->DesiredChatPartner;
	else
	{
		$USER->Browsers[0]->DesiredChatPartner = null;
		if(isset($_POST[POST_EXTERN_REQUESTED_INTERNID]) && !isnull($_POST[POST_EXTERN_REQUESTED_INTERNID]))
			$desired = getInternSessIdByUserId(AJAXDecode($_POST[POST_EXTERN_REQUESTED_INTERNID]));
		else if(!isnull(getCookieValue("internal_user")) && !isnull($CONFIG["gl_save_op"]))
		{
			$desired = getInternSessIdByUserId(getCookieValue("internal_user"));
			$fromCookie = $desired;
		}
	}
	foreach($GROUPS as $id => $group)
		$utilization[$id] = 0;
	foreach($INTERNAL as $sessId => $internal)
	{
		if($internal->LastActive > (time()-$CONFIG["timeout_clients"]))
		{
			$group_chats[$sessId] = $internal->GetExternalChatAmount();
			$group_names[$sessId] = $internal->Fullname;
			$group_available[$sessId] = GROUP_STATUS_UNAVAILABLE;

			if(in_array($USER->Browsers[0]->DesiredChatGroup,$internal->Groups))
			{
				if($internal->Status == USER_STATUS_ONLINE)
					$group_available[$sessId] = GROUP_STATUS_AVAILABLE;
				elseif($internal->Status== USER_STATUS_BUSY)
					$group_available[$sessId] = GROUP_STATUS_BUSY;
			}
			else
			{
				if($internal->Status == USER_STATUS_ONLINE)
					$backup_target = $internal;
				else if($internal->Status == USER_STATUS_BUSY && isnull($backup_target))
					$backup_target = $internal;
					
				if(!isnull($USER->Browsers[0]->DesiredChatPartner) && $USER->Browsers[0]->DesiredChatPartner == $sessId)
					$USER->Browsers[0]->DesiredChatPartner = null;
			}
			for($count=0;$count<count($internal->Groups);$count++)
			{
				if($USER->Browsers[0]->DesiredChatGroup == $internal->Groups[$count] /*|| $desired == $sessId*/)
				{
					if(!is_array($utilization[$internal->Groups[$count]]))
						$utilization[$internal->Groups[$count]] = Array();
					if($group_available[$sessId] == GROUP_STATUS_AVAILABLE)
						$utilization[$internal->Groups[$count]][$sessId] = $group_chats[$sessId];
				}
			}
		}
	}
	
	if(isset($utilization[$USER->Browsers[0]->DesiredChatGroup]) && is_array($utilization[$USER->Browsers[0]->DesiredChatGroup]))
	{
		arsort($utilization[$USER->Browsers[0]->DesiredChatGroup]);
		reset($utilization[$USER->Browsers[0]->DesiredChatGroup]);
		$util = end($utilization[$USER->Browsers[0]->DesiredChatGroup]);
		$INTLIST = $utilization[$USER->Browsers[0]->DesiredChatGroup];
	}
	
	if(isset($group_available) && is_array($group_available) && in_array(GROUP_STATUS_AVAILABLE,$group_available))
		$fromDepartment = true;
	elseif(isset($group_available) && is_array($group_available) && in_array(GROUP_STATUS_BUSY,$group_available))
		$fromDepartmentBusy = true;

	isForward();
	if(isset($group_chats) && is_array($group_chats) && isset($fromDepartment) && $fromDepartment)
		foreach($group_chats as $sessId => $amount)
		{
			if(($group_available[$sessId] == GROUP_STATUS_AVAILABLE  && $amount <= $util) || ($ISFORWARDPROCESSED && isset($desired) && $sessId == $desired))
				$available_internals[] = $sessId;
		}

	if($fromDepartment && sizeof($available_internals) > 0)
	{
		$USER->Browsers[0]->Waiting = false;
		if(is_array($available_internals))
		{
			if(!isnull($desired) && in_array($desired,$available_internals))
			{
				$matching_internal = $desired;
			}
			else
			{
				$matching_internal = array_rand($available_internals,1);
				$matching_internal = $available_internals[$matching_internal];
			}
		}
		if($CONFIG["gl_alloc_mode"] != ALLOCATION_MODE_ALL || $fromCookie == $matching_internal)
		{
			$USER->Browsers[0]->DesiredChatPartner = $matching_internal;
		}
		$response .= "lz_chat_add_system_text(6,null);";
	}
	elseif($fromDepartmentBusy)
	{	
		if(!$USER->Browsers[0]->Waiting)
		{
			$USER->AddFunctionCall("lz_chat_add_system_text(7,null);",false);
			$USER->Browsers[0]->Waiting = true;
		}
	}
	else
	{
		$USER->Browsers[0]->Waiting = false;
		$USER->AddFunctionCall("lz_chat_set_status(2);",false);
		$USER->AddFunctionCall("lz_chat_add_system_text(8,null);",false);
		$USER->AddFunctionCall("lz_chat_stop_system();",false);
		$INTLIST = array();
	}
	return $response;
}

function getSessionId()
{
	global $CONFIG;
	if(getCookieValue("userid") != null)
		$session = getCookieValue("userid");
	else
		setCookieValue("userid",$session = getId(USER_ID_LENGTH));
	return $session;
}

function replaceLoginDetails($_html)
{
	if(!isset($_GET[GET_EXTERN_USER_EMAIL]))
		$_html = str_replace("<!--login_value_email-->",htmlentities(getCookieValue("login_email"),ENT_QUOTES,"UTF-8"),$_html);
	else
		$_html = str_replace("<!--login_value_email-->",getParam(GET_EXTERN_USER_EMAIL),$_html);
	
	if(!isset($_GET[GET_EXTERN_USER_NAME]))
		$_html = str_replace("<!--login_value_name-->",htmlentities(getCookieValue("login_name"),ENT_QUOTES,"UTF-8"),$_html);
	else
		$_html = str_replace("<!--login_value_name-->",getParam(GET_EXTERN_USER_NAME),$_html);
		
	if(!isset($_GET[GET_EXTERN_USER_COMPANY]))
		$_html = str_replace("<!--login_value_company-->",htmlentities(getCookieValue("login_company"),ENT_QUOTES,"UTF-8"),$_html);
	else
		$_html = str_replace("<!--login_value_company-->",getParam(GET_EXTERN_USER_COMPANY),$_html);
		
	return $_html;
}
?>
