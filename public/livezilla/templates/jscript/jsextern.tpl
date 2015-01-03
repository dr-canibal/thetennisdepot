window.onerror = lz_global_handle_exception;

var lz_chat_data = new lz_chat_data();
var lz_poll_request;
var lz_shout_request

lz_chat_data.TempImage.onload=lz_chat_show_intern_image;
lz_chat_data.TempImage.onerror=new function(){};
	
function lz_get_session()
{
	return lz_chat_data.ExternalUser.Session;
}

function lz_chat_unload()
{
	if(lz_chat_data.Status.Login > lz_chat_data.STATUS_START && lz_chat_data.Status.Login < lz_chat_data.STATUS_STOPPED)
	{
		lz_chat_stop_system();
		lz_poll_request.TimeoutConnection();
		lz_shout_request.TimeoutConnection();
		var closeSessionConnect = new lz_connector("./server.php",lz_chat_get_post_values("logoff",false,false),60000);
		closeSessionConnect.ConnectSync();
	}
}
	
function lz_chat_set_parentid()
{			
	lz_chat_data.ExternalUser.Session = new lz_jssess();
	lz_chat_data.ExternalUser.Session.Load();
	try
	{
		if(window.opener != null && typeof(window.opener.lz_get_session) != 'undefined')
		{
			lz_chat_data.ExternalUser.Session.UserId = window.opener.lz_get_session().UserId;
			lz_chat_data.ExternalUser.Session.GeoResolved = window.opener.lz_get_session().GeoResolved;
		}
	}
	catch(ex){}
	lz_chat_data.ExternalUser.Session.Save();
}

function lz_chat_startup() 
{
	if(lz_chat_data.Groups == null)
	{
		lz_chat_data.Groups = new lz_group_list(frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.login.1.0'].document,frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.login.1.0'].document.getElementById("lz_chat_login_groups"));
		lz_chat_data.Groups.CreateHeader();
		lz_chat_reload_groups();
	}
}

function lz_chat_reload_groups()
{
	if(lz_chat_data.Status.Login > lz_chat_data.STATUS_START)
		return;

	if(!lz_chat_data.Status.Loaded)
	{
		lz_chat_change_group();
		lz_chat_server_request("./server.php",lz_chat_get_post_values("reloadgroups",true,true),25000,null);
	}
	else
	{
		frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.login.1.0'].document.getElementById('lz_chat_groups_loading').style.visibility = "visible";
		if(!lz_chat_data.ConnectionRunning)
			lz_chat_server_request("./server.php",lz_chat_get_post_values("reloadgroups",false,true),60000,null);
	}
}

function lz_chat_send_rate(_qualification,_politeness,_comment)
{
	if(lz_chat_data.InternalUser.Id.length > 0)
	{
		var values = lz_chat_get_post_values("rate",false,false) + "&p_rate_q=" + _qualification + "&p_rate_p=" + _politeness + "&p_rate_c=" + lz_global_utf8_encode(_comment,true);
		lz_chat_shout_request("./server.php",values,60000);
	}
	else
		frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.2.0'].RatingCallback(lz_chat_data.Language.WaitForRepresentative,true);
}

function lz_chat_send_rate_callback(_success)
{
	if(_success)
		frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.2.0'].RatingCallback(lz_chat_data.Language.RateSuccess);
	else
		frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.2.0'].RatingCallback(lz_chat_data.Language.RateMax);	
}

function lz_chat_get_post_values(_action, _groups, _params)
{
	var values = "p_request=extern";
	if(_action == "listen" || _action == "shout")
	{
		values += "&p_action=listen&p_gl_a="+lz_chat_data.PollHash+"&p_gl_acid="+lz_chat_data.PollAcid;
		values += "&p_username="+lz_global_utf8_encode(lz_chat_data.ExternalUser.Username,true)+"&p_group="+lz_global_utf8_encode(lz_chat_data.ExternalUser.Group,true)+"&p_email="+lz_global_utf8_encode(lz_chat_data.ExternalUser.Email,true)+"&p_company="+lz_global_utf8_encode(lz_chat_data.ExternalUser.Company,true);
		values += "&p_acid="+Math.random()+((lz_chat_data.ExternalUser.Typing)?"&p_typ=1":"");
		
		if(lz_chat_data.InternalUser != null && lz_chat_data.InternalUser.Id != null)
			values += "&p_requested_intern_userid=" + lz_global_utf8_encode(lz_chat_data.InternalUser.Id,true);
		
		if(_action == "shout")
			values += "&p_shout=1";
		else if(lz_chat_data.LastConnectionFailed)
			values += "&p_lcf=1";
		
		if(lz_chat_data.FileUpload.Running && !lz_chat_data.FileUpload.Permitted)
		{
			values += "&p_fu_n=" + lz_chat_data.FileUpload.Filename;
		}
		else if(lz_chat_data.FileUpload.Error)
		{
			values += "&p_fu_e=" + lz_chat_data.FileUpload.Error;
			values += "&p_fu_n=" + lz_chat_data.FileUpload.Filename;
		}
	}
	else if(_action == "logoff")
	{
		values += "&p_action=logoff";
		values += "&p_username=" + lz_global_utf8_encode(lz_chat_data.ExternalUser.Username,true)+"&p_group="+lz_global_utf8_encode(lz_chat_data.ExternalUser.Group,true)+"&p_email="+lz_global_utf8_encode(lz_chat_data.ExternalUser.Email,true)+"&p_company="+lz_global_utf8_encode(lz_chat_data.ExternalUser.Company,true);
	}
	else if(_action == "reloadgroups")
	{
		values += "&p_action=reloadgroups";
		values += "&p_requested_intern_userid=" + lz_global_utf8_encode(lz_chat_data.InternalUser.Id,true);
		values += "&p_tzo=" + lz_chat_data.TimezoneOffset + "&p_cd="+window.screen.colorDepth;
		values += "&p_resh="+screen.height + "&p_resw="+screen.width;
	}
	else if(_action == "send_mail")
	{
		values += "&p_action=mail";
		values += "&p_username=" + lz_global_utf8_encode(lz_chat_data.ExternalUser.Username,true) + "&p_group="+lz_global_utf8_encode(lz_chat_data.ExternalUser.Group,true)+"&p_email="+lz_global_utf8_encode(lz_chat_data.ExternalUser.Email,true)+"&p_company="+lz_global_utf8_encode(lz_chat_data.ExternalUser.Company,true)+"&p_extern_mail="+lz_global_utf8_encode(lz_chat_data.ExternalUser.MailText,true);
	}
	else if(_action == "rate")
	{
		values += "&p_action=rate";
		values += "&p_username=" + lz_global_utf8_encode(lz_chat_data.ExternalUser.Username,true) + "&p_group="+lz_global_utf8_encode(lz_chat_data.ExternalUser.Group,true)+"&p_email="+lz_global_utf8_encode(lz_chat_data.ExternalUser.Email,true)+"&p_company="+lz_global_utf8_encode(lz_chat_data.ExternalUser.Company,true)+"&p_requested_intern_userid="+lz_global_utf8_encode(lz_chat_data.InternalUser.Id,true);
	}
	
	if(_params && lz_chat_data.GetParameters.length > 0)
		values += "&" + lz_chat_data.GetParameters;

	values +="&p_extern_userid="+lz_chat_data.ExternalUser.Session.UserId+"&p_extern_browserid="+lz_chat_data.ExternalUser.Session.BrowserId;
	return values;
}

function lz_chat_login(_groupId) 
{
	lz_chat_data.Status.Login = lz_chat_data.STATUS_INIT;
	if(lz_chat_data.ConnectionRunning)
	{
		setTimeout("lz_chat_login('"+_groupId+"');",100);
		return;
	}
	lz_chat_data.ExternalUser.Group = _groupId;
	lz_chat_data.PermittedFrames=7;
	
	if(lz_chat_data.ExternalUser.Username.length == 0)
		lz_chat_data.ExternalUser.Username = lz_chat_data.Language.Guest;
	
	lz_chat_check_connection();
	frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.login.1.0'].document.lz_login_form.submit();
}

function lz_chat_loaded() 
{
	lz_chat_add_system_text(-1,lz_chat_data.Language.StartSystem + ' [' + lz_chat_get_locale_date() + ']');
	lz_chat_set_intern('','','',false,false);
	lz_chat_data.Status.Login = lz_chat_data.STATUS_ACTIVE;
	lz_chat_listen();
}

function lz_chat_listen() 
{	
	if(lz_chat_data.Status.Login < lz_chat_data.STATUS_STOPPED)
	{
		if(!lz_chat_data.ConnectionRunning)
			lz_chat_server_request("./server.php",lz_chat_get_post_values("listen",false,true),lz_global_get_long_poll_runtime()*1000,null);

		setTimeout("lz_chat_listen();",lz_chat_data.ChatFrequency * 1000);
	}
}

function lz_chat_reshout()
{	
	lz_chat_data.ShoutRunning = false;
	if(lz_chat_data.ShoutNeeded || lz_chat_data.FileUpload.Running)
		lz_chat_shout();
}

function lz_chat_shout()
{	
	if(lz_chat_data.Status.Login < lz_chat_data.STATUS_STOPPED && lz_chat_data.Status.Poll == 1)
	{
		if(!lz_chat_data.ShoutRunning)
		{
			lz_chat_data.ShoutRunning = true;
			lz_chat_shout_request("./server.php",lz_chat_get_post_values("shout",false,true),60000);
			lz_chat_data.ShoutNeeded = false;
		}
		else
			lz_chat_data.ShoutNeeded = true;
	}
	else
		lz_chat_data.ShoutNeeded = true;
}

function lz_chat_listen_hash(_hash,_acid)
{
	lz_chat_data.PollHash = _hash;
	lz_chat_data.PollAcid = _acid;
}

function lz_chat_server_request(_url, _post, _timeout, _errorEvent)
{	
	if(lz_chat_data.ShoutNeeded && !lz_chat_data.ShoutRunning && lz_chat_data.Status.Poll == 1)
		return lz_chat_shout();

	lz_chat_data.ConnectionRunning = true;
	if(lz_geo_resolution_needed && lz_chat_data.ExternalUser.Session.GeoResolved.length == 6)
	{
		_post += "&geo_lat=" + lz_chat_data.ExternalUser.Session.GeoResolved[0] + "&geo_long=" + lz_chat_data.ExternalUser.Session.GeoResolved[1] + "&geo_region=" + lz_chat_data.ExternalUser.Session.GeoResolved[2] + "&geo_city=" + lz_chat_data.ExternalUser.Session.GeoResolved[3] + "&geo_tz=" + lz_chat_data.ExternalUser.Session.GeoResolved[4] + "&geo_ctryiso=" + lz_chat_data.ExternalUser.Session.GeoResolved[5];
	}
	lz_poll_request	= new lz_connector(_url,_post,_timeout);
	lz_poll_request.OnEndEvent = lz_chat_handle_response;
	if(_errorEvent==null)
	{
		lz_poll_request.OnErrorEvent =
		lz_poll_request.OnTimeoutEvent = lz_chat_handle_connection_error;
	}
	else
	{
		lz_poll_request.OnErrorEvent =
		lz_poll_request.OnTimeoutEvent = _errorEvent;
	}
	lz_poll_request.ConnectAsync();
}

function lz_chat_shout_request(_url, _post, _timeout)
{	
	lz_chat_data.ShoutRunning = true;
	if(lz_chat_data.Status.Poll < 2)
	{
		for(var i = 0;i<lz_chat_data.ExternalUser.Messages.length;i++)
			lz_chat_data.ExternalUser.MessagesBackup[lz_chat_data.ExternalUser.MessagesBackup.length] = lz_chat_data.ExternalUser.Messages[i];

		lz_chat_data.ExternalUser.Messages = new Array();
		for(var i = 0;i<lz_chat_data.ExternalUser.MessagesBackup.length;i++)
			_post += "&p_p" + i.toString() + "=" + lz_chat_data.ExternalUser.MessagesBackup[i].MessageText + "&p_i" + i.toString() + "=" + lz_chat_data.ExternalUser.MessagesBackup[i].MessageId;
	}

	lz_shout_request = new lz_connector(_url,_post,_timeout);
	lz_shout_request.OnEndEvent = lz_chat_handle_shout_response;
	lz_shout_request.OnErrorEvent =
	lz_shout_request.OnTimeoutEvent = lz_chat_handle_connection_error;
	lz_shout_request.ConnectAsync();
}

function lz_chat_check_connection()
{
	if(lz_chat_data.LastConnection < (lz_global_timestamp() - lz_chat_data.PollTimeout) && lz_chat_data.Status.Login < lz_chat_data.STATUS_STOPPED)
	{
		if(!lz_chat_data.ConnectionBroken)
			lz_chat_add_system_text(-1,lz_chat_data.Language.ConnectionBroken);
		lz_chat_data.ConnectionBroken = true;
	}
	else
		lz_chat_data.ConnectionBroken = false;
	setTimeout("lz_chat_check_connection();",5000);
}

function lz_chat_set_config(_timeout,_frequency)
{
	lz_chat_data.PollTimeout = _timeout;
	lz_chat_data.ChatFrequency = _frequency;
}

function lz_chat_stop_system()
{
	window.status = "";
	if(lz_chat_data.Status.Login == lz_chat_data.STATUS_ACTIVE)
	{
		frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.5.0'].document.getElementById('lz_chat_text').disabled = 
		frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.5.0'].document.getElementById('lz_chat_submit').disabled = true;
	}
	lz_chat_data.Status.Login = lz_chat_data.STATUS_STOPPED;
	lz_chat_set_intern('','','',false,false);
	lz_chat_set_intern_image(false,false,false);
	lz_chat_file_reset();
}

function lz_chat_invalid_load()
{
	if(lz_chat_data.Status.Login == lz_chat_data.STATUS_ACTIVE)
		lz_chat_close();
}

function lz_chat_play_sound()
{
	if(lz_chat_data.SoundsAvailable && !lz_chat_data.ExternalUser.Typing)
	{
		frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.5.0'].document.getElementById('sound_player').innerHTML="";
		if(lz_chat_data.IECompatible)
		{
			var BGSOUND_OBJECT = frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.5.0'].document.createElement('bgsound');
			frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.5.0'].document.getElementById('sound_player').appendChild(BGSOUND_OBJECT);
			BGSOUND_OBJECT.src = "./sound/message.wav";
			frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.5.0'].document.getElementById('sound_player').removeChild(BGSOUND_OBJECT);
		}
		else
			frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.5.0'].document.getElementById('sound_player').innerHTML= "<embed src='./sound/message.wav' loop='false' autostart='true' voloume='100' hidden='true'><\/embed>";
	}
}

function lz_chat_switch_sound()
{
	lz_chat_data.SoundsAvailable = !lz_chat_data.SoundsAvailable;
	if(lz_chat_data.SoundsAvailable)
		frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.0.0'].document.getElementById('lz_chat_sound_button').src = "./images/button_s1.gif";
	else
		frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.0.0'].document.getElementById('lz_chat_sound_button').src = "./images/button_s0.gif";
}

function lz_chat_detect_sound()
{
	if(!lz_chat_data.SoundsAvailable && navigator.plugins)
	{
		for(var i = 0;i < navigator.plugins.length;i++)
		{
			if(navigator.plugins[i].name.indexOf("QuickTime") != -1)
				if(navigator.plugins[i].name.indexOf("7.5") != -1)
					lz_chat_data.SoundsAvailable = true;
		}
		
		if(lz_chat_data.SoundsAvailable)
			frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.login.2.0'].document.getElementById('silence_player').innerHTML="<embed src='./sound/preload.wav' loop='false' autostart='true' volume='100' hidden='true'><\/embed>";
	}
	if(window.opera)
		lz_chat_data.SoundsAvailable = true;
}

function lz_chat_apply_sound()
{
	if(!lz_chat_data.SoundsAvailable)
		frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.0.0'].document.getElementById('lz_chat_sound_button').src= "./images/button_s0.gif";
}

function lz_chat_set_group(_group)
{
	lz_chat_data.ExternalUser.Group = lz_global_base64_decode(_group);
}

function lz_chat_set_groups(_groups, _errors)
{
	frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.login.1.0'].document.getElementById('lz_chat_groups_loading').style.visibility = "hidden";

	lz_chat_data.Groups.Update(_groups);

	if(!lz_chat_data.DirectLogin || (lz_chat_data.DirectLogin && !lz_chat_validate_group()))
		lz_chat_data.DirectLogin = false;
	
	if(_errors.length > 0)
	{
		lz_chat_data.SetupError = _errors;
		lz_chat_chat_alert(_errors,frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.login.1.0'],-1);
		lz_chat_set_button_text(null);
	}
	else
		setTimeout("lz_chat_reload_groups();",25000);
}

function lz_chat_change_group()
{
	selGroup = (frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.login.1.0'] != null) ? lz_chat_data.Groups.GetGroupById(frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.login.1.0'].document.getElementById('lz_chat_login_groups').value) : null;
	
	if(lz_chat_data.Status.Login > 0 || selGroup != null)
		return lz_chat_set_button_text(selGroup);

	var selectBox = frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.login.1.0'].document.getElementById('lz_chat_login_groups');
	var position = selectBox.selectedIndex;
	var reset = false;
	while(selGroup == null)
	{
		position++;
		if(position == selectBox.childNodes.length)
			if(!reset)
			{
				position = 0;
				reset=true;
			}
			else
				break;
		selGroup = lz_chat_data.Groups.GetGroupById(frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.login.1.0'].document.getElementById('lz_chat_login_groups').childNodes[position].value);
	}
	
	if(selectBox.length > position)
	{
		selectBox.selectedIndex = position;
		lz_chat_set_button_text(selGroup);
	}
}

function lz_chat_set_button_text(_selGroup)
{
	frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.login.1.0'].document.getElementById('lz_chat_login_button').value = ((_selGroup!=null && selGroup.Amount > 0) || lz_chat_data.NoPreChatMessages) ? lz_chat_data.Language.StartChat : lz_chat_data.Language.LanguageLeaveMessageShort;
}

function lz_chat_validate_group()
{
	selGroup = lz_chat_data.Groups.GetGroupById(frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.login.1.0'].document.getElementById('lz_chat_login_groups').value);
	if(selGroup == null)
	{
		lz_chat_chat_alert(lz_chat_data.Language.SelectValidGroup,frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.login.1.0']);
		return false;	
	}
	if(selGroup.Amount == 0)
	{
		lz_chat_goto_message(false);
		return false;
	}
	return true;
}

function lz_chat_bookmark()
{
	var title = '<!--bookmark_name-->';
	if (window.sidebar) 
		window.sidebar.addPanel(lz_global_base64_decode(title),self.location.href,"");
	else if(window.external)
		window.external.AddFavorite(self.location.href,lz_global_base64_decode(title));
}  

function lz_chat_goto_message(_inChat)
{
	if(!_inChat && lz_chat_data.NoPreChatMessages)
	{
		lz_chat_chat_alert(lz_chat_data.Language.ClientNoInternUsersShort,frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.login.1.0']);
		return;
	}
	else if(<!--offline_message_mode--> == 1)
	{
		window.location.href = '<!--offline_message_http-->';
		window.resizeTo(screen.width,screen.height);
		window.screenX = 0;
		window.screenY = 0;
		return;
	}
	else if(<!--offline_message_mode--> == 2)
	{
		window.location.href = 'mailto:' + lz_chat_data.Groups.GetGroupById(frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.login.1.0'].document.getElementById('lz_chat_login_groups').value).Email;
		return;
	}
	
	if(lz_chat_data.SetupError)
		return lz_chat_chat_alert('<!--lang_index_not_available-->',frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.login.1.0']);
		
	lz_chat_data.Status.Login = lz_chat_data.STATUS_STOPPED;
	lz_chat_data.PermittedFrames++;

	if(!_inChat)
	{
		frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.login.1.0'].document.lz_login_form.action = './livezilla.php?template=lz_chat_frame.3.2.mail&' + lz_chat_data.GetParameters;
		frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.login.1.0'].document.lz_login_form.submit();
	}
	else
	{
		frames['lz_chat_frame.3.2'].location = './livezilla.php?template=lz_chat_frame.3.2.mail&' + lz_chat_data.GetParameters;
	}
}

function lz_chat_check_login_inputs()
{
	lz_chat_data.ExternalUser.Username = lz_global_replacements(frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.login.1.0'].document.getElementById('lz_chat_login_name').value.substr(0,32),false);;
	lz_chat_data.ExternalUser.Email = lz_global_replacements(frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.login.1.0'].document.getElementById('lz_chat_login_email').value.substr(0,50),false);
	lz_chat_data.ExternalUser.Company = lz_global_replacements(frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.login.1.0'].document.getElementById('lz_chat_login_company').value.substr(0,50),false);
	
	if(!lz_chat_validate_group())
		return;
	
	frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.login.1.0'].document.getElementById('lz_chat_login_button').disabled = true;
	lz_chat_login(selGroup.Id);
}

function lz_chat_check_mail_inputs()
{
	lz_chat_data.ExternalUser.Username = frames['lz_chat_frame.3.2'].document.getElementById('lz_chat_mail_name').value.substr(0,32);
	lz_chat_data.ExternalUser.Email = frames['lz_chat_frame.3.2'].document.getElementById('lz_chat_mail_email').value.substr(0,50);
	lz_chat_data.ExternalUser.Company = frames['lz_chat_frame.3.2'].document.getElementById('lz_chat_mail_company').value.substr(0,50);
	lz_chat_data.ExternalUser.MailText = frames['lz_chat_frame.3.2'].document.getElementById('lz_chat_mail_message').value;
	lz_chat_data.ExternalUser.Group = frames['lz_chat_frame.3.2'].document.getElementById('lz_chat_mail_groups').value;

	if(lz_chat_data.ExternalUser.MailText.length > 5000)
		lz_chat_chat_alert(lz_chat_data.Language.MessageTooLong,frames['lz_chat_frame.3.2']);
	else if(lz_chat_data.ExternalUser.Username.length > 0 && lz_chat_data.ExternalUser.Email.length > 0)
		lz_chat_send_mail();
	else
		lz_chat_chat_alert(lz_chat_data.Language.NameAndEmail,frames['lz_chat_frame.3.2']);
}

function lz_chat_send_mail()
{
	frames['lz_chat_frame.3.2'].document.getElementById('lz_chat_mail_button').disabled = true;
	lz_chat_server_request("./server.php",lz_chat_get_post_values("send_mail",false,true),20000,lz_chat_send_mail);
}

function lz_chat_mail_callback(_redirect)
{
	if(_redirect)
	{
		lz_chat_chat_alert(lz_chat_data.Language.MessageReceived,frames['lz_chat_frame.3.2'],lz_chat_close);
	}
	else
	{
		frames['lz_chat_frame.3.2'].document.getElementById('lz_chat_mail_button').disabled = false;
		lz_chat_chat_alert(lz_chat_data.Language.MessageFlood,frames['lz_chat_frame.3.2']);
	}
}

function lz_chat_mail_print()
{
  var printWindow = window.open("", "_blank", "width=600,height=500,menubar=yes,toolbar=yes");
  printWindow.document.write("<html><body><b><!--lang_client_your_name-->:</b> "+frames['lz_chat_frame.3.2'].document.getElementById('lz_chat_mail_name').value+"<br>");
  printWindow.document.write("<b><!--lang_client_your_email-->:</b> "+frames['lz_chat_frame.3.2'].document.getElementById('lz_chat_mail_email').value+"<br>");
  printWindow.document.write("<b><!--lang_client_your_company-->:</b> "+frames['lz_chat_frame.3.2'].document.getElementById('lz_chat_mail_company').value+"<br>");
  printWindow.document.write("<b><!--lang_client_group-->:</b> "+frames['lz_chat_frame.3.2'].document.getElementById('lz_chat_mail_groups').options[frames['lz_chat_frame.3.2'].document.getElementById('lz_chat_mail_groups').selectedIndex].text+"<br>");
  printWindow.document.write("<hr>" + frames['lz_chat_frame.3.2'].document.getElementById('lz_chat_mail_message').value.replace(/[\r\n]+/g, "<br>"));
  printWindow.document.write("<hr><a href=\"javascript:print();\"><!--lang_client_print--></a>");
  printWindow.document.write("</body></html>");
  printWindow.document.close();
  printWindow.focus();
}

function lz_chat_close()
{
	var windowName = window.name;
	if( lz_chat_data.GetParameters.indexOf("reset") == -1)
		window.location = "./livezilla.php?"+lz_chat_data.GetParameters + "&reset=true";
	else
		window.location = "./livezilla.php?"+lz_chat_data.GetParameters;
	window.name = windowName;
}

function lz_tracking_geo_result(_lat,_long,_region,_city,_tz,_ctryi2)
{
	lz_geo_resolution.OnTimeoutEvent = null;
	lz_chat_data.ExternalUser.Session.GeoResolved = Array(_lat,_long,_region,_city,_tz,_ctryi2);
	lz_chat_data.ExternalUser.Session.Save();
	lz_chat_startup();
}

function lz_tracking_geo_resolute()
{
	lz_geo_resolution = new lz_geo_resolver();
	lz_geo_resolution.OnEndEvent = "lz_tracking_geo_result";
	lz_geo_resolution.OnTimeoutEvent = lz_chat_startup;
	lz_geo_resolution.ResolveAsync();
}

function lz_chat_file_changed()
{
	lz_chat_file_unset_images();
	frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.1.0'].document.getElementById('lz_chat_file_name').value = frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.1.0'].document.getElementById('lz_chat_file_base').value;
}

function lz_chat_file_request_upload()
{
	if(lz_chat_data.Status.Login == lz_chat_data.STATUS_STOPPED)
		return lz_chat_chat_alert(lz_chat_data.Language.RepresentativeLeft,frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.4.0']);
		
	if(!lz_chat_data.InternalUser.Available)
		return lz_chat_chat_alert(lz_chat_data.Language.WaitForRepresentative,frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.4.0']);
	
	if(!lz_chat_data.FileUpload.Running)
	{
		lz_chat_file_unset_images();
		
		if(frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.1.0'].document.getElementById('lz_chat_file_name').value.length == 0)
			return lz_chat_chat_alert(lz_chat_data.Language.SelectFile,frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.4.0']);

		lz_chat_data.FileUpload.Filename = frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.1.0'].document.getElementById('lz_chat_file_name').value
		lz_chat_data.FileUpload.Permitted = false;
		lz_chat_data.FileUpload.Running = true;
		lz_chat_data.FileUpload.Error = false;
		
		frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.1.0'].document.getElementById('lz_chat_file_load').style.visibility = "visible";
		frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.1.0'].document.getElementById('lz_chat_file_load').src = "./images/lz_circle.gif?acid=" + lz_global_microstamp();
		frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.1.0'].document.getElementById('lz_chat_file_name').disabled =
		frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.1.0'].document.getElementById('lz_chat_file_select').disabled = true;
		frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.1.0'].document.getElementById('lz_chat_file_send').value = "<!--lang_client_abort-->";
		lz_chat_focus_textbox();
	}
	else
	{
		lz_chat_file_stop();
	}
	lz_chat_shout();
}

function lz_chat_file_start_upload(_file)
{
	lz_chat_data.PermittedFrames++;
	lz_chat_data.FileUpload.Permitted = true;
	frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.1.0'].document.getElementById('lz_chat_upload_form_userid').value = lz_chat_data.ExternalUser.Session.UserId;
	frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.1.0'].document.getElementById('lz_chat_upload_form_browser').value = lz_chat_data.ExternalUser.Session.BrowserId;
	lz_chat_data.FileUpload.Filename = frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.1.0'].document.getElementById('lz_chat_file_base').value;
	frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.1.0'].document.lz_file_form.submit();
}

function lz_chat_file_error(_value)
{
	lz_chat_file_stop();
	lz_chat_data.FileUpload.Error = _value;
	if(_value == lz_chat_data.FILE_UPLOAD_REJECTED)
		lz_chat_chat_alert(lz_chat_data.Language.FileUploadRejected,frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.4.0']);
	else if(_value == lz_chat_data.FILE_UPLOAD_OVERSIZED)
		lz_chat_chat_alert(lz_chat_data.Language.FileUploadOversized,frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.4.0']);
	frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.1.0'].document.getElementById('lz_chat_file_error').style.visibility = "visible";
}

function lz_chat_file_ready()
{
	lz_chat_data.FileUpload.Error = 
	lz_chat_data.FileUpload.Running = false;
	frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.1.0'].document.getElementById('lz_chat_file_success').style.visibility = "visible";
	lz_chat_chat_alert(lz_chat_data.Language.FileProvided,frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.4.0']);
}

function lz_chat_file_clear()
{
	lz_chat_data.PermittedFrames++;
	frames['lz_chat_frame.3.2'].document.getElementById('lz_chat_file_upload_frame').src = frames['lz_chat_frame.3.2'].document.getElementById('lz_chat_file_upload_frame').src + "&acid=" + lz_global_microstamp();
}

function lz_chat_file_stop()
{
	if(lz_chat_data.FileUpload.Running && lz_chat_data.FileUpload.Permitted)
		lz_chat_file_clear();
	
	lz_chat_data.FileUpload.Running = 
	lz_chat_data.FileUpload.Permitted = false;
	lz_chat_data.FileUpload.Error = true;
	lz_chat_shout();
	lz_chat_file_unset_images();
	frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.1.0'].document.getElementById('lz_chat_file_send').disabled = true;
	frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.1.0'].document.getElementById('lz_chat_file_name').disabled = 
	frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.1.0'].document.getElementById('lz_chat_file_select').disabled = false;
	frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.1.0'].document.getElementById('lz_chat_file_send').value = "<!--lang_client_send-->";
	setTimeout("lz_chat_file_reactivate();",lz_chat_data.ChatFrequency*2*1000);
}

function lz_chat_file_reactivate()
{
	frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.1.0'].document.getElementById('lz_chat_file_send').disabled = false;
}

function lz_chat_file_unset_images()
{
	frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.1.0'].document.getElementById('lz_chat_file_load').style.visibility = 
	frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.1.0'].document.getElementById('lz_chat_file_success').style.visibility = 
	frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.1.0'].document.getElementById('lz_chat_file_error').style.visibility = "hidden";
}

function lz_chat_file_reset()
{
	lz_chat_data.FileUpload.Running = false;
	lz_chat_file_stop();
	lz_chat_data.FileUpload.Error = false;
}
