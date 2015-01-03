<?php

/****************************************************************************************
* LiveZilla objects.global.users.inc.php
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
	
require(LIVEZILLA_PATH . "_lib/objects.global.inc.php");
class User
{
	var $IP;
	var $SessId;
	var $UserId;
	var $SystemId;
	var $Messages = array();
	var $Status = USER_STATUS_OFFLINE;
	var $Type;
	var $Folder;
	var $FolderInbox;
	var $FolderSent;
	var $SessionFile;
	var $FirstActive;
	var $LastActive;
	var $Fullname;
	var $Company;
	var $Email;
	var $Typing = false;
	var $FileError;
	
	function User($_userid)
   	{
		$this->UserId = $_userid;
   	}
	
	function GetPosts()
	{
		$messageFileCount = 0;
		$posts = array();
		$messageFiles = getDirectory($this->FolderInbox,false);
		sort($messageFiles);
		foreach($messageFiles as $index => $file)
		{
			if(strpos($file,"_") !== false && strpos($file,EX_MESSAGE) !== false)
			{
				$parts = explode("_",str_replace("." . EX_MESSAGE,"",$file));
				$dataProvider = new DataProvider($this->FolderInbox . $file);
				$dataProvider->Load();
				
				if(count($dataProvider->Errors) == 0 && is_array($dataProvider->Result))
				{
					foreach($dataProvider->Result as $post)
					{
						if(!isset($posts[$parts[1]]))
							$posts[$parts[1]] = Array();
						array_push($posts[$parts[1]],new Post("",$parts[1],$post["s_receiver"],$post["s_text"],$post["s_title"],$post["s_special"],getDataSetTime($this->FolderInbox . $file)));
					}
					unlinkDataSet($this->FolderInbox . $file);
				}
			}
			if(++$messageFileCount >= 10)
				break;
		}
		return $posts;
	}
	
	function AppendFromCookies()
	{
		if(getCookieValue("login_email") != null)
			$this->Email = (getCookieValue("login_email"));

		if(getCookieValue("login_name") != null)
			$this->Fullname = (getCookieValue("login_name"));

		if(getCookieValue("login_company") != null)
			$this->Company = (getCookieValue("login_company"));
	}
	
	function Save()
	{
		if(DATA_SOURCE == DATA_SOURCE_FILE && !@is_dir($this->Folder))
			createFolder($this->Folder);
			
		$dataProvider = new DataProvider($this->SessionFile);
		$dataProvider->Save($this->GetData());
		
		if(count($dataProvider->Errors) > 0)
			$this->FileError = $dataProvider->Errors;
	}
	
	function KeepAlive()
	{
		if(dataSetExists($this->SessionFile))
			touchDataSet($this->SessionFile);
		else
			$this->Save();
	}
	
	function Destroy()
	{
		unlinkDataSet($this->SessionFile);
	}
}

class UserInternal extends User
{
	var $Level = 0;
	var $Webspace = 0;
	var $LoginId;
	var $Password;
	var $Description;
	var $WebcamFile;
	var $PictureFile;
	var $VisitcardFile;
	var $ServerSetup = false;
	var $Authenticated = false;
	var $VisitorFileSizes;
	var $VisitorStaticReload;
	var $ExternalChats;
	var $PermissionSet;
	var $Groups;
	var $GroupsArray;

	function UserInternal($_sessid,$_userid)
   	{
		$this->SessId = $this->SystemId = $_sessid;
		$this->UserId = $_userid;
		$this->ExternalChats = array();
		$this->Folder = PATH_DATA_INTERNAL . $this->SessId . "/";
		$this->FolderInbox = $this->Folder . "_inbox/";
		$this->FolderSent = $this->Folder . "_sent/";
		
		if(DATA_SOURCE == DATA_SOURCE_FILE && !@is_dir($this->FolderInbox))
			createFolder($this->FolderInbox);
			
		if(DATA_SOURCE == DATA_SOURCE_FILE && !@is_dir($this->FolderSent))
			createFolder($this->FolderSent);

		$this->WebcamFile = PATH_INTERN_IMAGES . base64_encode($this->UserId) . FILE_EXTENSION_WEBCAM_PICTURE;
		$this->PictureFile = PATH_INTERN_IMAGES . base64_encode($this->UserId) . FILE_EXTENSION_PROFILE_PICTURE;
		$this->VisitcardFile = PATH_VISITCARDS . $this->UserId . FILE_EXTENSION_VISITCARD;
		$this->PasswordFile = PATH_USERS . $this->SessId . FILE_EXTENSION_PASSWORD;
		$this->ChangePasswordFile = PATH_USERS . $this->SessId . FILE_EXTENSION_CHANGE_PASSWORD;
		$this->Type = USER_TYPE_INTERN;
		$this->SessionFile = $this->Folder . $this->SessId . "." . EX_INTERN_SESSION;
		$this->VisitorFileSizes = array();
		$this->VisitorStaticReload = array();
   	}
	
	function Load()
	{
		$this->FileError = null;
		$dataProvider = new DataProvider($this->SessionFile);
		$dataProvider->Load();
		if(count($dataProvider->Errors) == 0)
		{
			$this->LoginId = $dataProvider->Result["s_login_id"];
			$this->FirstActive = $dataProvider->Result["s_first_active"];
			$this->Password = $dataProvider->Result["s_password"];
			$this->Status = $dataProvider->Result["s_status"];
			$this->Level = $dataProvider->Result["s_level"];
			$this->IP = $dataProvider->Result["s_ip"];
			$this->Typing = $dataProvider->Result["s_typing"];
			$this->VisitorFileSizes = $dataProvider->Result["s_vi_file_sizes"];
			$this->LastActive = getDataSetTime($this->SessionFile);
		}
		else
		{
			$this->FileError = $dataProvider->Errors;
		}
	}
	
	function IsExternal($_groupList, $_exclude=null)
	{
		foreach($this->Groups as $groupid)
			if($_groupList[$groupid]["gr_extern"] && !($_exclude != null && in_array($groupid,$_exclude)))
				return true;
		return false;
	}
	
	function GetData()
	{
		$data = Array();
		$data["s_login_id"] = $this->LoginId;
		$data["s_first_active"] = $this->FirstActive;
		$data["s_password"] = $this->Password;
		$data["s_status"] = $this->Status;
		$data["s_level"] = $this->Level;
		$data["s_ip"] = $this->IP;
		$data["s_typing"] = $this->Typing;
		$data["s_vi_file_sizes"] = $this->VisitorFileSizes;
		return $data;
	}

	function GetExternalObjects()
	{
		$actionfiles = getDirectory($this->Folder,false);
		sort($actionfiles);
		foreach($actionfiles as $index => $file)
		{
			if(strpos($file, "." . EX_CHAT_OPEN) !== false)
			{
				$chat = new Chat($this->Folder . $file);
				$this->ExternalChats[$chat->ExternalUser->SystemId] = $chat;
				$chat->IsActivated(null);
			}
			else if(strpos($file, "." . EX_FILE_UPLOAD_REQUEST) !== false)
			{
				$request = new FileUploadRequest(str_replace(".".EX_FILE_UPLOAD_REQUEST,"",$file),$this->SessId);
				$request->Load();
				
				if(isset($chat) && isset($this->ExternalChats[$chat->ExternalUser->SystemId]))
				{
					if($this->ExternalChats[$chat->ExternalUser->SystemId]->Activated != CHAT_STATUS_ACTIVE)
					{
						$request->Destroy();
					}
					else
					{
						if($request->Error)
						{
							$request->Destroy();
						}
						else if(file_exists($request->GetFile()))
						{
							if(@filemtime($request->GetFile()) > getDataSetTime($request->TargetFile))
							{
								$request->Download = true;
								$request->Destroy();
							}
						}
						$this->ExternalChats[$chat->ExternalUser->SystemId]->FileUploadRequest = $request;
					}
				}
				else
				{
					$request->Destroy();
				}
			}
		}
	}
	
	function GetExternalChatAmount($amount=0)
	{
		$actionfiles = getDirectory($this->Folder,false);
		foreach($actionfiles as $index => $file)
		{
			if(strpos($file, "." . EX_CHAT_OPEN) !== false)
				$amount++;
		}
		return $amount;
	}
	
	function SetStaticCaller()
	{
		define("CALLER_SYSTEM_ID",$this->SystemId);
	}
	
	function LoadPassword()
	{
		$this->Password = null;
		if(file_exists($this->PasswordFile))
		{
			$data = getFile($this->PasswordFile);
			$this->Password = $data;
		}
		return $this->Password;
	}
	
	function ChangePassword($_password)
	{
		createFile($this->PasswordFile,md5($_password),true);
		if(file_exists($this->ChangePasswordFile))
			@unlink($this->ChangePasswordFile);
	}
	
	function IsPasswordChangeNeeded()
	{
		return file_exists($this->ChangePasswordFile);
	}
	
	function SetPasswordChangeNeeded($_needed)
	{
		if($_needed)
			createFile($this->ChangePasswordFile,"",false);
		else if(file_exists($this->ChangePasswordFile))
			@unlink($this->ChangePasswordFile);
	}
	
	function GetPermission($_type)
	{
		return substr($this->PermissionSet,$_type,1);
	}

	function GetLoginReply($_extern,$_time)
	{
		return "<login>\r\n<login_return group=\"".base64_encode($this->GroupsArray)."\" name=\"".base64_encode($this->Fullname)."\" loginid=\"".base64_encode($this->LoginId)."\" level=\"".base64_encode($this->Level)."\" sess=\"".base64_encode($this->SystemId)."\" extern=\"".base64_encode($_extern)."\" timediff=\"".base64_encode($_time)."\" perms=\"".base64_encode($this->PermissionSet)."\" sm=\"".base64_encode(SAFE_MODE)."\" /></login>";
	}
}

class UserExternal extends User
{
	var $Browsers;
	var $ExternalStatic;
	var $Response;
	var $IsChat = false;
	var $ActiveChatRequest;
	
	function UserExternal($_userid)
   	{
		$this->Browsers = Array();
		$this->UserId = $_userid;
		$this->Folder = PATH_DATA_EXTERNAL . $this->UserId . "/";
   	}
	
	function SaveMail($_group)
	{
		$mail = new UserTicket(getServerParam("REMOTE_ADDR") . "_" . time() . "." . EX_MAIL);
		$mail->IP = getServerParam("REMOTE_ADDR");
		
		setCookieValue("login_name",AJAXDecode($_POST[POST_EXTERN_USER_NAME]));
		setCookieValue("login_email",AJAXDecode($_POST[POST_EXTERN_USER_EMAIL]));
		setCookieValue("login_company",AJAXDecode($_POST[POST_EXTERN_USER_COMPANY]));

		if(!$mail->IsFlood())
		{
			$mail->Fullname = AJAXDecode($_POST[POST_EXTERN_USER_NAME]);
			$mail->UserId = $_POST[POST_EXTERN_USER_USERID];
			$mail->Email = AJAXDecode($_POST[POST_EXTERN_USER_EMAIL]);
			$mail->Group = $_group;
			$mail->Company = AJAXDecode($_POST[POST_EXTERN_USER_COMPANY]);
			$mail->Text = AJAXDecode($_POST[POST_EXTERN_USER_MAIL]);
			$mail->Save();
			$this->AddFunctionCall("lz_chat_mail_callback(true);",false);
			
			if(!file_exists(FILE_ANS_MESSAGES))
				createFile(FILE_ANS_MESSAGES,null,false);
			else
				@touch(FILE_ANS_MESSAGES);
			return true;
		}
		else
			$this->AddFunctionCall("lz_chat_mail_callback(false);",false);
		return false;
	}
	
	function SendCopyOfMail($_group,$_config,$_groups)
	{
		$message = getFile(TEMPLATE_EMAIL_MAIL);
		$message = str_replace("<!--date-->",date("r"),$message);
		$message = str_replace("<!--name-->",AJAXDecode($_POST[POST_EXTERN_USER_NAME]),$message);
		$message = str_replace("<!--email-->",AJAXDecode($_POST[POST_EXTERN_USER_EMAIL]),$message);
		$message = str_replace("<!--company-->",AJAXDecode($_POST[POST_EXTERN_USER_COMPANY]),$message);
		$message = str_replace("<!--mail-->",AJAXDecode($_POST[POST_EXTERN_USER_MAIL]),$message);
		$sender = ($_config["gl_usmasend"] != null && isValidEmail(AJAXDecode($_POST[POST_EXTERN_USER_EMAIL])) && $_config["gl_smtpauth"] == null) ? AJAXDecode($_POST[POST_EXTERN_USER_EMAIL]) : $_config["gl_mail_sender"];
		if($_config["gl_scom"] != null)
			sendMail($_config["gl_scom"],$sender,AJAXDecode($_POST[POST_EXTERN_USER_EMAIL]),$message,$_config["gl_site_name"] . " - New Offline Message from " . AJAXDecode($_POST[POST_EXTERN_USER_EMAIL]));
		if($_config["gl_sgom"] != null)
			sendMail($_groups[$_group]["gr_email"],$sender,AJAXDecode($_POST[POST_EXTERN_USER_EMAIL]),$message,$_config["gl_site_name"] . " - New Offline Message from " . AJAXDecode($_POST[POST_EXTERN_USER_EMAIL]));
	}
	
	function StoreFile($_browserId,$_partner)
	{
		$filename = namebase($_FILES['userfile']['name']);
		if(!isValidUploadFile($filename))
			return false;

		$folder = PATH_UPLOADS_EXTERNAL . $this->UserId."/";
		$uploadfile = $folder . base64_encode($filename);
		if(strpos($uploadfile,"..") === false)
		{
			$file = $_browserId."_".substr(md5($filename),0,5);
			if(!@is_dir($folder))
			{
				createFolder($folder);
				createFile($folder . FILE_INDEX,"",false);
			}
		
			$permission = new Permission("FileUploadPermission",$this->UserId,$_browserId,$file);
			if(dataSetExists($permission->TargetFile))
			{
				$permission->Load();
				$permission->Destroy();
				$request = new FileUploadRequest($file,$_partner);
				if($permission->Result == PERMISSION_FULL)
				{	
					if(@move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile))
						return true;
				}
			}
		}
		return false;
	}
	
	function SaveRate($_internalName)
	{
		$rate = new Rating(bigTime() . "_" . getServerParam("REMOTE_ADDR"),getServerParam("REMOTE_ADDR"));
		if(!$rate->IsFlood())
		{
			$rate->RateComment = AJAXDecode($_POST[POST_EXTERN_RATE_COMMENT]);
			$rate->RatePoliteness = $_POST[POST_EXTERN_RATE_POLITENESS];
			$rate->RateQualification = $_POST[POST_EXTERN_RATE_QUALIFICATION];
			$rate->Fullname = AJAXDecode($_POST[POST_EXTERN_USER_NAME]);
			$rate->Email = AJAXDecode($_POST[POST_EXTERN_USER_EMAIL]);
			$rate->Company = AJAXDecode($_POST[POST_EXTERN_USER_COMPANY]);
			$rate->UserId = $_POST[POST_EXTERN_USER_USERID];
			$rate->InternId = $_internalName;
			$rate->Save();
			
			if(!file_exists(FILE_ANS_RATINGS))
				createFile(FILE_ANS_RATINGS,null,false);
			else
				@touch(FILE_ANS_RATINGS);
				
			$this->AddFunctionCall("lz_chat_send_rate_callback(true);",false);
		}
		else
			$this->AddFunctionCall("lz_chat_send_rate_callback(false);",false);
	}
	
	function AddFunctionCall($_call,$_overwrite)
	{
		if($this->Response == null)
			$this->Response = "";
		if($_overwrite)
			$this->Response = $_call;
		else
			$this->Response .= $_call;
	}
	
	function LoadStaticInformation()
	{
		$this->ExternalStatic = new ExternalStatic($this->UserId);
	}
}

class ExternalBrowser extends User
{
	var $BrowserId;
	var $Referrer;
	var $History;
	var $Request;
	var $Guide;
	var $Alerts;
	var $Type = BROWSER_TYPE_BROWSER;
	
	function ExternalBrowser($_browserid,$_userid)
   	{
		$this->BrowserId = $_browserid;
		$this->UserId = $_userid;
		$this->SystemId = $this->UserId . "~" . $this->BrowserId;
		$this->Folder = PATH_DATA_EXTERNAL . $this->UserId . "/browsers/" . $this->BrowserId . "/";
		$this->SessionFile = $this->Folder . $this->BrowserId . "." . EX_BROWSER_SESSION;
		$this->AppendFromCookies();
   	}
	
	function Load()
	{
		$dataProvider = new DataProvider($this->SessionFile);
		$dataProvider->Load();
		if(count($dataProvider->Errors) == 0)
		{
			$this->FirstActive = $dataProvider->Result["s_first_active"];
			$this->History = $dataProvider->Result["s_history"];
			$this->Referrer = $dataProvider->Result["s_referrer"];
			
			if(isset($dataProvider->Result["s_fullname"]))
				$this->Fullname = $dataProvider->Result["s_fullname"];
				
			if(isset($dataProvider->Result["s_email"]))
				$this->Email = $dataProvider->Result["s_email"];
			
			if(isset($dataProvider->Result["s_company"]))
				$this->Company = $dataProvider->Result["s_company"];
		}
		else
			$this->FileError = $dataProvider->Errors;
		$this->AppendFromCookies();
	}

	function GetActions()
	{
		$actionfiles = getDirectory($this->Folder,false);
		$this->Request = null;
		$this->Guide = null;
		foreach($actionfiles as $key => $actionfile)
		{
			if(strpos($actionfile, "." . EX_CHAT_REQUEST) !== false || strpos($actionfile, "." . EX_CHAT_REQUEST_ACCEPTED) !== false || strpos($actionfile, "." . EX_CHAT_REQUEST_DECLINED) !== false)
			{
				if($this->Request == null)
				{
					if(strpos($actionfile, "." . EX_CHAT_REQUEST) !== false)
						$this->Request = new ChatRequest(str_replace("." . EX_CHAT_REQUEST,"",$actionfile),$this->UserId,$this->BrowserId,REQUEST_STATUS_ACTIVE);
					if(strpos($actionfile, "." . EX_CHAT_REQUEST_ACCEPTED) !== false)
						$this->Request = new ChatRequest(str_replace("." . EX_CHAT_REQUEST_ACCEPTED,"",$actionfile),$this->UserId,$this->BrowserId,REQUEST_STATUS_ACCEPTED);
					if(strpos($actionfile, "." . EX_CHAT_REQUEST_DECLINED) !== false)
						$this->Request = new ChatRequest(str_replace("." . EX_CHAT_REQUEST_DECLINED,"",$actionfile),$this->UserId,$this->BrowserId,REQUEST_STATUS_DECLINED);
					$this->Request->Load();
				}
				else
					unlinkDataSet($this->Folder . $actionfile);
			}
			else if(strpos($actionfile, "." . EX_BROWSER_GUIDE) !== false)
			{
				if($this->Guide == null || ($this->Guide != null && @filemtime($this->Folder . $actionfile) > $this->Guide->Created))
				{
					$this->Guide = new Guide(str_replace("." . EX_BROWSER_GUIDE,"",$actionfile),$this->UserId,$this->BrowserId,true);
					$this->Guide->Created = getDataSetTime($this->Folder . $actionfile);
					$this->Guide->Load();
				}
				else
					unlinkDataSet($this->Folder . $actionfile);
			}
			else if(strpos($actionfile, "." . EX_ALERT) !== false)
			{
				$this->Alerts[$key] = new Alert(str_replace("." . EX_ALERT,"",$actionfile),$this->UserId,$this->BrowserId);
				$this->Alerts[$key]->Load();
			}
		}
	}
	
	function GetData()
	{
		$data = Array();
		$data["s_first_active"] = $this->FirstActive;
		$data["s_history"] = $this->History;
		$data["s_referrer"] = $this->Referrer;
		if(!isnull($this->Fullname))
			$data["s_fullname"] = $this->Fullname;
			
		if(!isnull($this->Email))
			$data["s_email"] = $this->Email;
		
		if(!isnull($this->Company))
			$data["s_company"] = $this->Company;
		
		return $data;
	}
	
	function Destroy($_config=null)
	{
		deleteDirectory($this->Folder);
	}
}

class ExternalChat extends ExternalBrowser
{
	var $DesiredChatGroup;
	var $DesiredChatPartner;
	var $DesiredChatPartnerTyping = false;
	var $Forward;
	var $Waiting;
	var $Chat;
	var $Code = "CHAT";
	var $Type = BROWSER_TYPE_CHAT;
	var $ConnectingMessageDisplayed = null;
	var $WaitingMessageDisplayed = null;
	var $ChatRequestReceiptants;

	function ExternalChat($_userid,$_browserId)
   	{
		$this->UserId = $_userid;
		$this->BrowserId = $_browserId;
		$this->SystemId = $this->UserId . "~" . $this->BrowserId;
		$this->Folder = PATH_DATA_EXTERNAL . $this->UserId . "/browsers/" . $this->BrowserId . "/";
		$this->FolderInbox = $this->Folder . "_inbox/";
		$this->FolderSent = $this->Folder . "_sent/";
		$this->SessionFile = $this->Folder . $this->BrowserId . "." . EX_CHAT_SESSION;
		$this->AppendFromCookies();
		
		if(DATA_SOURCE == DATA_SOURCE_FILE && !@is_dir($this->FolderInbox))
			createFolder($this->FolderInbox);
			
		if(DATA_SOURCE == DATA_SOURCE_FILE && !@is_dir($this->FolderSent))
			createFolder($this->FolderSent);
   	}
	
	function GetData()
	{
		$data = Array();
		$data["s_typing"] = $this->Typing;
		
		if(!isnull($this->Fullname))
			$data["s_fullname"] = $this->Fullname;
			
		$data["s_email"] = $this->Email;
		$data["s_company"] = $this->Company;
		$data["s_waiting"] = $this->Waiting;
		$data["s_code"] = $this->Code;
		$data["s_first_active"] = ($this->FirstActive != null) ? $this->FirstActive : time();
		$data["s_internal"] = $this->DesiredChatPartner;
		$data["s_group"] = $this->DesiredChatGroup;
		$data["s_waiting_message"] = $this->WaitingMessageDisplayed;
		return $data;
	}
	
	function SetCookieGroup()
	{
		setCookieValue("login_group",$this->DesiredChatGroup);
	}
	
	function RequestFileUpload($_user,$_filename)
	{
		$fileid = substr(md5(namebase($_filename)),0,5);
		$request = new FileUploadRequest($this->BrowserId . "_" . $fileid,$this->DesiredChatPartner);
		$request->SenderUserId = $this->UserId;
		$request->Text = namebase($_filename);
		$request->SenderBrowserId = $this->BrowserId;
		$permission = new Permission("FileUploadPermission",$this->UserId,$this->BrowserId,$this->BrowserId."_".$fileid);
		if(dataSetExists($permission->TargetFile))
		{
			$permission->Load();
			if($permission->Result == PERMISSION_FULL)
			{
				$_user->AddFunctionCall("top.lz_chat_file_start_upload('".$_filename."');",false);
			}
			else
			{
				$_user->AddFunctionCall("top.lz_chat_file_stop();",false);
				$_user->AddFunctionCall("top.lz_chat_file_error(1);",false);
				$permission->Destroy();
				$request->Destroy();
			}
		}
		else if(!dataSetExists($request->TargetFile) && !dataSetExists($permission->TargetFile))
		{
			if(!isValidUploadFile($_filename))
			{
				$_user->AddFunctionCall("top.lz_chat_file_error(2);",false);
			}
			else
			{
				$files = getDirectory(PATH_DATA_INTERNAL . $this->DesiredChatPartner,false);
				foreach($files as $file)
				{
					if(strpos($file, "." . EX_FILE_UPLOAD_REQUEST) !== false && strpos($file, $this->BrowserId) !== false)
						unlinkDataSet(PATH_DATA_INTERNAL . $this->DesiredChatPartner . "/" . $file);
				}
				$request->Save();
			}
		}
		return $_user;
	}
	
	function AbortFileUpload($_user,$_filename,$_error)
	{
		$fileid = substr(md5(namebase($_filename)),0,5);
		$request = new FileUploadRequest($this->BrowserId . "_" . $fileid,$this->DesiredChatPartner);
		if(dataSetExists($request->TargetFile))
		{
			$request->Load();
			$request->Error = $_error;
			$request->Save();
		}
		
		$permission = new Permission("FileUploadPermission",$this->UserId,$this->BrowserId,$this->BrowserId . "_" . $fileid);
		if(dataSetExists($permission->TargetFile))
			$permission->Destroy();
	}
	
	function Load()
	{
		$dataProvider = new DataProvider($this->SessionFile);
		$dataProvider->Load();
		if(count($dataProvider->Errors) == 0)
		{
			if(isset($dataProvider->Result["s_fullname"]))
				$this->Fullname = $dataProvider->Result["s_fullname"];
			
			$this->Email = $dataProvider->Result["s_email"];
			$this->Company = $dataProvider->Result["s_company"];
			$this->Waiting = $dataProvider->Result["s_waiting"];
			$this->FirstActive = $dataProvider->Result["s_first_active"];
			$this->Typing = $dataProvider->Result["s_typing"];
			$this->Code = $dataProvider->Result["s_code"];
			$this->DesiredChatPartner = $dataProvider->Result["s_internal"];
			$this->DesiredChatGroup = $dataProvider->Result["s_group"];
			$this->WaitingMessageDisplayed = $dataProvider->Result["s_waiting_message"];
		}
		else
			$this->FileError = $dataProvider->Errors;
		$this->AppendFromCookies();
	}
	
	function LoadChat($_config,$_internal)
	{
		$declined = true;
		$this->Chat = null;
		$this->ChatRequestReceiptants = array();
		$chatfiles = getDirectory($this->Folder,false);
		foreach($chatfiles as $chatfile)
			if(strpos($chatfile, "." . EX_CHAT_OPEN) !== false)
			{
				if(strpos($chatfile, "." . EX_CHAT_OPEN) !== false  && ($_config["gl_alloc_mode"] == ALLOCATION_MODE_ALL || $this->Chat == null))
				{
					$partnerid = str_replace("." . EX_CHAT_OPEN,"",$chatfile);
					$chat = new Chat($this->Folder . $chatfile);
					$activated = $chat->IsActivated($partnerid);
					
					if(!$chat->Declined)
					{
						$declined = false;
						if(!$activated)
							$this->ChatRequestReceiptants[] = $partnerid;
					}
					if(($activated || isnull($this->Chat)) || (CALLER_TYPE != CALLER_TYPE_EXTERNAL && !isnull($this->Chat) && ($partnerid == $_internal->SystemId && !$chat->IsActivated(null))))
					{
						if(CALLER_TYPE != CALLER_TYPE_EXTERNAL && $chat->Declined)
							continue;
							
						$this->Chat = $chat;
						if(isnull($_internal))
						{
							$_internal = new UserInternal($partnerid,null);
							$_internal->Load();
							$this->DesiredChatPartnerTyping = ($_internal->Typing == $this->SystemId);
						}
						
						$dataSetTime = getDataSetTime($_internal->SessionFile);
						if($_internal->Status == CHAT_STATUS_ACTIVE || $dataSetTime < (time()-$_config["timeout_clients"]))
						{
							$this->Chat->Closed = true;
						}
					}
				}
				else
				{
					unlinkDataSet($this->Folder . $chatfile);
				}
			}
			
		if(!isnull($this->Chat))
			$this->Chat->Declined = $declined;
	}
	
	function GetForwards()
	{
		$this->Forward = null;
		$actionfiles = getDirectory($this->Folder,false);
		sort($actionfiles);
		foreach($actionfiles as $index => $file)
		{
			if(strpos($file,EX_CHAT_FORWARDING) !== false)
			{
				if(isnull($this->Forward))
				{
					$this->Forward = new Forward($this->UserId,$this->BrowserId,str_replace("." . EX_CHAT_FORWARDING,"",$file));
					$this->Forward->Load();
				}
			}
		}
	}
	
	function CreateChat($_internalUser)
	{
		$this->Chat = new Chat($this,$_internalUser);
		$this->Chat->InternalDestroy();
		$this->Chat->ExternalDestroy();
		$this->Chat->Save();
	}
	
	function SendChatTranscript($_config)
	{
		if($this->Email != null)
		{
			$message = getChatTranscript($this->SystemId);
			if($_config["gl_scct"] != null || $_config["gl_soct"] != null)
			{
				if($message != null)
				{
					if($_config["gl_soct"])
						sendMail($this->Email,$_config["gl_mail_sender"],$_config["gl_mail_sender"],$message,$_config["gl_site_name"] . " Chat Transcript");
					if($_config["gl_scct"])
						sendMail($_config["gl_scct"],$_config["gl_mail_sender"],$_config["gl_mail_sender"],$message,$_config["gl_site_name"] . " Chat Transcript");
				}
			}
		}
	}
	
	function DestroyChatFiles()
	{
		$chatfiles = getDirectory($this->Folder,false);
		foreach($chatfiles as $chatfile)
			if(strpos($chatfile, "." . EX_CHAT_OPEN) !== false || strpos($chatfile, "." . EX_CHAT_ACTIVE) !== false || strpos($chatfile, "." . EX_CHAT_INTERN_CLOSED) != false || strpos($chatfile, "." . EX_CHAT_INTERN_DECLINED) != false)
				unlinkDataSet($this->Folder . $chatfile);
	}
	
	function Destroy($_config=null)
	{
		deleteDirectory($this->Folder);
		$this->SendChatTranscript($_config);
	}
}

class ExternalStatic extends User
{
	var $SystemInfo;
	var $Language;
	var $Resolution;
	var $Host;
	var $Name;
	var $Email;
	var $Company;
	var $Visits = 1;
	var $GeoCity;
	var $GeoCountryISO2;
	var $GeoRegion;
	var $GeoLongitude= -522;
	var $GeoLatitude= -522;
	var $GeoTimezoneOffset = "+00:00";
	var $GeoResultId = 0;
	
	function ExternalStatic($_userid)
   	{
		$this->UserId = $_userid;
		$this->Folder = PATH_DATA_EXTERNAL . $this->UserId . "/";
		$this->SessionFile = $this->Folder . $this->UserId . "." . EX_STATIC_INFO;
   	}
	
	function Load()
	{
		$dataProvider = new DataProvider($this->SessionFile);
		$dataProvider->Load();
		
		if(count($dataProvider->Errors) == 0)
		{
			$this->IP = $dataProvider->Result["s_ip"];
			$this->SystemInfo = $dataProvider->Result["s_system"];
			$this->Language = $dataProvider->Result["s_language"];
			$this->Resolution = $dataProvider->Result["s_resolution"];
			$this->Host = $dataProvider->Result["s_host"];
			
			if(isset($dataProvider->Result["s_geotz"]))
				$this->GeoTimezoneOffset = $dataProvider->Result["s_geotz"];
			if(isset($dataProvider->Result["s_geolong"]))
				$this->GeoLongitude = $dataProvider->Result["s_geolong"];
			if(isset($dataProvider->Result["s_geolat"]))
				$this->GeoLatitude = $dataProvider->Result["s_geolat"];
			if(isset($dataProvider->Result["s_geocity"]))
				$this->GeoCity = $dataProvider->Result["s_geocity"];
			if(isset($dataProvider->Result["s_geocountry"]))
				$this->GeoCountryISO2 = $dataProvider->Result["s_geocountry"];
			if(isset($dataProvider->Result["s_georegion"]))
				$this->GeoRegion = $dataProvider->Result["s_georegion"];
			if(isset($dataProvider->Result["s_visits"]))
				$this->Visits =	$dataProvider->Result["s_visits"];
			if(isset($dataProvider->Result["s_georid"]))
				$this->GeoResultId = $dataProvider->Result["s_georid"];
		}
		else
		{
			$this->FileError = $dataProvider->Errors;
		}
	}
	
	function GetData()
	{
		$data = Array();
		$data["s_ip"] = $this->IP;
		$data["s_system"] = $this->SystemInfo;
		$data["s_language"] = $this->Language;
		$data["s_resolution"] = $this->Resolution;
		$data["s_geotz"] = $this->GeoTimezoneOffset;
		if(is_numeric($this->GeoLongitude))
			$data["s_geolong"] = $this->GeoLongitude;
		if(is_numeric($this->GeoLatitude))
			$data["s_geolat"] = $this->GeoLatitude;
		$data["s_geocity"] = $this->GeoCity;
		$data["s_geocountry"] = $this->GeoCountryISO2;
		$data["s_georegion"] = $this->GeoRegion;
		$data["s_visits"] = $this->Visits;
		$data["s_host"] = $this->Host;
		$data["s_georid"] = $this->GeoResultId;
		return $data;
	}
}
?>