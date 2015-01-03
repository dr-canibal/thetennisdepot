<?php

/****************************************************************************************
* LiveZilla objects.external.inc.php
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
	
class GroupBuilder
{
	var $InternalGroups;
	var $InternalUsers;
	var $Config;
	var $GroupAvailable = false;
	var $GroupValues = Array();
	var $Result;
	var $ErrorHTML = "''";
	
	function GroupBuilder($_internalUsers,$_internalGroups,$_config,$_data)
	{
		$this->InternalUsers = $_internalUsers;
		$this->InternalGroups = $_internalGroups;
		$this->Config = $_config;
		$this->GroupValues["groups_online"] = Array();
		$this->GroupValues["groups_offline"] = Array();
		$this->GroupValues["groups_online_amounts"] = Array();
		$this->GroupValues["groups_output"] = Array();
		$this->GroupValues["groups_hidden"] = (isset($_data[GET_EXTERN_HIDDEN_GROUPS])) ? explode("?",base64_decode($_data[GET_EXTERN_HIDDEN_GROUPS])) : Array();
		$this->GroupValues["set_by_get_user"] = NULL;
		$this->GroupValues["set_by_get_group"] = NULL;
		$this->GroupValues["set_by_cookie"] = NULL;
		$this->GroupValues["set_by_standard"] = NULL;
		$this->GroupValues["set_by_online"] = NULL;
		$this->GroupValues["req_for_user"] = isset($_data[GET_EXTERN_INTERN_USER_ID]);
		$this->GroupValues["req_for_group"] = isset($_data[GET_EXTERN_GROUP]);
	}
	
	function GetHTML()
	{
		$html_groups = "";
		foreach($this->InternalGroups as $id => $group)
		{
			$name = (strlen($group["gr_desc"]) > 0) ? $group["gr_desc"] : $id;
			$selected = (isset($_POST[GET_EXTERN_GROUP]) && $_POST[GET_EXTERN_GROUP] == $id) ? " SELECTED" : "";
			if($group["gr_extern"] && !in_array($id,$this->GroupValues["groups_hidden"]))
				$html_groups .= "<option value=\"".$id."\"".$selected.">".$name."</option>";
		}
		return $html_groups;
	}
	
	function Generate()
	{
		foreach($this->InternalUsers as $internaluser)
		{
			if($internaluser->LastActive > (time()-$this->Config["timeout_clients"]) && $internaluser->Status < 2)
			{
				for($count=0;$count<count($internaluser->Groups);$count++)
				{
					if(isset($_POST[GET_EXTERN_INTERN_USER_ID]) && !isnull($_POST[GET_EXTERN_INTERN_USER_ID]) && $internaluser->UserId == base64_decode($_POST[GET_EXTERN_INTERN_USER_ID]))
						if($this->InternalGroups[$internaluser->Groups[$count]]["gr_extern"])
							$this->GroupValues["set_by_get_user"] = $internaluser->Groups[$count];
					if(!isset($this->GroupValues["groups_online_amounts"][$internaluser->Groups[$count]]))
						$this->GroupValues["groups_online_amounts"][$internaluser->Groups[$count]] = 0;
					$this->GroupValues["groups_online_amounts"][$internaluser->Groups[$count]]++;
				}
			}
		}
		$counter = 0;
		foreach($this->InternalGroups as $id => $group)
		{
			$used = false;
			$amount = (isset($this->GroupValues["groups_online_amounts"]) && is_array($this->GroupValues["groups_online_amounts"]) && array_key_exists($id,$this->GroupValues["groups_online_amounts"])) ? $this->GroupValues["groups_online_amounts"][$id] : 0;
			$transport = base64_encode($id) . "," . base64_encode($amount) . "," . base64_encode($group["gr_desc"]) . "," . base64_encode($group["gr_email"]);

			if($this->GroupValues["req_for_group"] && $amount > 0 && $id == base64_decode($_POST[GET_EXTERN_GROUP]))
				{$this->GroupValues["set_by_get_group"] = $id;$used=true;}
			elseif($amount > 0 && getCookieValue("login_group") != null && $id == getCookieValue("login_group") && !isset($requested_group) && $group["gr_extern"])
				{$this->GroupValues["set_by_cookie"] = $id;$used=true;}
			elseif($amount > 0 && $group["gr_standard"] == "1")
				{$this->GroupValues["set_by_standard"] = $id;$used=true;}
			elseif($amount > 0 && isnull($this->GroupValues["set_by_online"]) && $group["gr_extern"])
				{$this->GroupValues["set_by_online"] = $id;$used=true;}

			if(!in_array($id,$this->GroupValues["groups_hidden"]) && ($group["gr_extern"] || $used))
			{
				$counter++;
				if($amount > 0)
				{
					$this->GroupAvailable = true;
					$this->GroupValues["groups_online"][$id] = $transport;
				}
				else
					$this->GroupValues["groups_offline"][$id] = $transport;
			}
		}
		if(isset($_POST[GET_EXTERN_PREFERENCE]) && $_POST[GET_EXTERN_PREFERENCE] == "group")
		{
			if(isset($this->GroupValues["groups_online_amounts"][base64_decode($_POST[GET_EXTERN_GROUP])]) && $this->GroupValues["groups_online_amounts"][base64_decode($_POST[GET_EXTERN_GROUP])] > 0)
			{
				$this->GroupValues["set_by_get_user"] = NULL;
				$this->GroupValues["req_for_user"] = false;
			}
		}
		if(!isnull($this->GroupValues["set_by_get_user"]))
			$this->GroupValues["groups_output"][$this->GroupValues["set_by_get_user"]] = $this->GroupValues["groups_online"][$this->GroupValues["set_by_get_user"]];
		else if(!isnull($this->GroupValues["set_by_get_group"]))
			$this->GroupValues["groups_output"][$this->GroupValues["set_by_get_group"]] = $this->GroupValues["groups_online"][$this->GroupValues["set_by_get_group"]];
		else if(!isnull($this->GroupValues["set_by_cookie"]))
			$this->GroupValues["groups_output"][$this->GroupValues["set_by_cookie"]] = $this->GroupValues["groups_online"][$this->GroupValues["set_by_cookie"]];
		else if(!isnull($this->GroupValues["set_by_standard"]))
			$this->GroupValues["groups_output"][$this->GroupValues["set_by_standard"]] = $this->GroupValues["groups_online"][$this->GroupValues["set_by_standard"]];
		else if(!isnull($this->GroupValues["set_by_online"]))
			$this->GroupValues["groups_output"][$this->GroupValues["set_by_online"]] = $this->GroupValues["groups_online"][$this->GroupValues["set_by_online"]];
			
		foreach($this->GroupValues["groups_online"] as $id => $transport)
			if(!isset($this->GroupValues["groups_output"][$id]))
				$this->GroupValues["groups_output"][$id] = $transport;
	
		$result = array_merge($this->GroupValues["groups_output"],$this->GroupValues["groups_offline"]);
		foreach($result as $key => $value)
		{
			if(!isnull($this->Result))
				$this->Result .= ";" . $value;
			else
				$this->Result = $value;
		}
		if($counter == 0)
			$this->ErrorHTML = "lz_chat_data.Language.ClientErrorGroups";
	}
}

class RatingGenerator
{
	var $Fields;
	
	function RatingGenerator()
	{
		$this->Generate();
	}
	
	function Generate()
	{
		$this->Fields = array(4);
		for($int = 0;$int < 4;$int++)
			$this->Fields[$int]= str_replace("<!--box_id-->",$int,getFile(TEMPLATE_HTML_RATEBOX));
	}
}

?>
