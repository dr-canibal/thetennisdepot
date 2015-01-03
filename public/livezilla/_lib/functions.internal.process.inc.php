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

function processAcceptedConversations()
{
	if(isset($_POST[POST_INTERN_PROCESS_ACCEPTED_CHAT . "_va"]))
		appendAcceptedConversations();
}

function processAuthentications()
{
	if(isset($_POST[POST_INTERN_PROCESS_AUTHENTICATIONS . "_va"]))
		appendAuthentications();
}

function processStatus()
{
	global $INTERNAL;
	if(isset($_POST[POST_INTERN_USER_STATUS]))
	{
		appendStatus();
	}
	else
		confirmStatus(CALLER_SYSTEM_ID,$INTERNAL[CALLER_SYSTEM_ID]->Status);
}

function processAlerts()
{
	if(isset($_POST[POST_INTERN_PROCESS_ALERTS . "_va"]))
		appendAlerts();
}

function processClosures()
{
	if(isset($_POST[POST_INTERN_PROCESS_CLOSURES . "_va"]))
		appendClosures();
}

function processPosts()
{
	if(isset($_POST[POST_INTERN_PROCESS_POSTS . "_va"]))
		appendPosts();
}

function processForwards()
{
	if(isset($_POST[POST_INTERN_PROCESS_FORWARDS . "_va"]))
		appendForwards();
}

function processRequests()
{
	if(isset($_POST[POST_INTERN_PROCESS_REQUESTS . "_va"]))
		appendRequests();
}

function processGuides()
{
	if(isset($_POST[POST_INTERN_PROCESS_GUIDES . "_va"]))
		appendGuides();
}

function processFilters()
{
	if(isset($_POST[POST_INTERN_PROCESS_FILTERS . "_va"]))
		appendFilters();
}

function processDeleteFiles()
{
	if(isset($_POST[POST_INTERN_PROCESS_DELETE_FILES . "_va"]))
		appendDeleteFiles();
}

function processVisitcards()
{
	if(isset($_POST[POST_INTERN_PROCESS_VISITCARDS]))
		appendVisitcards();
}

function processProfilePictures()
{
	if(isset($_POST[POST_INTERN_PROCESS_PICTURES]))
		appendProfilePictures();
}

function processWebcamPictures()
{
	if(isset($_POST[POST_INTERN_PROCESS_PICTURES_WEBCAM]))
		appendWebcamPictures();
}

function processBannerPictures()
{
	if(isset($_POST[POST_INTERN_PROCESS_BANNERS]))
		appendBannerPictures();
}

function processPermissions()
{
	if(isset($_POST[POST_INTERN_PROCESS_PERMISSIONS . "_va"]))
		appendPermissions();
}

function processExternalReloads()
{
	if(isset($_POST[POST_INTERN_PROCESS_EXTERNAL_RELOADS]))
		appendExternalReloads();
}

function processClosedTickets()
{
	if(isset($_POST[POST_INTERN_PROCESS_ACCEPTED_MESSAGES . "_va"]))
		appendClosedTickets();
}

function processArchiveChats()
{
	if(isset($_POST[POST_INTERN_PROCESS_CHATS . "_va"]))
		appendArchivedChats();
}

function processResources()
{
	if(isset($_POST[POST_INTERN_PROCESS_RESOURCES]))
		appendResources();
}

function appendArchivedChats($xml="")
{
	global $RESPONSE;
	$cids = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_CHATS . "_va"]);
	$html = explode(POST_ACTION_VALUE_SPLITTER,slashesStrip($_POST[POST_INTERN_PROCESS_CHATS . "_vb"]));
	$timeend = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_CHATS . "_vc"]);
	$gzip = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_CHATS . "_vd"]);
	$times = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_CHATS . "_ve"]);
	foreach($cids as $key => $id)
	{
		archiveChat($cids[$key],$html[$key],$times[$key],$timeend[$key],$gzip[$key]);
		$xml .= "<c cid=\"".base64_encode($cids[$key])."\" te=\"".base64_encode($timeend[$key])."\" />\r\n";
	}
	$RESPONSE->SetStandardResponse(1,$xml);
}

function appendResources($xml="")
{
	global $RESPONSE;
	$rids = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_RESOURCES . "_va"]);
	$html = explode(POST_ACTION_VALUE_SPLITTER,slashesStrip($_POST[POST_INTERN_PROCESS_RESOURCES . "_vb"]));
	$type = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_RESOURCES . "_vc"]);
	$title = explode(POST_ACTION_VALUE_SPLITTER,slashesStrip($_POST[POST_INTERN_PROCESS_RESOURCES . "_vd"]));
	$disc = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_RESOURCES . "_ve"]);
	$parent = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_RESOURCES . "_vf"]);
	$rank = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_RESOURCES . "_vg"]);
	foreach($rids as $key => $id)
	{
		processResource($rids[$key],$html[$key],$type[$key],$title[$key],$disc[$key],$parent[$key],$rank[$key]);
		$xml .= "<r rid=\"".base64_encode($rids[$key])."\" disc=\"".base64_encode($disc[$key])."\" />\r\n";
	}
	$RESPONSE->SetStandardResponse(1,$xml);
}

function appendClosedTickets()
{
	global $INTERNAL;
	$msgnames = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_ACCEPTED_MESSAGES . "_va"]);
	$msgids = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_ACCEPTED_MESSAGES . "_vb"]);
	foreach($msgids as $key => $id)
	{
		$ticket = new ClosedTicket($id);
		if(!@file_exists($ticket->TargetFile))
		{
			$ticket->Sender = $msgnames[$key];
			$ticket->Save();
			$ticket->Announce();
		}
	}
}

function appendExternalReloads()
{
	global $INTERNAL;
	$INTERNAL[CALLER_SYSTEM_ID]->ExternalReloads = Array();
	$userids = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_EXTERNAL_RELOADS]);
	foreach($userids as $id)
		$INTERNAL[CALLER_SYSTEM_ID]->VisitorStaticReload[$id] = true;
}

function appendPermissions()
{
	$systemids = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_PERMISSIONS . "_va"]);
	$ids = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_PERMISSIONS . "_vb"]);
	$results = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_PERMISSIONS . "_vc"]);
	$types = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_PERMISSIONS . "_vd"]);

	foreach($ids as $key => $id)
	{
		$user_parts = explode("~",$systemids[$key]);
		$permission = new Permission($types[$key],$user_parts[0],$user_parts[1],$ids[$key]);
		$permission->Result = $results[$key];
		$permission->Save();
	}
}

function appendForwards()
{
	global $INTERNAL;
	$users = explode(POST_ACTION_VALUE_SPLITTER,utf8_decode($_POST[POST_INTERN_PROCESS_FORWARDS . "_va"]));
	$receivers = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_FORWARDS . "_vb"]);
	$additions = explode(POST_ACTION_VALUE_SPLITTER,slashesStrip($_POST[POST_INTERN_PROCESS_FORWARDS . "_vc"]));
	$conversations = explode(POST_ACTION_VALUE_SPLITTER,slashesStrip($_POST[POST_INTERN_PROCESS_FORWARDS . "_vd"]));
	$targetgroups = explode(POST_ACTION_VALUE_SPLITTER,slashesStrip($_POST[POST_INTERN_PROCESS_FORWARDS . "_ve"]));
	
	foreach($users as $key => $user)
	{
		$parts = explode("~",$user);
		$forward = new Forward($parts[0],$parts[1],$INTERNAL[CALLER_SYSTEM_ID]->SystemId);
		$forward->TargetSessId = $receivers[$key];
		$forward->TargetGroupId = $targetgroups[$key];
		if(strlen($additions[$key]) > 0)
			$forward->Text = $additions[$key];
		if(strlen($conversations[$key]) > 0)
			$forward->Conversation = $conversations[$key];
		$forward->Save();
	}
}

function appendAuthentications()
{
	global $INTERNAL,$RESPONSE;
	$users = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_AUTHENTICATIONS . "_va"]);
	$passwords = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_AUTHENTICATIONS . "_vb"]);
	foreach($users as $key => $user)
	{
		$INTERNAL[$user]->ChangePassword($passwords[$key]);
		$RESPONSE->Authentications = "<val userid=\"".base64_encode($user)."\" pass=\"".base64_encode($passwords[$key])."\" />\r\n";
	}
}

function appendGuides()
{
	global $INTERNAL,$VISITOR;
	$guides = Array();
	$visitors = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_GUIDES . "_va"]);
	$asks = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_GUIDES . "_vb"]);
	$urls = explode(POST_ACTION_VALUE_SPLITTER,slashesStrip($_POST[POST_INTERN_PROCESS_GUIDES . "_vc"]));
	$browids = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_GUIDES . "_vd"]);
	$texts = explode(POST_ACTION_VALUE_SPLITTER,slashesStrip($_POST[POST_INTERN_PROCESS_GUIDES . "_ve"]));
	foreach($visitors as $key => $visitor)
	{
		if(isset($VISITOR[$visitors[$key]]))
		{
			$guide = new Guide($INTERNAL[CALLER_SYSTEM_ID]->SessId,$visitors[$key],$browids[$key],$asks[$key]);
			$guide->SenderFullname = $INTERNAL[CALLER_SYSTEM_ID]->Fullname;
			$guide->Text = $texts[$key];
			$guide->TargetURL = $urls[$key];
			$guides[$visitors[$key].$browids[$key]] = $guide;
		}
	}
	foreach($guides as $guide)
		$guide->Save();
}

function appendAlerts()
{
	global $VISITOR;
	$alerts = explode(POST_ACTION_VALUE_SPLITTER,slashesStrip($_POST[POST_INTERN_PROCESS_ALERTS . "_va"]));
	$visitors = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_ALERTS . "_vb"]);
	$browsers = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_ALERTS . "_vc"]);
	foreach($alerts as $key => $text)
	{
		if(isset($visitors[$key]) && isset($VISITOR[$visitors[$key]]))
		{
			$alert = new Alert(bigTime(),$visitors[$key],$browsers[$key]);
			$alert->Alert = $alerts[$key];
			$alert->Save();
		}
	}
}

function appendRequests()
{
	global $INTERNAL,$VISITOR;
	$visitors = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_REQUESTS . "_va"]);
	$browids = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_REQUESTS . "_vb"]);
	$reqnames = explode(POST_ACTION_VALUE_SPLITTER,slashesStrip($_POST[POST_INTERN_PROCESS_REQUESTS . "_vc"]));
	$reqids = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_REQUESTS . "_vd"]);
	$reqtexts = explode(POST_ACTION_VALUE_SPLITTER,slashesStrip($_POST[POST_INTERN_PROCESS_REQUESTS . "_ve"]));
	foreach($reqids as $key => $requestid)
		if(isset($VISITOR[$visitors[$key]]))
		{
			$request = new ChatRequest($INTERNAL[CALLER_SYSTEM_ID]->SessId,$visitors[$key],$browids[$key],REQUEST_STATUS_ACTIVE);
			$request->SenderUserId = $INTERNAL[CALLER_SYSTEM_ID]->UserId;
			$request->SenderFullname = ($INTERNAL[CALLER_SYSTEM_ID]->Fullname != null) ? $INTERNAL[CALLER_SYSTEM_ID]->Fullname : $INTERNAL[CALLER_SYSTEM_ID]->UserId;
			$request->Text = $reqtexts[$key];
			$request->Save();
		}
}

function appendFilters()
{
	$creators = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_FILTERS . "_va"]);
	$createds = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_FILTERS . "_vb"]);
	$editors = explode(POST_ACTION_VALUE_SPLITTER,slashesStrip($_POST[POST_INTERN_PROCESS_FILTERS . "_vc"]));
	$ips = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_FILTERS . "_vd"]);
	$expiredates = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_FILTERS . "_ve"]);
	$userids = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_FILTERS . "_vf"]);
	$filternames = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_FILTERS . "_vg"]);
	$reasons = explode(POST_ACTION_VALUE_SPLITTER,slashesStrip($_POST[POST_INTERN_PROCESS_FILTERS . "_vh"]));
	$filterids = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_FILTERS . "_vi"]);
	$activestates = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_FILTERS . "_vj"]);
	$actiontypes = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_FILTERS . "_vk"]);
	$exertions = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_FILTERS . "_vl"]);
	$languages = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_FILTERS . "_vm"]);
	$activeuserids = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_FILTERS . "_vn"]);
	$activeipaddresses = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_FILTERS . "_vo"]);
	$activelanguages = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_FILTERS . "_vp"]);
	
	foreach($filterids as $key => $id)
	{
		$filter = new Filter($filterids[$key]);
		$filter->Creator = $creators[$key];
		$filter->Created = ($createds[$key] != "0") ? $createds[$key] : time();
		$filter->Editor = $editors[$key];
		$filter->Edited = time();
		$filter->IP = $ips[$key];
		$filter->Expiredate = $expiredates[$key];
		$filter->Userid = $userids[$key];
		$filter->Reason = $reasons[$key];
		$filter->Filtername = $filternames[$key];
		$filter->Activestate = $activestates[$key];
		$filter->Exertion = $exertions[$key];
		$filter->Languages = $languages[$key];
		$filter->Activeipaddress = $activeipaddresses[$key];
		$filter->Activeuserid = $activeuserids[$key];
		$filter->Activelanguage = $activelanguages[$key];
		
		if($actiontypes[$key] == POST_ACTION_ADD || $actiontypes[$key] == POST_ACTION_EDIT)
			$filter->Save();
		else if($actiontypes[$key] == POST_ACTION_REMOVE)
			$filter->Destroy();
	}
}

function appendClosures()
{
	global $INTERNAL,$CONFIG;
	$users = explode(POST_ACTION_VALUE_SPLITTER,utf8_decode($_POST[POST_INTERN_PROCESS_CLOSURES . "_va"]));
	$types = explode(POST_ACTION_VALUE_SPLITTER,utf8_decode($_POST[POST_INTERN_PROCESS_CLOSURES . "_vb"]));
	$browsers = explode(POST_ACTION_VALUE_SPLITTER,utf8_decode($_POST[POST_INTERN_PROCESS_CLOSURES . "_vc"]));
	foreach($users as $key => $userid)
	{
		$chat = new Chat(new ExternalChat($userid,$browsers[$key]),$INTERNAL[CALLER_SYSTEM_ID]);
		if($types[$key] == CHAT_CLOSED)
			$chat->InternalClose();
		else if($types[$key] == CHAT_DECLINED)
			$chat->InternalDecline();
	}
}

function appendPosts()
{
	global $INTERNAL,$GROUPS,$VISITOR,$CONFIG;
	$posts = explode(POST_ACTION_VALUE_SPLITTER,slashesStrip($_POST[POST_INTERN_PROCESS_POSTS . "_va"]));
	$receivers = explode(POST_ACTION_VALUE_SPLITTER,slashesStrip($_POST[POST_INTERN_PROCESS_POSTS . "_vb"]));
	$specials = explode(POST_ACTION_VALUE_SPLITTER,slashesStrip($_POST[POST_INTERN_PROCESS_POSTS . "_vc"]));
	$titles = explode(POST_ACTION_VALUE_SPLITTER,slashesStrip($_POST[POST_INTERN_PROCESS_POSTS . "_vd"]));
	$postids = explode(POST_ACTION_VALUE_SPLITTER,slashesStrip($_POST[POST_INTERN_PROCESS_POSTS . "_ve"]));
	
	$PostMan = new PostMan($INTERNAL[CALLER_SYSTEM_ID],$INTERNAL,$GROUPS,$CONFIG);
	foreach($posts as $key => $post)
		$PostMan->AddPost(new Post($postids[$key],$INTERNAL[CALLER_SYSTEM_ID],$receivers[$key],$posts[$key],$titles[$key],$specials[$key],time()));
	$PostMan->Save();
}

function appendStatus()
{
	global $INTERNAL,$CONFIG;
	if(!LOGIN)
		$INTERNAL[CALLER_SYSTEM_ID]->Status = $_POST[POST_INTERN_USER_STATUS];
	else
		$INTERNAL[CALLER_SYSTEM_ID]->Status = USER_STATUS_OFFLINE;
}

function appendDeleteFiles()
{
	$filesToDelete = explode(POST_ACTION_VALUE_SPLITTER,($_POST[POST_INTERN_PROCESS_DELETE_FILES . "_va"]));
	$fileOwners = explode(POST_ACTION_VALUE_SPLITTER,($_POST[POST_INTERN_PROCESS_DELETE_FILES . "_vb"]));
	$fileTypes = explode(POST_ACTION_VALUE_SPLITTER,utf8_decode($_POST[POST_INTERN_PROCESS_DELETE_FILES . "_vc"]));
	$fileOwnerTypes = explode(POST_ACTION_VALUE_SPLITTER,($_POST[POST_INTERN_PROCESS_DELETE_FILES . "_vd"]));
	foreach($filesToDelete as $key => $item)
		if(strlen($item) > 0)
		{
			if($fileOwnerTypes[$key] == 1)
			{
				if(file_exists(PATH_UPLOADS_INTERNAL.$fileOwners[$key]."/".$item))
					@unlink(PATH_UPLOADS_INTERNAL.$fileOwners[$key]."/".$item);
			}
			else
			{
				if(file_exists(PATH_UPLOADS_EXTERNAL.$fileOwners[$key]."/".$item))
					@unlink(PATH_UPLOADS_EXTERNAL.$fileOwners[$key]."/".$item);
				
				$files = getDirectory(PATH_UPLOADS_EXTERNAL.$fileOwners[$key]."/",false,true);
				if(count($files) <= 1)
					@rmdir(PATH_UPLOADS_EXTERNAL.$fileOwners[$key]);
			}
		}
}

function appendProfilePictures()
{
	global $INTERNAL;
	$pictures = explode(POST_ACTION_VALUE_SPLITTER,utf8_decode($_POST[POST_INTERN_PROCESS_PICTURES]));
	foreach($pictures as $key => $item)
	{
		$filename = PATH_INTERN_IMAGES . "/" . base64_encode($INTERNAL[CALLER_SYSTEM_ID]->UserId) . FILE_EXTENSION_PROFILE_PICTURE;
		if(file_exists($filename))
			@unlink($filename);
		if(strlen($item) > 0)
			base64ToFile($filename,$item);
	}
}

function appendRatings()
{
	$ratings = explode(POST_ACTION_VALUE_SPLITTER,utf8_decode($_POST[POST_INTERN_PROCESS_DELETE_RATING]));
	foreach($ratings as $key => $rating)
		@unlink(PATH_RATINGS . $rating . "." . EX_CHAT_RATING);
}

function appendWebcamPictures()
{
	global $INTERNAL;
	$pictures = explode(POST_ACTION_VALUE_SPLITTER,utf8_decode($_POST[POST_INTERN_PROCESS_PICTURES_WEBCAM]));
	foreach($pictures as $key => $item)
	{
		$filename = PATH_INTERN_IMAGES . "/" . base64_encode($INTERNAL[CALLER_SYSTEM_ID]->UserId) . FILE_EXTENSION_WEBCAM_PICTURE;
		if(file_exists($filename))
			@unlink($filename);
		if(strlen($item) > 0)
			base64ToFile($filename,$item);
	}
}

function appendBannerPictures()
{
	if(file_exists(PATH_BANNER.$_POST[POST_INTERN_PROCESS_BANNERS . "_vb"]))
		unlink(PATH_BANNER.$_POST[POST_INTERN_PROCESS_BANNERS . "_vb"]);
	base64ToFile(PATH_BANNER.$_POST[POST_INTERN_PROCESS_BANNERS . "_vb"],$_POST[POST_INTERN_PROCESS_BANNERS . "_va"]);
	
	if(file_exists(PATH_BANNER.$_POST[POST_INTERN_PROCESS_BANNERS . "_vd"]))
		unlink(PATH_BANNER.$_POST[POST_INTERN_PROCESS_BANNERS . "_vd"]);
	base64ToFile(PATH_BANNER.$_POST[POST_INTERN_PROCESS_BANNERS . "_vd"],$_POST[POST_INTERN_PROCESS_BANNERS . "_vc"]);
}

function appendVisitcards()
{
	global $INTERNAL;
	$visitcards = explode(POST_ACTION_VALUE_SPLITTER,$_POST[POST_INTERN_PROCESS_VISITCARDS]);
	foreach($visitcards as $key => $item)
	{
		$filename = PATH_VISITCARDS . "/" . $INTERNAL[CALLER_SYSTEM_ID]->UserId . FILE_EXTENSION_VISITCARD;
		if(strlen($item) > 0)
			createFile($filename,base64_decode($item),true);
		else
			@unlink($filename);
	}
}

function appendAcceptedConversations()
{
	global $INTERNAL;
	$users = explode(POST_ACTION_VALUE_SPLITTER,utf8_decode($_POST[POST_INTERN_PROCESS_ACCEPTED_CHAT . "_va"]));
	$browsers = explode(POST_ACTION_VALUE_SPLITTER,utf8_decode($_POST[POST_INTERN_PROCESS_ACCEPTED_CHAT . "_vb"]));
	foreach($users as $key => $userid)
	{
		if(strlen($browsers[$key]) > 0 && strlen($userid) > 0)
		{
			$chat = new Chat(new ExternalChat($userid,$browsers[$key]),$INTERNAL[CALLER_SYSTEM_ID]);
			$chat->InternalActivate($INTERNAL[CALLER_SYSTEM_ID]);
		}
	}
}
?>