var lzibst_height;
var lzibst_width;

function lz_livebox(_name,_width,_height,_template)
{
	this.lz_livebox_scroll_finished = false;
	this.lz_livebox_template = _template;
	this.lz_livebox_name = _name;
	this.lz_livebox_move = lz_livebox_move_box;
	this.lz_livebox_show = lz_livebox_show_box;
	this.lz_livebox_close = lz_livebox_close_box;
	this.lz_livebox_chat = lz_livebox_direct_chat;
	
	lzibst_height = _height;
	lzibst_width = _width;
	
	function lz_livebox_direct_chat(_sess,_id,_intid)
	{
		void(window.open('<!--server-->livezilla.php?intid='+_intid+'&en='+lz_global_utf8_encode(document.getElementById('lz_invitation_name').value,true)+'&code=<!--area_code-->','LiveZilla','width=<!--width-->,height=<!--height-->,left=0,top=0,resizable=yes,menubar=no,location=yes,status=yes,scrollbars=no'));
	}
	
	function lz_livebox_close_box(uid)
	{
		if(!this.lz_livebox_scroll_finished)
			return;
		
		document.body.removeChild(this.MainDiv);
		lz_request_window = null;
	}
	
	function lz_livebox_show_box()
	{
		this.MainDiv = document.createElement('DIV');
		this.MainDiv.id = this.lz_livebox_name;
		this.MainDiv.style.height = lzibst_height+'px';
		this.MainDiv.style.width = lzibst_width+'px';
		this.MainDiv.style.position = 'absolute';
		this.MainDiv.style.left = -400+'px';
		this.MainDiv.style.top = -400+'px';
		this.MainDiv.style.zIndex = 1001;
		this.MainDiv.innerHTML = this.lz_livebox_template;
		document.body.appendChild(this.MainDiv);
		window.setTimeout("window['"+ this.lz_livebox_name +"'].lz_livebox_move()",2500);
	}

	function lz_livebox_move_box()
	{
		var current = parseInt(this.MainDiv.style.top.replace("px","").replace("pt",""));
		current+=15;

		this.MainDiv.style.top = current+'px';
		this.MainDiv.style.left = lz_livebox_center_get_left()+'px';

		if(current < (lz_livebox_center_get_top()-15))
			window.setTimeout("window['"+ this.lz_livebox_name +"'].lz_livebox_move()",15);
		else
			this.lz_livebox_scroll_finished = true;

		if(this.lz_livebox_scroll_finished && document.body.onresize == null)
		{
			window.onresize = 
			window.onscroll = lz_livebox_center_box;
		}
	}
}

function lz_livebox_center_box()
{
	if(document.getElementById("lz_request_window") != null)
	{
		document.getElementById("lz_request_window").style.top = lz_livebox_center_get_top()+'px';
		document.getElementById("lz_request_window").style.left = lz_livebox_center_get_left()+'px';
	}
	if(document.getElementById("lz_alert_window") != null)
	{
		document.getElementById("lz_alert_window").style.top = lz_livebox_center_get_top()+'px';
		document.getElementById("lz_alert_window").style.left = lz_livebox_center_get_left()+'px';
	}
}

function lz_livebox_center_get_left()
{
	var scrollleft = (document.documentElement) ? Math.max(document.documentElement.scrollLeft, document.body.scrollLeft) : 50;
	var xc = (document.documentElement) ? 50 : window.pageXOffset;
	var xd = (document.documentElement) ? (document.documentElement.offsetWidth * 50 / 100) + scrollleft : (window.innerWidth * 50 / 100) + window.pageXOffset;
	var top;
	if (3 == 2) 
		top = xc + scrollleft;
	else if (3 == 3) 
		top = xd;
	else 
		top = 50;
	top -= (lzibst_width / 2);
	return top;
}

function lz_livebox_center_get_top()
{
	var scrolltop = (document.documentElement) ? Math.max(document.documentElement.scrollTop, document.body.scrollTop) : 50;
	var xc = (document.documentElement) ? 50 : window.pageYOffset;
	var xd = (document.documentElement) ? (document.documentElement.offsetHeight * 50 / 100) + scrolltop : (window.innerHeight * 50 / 100) + window.pageYOffset;

	if(window.pageYOffset != null)
		xd = (window.innerHeight * 50 / 100) + window.pageYOffset;
	
	var top;
	if (3 == 2) 
		top = xc + scrolltop;
	else if (3 == 3) 
		top = xd;
	else 
		top = 50;
	top -= (lzibst_height / 2);

	return top;
}