function lz_chat_message() 
{
	var msg = frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.5.0'].document.getElementById('lz_chat_text').value;
	if(lz_chat_data.Status.Login == lz_chat_data.STATUS_STOPPED)
		return lz_chat_chat_alert(lz_chat_data.Language.RepresentativeLeft,frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.4.0']);
	else if(msg.length > 0 && lz_global_trim(msg).length > 0 && msg.length <= lz_chat_data.MAXCHATLENGTH)
	{
		var message = new lz_chat_post();
		message.MessageText = lz_global_utf8_encode(msg,true);
		message.MessageId = Math.floor(Math.random()*100001)+100000;
		lz_chat_data.ExternalUser.Typing = false;
		lz_chat_add_extern_text(msg);
		lz_chat_data.ExternalUser.Messages[lz_chat_data.ExternalUser.Messages.length] = message;
		lz_chat_shout();
	}
	else if(msg.length > lz_chat_data.MAXCHATLENGTH)
		return lz_chat_chat_alert(lz_chat_data.Language.MessageTooLong,frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.4.0']);
	
	setTimeout("lz_chat_clear_field()",1);
	return false;
}

function lz_chat_clear_field()
{
	frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.5.0'].document.getElementById('lz_chat_text').value = "";
	lz_chat_focus_textbox();
}

function lz_chat_focus_textbox()
{
	frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.5.0'].focus();
	frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.5.0'].document.getElementById('lz_chat_text').focus();
}

function lz_chat_chat_alert_move()
{
	if(lz_chat_data.ActiveAlertFrame != null && lz_chat_data.ActiveAlertFrame.document.getElementById('lz_chat_alert_box').style.visibility == "visible")
		lz_chat_chat_alert_set_position(lz_chat_data.ActiveAlertFrame);
}

function lz_chat_chat_alert_set_position(_frame)
{
	if(frames['lz_chat_frame.3.2'].document.getElementById('lz_chat_frameset_chat') != null)
		var frame_rows = frames['lz_chat_frame.3.2'].document.getElementById('lz_chat_frameset_chat').rows.split(",");
	else
		var frame_rows = Array(0,0,0,0,0);

	var top = (_frame.document.body.clientHeight-_frame.document.getElementById('lz_chat_alert_box').offsetHeight-frame_rows[2]-frame_rows[3]) / 2;
	var left = (_frame.document.body.scrollWidth-_frame.document.getElementById('lz_chat_alert_box').offsetWidth+20) / 2;

	_frame.document.getElementById('lz_chat_alert_box').style.top = top + _frame.document.body.scrollTop;
	_frame.document.getElementById('lz_chat_alert_box').style.left = left;
}

function lz_chat_chat_alert(_text,_frame,_event)
{	
	_frame.document.getElementById('lz_chat_alert_box_text').innerHTML = _text;
	_frame.document.getElementById('lz_chat_alert_box').style.visibility = "visible";
	_frame.document.getElementById("lz_chat_alert_button").onclick = function(){_frame.document.getElementById('lz_chat_alert_box').style.visibility = 'hidden';};
	
	lz_chat_chat_alert_set_position(_frame);
	
	if(_event == -1)
		_frame.document.getElementById('lz_chat_alert_button').disabled = true;
	else if(_event != null)
		_frame.document.getElementById('lz_chat_alert_button').onclick = _event;
		
	lz_chat_data.ActiveAlertFrame = _frame;
}

function lz_chat_take_smiley(_smiley)
{
	var sign = "";
	switch(_smiley)
	{
		case"smile":sign = ":-)";break;
		case"sad":sign = ":-(";break;
		case"neutral":sign = ":-|";break;
		case"tongue":sign = ":-P";break;
		case"cry":sign = ":'-(";break;
		case"lol":sign = ":-]";break;
		case"shocked":sign = ":-O";break;
		case"wink":sign = ";-)";break;
		case"cool":sign = "8-)";break;
		case"sick":sign = ":-\\\\";break;
		case"question":sign = ":?";break;
		case"sleep":sign = "zzZZ";break;
	}
	lz_chat_switch_smiley_box();
	frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.5.0'].document.getElementById('lz_chat_text').value += sign;
	lz_chat_focus_textbox();
}

function lz_chat_set_intern(_id,_fullname,_groupname,_typing,_vcard)
{
	if(_id.length > 0)
	{
		lz_chat_data.InternalUser.Id = lz_global_utf8_decode(lz_global_base64_decode(_id));
		lz_chat_data.InternalUser.Fullname = (_fullname.length > 0) ? lz_global_utf8_decode(lz_global_base64_decode(_fullname)) : lz_chat_data.InternalUser.Id;
		frames['lz_chat_frame.1.1'].document.getElementById('lz_chat_internal_fullname').innerHTML = lz_chat_data.InternalUser.Fullname;
		frames['lz_chat_frame.1.1'].document.getElementById('lz_chat_internal_groupname').innerHTML = lz_global_utf8_decode(lz_global_base64_decode(_groupname));
		frames['lz_chat_frame.1.1'].document.getElementById('lz_chat_vcard_image').href = 
		frames['lz_chat_frame.1.1'].document.getElementById('lz_chat_vcard_text').href = "./visitcard.php?vcardid=" + encodeURIComponent(lz_chat_data.InternalUser.Id);
	}
	lz_chat_data.InternalUser.Available = (_id.length > 0);
	frames['lz_chat_frame.1.1'].document.getElementById('lz_chat_internal_typing').style.visibility = (_id.length > 0 && _typing) ? "visible" : "hidden";
	frames['lz_chat_frame.1.1'].document.getElementById('lz_chat_vcard_image').style.visibility = (_id.length > 0 && _vcard) ? "visible" : "hidden";
	frames['lz_chat_frame.1.1'].document.getElementById('lz_chat_vcard_text').style.visibility = (_id.length > 0 && _vcard) ? "visible" : "hidden";
	frames['lz_chat_frame.1.1'].document.getElementById('lz_chat_top_bg').style.visibility = (_id.length == 0) ? "visible" : "hidden";
	frames['lz_chat_frame.1.1'].document.getElementById('lz_chat_representative').style.visibility = (_id.length > 0) ? "visible" : "hidden";
}

function lz_chat_set_intern_image(_ispic,_update,_webcam,_filtered,_wcfile,_prfile)
{
	if(_filtered)
	{
		frames['lz_chat_frame.1.1'].document.getElementById('lz_chat_intern_image').src = "./intern_images/filtered.jpg";
		return;
	}
	if(_webcam)
		lz_chat_data.InternalUser.Webcam = 5;
	else if(lz_chat_data.InternalUser.Webcam > -1)
	{
		lz_chat_data.InternalUser.Webcam--;
		return;
	}
	
	var current_picture = frames['lz_chat_frame.1.1'].document.getElementById('lz_chat_intern_image').src;
	if(_ispic && (_webcam || current_picture.indexOf("nopic.jpg") != -1 || _update || (current_picture.indexOf("wc.jpg") != -1 && !_webcam)))
		lz_chat_data.TempImage.src = (_webcam) ? _wcfile+"?"+ Math.random() : _prfile+"?"+ Math.random();
	else if(!_ispic)
		frames['lz_chat_frame.1.1'].document.getElementById('lz_chat_intern_image').src = "./intern_images/nopic.jpg";
}

function lz_chat_show_intern_image()
{
	frames['lz_chat_frame.1.1'].document.getElementById('lz_chat_intern_image').src = lz_chat_data.TempImage.src;
	if(lz_chat_data.TempImage.height > 0)
		frames['lz_chat_frame.1.1'].document.getElementById('lz_chat_pic').style.width = (lz_chat_data.TempImage.width > 80) ? lz_chat_data.TempImage.width : 80;
}	

function lz_chat_switch_extern_typing(_typing)
{	
	var announce = (_typing != lz_chat_data.ExternalUser.Typing);
	if(_typing)
	{
		if(lz_chat_data.TimerTyping != null)
			clearTimeout(lz_chat_data.TimerTyping);
		lz_chat_data.TimerTyping = setTimeout("lz_chat_switch_extern_typing(false);",5000);
		lz_switch_title_mode(false);
	}
	lz_chat_data.ExternalUser.Typing = _typing;
	
	if(announce)
		lz_chat_shout();
}

function lz_chat_add_system_text(_type,_texta) 
{	
	if(_type == 0)
		_text = lz_chat_data.Language.ClientForwarding;
	else if(_type == 1)
		_text = lz_chat_data.Language.ClientInternArrives.replace("<!--intern_name-->",lz_global_utf8_decode(lz_global_base64_decode(_texta)));
	else if(_type == 2)
		_text = lz_chat_data.Language.ClientErrorUnavailable + lz_global_utf8_decode(lz_global_base64_decode(_texta));
	else if(_type == 3)
		_text = lz_chat_data.Language.ClientIntLeft + " <a class=\"lz_chat_link\" href=\"javascript:top.lz_chat_goto_message(true);\"><b>" + lz_chat_data.Language.LanguageLeaveMessageShort + "</b></a>. " + lz_chat_data.Language.ClientThankYou;
	else if(_type == 4)
		_text = lz_chat_data.Language.ClientIntDeclined + " <a class=\"lz_chat_link\" href=\"javascript:top.lz_chat_goto_message(true);\"><b>" + lz_chat_data.Language.LanguageLeaveMessageShort + "</b></a>. " + lz_chat_data.Language.ClientThankYou;
	else if(_type == 5)
		_text = lz_chat_data.Language.ClientStillWaitingInt + " <a class=\"lz_chat_link\" href=\"javascript:top.lz_chat_goto_message(true);\"><b>" + lz_chat_data.Language.LanguageLeaveMessageShort + "</b></a>. " + lz_chat_data.Language.ClientThankYou; 
	else if(_type == 6)
		_text = lz_chat_data.Language.ClientIntIsConnected;
	else if(_type == 7)
		_text = "<b>" + lz_chat_data.Language.ClientIntsAreBusy + "</b> " + lz_chat_data.Language.ClientThankYou + "<br>";
	else if(_type == 8)
		_text = lz_chat_data.Language.ClientNoInternUsers + " <a class=\"lz_chat_link\" href=\"javascript:top.lz_chat_goto_message(true);\"><b>" + lz_chat_data.Language.LanguageLeaveMessageShort + "</b></a>. " + lz_chat_data.Language.ClientThankYou;
	else
		_text = _texta;
		
	_text = lz_global_replace_smilies(_text,true);
	if(lz_chat_data.LastSender != lz_chat_data.SYSTEM)
	{
		_text = lz_global_base64_decode(lz_chat_data.Templates.MessageExternal).replace("<!--message-->",_text);
		lz_chat_data.AlternateRow = true;
	}
	else
	{
		if(lz_chat_data.AlternateRow)
			_text = lz_global_base64_decode(lz_chat_data.Templates.MessageAddAlt).replace("<!--message-->",_text);
		else
			_text = lz_global_base64_decode(lz_chat_data.Templates.MessageAdd).replace("<!--message-->",_text);
		lz_chat_data.AlternateRow = !lz_chat_data.AlternateRow;
	}
		

	_text = _text.replace("<!--time-->",lz_chat_get_locale_time());
	_text = _text.replace("<!--name-->",lz_chat_data.Language.System);
	lz_chat_append_text(_text);
	lz_chat_data.LastSender = lz_chat_data.SYSTEM;
}

function lz_chat_add_intern_text(_text, _title, _type, _acid) 
{
	_title = lz_global_utf8_decode(lz_global_base64_decode(_title));
	_text = lz_global_utf8_decode(lz_global_base64_decode(_text));
	
	if(lz_chat_data.LastSender != lz_chat_data.INTERNAL)
	{
		_text = lz_global_base64_decode(lz_chat_data.Templates.MessageInternal).replace("<!--message-->",_text);
		lz_chat_data.AlternateRow = true;
	}
	else
	{
		if(lz_chat_data.AlternateRow)
			_text = lz_global_base64_decode(lz_chat_data.Templates.MessageAddAlt).replace("<!--message-->",_text);
		else
			_text = lz_global_base64_decode(lz_chat_data.Templates.MessageAdd).replace("<!--message-->",_text);
		lz_chat_data.AlternateRow = !lz_chat_data.AlternateRow;
	}
		
	_text = _text.replace("<!--time-->",lz_chat_get_locale_time());
	_text = _text.replace("<!--name-->",lz_chat_data.InternalUser.Fullname);
	
	_text = lz_global_replace_smilies(_text,true);
	
	var icon = "";
	if(_type == "file")
		icon = "<td width=\"22\"><img src=\"./images/icon_save.gif\" alt=\"\" border=\"0\"></td>";
	else if(_type == "url")
		icon = "<td width=\"22\"><img src=\"./images/icon_link.gif\" alt=\"\" border=\"0\"></td>";
	_text = _text.replace("<!--icon-->",icon);
	
	lz_switch_title_mode(true);
	lz_chat_append_text(_text);
	
	clearTimeout(lz_chat_data.TimerSound);
	lz_chat_data.TimerSound = setTimeout(lz_chat_play_sound,30);
	lz_chat_data.LastSender = lz_chat_data.INTERNAL;
}

function lz_chat_add_extern_text(_text) 
{
	_text = _text.replace(/\</g,"&lt;");
	_text = _text.replace(/\>/g,"&gt;");
	_text = lz_global_replace_breaks(_text);
	
	if(lz_chat_data.LastSender != lz_chat_data.EXTERNAL)
	{
		_text = lz_global_base64_decode(lz_chat_data.Templates.MessageExternal).replace("<!--message-->",_text);
		lz_chat_data.AlternateRow = true;
	}
	else
	{
		if(lz_chat_data.AlternateRow)
			_text = lz_global_base64_decode(lz_chat_data.Templates.MessageAddAlt).replace("<!--message-->",_text);
		else
			_text = lz_global_base64_decode(lz_chat_data.Templates.MessageAdd).replace("<!--message-->",_text);
		lz_chat_data.AlternateRow = !lz_chat_data.AlternateRow;
	}
		
	_text = lz_global_replace_smilies(_text,true);
	_text = _text.replace("<!--time-->",lz_chat_get_locale_time());
	_text = _text.replace("<!--name-->",lz_chat_data.ExternalUser.Username);
	lz_chat_append_text(_text);
	lz_chat_data.LastSender = lz_chat_data.EXTERNAL;
}

function lz_chat_append_text(_html)
{
	window.focus();
	if(!lz_chat_data.Status.Loaded)
		return;

	var newMessage = frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.4.0'].document.createElement("DIV");
	newMessage.innerHTML = _html;
	frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.4.0'].document.getElementById('lz_chat_main').appendChild(newMessage);
	setTimeout("frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.4.0'].scroll(0,1000000000)",10);

	if(lz_chat_data.Status.Login < lz_chat_data.STATUS_STOPPED)
		lz_chat_focus_textbox();
}

function lz_chat_print() 
{
	frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.4.0'].focus();
	frames['lz_chat_frame.3.2'].frames['lz_chat_frame.3.2.chat.4.0'].print();
}

function lz_chat_release_posts()
{	
	if(lz_chat_data.Status.Poll < 2)
		lz_chat_data.ExternalUser.MessagesBackup = new Array();
}

function lz_chat_change_window_state(_minimized)
{
	lz_switch_title_mode(_minimized);
}

function lz_chat_set_status(_status)
{	
	lz_chat_data.Status.Poll = _status;
}

function lz_chat_handle_response(_status, _response)
{
	lz_chat_data.LastConnectionFailed = false;
	lz_chat_data.ConnectionRunning = false;
	lz_chat_process_xml(_response);
}

function lz_chat_handle_shout_response(_status, _response)
{
	lz_chat_process_xml(_response);
	setTimeout("lz_chat_reshout()",lz_chat_data.ChatFrequency * 1000);
}

function lz_chat_process_xml(_xml)
{
	try
	{
		if(_xml.length > 0 && _xml.indexOf("<livezilla_js>") != -1)
		{
			lz_chat_data.LastConnection = lz_global_timestamp();
			var lzTstart = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?><livezilla_js>";
			var lzTend = "</livezilla_js>";
			eval(lz_global_base64_decode(_xml.substr(_xml.indexOf(lzTstart) + lzTstart.length,_xml.indexOf(lzTend)-_xml.indexOf(lzTstart)-lzTstart.length)));
		}
		else if(lz_chat_data.Status.Login < lz_chat_data.STATUS_INIT)
			setTimeout("lz_chat_handle_connection_error(null,null);",2000);
	}
	catch(ex){}
}

function lz_chat_handle_connection_error(_status, _response)
{
	lz_chat_data.ShoutNeeded = true;
	lz_chat_data.ConnectionRunning = 
	lz_chat_data.ShoutRunning = false;
	lz_chat_data.LastConnectionFailed = true;
	if(lz_chat_data.Status.Login < lz_chat_data.STATUS_INIT)
		lz_chat_reload_groups();
}
