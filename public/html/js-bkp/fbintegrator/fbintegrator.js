/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/LICENSE-M1.txt
 *
 * @category   AW
 * @package    AW_FBIntegrator
 * @copyright  Copyright (c) 2003-2009 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/LICENSE-M1.txt
 */

var FBIntegrator = Class.create();
FBIntegrator.prototype = {
	initialize: function(apiKey, xdUrl, ajaxUrl, ajaxLogin) {
		this.ajaxUrl = ajaxUrl;
		this.onSuccess = this.success.bindAsEventListener(this);
		this.ajaxLogin = ajaxLogin;
		FB.init(apiKey, xdUrl);
	},
	
	login: function() {
		var request = new Ajax.Request(
			this.ajaxUrl+'login',
            {
                method:'post',
                onSuccess: this.onSuccess
            }
        );
	},
	
	validateForm: function(form){
		var validator = new Validation(form);
		if (!validator.validate()) {
            return false;
        }
		return true;
	},
	
	register: function(form) {
		var request = new Ajax.Request(
			this.ajaxUrl+'register',
            {
                method:'post',
                onSuccess: this.onSuccess,
                parameters: Form.serialize(form)
            }
        );
	},
	
	link: function() {
		var request = new Ajax.Request(
			this.ajaxUrl+'link',
            {
                method:'post',
                onSuccess: this.onSuccess
            }
        );		
	},
	
	savePermissions: function(permissions) {
		if (permissions == '') return false;
		var request = new Ajax.Request(
			this.ajaxUrl+'savePermissions',
            {
                method:'post',
                onSuccess: this.onSuccess,
                parameters: new Hash({'permissions' : permissions})
            }
        );
	},

	storeLogin: function(){
		this.ajaxLoader.hide();
		this.ajaxLogin.activate();
		Event.stop(event);		
	},
	
	success: function(transport) {
        if (transport && transport.responseText){
            try{
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }
		if (response.needlogin)
		{
			this.storeLogin();
		}
        if (response.error){
            if ((typeof response.message) == 'string') {
                alert(response.message);
            } else {
                alert(response.message.join("\n"));
            }
        }
        if ('undefined' != typeof(response.redirect)){
        	location.href = response.redirect;
        } else {
        	this.ajaxLoader.hide();
        }
    }
}

/*------------------------------------------------------------------------------------------------------*/
/*------------------------------------------------------------------------------------------------------*/

var fbiDetect = navigator.userAgent.toLowerCase();
var fbiOS,fbiBrowser,fbiVersion,fbiTotal,fbiThestring;

function fbiGetBrowserInfo() {
	if (fbiCheckIt('konqueror')) {
		fbiBrowser = "Konqueror";
		fbiOS = "Linux";
	}
	else if (fbiCheckIt('safari')) fbiBrowser 	= "Safari"
	else if (fbiCheckIt('omniweb')) fbiBrowser 	= "OmniWeb"
	else if (fbiCheckIt('opera')) fbiBrowser 	= "Opera"
	else if (fbiCheckIt('webtv')) fbiBrowser 	= "WebTV";
	else if (fbiCheckIt('icab')) fbiBrowser 	= "iCab"
	else if (fbiCheckIt('msie')) fbiBrowser 	= "Internet Explorer"
	else if (!fbiCheckIt('compatible')) {
		fbiBrowser = "Netscape Navigator"
		fbiVersion = fbiDetect.charAt(8);
	}
	else fbiBrowser = "An unknown browser";

	if (!fbiVersion) fbiVersion = fbiDetect.charAt(place + fbiThestring.length);

	if (!fbiOS) {
		if (fbiCheckIt('linux')) fbiOS 		= "Linux";
		else if (fbiCheckIt('x11')) fbiOS 	= "Unix";
		else if (fbiCheckIt('mac')) fbiOS 	= "Mac"
		else if (fbiCheckIt('win')) fbiOS 	= "Windows"
		else fbiOS 								= "an unknown operating system";
	}
}

function fbiCheckIt(string) {
	place = fbiDetect.indexOf(string) + 1;
	fbiThestring = string;
	return place;
}

/*-----------------------------------------------------------------------------------------------*/

//Event.observe(window, 'load', rafInitialize, false);
Event.observe(window, 'load', fbiGetBrowserInfo, false);
//Event.observe(window, 'unload', Event.unloadCache, false);

/*------------------------------------------------------------------------------------------------------*/

var FBIntegratorLogin = Class.create();
FBIntegratorLogin.prototype = {
	yPos : 0,
	xPos : 0,
    isLoaded : false,

	initialize: function(url) {
		if (url){
			this.content = url;
		}
        $('fbintegrator').hide().observe('click', (function(event) {if ((event.element().id == 'fbintegrator-cancel') || (event.element().id == 'span-fbintegrator-cancel')  ) this.deactivate(); }).bind(this));
	},

	activate: function(){
		if (fbiBrowser == 'Internet Explorer'){
			this.getScroll();
			this.prepareIE('100%', 'hidden');
			this.setScroll(0,0);
			this.hideSelects('hidden');
		}
		this.displayFBIntergator("block");
	},

	prepareIE: function(height, overflow){
		bod = document.getElementsByTagName('body')[0];
		bod.style.height = height;
		bod.style.overflow = overflow;

		htm = document.getElementsByTagName('html')[0];
		htm.style.height = height;
		htm.style.overflow = overflow;
	},

	hideSelects: function(visibility){
		selects = document.getElementsByTagName('select');
		for(i = 0; i < selects.length; i++) {
			selects[i].style.visibility = visibility;
		}
	},

	getScroll: function(){
		if (self.pageYOffset) {
			this.yPos = self.pageYOffset;
		} else if (document.documentElement && document.documentElement.scrollTop){
			this.yPos = document.documentElement.scrollTop;
		} else if (document.body) {
			this.yPos = document.body.scrollTop;
		}
	},

	setScroll: function(x, y){
		window.scrollTo(x, y);
	},

	displayFBIntergator: function(display){
		$('fbintegrator-overlay').style.display = display;
		$('fbintegrator').style.display = display;
		if(display != 'none') this.loadInfo();
	},

	loadInfo: function() {
        $('fbintegrator').className = "loading";
		var ContentAjax = new Ajax.Request(
            this.content,
            {method: 'post', parameters: "", onComplete: this.processInfo.bindAsEventListener(this)}
		);
	},

	processInfo: function(response){
        $('fbiContent').update(response.responseText);
		$('fbintegrator').className = "done";
        this.isLoaded = true;
		this.actions();
	},

	actions: function(){
		fbiActions = document.getElementsByClassName('fbiAction');
	},

	deactivate: function(){
		//Element.remove($('fibContent'));
		if (fbiBrowser == "Internet Explorer"){
			this.setScroll(0,this.yPos);
			this.prepareIE("auto", "auto");
			this.hideSelects("visible");
		}
		this.displayFBIntergator("none");
	}
}

/*------------------------------------------------------------------------------------------------*/


var FBIntegratorForm = Class.create();
FBIntegratorForm.prototype = {
    initialize: function(form){
        this.form = form;
        if ($(this.form)) {
            this.sendUrl = $(this.form).action;
            $(this.form).observe('submit', function(event){ this.send();Event.stop(event);  }.bind(this));
        }

		this.set_email();
        this.loadWaiting = false;
        this.validator = new Validation(this.form);
        this.onSuccess = this.success.bindAsEventListener(this);
        this.onComplete = this.resetLoadWaiting.bindAsEventListener(this);
        this.onFailure = this.resetLoadWaiting.bindAsEventListener(this);
    },

	set_email: function() {
		aw_email = document.getElementById('aw_email');
		m_email = document.getElementById('email');
		if (!aw_email.value && m_email != null){
			aw_email.value = m_email.value;
		}
	},

    send: function(){
        if(!this.validator.validate()) {
            return false;
        }
        this.setLoadWaiting(true);
        var request = new Ajax.Request(
            this.sendUrl,
            {
                method:'post',
                onComplete: this.onComplete,
                onSuccess: this.onSuccess,
                onFailure: this.onFailure,
                parameters: Form.serialize(this.form)
            }
        );
    },

    success: function(transport) {
        this.resetLoadWaiting();
        if (transport && transport.responseText){
            try{
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }
        if (response.error){
            if ((typeof response.message) == 'string') {
                alert(response.message);
            } else {
                alert(response.message.join("\n"));
            }
            return false;
        }
		if (response.success)
		{
			FBLogin.deactivate();
			Facebook.link();
			return false;
		}
		if (response.content)
		{
			$('fbiContent').update(response.content);
			return false;
		}
		$('fbiContent').update(transport.responseText);
    },

    _disableEnableAll: function(element, isDisabled) {
        var descendants = element.descendants();
        for (var k in descendants) {
            descendants[k].disabled = isDisabled;
        }
        element.disabled = isDisabled;
    },

    setLoadWaiting: function(isDisabled) {
        if (isDisabled){
            Element.show('login-please-wait');
            this.loadWaiting = true;
        } else {
            Element.hide('login-please-wait');
            this.loadWaiting = false;
        }
    },

    resetLoadWaiting: function(transport){
        this.setLoadWaiting(false);
    }
}



