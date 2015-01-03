<?php

/****************************************************************************************
* LiveZilla objects.global.inc.php
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
	
class Post
{
	var $Receiver;
	var $Type = "";
	var $Text;
	var $Id;
	var $Special = "";
	var $Title = "";
	var $Created;
	var $Sender;
	
	function Post($_id,$_sender,$_receiver,$_text,$_title,$_special,$_created)
   	{
		$this->Id = $_id;
		$this->Receiver = $_receiver;
		$this->Text = $_text;
		$this->Title = $_title;
		$this->Special = $_special;
		$this->Created = $_created;
		$this->Sender = $_sender;
   	}
	
	function GetData()
	{
		$data = Array();
		$data["s_receiver"] = $this->Receiver;
		$data["s_text"] = $this->Text;
		$data["s_title"] = ($this->Title != null) ? $this->Title : "";
		$data["s_special"] = ($this->Special != null) ? $this->Special : "";
		return $data;
	}
	
	function GetXml($_sender)
	{
		return "<val id=\"".base64_encode($_sender)."\" rec=\"".base64_encode($this->Receiver)."\" date=\"".base64_encode($this->Created)."\">".base64_encode($this->Text)."</val>\r\n";
	}
	
	function GetCommand()
	{
		return "lz_chat_add_intern_text(\"".base64_encode($this->Text)."\" , \"".base64_encode($this->Title)."\" , \"" . $this->Special . "\",\"".base64_encode($this->Id)."\");";
	}
}

class PostMan
{
	var $Posts;
	var $Internals;
	var $Groups;
	var $Sender;
	var $Config;
	
	function PostMan($_sender,$_internals,$_groups,$_config)
   	{
		$this->Sender = $_sender;
		$this->Internals = $_internals;
		$this->Groups = $_groups;
		$this->Posts = array();
		$this->Config = $_config;
		$this->Blocked = array();
   	}
	
	function AddPost($_post)
	{
		if(!dataSetExists($_post->Sender->FolderSent . $_post->Id . "." . EX_MESSAGE))
		{
			if(!isset($this->Posts[$_post->Receiver]))
				$this->Posts[$_post->Receiver] = Array();
			array_push($this->Posts[$_post->Receiver],$_post);
			return true;
		}
		return false;
	}
	
	function Save()
	{
		foreach($this->Posts as $receiverSystemId => $posts)
		{
			$postbox = Array();
			foreach($posts as $post)
				array_push($postbox,$post->GetData());

			$time = bigTime();
			if($receiverSystemId == GROUP_EVERYONE_INTERN || is_array($this->Groups) && is_array($this->Internals) && isset($this->Groups[$receiverSystemId]))
			{
				foreach($this->Internals as $internal)
					if($receiverSystemId == GROUP_EVERYONE_INTERN || in_array($receiverSystemId,$internal->Groups))
						if($internal->SystemId != $this->Sender->SystemId)
						{
							$dataProvider = new DataProvider($internal->FolderInbox . $time . "_". $this->Sender->SystemId . "." . EX_MESSAGE);
							$dataProvider->Save($postbox);
						}
			}
			else if($receiverSystemId == GROUP_EVERYONE_EXTERN)
			{
				foreach($this->Sender->ExternalChats as $chat)
				{
					$dataProvider = new DataProvider($chat->ExternalUser->FolderInbox . $time . "_" . $this->Sender->SystemId . "." . EX_MESSAGE);
					$dataProvider->Save($postbox);
				}
			}
			else if(is_array($this->Internals) && isset($this->Internals[$receiverSystemId]))
			{
				$dataProvider = new DataProvider($this->Internals[$receiverSystemId]->FolderInbox . $time . "_". $this->Sender->SystemId . "." . EX_MESSAGE);
				$dataProvider->Save($postbox);
			}
			else if(is_array($this->Internals))
			{
				$parts = explode("~",$receiverSystemId);
				$external = new ExternalChat($parts[0],$parts[1]);
				$external->Load();
				$external->LoadChat($this->Config,$this->Internals[CALLER_SYSTEM_ID]);
				
				if($external->Chat->IsActivated(CALLER_SYSTEM_ID))
				{
					$dataProvider = new DataProvider($external->FolderInbox . $time . "_" . $this->Sender->SystemId . "." . EX_MESSAGE);
					$dataProvider->Save($postbox);
					logPost($receiverSystemId,$this->Posts[$receiverSystemId][0],CALLER_SYSTEM_ID,true);
				}
			}
			else
			{
			 	$internal = new UserInternal($receiverSystemId,null);
				$dataProvider = new DataProvider($internal->FolderInbox . $time . "_" . $this->Sender->SystemId . "." . EX_MESSAGE);
				$dataProvider->Save($postbox);
			}
			
			foreach($posts as $post)
			{
				if(!dataSetExists($post->Sender->FolderSent . $post->Id . "." . EX_MESSAGE))
				{
					$dataProvider = new DataProvider($post->Sender->FolderSent . $post->Id . "." . EX_MESSAGE);
					$dataProvider->Save(null);
				}
			}
		}
		if(rand(0,10) == 0)
		{
			$oldquittances = getDirectory($this->Sender->FolderSent,false);
			foreach($oldquittances as $quittance)
				if(getDataSetTime($this->Sender->FolderSent . $quittance) < time()-QUITTANCE_TIME)
					unlinkDataSet($this->Sender->FolderSent . $quittance);
		}
	}
}

class FilterSystem
{
	var $Filters;
	var $Message;
	
	function FilterSystem()
   	{
		$this->Filters = Array();
   	}
	
	function Populate()
	{
		$filters = getDirectory(PATH_FILTER,false,true);
		foreach($filters as $file)
		{
			if(stristr($file,FILE_EXTENSION_FILTER))
			{
				$filter = new Filter(str_replace(FILE_EXTENSION_FILTER,"",$file));
				$filter->Load();
				$this->Filters[$filter->FilterId] = $filter;
			}
		}
	}
	
	function Match($_ip,$_languages,$_userid)
	{
		foreach($this->Filters as $filterid => $filter)
		{
			if($filter->Activestate == FILTER_TYPE_INACTIVE)
				continue;
			
			$this->Message = $filter->Reason;
			$compare["match_ip"] = $this->IpCompare($_ip,$filter->IP);
			$compare["match_lang"] = $this->LangCompare($_languages,$filter->Languages);
			$compare["match_id"] = ($filter->Userid == $_userid);
			if($compare["match_ip"] && $filter->Exertion == FILTER_EXERTION_BLACK && $filter->Activeipaddress == FILTER_TYPE_ACTIVE)
				define("ACTIVE_FILTER_ID",$filter->FilterId);
			else if(!$compare["match_ip"] && $filter->Exertion == FILTER_EXERTION_WHITE && $filter->Activeipaddress == FILTER_TYPE_ACTIVE)
				define("ACTIVE_FILTER_ID",$filter->FilterId);
			else if($compare["match_lang"] && $filter->Exertion == FILTER_EXERTION_BLACK && $filter->Activelanguage == FILTER_TYPE_ACTIVE)
				define("ACTIVE_FILTER_ID",$filter->FilterId);
			else if(!$compare["match_lang"] && $filter->Exertion == FILTER_EXERTION_WHITE && $filter->Activelanguage == FILTER_TYPE_ACTIVE)
				define("ACTIVE_FILTER_ID",$filter->FilterId);
			else if($compare["match_id"] && $filter->Exertion == FILTER_EXERTION_BLACK && $filter->Activeuserid == FILTER_TYPE_ACTIVE)
				define("ACTIVE_FILTER_ID",$filter->FilterId);
			else if(!$compare["match_id"] && $filter->Exertion == FILTER_EXERTION_WHITE && $filter->Activeuserid == FILTER_TYPE_ACTIVE)
				define("ACTIVE_FILTER_ID",$filter->FilterId);
			if(defined("ACTIVE_FILTER_ID"))
				return true;
		}
		return false;
	}
	
	function IpCompare($_ip, $_comparer)
	{
		$array_ip = explode(".",$_ip);
		$array_comparer = explode(".",$_comparer);
		if(count($array_ip) == 4 && count($array_comparer) == 4)
		{
			foreach($array_ip as $key => $octet)
			{
				if($array_ip[$key] != $array_comparer[$key])
				{
					if($array_comparer[$key] == -1)
						return true;
					return false;
				}
			}
			return true;
		}
		else
			return false;
	}
	
	function LangCompare($_lang, $_comparer)
	{
		$array_lang = explode(",",$_lang);
		$array_comparer = explode(",",$_comparer);
		foreach($array_lang as $key => $lang)
			foreach($array_comparer as $keyc => $langc)
				if(strtoupper($array_lang[$key]) == strtoupper($langc))
					return true;
		return false;
	}
}

class Filter
{
	var $Creator;
	var $Created;
	var $Editor;
	var $Edited;
	var $IP;
	var $Expiredate;
	var $Userid;
	var $Reason;
	var $Filtername;
	var $FilterId;
	var $Activestate;
	var $Exertion;
	var $Languages;
	var $Activeipaddress;
	var $Activeuserid;
	var $Activelanguage;
	
	function GetData()
	{
		$data = Array();
		$data["s_creator"] = $this->Creator;
		$data["s_created"] = $this->Created;
		$data["s_editor"] = $this->Editor;
		$data["s_edited"] = $this->Edited;
		$data["s_ip"] = $this->IP;
		$data["s_expiredate"] = $this->Expiredate;
		$data["s_userid"] = $this->Userid;
		$data["s_reason"] = $this->Reason;
		$data["s_filtername"] = $this->Filtername;
		$data["s_filterid"] = $this->FilterId;
		$data["s_activestate"] = $this->Activestate;
		$data["s_exertion"] = $this->Exertion;
		$data["s_languages"] = $this->Languages;
		$data["s_activeipaddress"] = $this->Activeipaddress;
		$data["s_activeuserid"] = $this->Activeuserid;
		$data["s_activelanguage"] = $this->Activelanguage;
		return $data;
	}
	
	function Filter($_id)
   	{
		$this->FilterId = $_id;
		$this->Edited = time();
   	}
	
	function GetXML()
	{
		return "<val active=\"".base64_encode($this->Activestate)."\" edited=\"".base64_encode($this->Edited)."\" editor=\"".base64_encode($this->Editor)."\" activeipaddresses=\"".base64_encode($this->Activeipaddress)."\" activeuserids=\"".base64_encode($this->Activeuserid)."\" activelanguages=\"".base64_encode($this->Activelanguage)."\" expires=\"".base64_encode($this->Expiredate)."\" creator=\"".base64_encode($this->Creator)."\" created=\"".base64_encode($this->Created)."\" userid=\"".base64_encode($this->Userid)."\" ip=\"".base64_encode($this->IP)."\" filtername=\"".base64_encode($this->Filtername)."\" filterid=\"".base64_encode($this->FilterId)."\" reason=\"".base64_encode($this->Reason)."\" exertion=\"".base64_encode($this->Exertion)."\" languages=\"".base64_encode($this->Languages)."\" />\r\n";
	}
	
	function Load()
	{
		$dataProvider = new FileEditor(PATH_FILTER . $this->FilterId . FILE_EXTENSION_FILTER);
		$dataProvider->Load();
		$this->Creator = $dataProvider->Result["s_creator"];
		$this->Created = $dataProvider->Result["s_created"];
		$this->Editor = $dataProvider->Result["s_editor"];
		$this->Edited = $dataProvider->Result["s_edited"];
		$this->IP = $dataProvider->Result["s_ip"];
		$this->Expiredate = $dataProvider->Result["s_expiredate"];
		$this->Userid = $dataProvider->Result["s_userid"];
		$this->Reason = $dataProvider->Result["s_reason"];
		$this->Filtername = $dataProvider->Result["s_filtername"];
		$this->FilterId = $dataProvider->Result["s_filterid"];
		$this->Activestate = $dataProvider->Result["s_activestate"];
		$this->Exertion = $dataProvider->Result["s_exertion"];
		$this->Languages = $dataProvider->Result["s_languages"];
		$this->Activeipaddress = $dataProvider->Result["s_activeipaddress"];
		$this->Activeuserid = $dataProvider->Result["s_activeuserid"];
		$this->Activelanguage = $dataProvider->Result["s_activelanguage"];
	}
	
	function Save()
	{
		$dataProvider = new FileEditor(PATH_FILTER . $this->FilterId . FILE_EXTENSION_FILTER);
		$dataProvider->Save($this->GetData());
	}
	
	function Destroy()
	{
		if(file_exists(PATH_FILTER . $this->FilterId . FILE_EXTENSION_FILTER))
			@unlink(PATH_FILTER . $this->FilterId . FILE_EXTENSION_FILTER);
	}
}

class Rating extends Action
{	
	var $Fullname = "";
	var $Email="";
	var $Company="";
	var $InternId="";
	var $UserId="";
	var $RateQualification=0;
	var $RatePoliteness=0;
	var $RateComment=0;

	function Rating($_id)
	{
		$this->Id = $_id;
		$this->Folder = PATH_RATINGS;
		$this->TargetFile = $this->Folder . $_id . "." . EX_CHAT_RATING;
	}
	
	function GetData()
	{
		$data = Array();
		$data["s_rate_c"] = $this->RateComment;
		$data["s_rate_p"] = $this->RatePoliteness;
		$data["s_rate_q"] = $this->RateQualification;
		$data["s_fullname"] = $this->Fullname;
		$data["s_email"] = $this->Email;
		$data["s_company"] = $this->Company;
		$data["s_internid"] = $this->InternId;
		$data["s_userid"] = $this->UserId;
		return $data;
	}
	
	function IsFlood($counter=0)
	{
		$files = getDirectory(PATH_RATINGS,".htm",true);
		foreach($files as $rate)
		{
			$parts = explode("_",str_replace("." . EX_CHAT_RATING,"",$rate));
			if(count($parts) > 1 && $parts[1] == getServerParam("REMOTE_ADDR") && @filemtime(PATH_RATINGS . $rate) > time()-86400)
			{
				if(++$counter >= MAX_RATES_PER_DAY)
					return true;
			}
		}
		return false;
	}
	
	function Load()
	{
		$dataProvider = new FileEditor($this->TargetFile);
		$dataProvider->Load();
		if(count($dataProvider->Errors) == 0)
		{
			$this->RateComment = $dataProvider->Result["s_rate_c"];
			$this->RatePoliteness = $dataProvider->Result["s_rate_p"];
			$this->RateQualification = $dataProvider->Result["s_rate_q"];
			$this->Fullname = $dataProvider->Result["s_fullname"];
			$this->Email = $dataProvider->Result["s_email"];
			$this->Company = $dataProvider->Result["s_company"];
			$this->InternId = $dataProvider->Result["s_internid"];
			$this->UserId = $dataProvider->Result["s_userid"];
			$this->Created = @filemtime($this->TargetFile);
		}
	}
	
	function GetXML($_internal,$_full)
	{
		if($_full)
		{
			$intern = (isset($_internal[getInternSessIdByUserId($this->InternId)])) ? $_internal[getInternSessIdByUserId($this->InternId)]->Fullname : $this->InternId;
			return "<val id=\"".base64_encode($this->Id)."\" rc=\"".base64_encode($this->RateComment)."\" rp=\"".base64_encode($this->RatePoliteness)."\" rq=\"".base64_encode($this->RateQualification)."\" fn=\"".base64_encode($this->Fullname)."\" em=\"".base64_encode($this->Email)."\" co=\"".base64_encode($this->Company)."\" ii=\"".base64_encode($intern)."\" ui=\"".base64_encode($this->UserId)."\" />\r\n";
		}
		else
			return "<val id=\"".base64_encode($this->Id)."\" />\r\n";
	}
	
	function Save()
	{
		$dataProvider = new FileEditor($this->TargetFile);
		$dataProvider->Save($this->GetData());
		return (count($dataProvider->Errors) == 0);
	}
}

class ClosedTicket extends Action
{
	function ClosedTicket($_id)
	{
		$this->Id = $_id;
		$this->Folder = PATH_MESSAGES_CLOSED;
		if(!@is_dir($this->Folder))
			createFolder($this->Folder);
			
		$this->TargetFile = $this->Folder . $_id . "." . EX_MAIL;
	}
	
	function GetData()
	{
		$data = Array();
		$data["s_sender"] = $this->Sender;
		return $data;
	}
	
	function Announce()
	{
		@touch(FILE_ANS_MESSAGES);
	}
	
	function Load()
	{
		$dataProvider = new FileEditor($this->TargetFile);
		$dataProvider->Load();
		$this->Sender = $dataProvider->Result["s_sender"];
	}

	function GetXML($_time)
	{
		return "<cl id=\"".base64_encode($this->Id)."\" ed=\"".base64_encode($this->Sender)."\" ti=\"".base64_encode($_time)."\"/>\r\n";
	}
	
	function Save()
	{
		$dataProvider = new FileEditor($this->TargetFile);
		$dataProvider->Save($this->GetData());
		return (count($dataProvider->Errors) == 0);
	}
}

class UserTicket extends Action
{
	var $Fullname = "";
	var $Email="";
	var $Group="";
	var $Company="";
	var $IP="";
	var $UserId="";
	
	function UserTicket($_file)
	{
		$this->Id = getId(8);
		$this->Folder = PATH_MESSAGES;
		$this->TargetFile = $this->Folder . $_file;
	}
	
	function GetData()
	{
		$data = Array();
		$data["s_text"] = $this->Text;
		$data["s_id"] = $this->Id;
		$data["s_fullname"] = $this->Fullname;
		$data["s_email"] = $this->Email;
		$data["s_company"] = $this->Company;
		$data["s_group"] = $this->Group;
		$data["s_ip"] = $this->IP;
		$data["s_userid"] = $this->UserId;
		return $data;
	}
	
	function IsFlood()
	{
		$files = getDirectory($this->Folder,".htm",true);
		foreach($files as $message)
		{
			$parts = explode("_",str_replace("." . EX_MAIL,"",$message));
			if($parts[0] == $this->IP && (time()-MAX_MAIL_INTERVAL) < $parts[1])
				return true;
		}
		return false;
	}
	
	function Load()
	{
		$dataProvider = new FileEditor($this->TargetFile);
		$dataProvider->Load();
		if(count($dataProvider->Errors) == 0)
		{
			$this->Text = $dataProvider->Result["s_text"];
			$this->Fullname = $dataProvider->Result["s_fullname"];
		 	$this->Email = $dataProvider->Result["s_email"];
			$this->Company = $dataProvider->Result["s_company"];
			$this->Group = $dataProvider->Result["s_group"];
			$this->IP = $dataProvider->Result["s_ip"];
			$this->Id = $dataProvider->Result["s_id"];
			$this->UserId = $dataProvider->Result["s_userid"];
			$this->Created = @filemtime($this->TargetFile);
		}
	}

	function GetXML($_groups,$_full)
	{
		if($_full)
		{
			$xml = "<val id=\"".base64_encode($this->Id)."\" ct=\"".base64_encode($this->Created)."\" gr=\"".base64_encode($this->Group)."\" mt=\"".base64_encode($this->Text)."\" fn=\"".base64_encode($this->Fullname)."\" em=\"".base64_encode($this->Email)."\" co=\"".base64_encode($this->Company)."\" ui=\"".base64_encode($this->UserId)."\" />\r\n";
			if(@file_exists(PATH_MESSAGES_CLOSED . $this->Id . "." . EX_MAIL))
			{
				$ticket = new ClosedTicket($this->Id);
				$ticket->Load();
				$xml .= $ticket->GetXML(@filemtime(PATH_MESSAGES_CLOSED . $this->Id . "." . EX_MAIL));
			}
			return $xml;
		}
		else
			return "<val id=\"".base64_encode($this->Id)."\" ct=\"".base64_encode($this->Created)."\" />\r\n";
	}

	function Save()
	{
		$dataProvider = new FileEditor($this->TargetFile);
		$dataProvider->Save($this->GetData());
		return (count($dataProvider->Errors) == 0);
	}
}

class Response
{
	var $XML = "";
	var $Internals="";
	var $Groups="";
	var $InternalProfilePictures="";
	var $InternalWebcamPictures="";
	var $InternalVcards="";
	var $Typing="";
	var $Exceptions="";
	var $InternalFiles="";
	var $Filter="";
	var $Authentications="";
	var $Posts="";
	var $Login;
	var $Ratings="";
	var $Messages="";
	var $Archive="";
	var $Resources="";
	var $GlobalHash;
	
	function SetStandardResponse($_code,$_sub)
	{
		$this->XML = "<response><value id=\"".base64_encode($_code)."\" />" . $_sub . "</response>";
	}
	
	function SetValidationError($_code,$_addition="")
	{
		if(!isnull($_addition))
			$this->XML = "<validation_error value=\"".base64_encode($_code)."\" error=\"".base64_encode($_addition)."\" />";
		else
			$this->XML = "<validation_error value=\"".base64_encode($_code)."\" />";
	}
	
	function GetXML()
	{
		return "<?xml version=\"1.0\" encoding=\"UTF-8\" ?><livezilla_xml><livezilla_version>".base64_encode(VERSION)."</livezilla_version>" . $this->XML . "</livezilla_xml>";
	}
}

class FileEditor
{
	var $Errors;
	var $Result;
	var $TargetFile;
	
	function FileEditor($_file)
	{
		$this->Errors = Array();
		$this->TargetFile = $_file;
	}
	
	function Load()
	{
		if(file_exists($this->TargetFile))
		{
			$handle = @fopen ($this->TargetFile, "r");
			while (!@feof($handle))
	   			$this->Result .= @fgets($handle, 4096);
			
			$length = strlen($this->Result);
			$this->Result = @unserialize($this->Result);
			@fclose($handle);
			
			if($length == 0 || gettype($this->Result) != "array")
				$this->Errors[] = 0;
		}
	}

	function Save($_data)
	{
		$handle = @fopen($this->TargetFile, "w");
		if($_data != null)
		{
			if($handle)
				$length = @fputs($handle,serialize($_data));
			else
				$this->Errors[] = 1;
		}
		@fclose($handle);
	}
}

class DatabaseEditor
{
	var $TargetFile;
	var $Errors;
	var $Result;
	
	function DatabaseEditor($_file)
	{
		$this->Errors = Array();
		$this->TargetFile = $_file;
	}
	
	function Load()
	{
		$this->Result = getDataSet($this->TargetFile);
	}
	
	function Save($_data)
	{
		writeDataSet($this->TargetFile,$_data);
	}
}

class DataProvider
{
	var $Provider;
	function DataProvider($_index)
	{
		if(DATA_SOURCE == DATA_SOURCE_DATABASE)
			$this->Provider = new DatabaseEditor($_index);
		else
			$this->Provider = new FileEditor($_index);
		
		$this->Provider->Errors = array();
		$this->Provider->TargetFile = $_index;
	}
	
	function Load()
	{
		$this->Provider->Load();
		$this->Errors = $this->Provider->Errors;
		$this->Result = $this->Provider->Result;
	}

	function Save($_data)
	{
		$this->Provider->Save($_data);
		$this->Errors = $this->Provider->Errors;
	}
}

class Action
{
	var $Id;
	var $Folder;
	var $ReceiverUserId;
	var $ReceiverBrowserId;
	var $SenderSessId;
	var $SenderUserId;
	var $SenderFullname;
	var $Text;
	var $BrowserId;
	var $Status;
	var $TargetFile;
	var $Extension;
	var $Created;
	
	function Save()
	{
		if(DATA_SOURCE == DATA_SOURCE_FILE && !@is_dir($this->Folder))
			createFolder($this->Folder);
			
		$dataProvider = new DataProvider($this->TargetFile);
		$dataProvider->Save($this->GetData());
		return (count($dataProvider->Errors) == 0);
	}
	
	function Destroy()
	{
		unlinkDataSet($this->TargetFile);
	}
}

class Alert extends Action
{
	var $Alert;
	
	function Alert($_id,$_receiver,$_browserId)
	{
		$this->Id = $_id;
		$this->ReceiverUserId = $_receiver;
		$this->BrowserId = $_browserId;
		$this->Extension = EX_ALERT;
		$this->Folder = PATH_DATA_EXTERNAL . $this->ReceiverUserId . "/browsers/" . $this->BrowserId . "/";
		$this->TargetFile = $this->Folder . $_id . "." . $this->Extension;
	}
	
	function GetData()
	{
		$data = Array();
		$data["s_alert"] = $this->Alert;
		return $data;
	}
	
	function Load()
	{
		$dataProvider = new DataProvider($this->TargetFile);
		$dataProvider->Load();
		if(count($dataProvider->Errors) == 0)
		{
			$this->Alert = $dataProvider->Result["s_alert"];
			$this->Created = @filemtime($this->TargetFile);
		}
	}
	
	function GetCommand()
	{
		return "lz_tracking_send_alert('".base64_encode($this->Alert)."');";
	}
}

class Permission extends Action
{
	var $Type;
	var $Result;
	function Permission($_type,$_userId,$_browserId,$_id)
	{
		$this->Id = $_id;
		$this->ReceiverUserId = $_userId;
		$this->ReceiverBrowserId = $_browserId;
		$this->Extension = EX_PERMISSION;
		$this->Folder = PATH_DATA_EXTERNAL . $_userId . "/browsers/" . $_browserId . "/";
		$this->TargetFile = $this->Folder . $this->Id . "." . $this->Extension;
	}
	
	function GetData()
	{
		$data = Array();
		$data["s_result"] = $this->Result;
		return $data;
	}
	
	function Load()
	{
		$dataProvider = new DataProvider($this->TargetFile);
		$dataProvider->Load();
		if(count($dataProvider->Errors) == 0)
			$this->Result = $dataProvider->Result["s_result"];
	}
}

class FileUploadRequest extends Action
{
	var $Error = false;
	var $Download = false;
	function FileUploadRequest($_fileId,$_receiverId)
	{
		$this->Id = getId(5);
		$this->FileId = $_fileId;
		$this->ReceiverUserId = $_receiverId;
		$this->Extension = EX_FILE_UPLOAD_REQUEST;
		$this->Folder = PATH_DATA_INTERNAL . $this->ReceiverUserId . "/";
		$this->TargetFile = $this->Folder . $this->FileId . "." . $this->Extension;
	}
	
	function GetData()
	{
		$data = Array();
		$data["s_id"] = $this->Id;
		$data["s_filename"] = $this->Text;
		$data["s_senderUserId"] = $this->SenderUserId;
		$data["s_senderBrowserId"] = $this->SenderBrowserId;
		$data["s_error"] = $this->Error;
		return $data;
	}
	
	function Load()
	{
		$dataProvider = new DataProvider($this->TargetFile);
		$dataProvider->Load();
		if(count($dataProvider->Errors) == 0)
		{
			$this->Id = $dataProvider->Result["s_id"];
			$this->Text = $dataProvider->Result["s_filename"];
			$this->SenderUserId = $dataProvider->Result["s_senderUserId"];
			$this->SenderBrowserId = $dataProvider->Result["s_senderBrowserId"];
			$this->Error = $dataProvider->Result["s_error"];
		}
	}
	
	function GetFile()
	{
		return PATH_UPLOADS_EXTERNAL . $this->SenderUserId . "/" . base64_encode($this->Text);
	}
}

class Guide extends Action
{
	var $TargetURL;
	var $Ask;
	
	function Guide($_sender,$_receiver,$_browserId,$_ask)
	{
		$this->Id = getId(USER_ID_LENGTH);
		$this->Ask = $_ask;
		$this->SenderSessId = $_sender;
		$this->ReceiverUserId = $_receiver;
		$this->BrowserId = $_browserId;
		$this->Extension = EX_BROWSER_GUIDE;
		$this->Folder = PATH_DATA_EXTERNAL . $this->ReceiverUserId . "/browsers/" . $this->BrowserId . "/";
		$this->TargetFile = $this->Folder . $this->SenderSessId . "." . $this->Extension;
	}
	
	function GetData()
	{
		$data = Array();
		$data["s_text"] = $this->Text;
		$data["s_ask"] = $this->Ask;
		$data["s_url"] = $this->TargetURL;
		$data["s_sendername"] = $this->SenderFullname;
		return $data;
	}
	
	function Load()
	{
		$dataProvider = new DataProvider($this->TargetFile);
		$dataProvider->Load();
		if(count($dataProvider->Errors) == 0)
		{
			$this->Text = $dataProvider->Result["s_text"];
			$this->Ask = $dataProvider->Result["s_ask"];
			$this->TargetURL = $dataProvider->Result["s_url"];
			$this->SenderFullname = $dataProvider->Result["s_sendername"];
			$this->Created = @filemtime($this->TargetFile);
		}
	}
	
	function GetCommand()
	{
		return "lz_tracking_guide('".base64_encode($this->TargetURL)."','".base64_encode($this->Text)."','".$this->Ask."','".time()."');";
	}
}

class Forward extends Action
{
	var $Conversation;
	var $TargetSessId;
	var $TargetGroupId;
	var $Processed = false;
	
	function Forward($_receiverUserId,$_receiverBrowserId,$_senderSessId)
	{
		$this->Id = getId(5);
		$this->ReceiverUserId = $_receiverUserId;
		$this->BrowserId = $_receiverBrowserId;
		$this->SenderSessId = $_senderSessId;
		$this->Extension = EX_CHAT_FORWARDING;
		$this->Folder = PATH_DATA_EXTERNAL . $this->ReceiverUserId . "/browsers/" . $this->BrowserId . "/";
		$this->TargetFile = $this->Folder . $this->SenderSessId . "." . $this->Extension;
	}
	
	function GetData()
	{
		$data = Array();
		$data["p_id"] = $this->Id;
		$data["p_sendersessid"] = $this->SenderSessId;
		$data["p_targetsessid"] = $this->TargetSessId;
		$data["p_targetgroupid"] = $this->TargetGroupId;
		$data["p_browserid"] = $this->BrowserId;
		$data["p_receiveruserid"] = $this->ReceiverUserId;
		$data["p_conversation"] = $this->Conversation;
		$data["p_text"] = $this->Text;
		$data["p_processed"] = $this->Processed;
		return $data;
	}
	
	function Load()
	{
		$dataProvider = new DataProvider($this->TargetFile);
		$dataProvider->Load();
		if(count($dataProvider->Errors) == 0)
		{
			$this->Id = $dataProvider->Result["p_id"];
			$this->SenderSessId = $dataProvider->Result["p_sendersessid"];
			$this->TargetSessId = $dataProvider->Result["p_targetsessid"];
			$this->TargetGroupId = $dataProvider->Result["p_targetgroupid"];
			$this->BrowserId = $dataProvider->Result["p_browserid"];
			$this->ReceiverUserId = $dataProvider->Result["p_receiveruserid"];
			$this->Conversation = $dataProvider->Result["p_conversation"];
			$this->Text = $dataProvider->Result["p_text"];
			$this->Processed = $dataProvider->Result["p_processed"];
		}
	}
}

class ChatRequest extends Action
{
	function ChatRequest($_sender,$_receiver,$_browserId,$_status)
   	{
		$this->Id = getId(USER_ID_LENGTH);
		$this->SenderSessId = $_sender;
		$this->ReceiverUserId = $_receiver;
		$this->BrowserId = $_browserId;
		$this->Folder = PATH_DATA_EXTERNAL . $this->ReceiverUserId . "/browsers/" . $this->BrowserId . "/";
		$this->SetStatus($_status);
   	}
	
	function SetStatus($_status)
	{
		$this->Status = $_status;
		if($this->Status == REQUEST_STATUS_ACTIVE)
			$this->Extension = EX_CHAT_REQUEST;
		else if($this->Status == REQUEST_STATUS_ACCEPTED)
			$this->Extension = EX_CHAT_REQUEST_ACCEPTED;
		else if($this->Status == REQUEST_STATUS_DECLINED)
			$this->Extension = EX_CHAT_REQUEST_DECLINED;
		$this->TargetFile =  $this->Folder . $this->SenderSessId . "." . $this->Extension;
	}
	
	function GetData()
	{
		$data = Array();
		$data["p_id"] = $this->Id;
		$data["p_text"] = $this->Text;
		$data["p_senderuserid"] = $this->SenderUserId;
		$data["p_sendername"] = $this->SenderFullname;
		return $data;
	}
	
	function Load()
	{
		$dataProvider = new DataProvider($this->TargetFile);
		$dataProvider->Load();
		if(count($dataProvider->Errors) == 0)
		{
			$this->Id = $dataProvider->Result["p_id"];
			$this->Text = $dataProvider->Result["p_text"];
			$this->SenderFullname = $dataProvider->Result["p_sendername"];
			$this->SenderUserId = $dataProvider->Result["p_senderuserid"];
			$this->Created = @filemtime($this->TargetFile);
		}
	}
	
	function Save()
	{
		if(DATA_SOURCE == DATA_SOURCE_FILE && !@is_dir($this->Folder))
			createFolder($this->Folder);
		$actionfiles = getDirectory($this->Folder,true);
		foreach($actionfiles as $actionfile)
		{
			if(strpos($actionfile, "." . EX_CHAT_REQUEST_DECLINED) !== false)
				unlinkDataSet($this->Folder . $actionfile);
				
			if(strpos($actionfile, "." . EX_CHAT_REQUEST_ACCEPTED) !== false)
				unlinkDataSet($this->Folder . $actionfile);
				
			if(strpos($actionfile, "." . EX_CHAT_REQUEST) !== false)
				return false;
		}
		$dataProvider = new DataProvider($this->TargetFile);
		$dataProvider->Save($this->GetData());
		return (count($dataProvider->Errors) == 0);
	}
	
	function Decline()
	{
		renameDataSet($this->TargetFile,str_replace(".".$this->Extension,".".EX_CHAT_REQUEST_DECLINED,$this->TargetFile));
		$this->SetStatus(REQUEST_STATUS_DECLINED);
	}
	
	function Accept()
	{
		if(dataSetExists($this->TargetFile))
		{
			@touchDataSet($this->TargetFile);
			renameDataSet($this->TargetFile,str_replace(".".$this->Extension,".".EX_CHAT_REQUEST_ACCEPTED,$this->TargetFile));
		}
		$this->SetStatus(REQUEST_STATUS_ACCEPTED);
	}
	
	function GetCommand($_template,$_text,$_width,$_height)
	{
		return "lz_tracking_request_conversation('" . base64_encode($this->Id) . "','".base64_encode($_template)."','" . base64_encode($_text) . "',".$_width.",".$_height.");";
	}
}

class Chat
{
	var $Activated;
	var $Closed;
	var $Declined;
	var $MemberCount;
	var $TargetFileExternal;
	var $TargetFileInternal;
	var $TargetFileInternalActivation;
	var $TargetFileExternalActivation;
	var $TargetFileInternalClosed;
	var $TargetFileInternalDeclined;
	var $InternalUser;
	var $ExternalUser;
	var $FileError;
	var $FileUploadRequest = null;
	
	function Chat()
	{
		if(func_num_args() == 2)
		{
			$this->Id = getId(5);
			$this->ExternalUser = func_get_arg(0);
			$this->InternalUser = func_get_arg(1);
			$this->SetDirectories();
		}
		else
		{
			$this->Load(func_get_arg(0));
		}
	}
	
	function SetDirectories()
	{
		$this->TargetFileExternal = $this->ExternalUser->Folder . $this->InternalUser->SessId . "." . EX_CHAT_OPEN;
		$this->TargetFileInternal = $this->InternalUser->Folder . $this->ExternalUser->BrowserId . "." . EX_CHAT_OPEN;
		$this->TargetFileExternalActivation = $this->InternalUser->Folder . $this->ExternalUser->BrowserId . "." . EX_CHAT_ACTIVE;
		$this->TargetFileInternalClosed = $this->ExternalUser->Folder . $this->InternalUser->SessId . "." . EX_CHAT_INTERN_CLOSED;
		$this->TargetFileInternalDeclined = $this->ExternalUser->Folder . $this->InternalUser->SessId . "." . EX_CHAT_INTERN_DECLINED;
		$this->TargetFileInternalActivation = $this->ExternalUser->Folder . $this->InternalUser->SessId . "." . EX_CHAT_ACTIVE;
		
		$this->Declined = (dataSetExists($this->TargetFileInternalDeclined));
		$this->Closed = (dataSetExists($this->TargetFileInternalClosed));
	}
	
	function IsActivated($_systemId)
	{
		$activated = false;
		$files = getDirectory($this->ExternalUser->Folder,false);
		foreach($files as $file)
			if(strpos($file,EX_CHAT_ACTIVE) !== false)
				if($_systemId == null || ($_systemId != null && strpos(trim($file),trim($_systemId)) !== false))
					$activated = true;
		
		$existance = array(dataSetExists($this->TargetFileExternalActivation),dataSetExists($this->TargetFileInternalActivation),dataSetExists($this->TargetFileInternal),dataSetExists($this->TargetFileExternal));
		$this->Activated = (($existance[0] && $existance[1]) ? CHAT_STATUS_ACTIVE : (($existance[0] || $existance[1]) ? CHAT_STATUS_WAITING : CHAT_STATUS_OPEN));
		if(!$this->Closed)
			$this->Closed = ($existance[0] && !$existance[1]);
		return $activated;
	}
	
	function GetData()
	{
		$data = Array();
		$data["s_internal_userid"] = $this->InternalUser->UserId;
		$data["s_internal_sessid"] = $this->InternalUser->SessId;
		$data["s_internal_fullname"] = $this->InternalUser->Fullname;
		$data["s_external_userid"] = $this->ExternalUser->UserId;
		$data["s_external_browserid"] = $this->ExternalUser->BrowserId;
		$data["s_id"] = $this->Id;
		return $data;
	}

	function InternalDecline()
	{
		$dataProvider = new DataProvider($this->TargetFileInternalDeclined);
		$dataProvider->Save(Array());
	}
	
	function InternalClose()
	{
		$dataProvider = new DataProvider($this->TargetFileInternalClosed);
		$dataProvider->Save(Array());
	}
	
	function InternalActivate($_internal)
	{
		$this->InternalUser = $_internal;
		if(!$this->IsActivated(null))
		{
			$this->TargetFileInternalActivation = $this->ExternalUser->Folder . $this->InternalUser->SessId . "." . EX_CHAT_ACTIVE;
			$dataProvider = new DataProvider($this->TargetFileInternalActivation);
			$dataProvider->Save(Array());
		}
		else
		{
			unlinkDataSet($this->TargetFileInternal);
			unlinkDataSet($this->TargetFileExternalActivation);
		}
	}
	
	function ExternalActivate()
	{
		$dataProvider = new DataProvider($this->TargetFileExternalActivation);
		$dataProvider->Save(Array());
	}
	
	function ExternalDestroy()
	{
		unlinkDataSet($this->TargetFileExternal);
		unlinkDataSet($this->TargetFileInternalActivation);
		unlinkDataSet($this->TargetFileInternalClosed);
		unlinkDataSet($this->TargetFileInternalDeclined);
	}
	
	function InternalDestroy()
	{
		unlinkDataSet($this->TargetFileExternalActivation);
		unlinkDataSet($this->TargetFileInternal);
	}
	
	function Load($_chatfile)
	{
		if($_chatfile == null)
			$dataProvider = new DataProvider($this->TargetFileExternal);
		else
			$dataProvider = new DataProvider($_chatfile);
		$dataProvider->Load();

		$this->Id = $dataProvider->Result["s_id"];
		$this->InternalUser = new UserInternal($dataProvider->Result["s_internal_sessid"],$dataProvider->Result["s_internal_userid"]);
		$this->ExternalUser = new ExternalChat($dataProvider->Result["s_external_userid"],$dataProvider->Result["s_external_browserid"]);
		
		$this->InternalUser->Fullname = $dataProvider->Result["s_internal_fullname"];
		$this->SetDirectories();
	}
	
	function Save()
	{
		if(DATA_SOURCE == DATA_SOURCE_FILE && !@is_dir($this->ExternalUser->Folder))
			createFolder($this->ExternalUser->Folder);

		$dataProvider = new DataProvider($this->TargetFileExternal);
		$dataProvider->Save($this->GetData());
		
		$dataProvider = new DataProvider($this->TargetFileInternal);
		$dataProvider->Save($this->GetData());
	}
	
	function Destroy()
	{
		unlinkDataSet($this->TargetFileExternal);
		unlinkDataSet($this->TargetFileInternal);
	}
}

class DataSet
{
	var $LastActive;
	var $Data;
	var $Name;
	var $Size;
}
?>