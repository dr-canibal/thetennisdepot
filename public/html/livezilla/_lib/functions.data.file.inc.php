<?php

/****************************************************************************************
* LiveZilla functions.data.file.inc.php
* 
* Copyright 2001-2009 SPAUN Power GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors. It is strongly 
* recommended to desist from editing this file.
* 
***************************************************************************************/ 

function getDataSetTime($_file,$_read=false)
{
	global $DATASETS;
	$files = array();
	if(!$_read)
	{
		if(!isset($DATASETS[$_file]))
		{
			$DATASETS[$_file] = new DataSet();
			$DATASETS[$_file]->LastActive = @filemtime($_file);
		}
		return $DATASETS[$_file]->LastActive;
	}
	else
	{
		return @filemtime($_file);
	}
}

function unlinkDataSet($_file)
{
	@unlink($_file);
}

function getArchive($_time)
{
	 return array();
}

function getResources($_time)
{
	 return array();
}

function dataSetExists($_file)
{
	return file_exists($_file);
}

function touchDataSet($_file)
{
	@touch($_file);
}

function getDataSetSize($_file)
{
	global $DATASETS;
	if(!isset($DATASETS[$_file]))
		$DATASETS[$_file] = new DataSet();
	if($DATASETS[$_file]->Data == null)
		$DATASETS[$_file]->Size = @filesize($_file);
	return $DATASETS[$_file]->Size;
}

function renameDataSet($_file,$_new)
{
	if(file_exists($_file))
		rename($_file,$_new);
}

function deleteDirectory($_dir)
{
	if(@is_dir($_dir))
	{
		$handle=@opendir($_dir);
		$files = Array();
		while ($filename = @readdir($handle)) 
		{
		   	if($filename != "." && $filename != "..") 
			{
		    	if(@is_dir($_dir."/".$filename))
					deleteDirectory($_dir."/".$filename);
				else
					@unlink($_dir."/".$filename);
		    }
		}
		@closedir($handle);
		@rmdir($_dir);
		return true;
	}
	return false;
}

function getDirectory($_dir,$_oddout,$_ignoreSource=false)
{
	$files = array();
	if(!@is_dir($_dir))
		return $files;
	$handle=@opendir($_dir);
	
	while ($filename = @readdir ($handle)) 
	   	if ($filename != "." && $filename != ".." && ($_oddout == false || !stristr($filename,$_oddout)))
			if($_oddout != "." || ($_oddout == "." && @is_dir($_dir . "/" . $filename)))
	       		$files[]=$filename;
				
	@closedir($handle);
	return $files;
}

function logPost($_systemId,$_post,$_out)
{
	global $CONFIG,$INTERNAL,$VISITOR,$GROUPS;

	if(isset($INTERNAL[$_systemId]) || $_systemId == GROUP_EVERYONE_INTERN || $_systemId == GROUP_EVERYONE_EXTERN)
		return;

 	$_file = PATH_CHAT_LOGS . CALLER_TYPE_EXTERNAL . "_" . CALLER_SYSTEM_ID . "_" . $_systemId . ".log";
	if(!@is_dir(PATH_CHAT_LOGS))
		@mkdir(PATH_CHAT_LOGS);

	if(!file_exists(PATH_CHAT_LOGS . FILE_INDEX))
		if(!@copy(LIVEZILLA_PATH . "_log/" . FILE_INDEX,PATH_CHAT_LOGS . FILE_INDEX))
			createFile(PATH_CHAT_LOGS . FILE_INDEX,"",false);

	$parts = explode("~",$_systemId);
	if($_out)
	{
		$sender = $INTERNAL[CALLER_SYSTEM_ID]->Fullname;
		$_post->Text = strip_tags($_post->Text);
	}
	else
		$sender = $VISITOR[$parts[0]]->Browsers[$parts[1]]->Fullname;

	if(!file_exists($_file))
	{
		$user = $VISITOR[$parts[0]]->Browsers[$parts[1]];
		if($_out)
			$user->Load();
		logit("Live Support Chat Transcript [powered by LiveZilla.net]\r\nDate: " . date("r")."\r\n-------------------------------------------------------\r\nName: " . $user->Fullname . "\r\nEmail: " . $user->Email . "\r\nCompany: " . $user->Company ."\r\n-------------------------------------------------------",$_file);
		
	}
	$special = strlen($_post->Special) > 0 ? " (" . $_post->Special . ")" : "";
	logit("|" . date("m.d.y H:i:s") . "| " . $sender .  ": " . $_post->Text . $special,$_file);
	
	if(rand(1,20) == 1 && $CONFIG["gl_rm_logs"] != null)
	{
		$chats = getDirectory(PATH_CHAT_LOGS,FILE_INDEX,true);
		foreach($chats as $chat)
			if(@filemtime(PATH_CHAT_LOGS . $chat) < (time()-$CONFIG["gl_rm_logs_time"]))
				@unlink(PATH_CHAT_LOGS . $chat);
	}
}

function getChatTranscript($_id,$_text="")
{
	$myChats = array();
	$chats = getDirectory(PATH_CHAT_LOGS,FILE_INDEX,true);
	foreach($chats as $chat)
		if(strpos($chat,trim($_id)) !== false)
			$myChats[@filemtime(PATH_CHAT_LOGS . $chat)] = PATH_CHAT_LOGS . $chat;
	ksort($myChats);
	foreach($myChats as $chat)
	{
		$_text .= getFile($chat) . "\r\n";
		logit($chat);
	}
	
	return $_text;
}

function buildTracking()
{
	global $VISITOR,$CONFIG;
	$VISITOR = Array();
	$userdirs = getDirectory(PATH_DATA_EXTERNAL,".");
	$outdatedVisitors = Array();
	foreach($userdirs as $userdir)
	{
		if(@is_dir(PATH_DATA_EXTERNAL . $userdir))
		{
			$userid = $userdir;
			$browsers = getDirectory(PATH_DATA_EXTERNAL . $userdir . "/browsers/",".");
			if(@is_dir(PATH_DATA_EXTERNAL . $userdir . "/browsers/"))
			{
				if(count($browsers) == 0 && @filemtime(PATH_DATA_EXTERNAL . $userdir) < (time()-$CONFIG["timeout_track"]))
					array_push($outdatedVisitors,$userid);
					
				foreach($browsers as $browserid)
				{
					$browser = new ExternalBrowser($browserid,$userid);
					$chat = new ExternalChat($userid,$browserid);
					
					if(!isset($VISITOR[$userid]))
						$VISITOR[$userid] = new UserExternal($userid);
					
					if(file_exists($browser->SessionFile))
					{
						if(@filemtime($browser->SessionFile) < time()-$CONFIG["timeout_track"])
						{
							$browser->Destroy();
							continue;
						}
						$VISITOR[$userid]->Browsers[$browserid] = $browser;
					}
					else if(file_exists($chat->SessionFile))
					{
						$chat->Load();
						if(@filemtime($chat->SessionFile) < time()-$CONFIG["timeout_clients"])
						{
							$chat->Destroy($CONFIG);
							continue;
						}
						
						if($chat->FirstActive == null)
							$chat->FirstActive = time();
							
						$chat->History[0] = array($chat->FirstActive,(((getServerParam("HTTPS") != null) && (getServerParam("HTTPS") == "on")) ? SCHEME_HTTP_SECURE : SCHEME_HTTP) . getServerParam("HTTP_HOST") . str_replace(FILE_SERVER_FILE,"",getServerParam("PHP_SELF") . FILE_CHAT),$chat->Code,true);
						$VISITOR[$userid]->Browsers[$browserid] = $chat;
					}
					else
					{
						$browser->Destroy();
						$chat->Destroy($CONFIG);
					}
				}
			}
			else
				array_push($outdatedVisitors,$userid);
		}
	}
	foreach($outdatedVisitors as $folder)
	{
		if(@is_dir(PATH_DATA_EXTERNAL . $folder))
			deleteDirectory( PATH_DATA_EXTERNAL . $folder );
	}
}

function removeOutdatedDataSets($_config){}
function getDataSets(){}
function unloadDataProvider(){}
function logStatus($_internalSystemId,$_status){}
function confirmStatus($_internalSystemId,$_status){}

?>