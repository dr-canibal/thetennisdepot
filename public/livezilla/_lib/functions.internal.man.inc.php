<?php

/****************************************************************************************
* LiveZilla functions.internal.man.inc.php
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

function setAvailability($_available)
{
	global $INTERNAL,$RESPONSE;
	if($INTERNAL[CALLER_SYSTEM_ID]->Level==USER_LEVEL_ADMIN)
	{
		if($_available=="1" && file_exists(FILE_SERVER_DISABLED))
			@unlink(FILE_SERVER_DISABLED);
		else if($_available=="0")
			createFile(FILE_SERVER_DISABLED,time(),true);
		$RESPONSE->SetStandardResponse(1,"");
	}
}

function setIdle($_idle)
{
	global $INTERNAL,$RESPONSE;
	if($INTERNAL[CALLER_SYSTEM_ID]->Level==USER_LEVEL_ADMIN)
	{
		if($_idle=="0" && file_exists(FILE_SERVER_IDLE))
			@unlink(FILE_SERVER_IDLE);
		else if($_idle=="1")
			createFile(FILE_SERVER_IDLE,time(),true);
		$RESPONSE->SetStandardResponse(1,"");
	}
}

function getBannerList($list = "")
{
	global $VISITOR,$CONFIG,$RESPONSE;
	$banners = getDirectory(PATH_BANNER,".php",true);
	foreach($banners as $banner)
	{
		if(@is_dir(PATH_BANNER . $banner) || (strpos($banner,"_0.png") === false && strpos($banner,"_1.png") === false))
			continue;
		$list .= "<banner name=\"".base64_encode($banner)."\" hash=\"".base64_encode(hashFile(PATH_BANNER . $banner))."\"/>\r\n";
	}
	$RESPONSE->SetStandardResponse(1,"<banner_list>".$list."</banner_list>");
}

function setManagement()
{
	global $INTERNAL,$RESPONSE;
	if($INTERNAL[CALLER_SYSTEM_ID]->Level == USER_LEVEL_ADMIN)
	{
		createFile(PATH_USERS . "intern.inc.php",base64_decode($_POST[POST_INTERN_FILE_INTERN]),true);
		createFile(PATH_GROUPS . "groups.inc.php",base64_decode($_POST[POST_INTERN_FILE_GROUPS]),true);
		getData(true,true,true,false);
		if(isset($_POST[POST_INTERN_EDIT_USER]))
		{
			$combos = explode(";",$_POST[POST_INTERN_EDIT_USER]);
			for($i=0;$i<count($combos);$i++)
				if(strpos($combos[$i],",") !== false)
				{
					$vals = explode(",",$combos[$i]);
					if(strlen($vals[1])>0)
						$INTERNAL[$vals[0]]->ChangePassword($vals[1]);
					$INTERNAL[$vals[0]]->SetPasswordChangeNeeded(($vals[2] == 1));
				}
		}
		$userdirs = getDirectory(PATH_DATA_INTERNAL,".htm",true);
		foreach($userdirs as $userdir)
			if(!isset($INTERNAL[$userdir]))
				deleteDirectory(PATH_DATA_INTERNAL . $userdir);
				
		$datafiles = getDirectory(PATH_USERS,".htm",true);
		foreach($datafiles as $datafile)
			if(strpos($datafile, FILE_EXTENSION_PASSWORD) !== false || strpos($datafile, FILE_EXTENSION_CHANGE_PASSWORD) !== false)
			{
				$parts = explode(".",$datafile);
				if(!isset($INTERNAL[$parts[0]]))
					@unlink(PATH_USERS . $datafile);
			}
		setIdle(0);
		$RESPONSE->SetStandardResponse(1,"");
	}
}

function setConfig($id = 0)
{
	global $INTERNAL,$RESPONSE;
	if(SERVERSETUP)
	{
		$id = createFile(FILE_CONFIG,base64_decode($_POST[POST_INTERN_UPLOAD_VALUE]),true);
		if(isset($_POST[POST_INTERN_SERVER_AVAILABILITY]))
			setAvailability($_POST[POST_INTERN_SERVER_AVAILABILITY]);
		
		if(isset($_POST[POST_INTERN_FILE_CARRIER_LOGO]) && strlen($_POST[POST_INTERN_FILE_CARRIER_LOGO]) > 0)
			base64ToFile(FILE_CARRIERLOGO,$_POST[POST_INTERN_FILE_CARRIER_LOGO]);
		else if(isset($_POST[POST_INTERN_FILE_CARRIER_LOGO]) && file_exists(FILE_CARRIERLOGO))
			@unlink(FILE_CARRIERLOGO);
			
		if(isset($_POST[POST_INTERN_FILE_INVITATION_LOGO]) && strlen($_POST[POST_INTERN_FILE_INVITATION_LOGO]) > 0)
			base64ToFile(FILE_INVITATIONLOGO,$_POST[POST_INTERN_FILE_INVITATION_LOGO]);
		else if(isset($_POST[POST_INTERN_FILE_INVITATION_LOGO]) && file_exists(FILE_INVITATIONLOGO))
			@unlink(FILE_INVITATIONLOGO);
	}
	setIdle(0);
	$RESPONSE->SetStandardResponse($id,"");
}

function scriptTest($id=0)
{
	global $RESPONSE;
	if(checkPhpVersion(PHP_NEEDED_MAJOR,PHP_NEEDED_MINOR,PHP_NEEDED_BUILD))
		$id = 1;
	$RESPONSE->SetStandardResponse($id,"");
}

function dataBaseTest($id=0)
{
	global $RESPONSE;
	$res = testDataBase($_POST[POST_INTERN_DATABASE_HOST],$_POST[POST_INTERN_DATABASE_USER],$_POST[POST_INTERN_DATABASE_PASS],$_POST[POST_INTERN_DATABASE_NAME],$_POST[POST_INTERN_DATABASE_PREFIX]);
	if($res == null)
		$RESPONSE->SetStandardResponse(1,base64_encode(""));
	else
		$RESPONSE->SetStandardResponse(2,base64_encode($res));
	
}

function createTables($id=0)
{
	global $RESPONSE;
	$connection = @mysql_connect($_POST[POST_INTERN_DATABASE_HOST],$_POST[POST_INTERN_DATABASE_USER],$_POST[POST_INTERN_DATABASE_PASS]);
	if(!$connection)
	{
		$error = mysql_error();
		$RESPONSE->SetStandardResponse($id,base64_encode("Can't connect to database. Invalid host or login! (" . mysql_errno() . (($error != null) ? ": " . $error : "") . ")"));
	}
	else
	{
		$db_selected = mysql_select_db(mysql_real_escape_string($_POST[POST_INTERN_DATABASE_NAME]),$connection);
		if (!$db_selected) 
    		$RESPONSE->SetStandardResponse($id,base64_encode(mysql_errno() . ": " . mysql_error()));
		else
		{
			$sql = "CREATE TABLE `".mysql_real_escape_string($_POST[POST_INTERN_DATABASE_PREFIX]).DATABASE_CHATS."` (`id` varchar(32) NOT NULL,`time` int(11) NOT NULL,`endtime` int(11) NOT NULL,`closed` int(11) NOT NULL,`chat_id` varchar(64) NOT NULL,`external_id` varchar(32) NOT NULL,`fullname` varchar(32) NOT NULL,`internal_id` varchar(32) NOT NULL,`html` longtext NOT NULL,`plain` longtext NOT NULL,`email` varchar(50) NOT NULL,`company` varchar(50) NOT NULL,`iso_language` varchar(8) NOT NULL,`host` varchar(64) NOT NULL,`ip` varchar(15) NOT NULL,`gzip` smallint(1) NOT NULL,PRIMARY KEY  (`id`)) ENGINE=MyISAM;";
			$result = mysql_query($sql,$connection);
			if(!$result && mysql_errno() != 1050)
			{
				$RESPONSE->SetStandardResponse($id,base64_encode(mysql_errno() . ": " . mysql_error()));
				return;
			}
			$sql = "CREATE TABLE `".mysql_real_escape_string($_POST[POST_INTERN_DATABASE_PREFIX]).DATABASE_DATA."` (`file` varchar(254) NOT NULL,`time` int(11) NOT NULL,`data` text NOT NULL,`size` mediumint(9) NOT NULL,UNIQUE KEY `file` (`file`)) ENGINE=MyISAM;";
			$result = mysql_query($sql,$connection);
			if(!$result && mysql_errno() != 1050)
			{
				$RESPONSE->SetStandardResponse($id,base64_encode(mysql_errno() . ": " . mysql_error()));
				return;
			}
			$sql = "CREATE TABLE `".mysql_real_escape_string($_POST[POST_INTERN_DATABASE_PREFIX]).DATABASE_INFO."` (`version` varchar(15) NOT NULL,UNIQUE KEY `file` (`version`)) ENGINE=MyISAM;";
			$result = mysql_query($sql,$connection);
			if(!$result && mysql_errno() != 1050)
			{
				$RESPONSE->SetStandardResponse($id,base64_encode(mysql_errno() . ": " . mysql_error()));
				return;
			}
			$sql = "INSERT INTO `".mysql_real_escape_string($_POST[POST_INTERN_DATABASE_PREFIX]).DATABASE_INFO."` (`version`) VALUES ('".VERSION."');";
			$result = mysql_query($sql,$connection);
			if(!$result && mysql_errno() != 1062)
			{
				$RESPONSE->SetStandardResponse($id,base64_encode(mysql_errno() . ": " . mysql_error()));
				return;
			}
			$sql = "CREATE TABLE `".mysql_real_escape_string($_POST[POST_INTERN_DATABASE_PREFIX]).DATABASE_INTERNAL."` (`id` bigint(20) NOT NULL auto_increment,`time` int(11) NOT NULL,`time_confirmed` int(11) NOT NULL,`internal_id` varchar(15) NOT NULL,`status` tinyint(1) NOT NULL,PRIMARY KEY  (`id`)) ENGINE=MyISAM;";
			$result = mysql_query($sql,$connection);
			if(!$result && mysql_errno() != 1050)
			{
				$RESPONSE->SetStandardResponse($id,base64_encode(mysql_errno() . ": " . mysql_error()));
				return;
			}
			$sql = "CREATE TABLE `".mysql_real_escape_string($_POST[POST_INTERN_DATABASE_PREFIX]).DATABASE_RESOURCES."`  (`id` varchar(32) NOT NULL,`owner` varchar(15) NOT NULL,`editor` varchar(15) NOT NULL,`html` longtext NOT NULL,`edited` int(11) NOT NULL,`title` varchar(255) NOT NULL,`created` int(11) NOT NULL,`type` tinyint(1) NOT NULL,`discarded` tinyint(1) NOT NULL,`parentid` varchar(32) NOT NULL,`rank` int(11) NOT NULL, UNIQUE KEY `id` (`id`)) ENGINE=MyISAM;";
			$result = mysql_query($sql,$connection);
			if(!$result && mysql_errno() != 1050)
			{
				$RESPONSE->SetStandardResponse($id,base64_encode(mysql_errno() . ": " . mysql_error()));
				return;
			}
			$RESPONSE->SetStandardResponse(1,base64_encode(""));
		}
	}
}

function testDataBase($_host,$_user,$_pass,$_dbname,$_prefix)
{
	if(!function_exists("mysql_connect"))
		return "PHP/MySQL extension is missing (php_mysql.dll)";
		
	$connection = @mysql_connect($_host,$_user,$_pass);
	if(!$connection)
	{
		$error = mysql_error();
		return "Can't connect to database. Invalid host or login! (" . mysql_errno() . (($error != null) ? ": " . $error : "") . ")";
	}
	else
	{
		$db_selected = @mysql_select_db(mysql_real_escape_string($_dbname),$connection);
		if (!$db_selected) 
    		return mysql_errno() . ": " . mysql_error();
		else
		{
			$rand = substr(rand(10000,1000000),0,15);
			$tables = array(DATABASE_DATA=>array("`file`","`time`","`data`","`size`"),DATABASE_CHATS=>array("`id`","`time`","`endtime`","`closed`","`chat_id`","`external_id`","`fullname`","`internal_id`","`html`","`plain`","`email`","`company`","`iso_language`","`host`","`ip`","`gzip`"),DATABASE_INFO=>array("`version`"),DATABASE_INTERNAL=>array("`id`","`time`","`time_confirmed`","`internal_id`","`status`"),DATABASE_RESOURCES=>array("`id`","`owner`","`editor`","`html`","`edited`","`title`","`created`","`type`","`discarded`","`parentid`","`rank`"));
			foreach($tables as $dbName => $fieldlist)
			{
				$result = @mysql_query("SHOW COLUMNS FROM `".mysql_real_escape_string($_prefix).$dbName."`",$connection);
				if(!$result)
					return mysql_errno() . ": " . mysql_error();
				else
				{
					if(@mysql_num_rows($result) == count($fieldlist))
					{
						$queryFields = "INSERT INTO `".mysql_real_escape_string($_prefix).$dbName."` (";
						$queryValues = ") VALUES (";

						for($i = 0;$i<count($fieldlist);$i++)
						{
							$queryFields .= $fieldlist[$i] . (($i < count($fieldlist)-1) ? "," : "");
							$queryValues .= (($i == 0) ? $rand : "0") . (($i < count($fieldlist)-1) ? "," : "");
						}
	    				$result = mysql_query($queryFields . $queryValues . ")",$connection);
	    				if(!$result)
							return mysql_errno() . ": " . mysql_error();
						else
						{
							$result = mysql_query("DELETE FROM ".mysql_real_escape_string($_prefix).$dbName." WHERE ".substr($fieldlist[0],1,strlen($fieldlist[0])-2)."=".$rand,$connection);
	    					if(!($result && @mysql_affected_rows() > 0))
								return mysql_errno() . ": " . mysql_error();
						}
					}
					else
						return "Invalid field count for " . $_prefix.$dbName . ". Delete " . $_prefix.$dbName. " manually and try to recreate the tables.";
				}
			}
			return null;
		}
	}
}


?>
