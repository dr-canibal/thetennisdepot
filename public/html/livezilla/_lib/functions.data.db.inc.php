<?php

/****************************************************************************************
* LiveZilla functions.data.db.inc.php
* 
* Copyright 2001-2009 SPAUN Power GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors. It is strongly 
* recommended to desist from editing this file.
* 
***************************************************************************************/ 

function createDBConnector()
{
	global $CONFIG,$DB_CONNECTOR,$DATASETS;
	if(function_exists("mysql_connect"))
	{
		$DB_CONNECTOR = @mysql_connect($CONFIG["gl_db_host"], $CONFIG["gl_db_user"], $CONFIG["gl_db_pass"]);
		if($DB_CONNECTOR)
		{
			if(@mysql_select_db($CONFIG["gl_db_name"], $DB_CONNECTOR))
			{
				define("DB_CONNECTION",true);
				getDataSets();
				return;
			}
		}
	}
	define("DB_CONNECTION",false);
}

function queryDB($_log,$_sql)
{
	global $CONFIG,$DB_CONNECTOR;
	$res = @mysql_query($_sql, $DB_CONNECTOR);
	return $res;
}

function unloadDataProvider()
{
	global $DB_CONNECTOR;
	if($DB_CONNECTOR)
		@mysql_close($DB_CONNECTOR);
}

function getDataSets()
{
	global $DATASETS;
	$DATASETS = array();
	$result = queryDB(true,"SELECT `file`,`time`,`size` FROM `".DB_PREFIX.DATABASE_DATA."`;");
	while($row = @mysql_fetch_array($result, MYSQL_BOTH))
	{
		$DATASETS[$row["file"]] = new DataSet();
		$DATASETS[$row["file"]]->LastActive = $row["time"];
		$DATASETS[$row["file"]]->Size = $row["size"];
	}
}

function removeOutdatedDataSets($_config)
{
	if(rand(0,100) == 0)
	{
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_DATA."` WHERE size<6 AND time<" . (time()-3600));
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_DATA."` WHERE time<" . (time()-DATA_LIFETIME));
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_INTERNAL."` WHERE time<" . (time()-DATA_LIFETIME));
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_CHATS."` WHERE html='0' AND time<" . (time()-DATA_LIFETIME));
		if($_config["gl_log"] != null)
			queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_CHATS."` WHERE time<" . (time()-$_config["gl_rm_logs_time"]));
	}
}

function getDataSetTime($_file,$_read=false)
{
	global $DATASETS;
	if(!$_read)
	{
		$files = array();
		if(!isset($DATASETS[$_file]))
			return -1;
		return $DATASETS[$_file]->LastActive;
	}
	else
	{
		if($result = queryDB(true,"SELECT `time` FROM `".DB_PREFIX.DATABASE_DATA."` WHERE file='".mysql_real_escape_string($_file)."' LIMIT 1"))
		{
			$row = mysql_fetch_array($result, MYSQL_BOTH);
			return $row[0];
		}
		return -1;
	}
}

function unlinkDataSet($_file)
{
	queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_DATA."` WHERE file='".mysql_real_escape_string($_file)."'");
}

function dataSetExists($_file)
{
	global $DATASETS;
	return isset($DATASETS[$_file]);
}

function touchDataSet($_file)
{
	queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_DATA."` SET time='".time()."' WHERE file='".mysql_real_escape_string($_file)."'");
}

function getDataSet($_file)
{
	if($result = queryDB(true,"SELECT `data` FROM `".DB_PREFIX.DATABASE_DATA."` WHERE file='".mysql_real_escape_string($_file)."' LIMIT 1"))
	{
		$row = mysql_fetch_array($result, MYSQL_BOTH);
		return @unserialize($row[0]);
	}
}

function getArchive($_time)
{
	$chats = array();
	if($_time == XML_CLIP_NULL)
		$_time = 0;
	
	if($result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_CHATS."` WHERE html!='0' AND closed>".mysql_real_escape_string($_time)." AND closed<".time()." AND internal_id !='0' ORDER BY `time` ASC"))
	{
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
			$chats[] = $row;
	}
	return $chats;
}

function getResources($_time)
{
	$res = array();
	if($_time == XML_CLIP_NULL)
		$_time = 0;

	if($result = queryDB(true,"SELECT * FROM `".DB_PREFIX.DATABASE_RESOURCES."` WHERE edited>".mysql_real_escape_string($_time)." AND edited<".time()." ORDER BY `edited` ASC"))
	{
		while($row = mysql_fetch_array($result, MYSQL_BOTH))
			$res[] = $row;
	}
	if(rand(1,20) == 1)
		queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_RESOURCES."` WHERE discarded=1 AND edited<" . (time()-DATA_LIFETIME));
	return $res;
}

function writeDataSet($_file,$_data)
{
	$serdat = serialize($_data);
	$result = queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_DATA."` SET data='".mysql_real_escape_string($serdat)."',time='".time()."',size='".strlen($serdat)."' WHERE file='".mysql_real_escape_string($_file)."' LIMIT 1;");
	if(mysql_affected_rows() == 0)
		queryDB(false,"INSERT INTO `".DB_PREFIX.DATABASE_DATA."` (`file`,`time`,`data`,`size`) VALUES ('".mysql_real_escape_string($_file)."',".time().",'".mysql_real_escape_string(serialize($_data))."','".strlen($serdat)."');");
}

function getDataSetSize($_file)
{
	global $DATASETS;
	return $DATASETS[$_file]->Size;
}

function renameDataSet($_file,$_new)
{
	queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_DATA."` SET file='".mysql_real_escape_string($_new)."' WHERE file='".mysql_real_escape_string($_file)."';");
}

function deleteDirectory($_dir)
{
	queryDB(true,"DELETE FROM `".DB_PREFIX.DATABASE_DATA."` WHERE file like '".mysql_real_escape_string($_dir)."%';");
	return false;
}

function getDirectory($_dir,$_oddout,$_ignoreSource=false)
{
	global $DATASETS;
	$files = array();
	if($_ignoreSource)
	{
		if(!@is_dir($_dir))
			return $files;
		$handle=@opendir($_dir);
		
		while ($filename = @readdir ($handle)) 
		   	if ($filename != "." && $filename != ".." && ($_oddout == false || !stristr($filename,$_oddout)))
				if($_oddout != "." || ($_oddout == "." && @is_dir($_dir . "/" . $filename)))
		       		$files[]=$filename;
					
		@closedir($handle);
	}
	else
	{
		foreach($DATASETS as $file => $ds)
		{
			if(strpos($file,$_dir) !== false && substr($file,0,strlen($_dir)) == $_dir)
			{
				$parts = explode("/",str_replace($_dir,"",$file));
				if(count($parts) > 0)
					if(!in_array($parts[0],$files))
						$files[]=$parts[0];
			}
		}
	}
	return $files;
}

function logPost($_systemId,$_post,$_out)
{
	global $CONFIG,$INTERNAL,$VISITOR,$GROUPS;
	$_file = CALLER_TYPE_EXTERNAL . "_" . CALLER_SYSTEM_ID . "_" . $_systemId;
	$parts = explode("~",$_systemId);
	if(isset($VISITOR[$parts[0]]))
	{
		$sender = ($_out) ? $INTERNAL[CALLER_SYSTEM_ID]->Fullname : $VISITOR[$parts[0]]->Browsers[$parts[1]]->Fullname;
		$_file = md5($_file);
		$post = "\r\n|" . date("m.d.y H:i:s") . "| " . $sender .  ": " . strip_tags($_post->Text) . (strlen($_post->Special) > 0 ? " (" . $_post->Special . ")" : "");
		if(is_resource($result = queryDB(true,"SELECT `html` FROM `".DB_PREFIX.DATABASE_CHATS."` WHERE chat_id='".mysql_real_escape_string($_file)."' AND endtime=0 AND html='0'")) && mysql_num_rows($result) == 0)
		{	
			$user = $VISITOR[$parts[0]]->Browsers[$parts[1]];
			$user->Load();
			$VISITOR[$parts[0]]->LoadStaticInformation();
			$static = $VISITOR[$parts[0]]->ExternalStatic;
			$static->Load();

			$etpl = getFile(TEMPLATE_EMAIL_TRANSCRIPT);
			$etpl = str_replace("<!--date-->",date("r"),$etpl);
			$etpl = str_replace("<!--name-->",$user->Fullname,$etpl);
			$etpl = str_replace("<!--email-->",$user->Email,$etpl);
			$etpl = str_replace("<!--company-->",$user->Company,$etpl);
			$etpl = str_replace("<!--chat-->",$post,$etpl);
			
			queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_CHATS."` (`id`, `time`, `endtime`, `closed`, `chat_id`, `external_id`, `fullname`, `internal_id`, `html`, `plain`, `email`, `company`, `iso_language`, `host`, `ip`, `gzip`) VALUES ('".md5(uniqid(rand()))."',".time().",0,0,'".mysql_real_escape_string($_file)."','0','0','0','0','".mysql_real_escape_string($etpl)."','0','0','0','0','0',0);");
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_CHATS."` SET internal_id='".mysql_real_escape_string($user->DesiredChatPartner)."',external_id='".mysql_real_escape_string($user->SystemId)."',fullname='".mysql_real_escape_string($user->Fullname)."',email='".mysql_real_escape_string($user->Email)."',company='".mysql_real_escape_string($user->Company)."',iso_language='".mysql_real_escape_string($static->Language)."',host='".mysql_real_escape_string($static->Host)."',ip='".mysql_real_escape_string($static->IP)."' WHERE chat_id='".mysql_real_escape_string($_file)."' AND html='0' ORDER BY `time` DESC LIMIT 1");
		}
		else
		{
			$result = queryDB(true,"SELECT `html`,`plain` FROM `".DB_PREFIX.DATABASE_CHATS."` WHERE chat_id='".mysql_real_escape_string($_file)."' AND html='0' ORDER BY `time` ASC LIMIT 1");
			$row = mysql_fetch_array($result, MYSQL_BOTH);
			queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_CHATS."` SET plain='".mysql_real_escape_string($row["plain"].$post)."' WHERE chat_id='".mysql_real_escape_string($_file)."' AND html='0' ORDER BY `time` ASC LIMIT 1");
		}
	}
}

function processResource($_resId,$_html,$_type,$_title,$_disc,$_parentId,$_rank)
{
	$result = queryDB(true,"SELECT `id` FROM `".DB_PREFIX.DATABASE_RESOURCES."` WHERE id='".mysql_real_escape_string($_resId)."'");
	if(mysql_num_rows($result) == 0)
		queryDB(true,$result = "INSERT INTO `".DB_PREFIX.DATABASE_RESOURCES."` (`id`,`owner`,`editor`,`html`,`edited`,`title`,`created`,`type`,`discarded`,`parentid`,`rank`) VALUES ('".mysql_real_escape_string($_resId)."','".mysql_real_escape_string(CALLER_SYSTEM_ID)."','".mysql_real_escape_string(CALLER_SYSTEM_ID)."','".mysql_real_escape_string($_html)."','".time()."','".mysql_real_escape_string($_title)."','".time()."','".mysql_real_escape_string($_type)."','0','".mysql_real_escape_string($_parentId)."','".mysql_real_escape_string($_rank)."')");
	else
		queryDB(true,$result = "UPDATE `".DB_PREFIX.DATABASE_RESOURCES."` SET html='".mysql_real_escape_string($_html)."',editor='".mysql_real_escape_string(CALLER_SYSTEM_ID)."',title='".mysql_real_escape_string($_title)."',edited='".time()."',discarded='".mysql_real_escape_string($_disc)."',parentid='".mysql_real_escape_string($_parentId)."',rank='".mysql_real_escape_string($_rank)."' WHERE id='".mysql_real_escape_string($_resId)."' LIMIT 1");
}

function archiveChat($_chatId,$_html,$_timeStart,$_timeEnd,$_gzip)
{
	queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_CHATS."` SET html='".mysql_real_escape_string($_html)."',time='".mysql_real_escape_string($_timeStart)."',endtime='".mysql_real_escape_string($_timeEnd)."',closed='".mysql_real_escape_string(time()+1)."',gzip='".mysql_real_escape_string($_gzip)."',internal_id='".mysql_real_escape_string(CALLER_SYSTEM_ID)."' WHERE chat_id='".mysql_real_escape_string($_chatId)."' AND html='0' ORDER BY `time` ASC LIMIT 1");
}

function closeOpenChatLog($_internalSystemId,$_externalSystemId)
{
	$file = md5(CALLER_TYPE_EXTERNAL . "_" . $_internalSystemId . "_" . $_externalSystemId);
	queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_CHATS."` SET endtime='".time()."' WHERE chat_id='".mysql_real_escape_string($file)."' AND endtime=0 ORDER BY `time` ASC LIMIT 1");
}

function getChatTranscript($_id,$_text="")
{
	$result = queryDB(true,"SELECT `plain` FROM `".DB_PREFIX.DATABASE_CHATS."` WHERE external_id='".mysql_real_escape_string($_id)."' ORDER BY `time` ASC");
	while($row = mysql_fetch_array($result, MYSQL_BOTH))
		$_text .= $row["plain"] . "\r\n\r\n";
	return $_text;
}

function logStatus($_internalSystemId,$_status)
{
	queryDB(true,"INSERT INTO `".DB_PREFIX.DATABASE_INTERNAL."` (`id`,`time`,`time_confirmed`,`internal_id`,`status`) VALUES ('".mysql_real_escape_string(bigTime())."','".time()."','".time()."','".mysql_real_escape_string($_internalSystemId)."','".mysql_real_escape_string($_status)."');");
}

function confirmStatus($_internalSystemId,$_status)
{
	queryDB(true,"UPDATE `".DB_PREFIX.DATABASE_INTERNAL."` SET time_confirmed='".time()."' WHERE internal_id='".mysql_real_escape_string($_internalSystemId)."' AND status=".mysql_real_escape_string($_status)." ORDER BY `time` DESC LIMIT 1");
}

function buildTracking()
{
	global $VISITOR,$CONFIG,$DATASETS;
	$VISITOR = array();
	$outdatedVisitors = array();
	$itarray = array_keys($DATASETS);
	foreach($itarray as $file)
	{
		$dataset = $DATASETS[$file];
		if(strpos($file,PATH_DATA_EXTERNAL) !== false && substr($file,0,strlen(PATH_DATA_EXTERNAL)) == PATH_DATA_EXTERNAL)
		{
			$userid = substr(str_replace(PATH_DATA_EXTERNAL,"",$file),0,USER_ID_LENGTH);
			$browsers = getDirectory(PATH_DATA_EXTERNAL . $userid . "/browsers/",".");
			if(count($browsers) > 0)
			{
				foreach($browsers as $browserid)
				{
					$browser = new ExternalBrowser($browserid,$userid);
					$chat = new ExternalChat($userid,$browserid);
					
					if(!isset($VISITOR[$userid]))
						$VISITOR[$userid] = new UserExternal($userid);
					
					if(($bStime = getDataSetTime($browser->SessionFile)) != -1)
					{
						if($bStime < time()-$CONFIG["timeout_track"])
						{
							$browser->Destroy();
							continue;
						}
						$VISITOR[$userid]->Browsers[$browserid] = $browser;
					}
					else if(($cStime = getDataSetTime($chat->SessionFile)) != -1)
					{
						$chat->Load();
						if($cStime < time()-$CONFIG["timeout_clients"])
						{
							$chat->Destroy($CONFIG);
							continue;
						}
						
						if($chat->FirstActive == null)
							$chat->FirstActive = time();

						$chat->History[0] = array($chat->FirstActive,LIVEZILLA_URL . FILE_CHAT,$chat->Code,true);
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
			{
				$outdatedVisitors[] = $userid;
			}
		}
	}
	foreach($outdatedVisitors as $folder)
		deleteDirectory(PATH_DATA_EXTERNAL . $folder);
}
?>