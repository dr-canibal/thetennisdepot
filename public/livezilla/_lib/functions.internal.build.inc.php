<?php

/****************************************************************************************
* LiveZilla intern.build.inc.php
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

function buildFiles()
{
	global $INTERNAL,$RESPONSE;
	$RESPONSE->InternalFiles = "";
	$dirs = array(PATH_UPLOADS_INTERNAL,PATH_UPLOADS_EXTERNAL);
	foreach($dirs as $tdir)
	{
		$subdirs = getDirectory($tdir,false,true);
		foreach($subdirs as $dir)
		{
			if(@is_dir($tdir.$dir."/"))
			{
				$owner = ($tdir == PATH_UPLOADS_INTERNAL) ? getInternSessIdByUserId($dir) : GROUP_EVERYONE_EXTERN;
				if($owner != null)
				{
					$files = getDirectory($tdir.$dir."/",false,true);
					foreach($files as $file)
					{
						if($file != FILE_INDEX)
							$RESPONSE->InternalFiles .= "<v os=\"".base64_encode($owner)."\" oi=\"".base64_encode($dir)."\" n=\"".base64_encode($file)."\" s=\"".base64_encode(filesize($tdir.$dir."/".$file))."\" e=\"".base64_encode(($tdir != PATH_UPLOADS_INTERNAL))."\" />\r\n";
					}
				}
				else
				{
					deleteDirectory($tdir.$dir."/");
				}
			}
		}
	}
}

function buildFilters()
{
	global $FILTERS,$GROUPS,$INTERNAL,$RESPONSE;
	$RESPONSE->Filter = "";
	if(!$INTERNAL[CALLER_SYSTEM_ID]->IsExternal($GROUPS))
		return;
	foreach($FILTERS->Filters as $id => $filter)
	{
		if($filter->Expiredate != -1 && ($filter->Expiredate + $filter->Created) < time())
			$filter->Destroy();
		else
			$RESPONSE->Filter .= $filter->GetXML();
	}
}

function buildResources($xml="",$count=0,$last=0)
{
	global $RESPONSE,$INTERNAL;
	$resources = getResources($_POST[POST_INTERN_XMLCLIP_RESSOURCES_END_TIME]);
	foreach($resources as $res)
	{
		if(++$count <= DATA_ITEM_LOADS || $res["edited"] == $last)
			$xml .= "<r rid=\"".base64_encode($res["id"])."\" di=\"".base64_encode($res["discarded"])."\" oid=\"".base64_encode($res["owner"])."\" eid=\"".base64_encode($res["editor"])."\" ty=\"".base64_encode($res["type"])."\" ti=\"".base64_encode($res["title"])."\" ed=\"".base64_encode($last = $res["edited"])."\" pid=\"".base64_encode($res["parentid"])."\" ra=\"".base64_encode($res["rank"])."\">".base64_encode($res["html"])."</r>\r\n";
		else
			break;
	}
	$RESPONSE->Resources = (strlen($xml) > 0) ? $xml : null;
}

function buildArchive($_external,$xml="",$count=0,$last=0)
{
	global $RESPONSE,$INTERNAL;
	$permission = ($INTERNAL[CALLER_SYSTEM_ID]->GetPermission(PERMISSION_CHATS) != PERMISSION_NONE);
	$chats = getArchive($_POST[POST_INTERN_XMLCLIP_ARCHIVE_END_TIME]);
	foreach($chats as $chat)
	{
		if(++$count < DATA_ITEM_LOADS || $chat["closed"] == $last)
		{
			if($chat["internal_id"] != CALLER_SYSTEM_ID && $_external && $permission)
				$xml .= "<c cid=\"".base64_encode($chat["chat_id"])."\" iid=\"".base64_encode($chat["internal_id"])."\" eid=\"".base64_encode($chat["external_id"])."\" en=\"".base64_encode($chat["fullname"])."\" ts=\"".base64_encode($chat["time"])."\" cl=\"".base64_encode($last = $chat["closed"])."\" te=\"".base64_encode($chat["endtime"])."\" em=\"".base64_encode($chat["email"])."\" co=\"".base64_encode($chat["company"])."\" il=\"".base64_encode($chat["iso_language"])."\" ho=\"".base64_encode($chat["host"])."\" ip=\"".base64_encode($chat["ip"])."\" gzip=\"".base64_encode($chat["gzip"])."\">".base64_encode($chat["html"])."</c>\r\n";
			else
				$xml .= "<c cl=\"".base64_encode($last = $chat["closed"])."\" />\r\n";
		}
		else
			break;
	}
	$RESPONSE->Archive = (strlen($xml) > 0) ? $xml : null;
}

function buildRatings($_external,$xml="",$count=0)
{
	global $RESPONSE,$INTERNAL;

	if(file_exists(FILE_ANS_RATINGS) && $_POST[POST_INTERN_XMLCLIP_RATING_END_TIME] != XML_CLIP_NULL && $_POST[POST_INTERN_XMLCLIP_RATING_END_TIME] >= @filemtime(FILE_ANS_RATINGS))
		return;

	$permission = $INTERNAL[CALLER_SYSTEM_ID]->GetPermission(PERMISSION_RATINGS);
	
	$ratings = getDirectory(PATH_RATINGS,"*.htm",true);
	sort($ratings);
	$ratingss = array();
	foreach($ratings as $file)
		$ratingss[$file] = substr($file,0,10);
	asort($ratingss);
	foreach($ratingss as $file => $time)
	{
		if(stristr($file,EX_CHAT_RATING))
		{
			$id = substr($file,0,strlen($file)-5);
			if($time > $_POST[POST_INTERN_XMLCLIP_RATING_END_TIME] || $_POST[POST_INTERN_XMLCLIP_RATING_END_TIME] == XML_CLIP_NULL)
			{
				$rating = new Rating($id);
				$rating->Load();
				$xml .= $rating->GetXML($INTERNAL,(($rating->InternId == $INTERNAL[CALLER_SYSTEM_ID]->UserId && $permission != PERMISSION_NONE) || $permission == PERMISSION_FULL));
				if(++$count >= DATA_ITEM_LOADS)
					break;
			}
			else if($time < time()-DATA_LIFETIME)
				@unlink(PATH_RATINGS . $file);
		}
	}
	$RESPONSE->Ratings = (strlen($xml) > 0) ? $xml : null;
}

function buildMessages($_external,$xml="",$count=0)
{
	global $RESPONSE,$INTERNAL,$GROUPS;
	
	if(file_exists(FILE_ANS_MESSAGES) && $_POST[POST_INTERN_XMLCLIP_MESSAGES_END_TIME] != XML_CLIP_NULL && $_POST[POST_INTERN_XMLCLIP_MESSAGES_END_TIME] >= @filemtime(FILE_ANS_MESSAGES))
		return;

	$permission = $INTERNAL[CALLER_SYSTEM_ID]->GetPermission(PERMISSION_MESSAGES);
	$messages = getDirectory(PATH_MESSAGES,"*.htm",true);
	$messagess = array();
	foreach($messages as $file)
		$messagess[$file] = @filemtime(PATH_MESSAGES . $file);
	asort($messagess);
	foreach($messagess as $file => $time)
	{
		if(stristr($file,EX_MAIL))
		{
			$id = substr($file,0,strlen($file)-5);
			if($time > $_POST[POST_INTERN_XMLCLIP_MESSAGES_END_TIME] || $_POST[POST_INTERN_XMLCLIP_MESSAGES_END_TIME] == XML_CLIP_NULL)
			{
				$last = $time;
				$message = new UserTicket($file);
				$message->Load();
				$xml .= $message->GetXML($GROUPS,((in_array($message->Group,$INTERNAL[CALLER_SYSTEM_ID]->Groups) && $permission != PERMISSION_NONE) || $permission == PERMISSION_FULL));
				if(++$count >= DATA_ITEM_LOADS)
					break;
			}
			else if($time < time()-DATA_LIFETIME)
				@unlink(PATH_MESSAGES . $file);
		}
	}

	if($count == 0)
	{
		$closed = getDirectory(PATH_MESSAGES_CLOSED,"*.htm",true);
		$closeds = array();
		
		foreach($closed as $file)
			$closeds[$file] = @filemtime(PATH_MESSAGES_CLOSED . $file);
		asort($closeds);
		
		foreach($closeds as $file => $time)
		{
			if(stristr($file,EX_MAIL))
			{
				if($time > $_POST[POST_INTERN_XMLCLIP_MESSAGES_END_TIME] || $_POST[POST_INTERN_XMLCLIP_MESSAGES_END_TIME] == XML_CLIP_NULL)
				{
					$id = str_replace("." . EX_MAIL,"",$file);
					$ticket = new ClosedTicket($id);
					$ticket->Load();
					
					$xml .= $ticket->GetXML($time);
				}
				else if($time < time()-DATA_LIFETIME)
					@unlink(PATH_MESSAGES_CLOSED . $file);
			}
		}
	}
	$RESPONSE->Messages = (strlen($xml) > 0) ? $xml : null;
}

function buildNewPosts()
{
	global $INTERNAL,$CONFIG,$RESPONSE;
	foreach($INTERNAL[CALLER_SYSTEM_ID]->GetPosts() as $systemid => $postarray)
		foreach($postarray as $post)
		{
			$RESPONSE->Posts .= $post->GetXml($systemid);
			logPost($systemid,$post,false);
		}
}

function buildIntern()
{
	global $CONFIG,$INTERNAL,$GROUPS,$RESPONSE;
	$builder = new InternalXMLBuilder($CONFIG,$INTERNAL[CALLER_SYSTEM_ID],$INTERNAL,$GROUPS);
	$builder->Generate();
	$RESPONSE->Internals = $builder->XMLInternal;
	$RESPONSE->Typing .= $builder->XMLTyping;
	$RESPONSE->InternalProfilePictures = $builder->XMLProfilePictures;
	$RESPONSE->InternalWebcamPictures = $builder->XMLWebcamPictures;
	$RESPONSE->Groups = $builder->XMLGroups;
	$RESPONSE->InternalVcards = $builder->XMLVisitcards;
}

function buildExtern($objectCount=0)
{
	global $CONFIG,$VISITOR,$INTERNAL,$GROUPS,$RESPONSE;
	$RESPONSE->Tracking = "";
	if(count($VISITOR) > 0)
	{
		$builder = new ExternalXMLBuilder($CONFIG,$INTERNAL[CALLER_SYSTEM_ID],$VISITOR,(isset($_POST[POST_INTERN_RESYNC]) || LOGIN || $_POST[POST_INTERN_XMLCLIP_HASH_EXTERN] == XML_CLIP_NULL),($GROUPS[$INTERNAL[CALLER_SYSTEM_ID]->Groups[0]]["gr_extern"]));
		$builder->SessionFileSizes = $INTERNAL[CALLER_SYSTEM_ID]->VisitorFileSizes;
		$builder->StaticReload = $INTERNAL[CALLER_SYSTEM_ID]->VisitorStaticReload;
		$builder->Generate();
		$RESPONSE->Tracking = $builder->XMLCurrent;
		foreach($builder->DiscardedObjects as $uid => $list)
		{
			$RESPONSE->Tracking .= "<cd id=\"".base64_encode($uid)."\">\r\n";
			if($list != null)
				foreach($builder->DiscardedObjects[$uid] as $list => $bid)
					$RESPONSE->Tracking .= " <bd id=\"".base64_encode($bid)."\" />\r\n";
			$RESPONSE->Tracking .= "</cd>\r\n";
		}
		$RESPONSE->Typing .= $builder->XMLTyping;
		$INTERNAL[CALLER_SYSTEM_ID]->VisitorFileSizes = $builder->SessionFileSizes;
		$INTERNAL[CALLER_SYSTEM_ID]->VisitorStaticReload = $builder->StaticReload;
		if($builder->GetAll && !LOGIN)
			$RESPONSE->Tracking .= "<resync />\r\n";
		$objectCount = $builder->ObjectCounter;
	}
	else
		$INTERNAL[CALLER_SYSTEM_ID]->VisitorFileSizes = array();
		
	$RESPONSE->Tracking .= "<sync>".base64_encode($objectCount)."</sync>\r\n";
}
?>
