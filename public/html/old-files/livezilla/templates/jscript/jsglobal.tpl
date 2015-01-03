var lz_title_timer;
var lz_title_step = 0;
var lz_title_modes = new Array(document.title,"<!--lang_client_new_messages-->");
var lz_standard_title = document.title;
var lz_document_head = document.getElementsByTagName("head")[0];
var lz_geo_resolution;
var lz_geo_resolution_needed = <!--geo_resolute-->;
var lz_geo_retries = 1;

if (typeof(encodeURIComponent) == 'undefined')
{
	encodeURIComponent = function(uri)
	{
		return (escape(uri));
	}
}

function lz_jssess()
{	
	this.Save = lz_jssess_save;
	this.Load = lz_jssess_load;

	this.GeoResolutions = 0;
	this.GeoResolved = Array();
	this.BrowserId = "<!--browser_id-->";
	this.UserId = "<!--user_id-->";

	function lz_jssess_save()
	{
		var data = "<LiveZilla>" + this.BrowserId + ";" + this.UserId + ";" + this.GeoResolved  + ";" + this.GeoResolutions + "</LiveZilla>";
		if(window.name == null || window.name == "undefinded" || window.name  == "" || (window.name == null && window.name.indexOf("<LiveZilla>") == -1))
		{
			if(window.name == null || window.name == "undefinded" || window.name  == "")
				window.name = data;
			else
				window.name += data;
		}
		else
		{
			var regex = new RegExp(/\<LiveZilla>.*?<\/LiveZilla>/g);
			window.name = window.name.replace(regex,"");
			window.name += data;
		}
	}
	
	function lz_jssess_load()
	{
		var data = window.name;
		if(data.indexOf("<LiveZilla>") != -1)
		{
			data = data.split("<LiveZilla>")[1].split("</LiveZilla>")[0].split(";");
			this.BrowserId = data[0];
			this.UserId = data[1];
			this.GeoResolved = data[2].split(",");
			this.GeoResolutions = data[3];
		}
	}
}

function lz_geo_resolver()
{
	this.ResolveAsync = lz_resolver_connect_async;
	this.TimeoutConnection = lz_resolver_timeout_connection;

	this.OnEndEvent;
	this.OnTimeoutEvent;	
	
	var OnEnd;
	var OnTimeout;
	
	var lz_resolver_request;
	var lz_resolver_timeout_timer;
	
	function lz_resolver_connect_async()
	{
		OnEnd = this.OnEndEvent;
		OnTimeout = this.OnTimeoutEvent;

		lz_resolver_timeout_timer = setTimeout(this.TimeoutConnection,15000);
		lz_resolver_request = document.createElement("script");
		lz_resolver_request.id = "livezilla_geoscript";
		lz_resolver_request.src = "<!--geo_url-->&method=" + OnEnd + "&spanm=lz_tracking_set_geo_span";
		lz_document_head.appendChild(lz_resolver_request);
	}

	function lz_resolver_timeout_connection()
	{
		if(OnTimeout != null)
			OnTimeout();
	}
}

function lz_global_replace_breaks(_text)
{
	_text = _text.replace(/[\r\n]+/g, "<br>");
	return _text.replace(/[\t]+/g, "&nbsp;&nbsp;&nbsp;");
}
	
function lz_global_base64_decode(_text)
{
	var base64_chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
	var bits;
	var decOut = '';
	var i = 0;
	for(; i<_text.length; i += 4)
	{
		bits = (base64_chars.indexOf(_text.charAt(i)) & 0xff) <<18 |
		(base64_chars.indexOf(_text.charAt(i +1)) & 0xff) <<12 |
		(base64_chars.indexOf(_text.charAt(i +2)) & 0xff) << 6 |
		base64_chars.indexOf(_text.charAt(i +3)) & 0xff;
		decOut += String.fromCharCode((bits & 0xff0000) >>16, (bits & 0xff00) >>8, bits & 0xff);
	}
	if(_text.charCodeAt(i -2) == 61)
		return(decOut.substring(0, decOut.length -2));
	else if(_text.charCodeAt(i -1) == 61)
		return(decOut.substring(0, decOut.length -1));
	else 
		return(decOut);
}

function lz_global_base64_encode(_input) 
{
	var base64_chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
   	var output = "";
   	var chr1, chr2, chr3;
   	var enc1, enc2, enc3, enc4;
	var i = 0;
	
	do {
	   chr1 = _input.charCodeAt(i++);
	   chr2 = _input.charCodeAt(i++);
	   chr3 = _input.charCodeAt(i++);
	
	   enc1 = chr1 >> 2;
	   enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
	   enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
	   enc4 = chr3 & 63;
	
	   if (isNaN(chr2)) {
	      enc3 = enc4 = 64;
	   } else if (isNaN(chr3)) {
	      enc4 = 64;
	   }
	
	   output = output + base64_chars.charAt(enc1) + base64_chars.charAt(enc2) +
	      base64_chars.charAt(enc3) + base64_chars.charAt(enc4);
	} while (i < _input.length);
	
	return output;
}

function lz_global_utf8_encode(_string,_encodeuri) 
{
	_string = _string.replace(/\r\n/g,"\n");
	var utftext = "";
	
	for (var n = 0; n < _string.length; n++) 
	{
	    var c = _string.charCodeAt(n);
	
	    if (c < 128) 
		{
	        utftext += String.fromCharCode(c);
	    }
	    else if((c > 127) && (c < 2048)) 
		{
	        utftext += String.fromCharCode((c >> 6) | 192);
	        utftext += String.fromCharCode((c & 63) | 128);
	    }
	    else 
		{
	        utftext += String.fromCharCode((c >> 12) | 224);
	        utftext += String.fromCharCode(((c >> 6) & 63) | 128);
	        utftext += String.fromCharCode((c & 63) | 128);
	    }
	}
	if(_encodeuri)
		return encodeURIComponent(utftext);
	else
		return utftext;
}

function lz_global_utf8_decode(utftext) 
{
	var string = "";
    var i = 0;
    var c = c1 = c2 = 0;

    while ( i < utftext.length ) 
	{
        c = utftext.charCodeAt(i);
        if (c < 128) 
		{
            string += String.fromCharCode(c);
            i++;
        }
        else if((c > 191) && (c < 224)) 
		{
            c2 = utftext.charCodeAt(i+1);
            string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
            i += 2;
        }
        else 
		{
            c2 = utftext.charCodeAt(i+1);
            c3 = utftext.charCodeAt(i+2);
            string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
            i += 3;
        }
    }
    return string;
}

function lz_set_title_mode()
{
    document.title = lz_title_modes[lz_title_step++ % 2];
}

function lz_switch_title_mode(_active)
{
	if(_active)
	{
		if(lz_title_timer == null)
    		lz_title_timer = setInterval("lz_set_title_mode()",800);
	}
	else
	{
		clearInterval(lz_title_timer);
		lz_title_timer = null;
   		document.title = lz_standard_title;
		lz_title_step = 0;
	}
}

function lz_global_replace_smilies(_text)
{
	var shorts = new Array(/:-\)/g,/::smile/g,/:\)/g,/:-\(/g,/::sad/g,/:\(/g,/:-]/g,/::lol/g,/;-\)/g,/::wink/g,/;\)/g,/:'-\(/g,/::cry/g,/:-O/g,/::shocked/g,/:-\\\\/g,/::sick/g,/:-p/g,/::tongue/g,/:-P/g,/:\?/g,/::question/g,/8-\)/g,/::cool/g,/zzZZ/g,/::sleep/g,/:-\|/g,/::neutral/g);
	var images = new Array("smile","smile","smile","sad","sad","sad","lol","lol","wink","wink","wink","cry","cry","shocked","shocked","sick","sick","tongue","tongue","tongue","question","question","cool","cool","sleep","sleep","neutral","neutral");
	for(var i = 0;i<shorts.length;i++)
		_text = _text.replace(shorts[i]," <img border=0 src='./images/smilies/"+images[i]+".gif'> ");
	return _text;
}

function lz_global_replacements(_text,_smileys)
{
	_text = _text.replace(/\\'/g,"'");
	_text = _text.replace(/\"/g,"\"");
	if(_smileys)
		_text = lz_global_replace_smilies(_text);
	return _text;
}

function lz_global_timestamp()
{
	var now = new Date();
	var ts = Math.round((now.getTime()/1000));
	return (ts);
}

function lz_global_microstamp()
{
	var now = new Date();
	var ts = now.getTime();
	return (ts);
}

function lz_global_get_long_poll_runtime()
{
	if(lz_chat_data.LastConnectionFailed)
		return 20;
	var value = lz_chat_data.PollTimeout - lz_chat_data.ChatFrequency - 25;
	if(value >= 60)
		value = 65;
	return value;
}

function lz_chat_get_locale_time()
{
	var time = new Date().toLocaleTimeString()
	time = time.split(" (");
	return time[0];
}

function lz_chat_get_locale_date()
{
	var date = new Date().toLocaleString();
	date = date.split(" (");
	return date[0];
}

function lz_global_handle_exception(exception,file,line)
{
	//alert(exception);
	return true;
}

function lz_global_trim(_str) 
{
	return _str.replace(/^\s+|\s+$/g,"");
}