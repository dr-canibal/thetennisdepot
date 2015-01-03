
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* pico.js custom bild
* Core version 1.0-b6 +hg20081030
* Built at 2009-02-16 11:02:18 GMT [1234782138]
* Selected plugins: XMLHttp, Form.XMLHttp, Form.Inputs.Values, CSS, CSS.RGB, CSS.Mutation, DOM.Coords, String, Collection, String.DOMAccess, String.XOR, Events, Visio
* Auto-selected dependent plugins: CSS.Mutation, CSS, CSS.RGB, String.DOMAccess, XMLHttp, Form.Inputs.Values
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	$_ = function(){
	var O = typeof arguments[0] == 'undefined' ? null:arguments[0];
	var o = O;
	var l = arguments.length;
	var r = [];
	if(!l)	return document;
	if(l>1){
		for(var i=0; i<l; i++)
			r.push($_(arguments[i]))
	}else if(
		o && (
			o instanceof Array ||
			(
				typeof o != 'string' && 
				!(o instanceof String)
			) &&
			(
				!(o instanceof Function)
			) &&
			((typeof o.length)+'') != 'undefined' &&  
			!o.tagName
		)
		){ // single array or DOM collection
			var l = o.length;
			for(var i=0; i<l; i++){
				r.push($_(o[i]))
		}
	}else if(l){														// one argument
		if(o && !o.$__OK){
			if(typeof o == 'string' || o instanceof String)
				o = $_.$_STR.$__init(o);
			else if(typeof o == 'number' || o instanceof Number)
				o = $_.$_NUMBER.$__init(o);
			else
				switch(typeof o){
					case 'function':
						$_.$_FUNC.$__init(o);
					break;
					default:
						if(o.nodeType)
							$_.$_DOM.$__init(o)
						else
							$_.$_OBJ.$__init(o)
				}
			if(o){
				o.$__OK = true;
				o.empty = o.empty || function(){return !this}
			}
			
		}
		r.push(o)
	}
	
	if(r.length != 1){
		var res = $_.$__flat(r);
		if(res[0] && res[0].$__EXT && res[0].$__TYPE){
			var sample = res[0].$__EXT;
			var type = res[0].$__TYPE;
			for(var i=sample.length-1;i>=0;i--){ //Foreach "extenders"
				
				for(var k=res.length-1; k>=0;k--){ //Foreach element in collection
					if(res[k].$__TYPE === type){
						if($_[type].$_$_ && $_[type].$_$_[sample[i]]){
							if(!$_[type].$_$_[sample[i]].accepts ||$_[type].$_$_[sample[i]].accepts(res)){
								
								$_[type].$_$_[sample[i]].init(res)
							}
							break;
						}
					}
				}
			}
		}
		return res;
	}else
		return r[0];
}


$_.ie = !!(navigator.appName == "Microsoft Internet Explorer" && !window.opera);
$_.ie6 = $_.ie && navigator.appVersion.match('6');
$_.opera = !!window.opera;
$_.gecko = !!((window.netscape && !window.opera));
$_.safari = !!(navigator.userAgent.toLowerCase().indexOf('safari') != -1);


$_.__ = {} // Engine's cache


$_.raise = function(module,message){ // Error messaging
	var msg  = [
		'Error::',
		module||'',
		(module && message)?'::':'',
		(module && message)?message:(module?"":'Unknown error')
	]
	var m = msg.join('')
	if(console.log)
		console.log(m);
	else
		alert(m);
	//var err = new Error(m)	
	//err.type = (module && message) ? module : false;
	//throw err;
}

$_.$_STR = {
	$_:{},
	$_$_:{},
	$__init : function(str){
		var el = str instanceof String ? str : new String(str);
		if(el)
			el.$__EXT = [];
			el.$__TYPE = '$_STR'
			for(var i in $_.$_STR.$_){
				if(!$_.$_STR.$_[i].accepts || $_.$_STR.$_[i].accepts(el)){
					el = $_.$_STR.$_[i].init(el);
					
					if(!el || !el.$__TYPE || el.$__TYPE !== '$_STR') break;
					el.$__EXT[el.$__EXT.length] = i;
				}
			}
		return el;
	}
}

$_.$_DOM = {
	$_:{},
	$_$_:{},
	$__init : function(obj){
		obj.$__EXT = [];
		obj.$__TYPE = '$_DOM';
		for(var i in $_.$_DOM.$_){
			if(!$_.$_DOM.$_[i].accepts || $_.$_DOM.$_[i].accepts(obj)){
				$_.$_DOM.$_[i].init(obj);
				obj.$__EXT[obj.$__EXT.length] = i;
			}
		}
	}
}
$_.$_OBJ = {
	$_:{},
	$_$_:{},
	$__init : function(obj){
		obj.$__EXT = [];
		obj.$__TYPE = '$_OBJ';
		for(var i in $_.$_OBJ.$_){
			if(!$_.$_OBJ.$_[i].accepts || $_.$_OBJ.$_[i].accepts(obj)){
				$_.$_OBJ.$_[i].init(obj);
				obj.$__EXT[obj.$__EXT.length] = i;
			}
		}
	}
}
$_.$_NUMBER = {
	$_:{},
	$_$_:{},
	$__init : function(el){
		
		var obj = el instanceof Number ? el : new Number(el);
		obj.$__EXT = [];
		obj.$__TYPE = '$_NUMBER';
		for(var i in $_.$_NUMBER.$_){
			if(!$_.$_NUMBER.$_[i].accepts || $_.$_NUMBER.$_[i].accepts(obj)){
				$_.$_NUMBER.$_[i].init(obj);
				obj.$__EXT[obj.$__EXT.length] = i;
			}
		}
		return obj;
	}
}
$_.$_FUNC = {
	$_:{},
	$__exec:[],
	$__init : function(obj){
		return obj;
	}
}

$_.copy = $_.$__copy = function(arr1){
	var arr2 = [];
	var l = arr1.length;
	for(var i =0;i<l;i++){
		arr2.push(arr1[i])
	}
	return arr2;
}
$_.flat = $_.$__flat = function(arr, fl){
	var arr2 = fl || [];
	var l = arr.length;
	for(var i =0;i<l;i++){
		if(arr[i] && arr[i].length && (typeof arr[i] != 'string' && !(arr[i] instanceof String)) && !arr[i].nodeType){
			$_.flat(arr[i], arr2)
		}
		else if(!(arr[i] instanceof Array))
			arr2.push(arr[i])
	}
	return arr2;
}


Array.prototype.contains = function (value) {
	var i;
	var l = this.length;
	for (i=0; i < l; i++)
		if (this[i] === value)
			return true;
	return null;
};
Array.prototype.search = function(value){
	var l = this.length;
	if(typeof value == 'function'){
		for (i=0; i < l; i++)
			if (value(this[i]))
				return i;
	}else
		for (i=0; i < l; i++)
			if (this[i] == value)
				return i;
	return null;
}

Array.prototype.intersects = function() {
	// Return intersecting elements for arrays as array
	var out = [];
	var l = this.length;
	var al = arguments.length;
	for(var i=0; i<l; i++){
		var skipFlag = false;
		for(var j=0; j<al; j++){
			if(arguments[j] === null || arguments[j] === false) var skipFlag = true;
			else if(arguments[j].contains && typeof arguments[j].length!='undefined'){
					if(!arguments[j].contains(this[i])){
						var skipFlag = true;
						break;
					}
				}else if(arguments[j] != this[i]){
					var skipFlag = true;
						break;
				}
				
		}
		if(!skipFlag) out.push(this[i])
	} 
	if(out){
		if(out.length) return out;
		else{ return []}
	}else
		return []
	
	return (out || out.length) ? out:null;
}


Array.prototype.apply = function(func){
	for(var i=0; i<this.length; i++){
		func(this[i])
	}
}

Array.prototype.empty = function(){
	return !this.join('').length
}


$_.extend = function(destination,source) {
    for (var property in source)
        destination[property] = source[property];
    return destination;
}

		$_.__.XMLHttp = function(url, data){
	this.URL = url || window.location.protocol+'//'+window.location.host+window.location.pathname;
	this.requestMethod = data ? 'POST' : 'GET';
	this.data = data ? data:false;
	this.is_async = false;
	this.callBack == false;
	this.request = window.XMLHttpRequest ? (new XMLHttpRequest()) : (new ActiveXObject("Microsoft.XMLHTTP"));
};

$_.__.XMLHttp.prototype.async = function(func){
	this.callBack = (typeof func == 'function') ? func : false;
	return this;
}
$_.__.XMLHttp.prototype.parse = function(){
	var self = this;
	if(typeof this.data != 'string'){
		var k = 0;
		var newData = '';
		for(var i in this.data){
			newData = newData + (k ? '&':'')+encodeURIComponent(i)+'='+encodeURIComponent(this.data[i]);
			k++;
		}
		this.data = newData;
	}
	if(this.requestMethod == 'GET' && this.data.length){
		
		
		var c = (this.URL.lastIndexOf('?') == -1) ? '?':'';
		this.URL = this.URL + c + this.data;
		this.data = null;
	}
	
	this.request.open (this.requestMethod, this.URL, (this.callBack ? true:false));
	if((this.callBack ? true:false)){
		this.request.onreadystatechange = function(s){return function(){s.onStateChange()}}(this);
	}
	if ( typeof(this.request.setRequestHeader) != "undefined" ) {
        this.request.setRequestHeader( "Content-Type", "application/x-www-form-urlencoded" );
    }
	this.request.send((this.data && this.data.length) ? this.data:null);
	if(!this.callBack)
		return this.doParse();
}
$_.__.XMLHttp.prototype.onStateChange = function(){
	if (this.request.readyState == 4) {
        if (!parseInt(this.request.status) || this.request.status == 200) {
        	this.doParse();
        } else {
        	if(typeof this.onError[this.request.status] == 'function'){
				this.callBack(this.onError[this.request.status](this.URL))
			}
			return this.callBack(false);
        }
    }
}
$_.__.XMLHttp.prototype.doParse = function(){
	var parsed = null;
	if (this.request.status && this.request.status != 200) {
		if(typeof this.onError[this.request.status] == 'function'){
			this.onError[this.request.status](this.URL)
		} else return false;
	}else{
		var ct = this.request.getResponseHeader('Content-type');
		// Do something regarding to content-type
	
		switch(ct){
			case 'application/x-javascript':
			case 'text/javascript':
			case 'application/ecmascript':
			case 'text/ecmascript':
				try{
					// Detect JSON or JS?
					var detectChar =this.request.responseText.match(/\s*(.{1})/);
					var parsed = /[0-9{(['"]/.test(detectChar) ? eval('('+this.request.responseText + ')'): this.request.responseText;
				}catch(e){
					if(e.name == 'SyntaxError'){
						$_.raise('Transfer','Got syntax error in '+this.URL)
					}
					else
						$_.raise('Transfer','Unknown error while parsing remote JS '+this.URL);
				}
			break;
			default:
				parsed = this.request.responseText;	
		}
		
		this.parsed = parsed?parsed:null;
		
		if((typeof this.callBack) == 'function'){ // Async mode
			this.callBack(this.parsed);
			this.callBack = null;
			return true;
		}else
			return this.parsed;						//Sync mode
	}
	
}
/*
$_.__.XMLHttp.prototype.parseHandlers = {
	/ Built-in parsers /
	'text' : function(req){
		return req.responseText;
	},
	'json' : function(req){
		var detectChar = req.responseText.match(/\s*(.{1})/);
		var parsed = /[0-9{(['"]/.test(detectChar) ? eval('('+this.request.responseText + ')'): this.request.responseText;
	}
}
*/

$_.__.XMLHttp.prototype.onError = {
	1403 : function(url){
		$_.raise('Transfer','Acess denied while accessing '+url)
	},
	1404 : function(url){
		$_.raise('Transfer','Not found while accessing '+url)
		return false;
	}
}


$_.$_STR.$_.XMLHttp = {
	accepts:function(str){
		return true;
	},
	init:function(str){
		str.GET = function(cbf){
			return new $_.__.XMLHttp(str).async(cbf||null).parse();
		}
		str.POST = function(data, cbf){
			return new $_.__.XMLHttp(str, data||null).async(cbf||null).parse();
		}
		return str;
	}
}

		
		$_.$_DOM.$_.XMLHttp = {
	accepts:function(el){
		return el.tagName && el.tagName.toLowerCase()=='form'
	},
	init:function(el){
		el.getData = function(){
			var el = this;
			var els = el.elements;
			var out = [];
			var radios = {};
			for(var i=els.length-1;i>=0;i--){
				var el = $_(els[i])
				if(el.name && el.type !='file' && el.type !='radio'){
					var val = el.VAL()
					if(val !== false) out[out.length] = encodeURIComponent(el.name)+'='+encodeURIComponent(val);
				}else if( el.type =='radio'){
					// remember only one selected radio
					if(!radios[el.name]){
						if(el.checked){
							radios[el.name] = 1;
							out[out.length] = encodeURIComponent(el.name)+'='+encodeURIComponent(el.VAL());
						}
					}
				}	 
			}
			return out.join('&');
		}
		el.GET = function(cbf){
			var J = new $_.__.XMLHttp(el.action, el.getData());
			J.requestMethod = 'GET'
			if(cbf) J.async(cbf)
			return J.parse();
		},
		el.POST = function(cbf){
			var J = new $_.__.XMLHttp(el.action, el.getData());
			if(cbf) J.async(cbf)
			return J.parse();
		},	
		el.SEND = function(cbf){
			el[(el.method.toLowerCase() == 'post') ? 'POST':'GET'](cbf)
		}	
	}
}
$_.$_DOM.$_.valueSetGet = {
	accepts : function(obj){
		return (['INPUT','SELECT','TEXTAREA']).contains(obj.tagName)
	},
	init:function(obj){
		obj.VAL = function(arg, fireEvt){
			if(!arg){
				switch(obj.tagName){
					case 'SELECT':
						return obj.options[obj.selectedIndex].value || obj.options[obj.selectedIndex].text
					break;
					case 'INPUT':
						if(obj.type.toLowerCase() === 'radio' && obj.form &&obj.name){
							var arr = obj.form[obj.name].length ? obj.form[obj.name] : [obj.form[obj.name]]
							for(var i=0; i<arr.length; i++){
								if(arr[i].checked)
									return arr[i].value
							}
							return false;
						}
						if(obj.type.toLowerCase() == 'checkbox' && !obj.checked) return false;
						
					default:
						return obj.value
				}
			}else{
				switch(obj.tagName){
					case 'SELECT':
						for(var i = obj.options.length-1; i>=0; i--){
							if(obj.options[i].value == arg){
								obj.options[i].selected = true;
								break;
							}
						}
					break;
					default:
						return obj.value = arg
				}
			}
			if(fireEvt){
				if(!$_.ie){
					var e = document.createEvent('HTMLEvents');
					e.initEvent('change', false, false);
					obj.dispatchEvent(e);
				}else{
					obj.fireEvent('onchange')
				}
			}
		}
		obj.encode2URI = function(f){
			var val = obj.VAL();
			return ((obj.name||f)&&(val!==false)) ? encodeURIComponent(obj.name)+'='+encodeURIComponent(val):false;
		}
		obj.CRC32 = function(){
			var val = obj.encode2URI(1);
			return (val!==false) ? $_(val).CRC32():false;
		}
	}
}

	
$_.$_DOM.$_$_.valueSetGet = {
	init:function(obj){
		obj.VAL = function(arg){
			var l = obj.length;
			if(!arg){
				var out = [];
				var bl = {};
				for(var i=0; i<l;i++){
					if(obj[i].type.toLowerCase() !== 'radio'){
						var v = obj[i].VAL()
						if(v !== false) out[out.length] = v;
					}else{
						if(!bl[obj.name]){
							bl[obj.name] = true;
							out[out.length] = obj[i].VAL()
						}
					}	
				}
				return $_(out);	
			}else{
				for(var i=0; i<l;i++){
					if(obj[i].type.toLowerCase() !== 'radio'){
					}else{
						if(obj[i].value == arg)
							obj[i].checked = true;
					}
				}
			}
		}
		obj.encode2URI = function(){
			var l = obj.length;
			var out = [];var bl = {};
			for(var i=0; i<l;i++){
				var val = false;
				if(obj[i].type.toLowerCase() !== 'radio')
					val = obj[i].encode2URI()
				else{
					if(!bl[obj.name]){
						bl[obj.name] = true;
						val = obj[i].encode2URI()
					}
				}	
				if(val !== false) out[out.length] = obj[i].encode2URI();
			}
			return out.join('&')
		}
		obj.CRC32 = function(){
			var v = obj.encode2URI();
			return (v!==false) ? $_(v).CRC32():false;
		}
	}
}
	 
$_.$_DOM.$_.All = {
	init : function(obj){
		obj.setLoading = function(){}

		obj.$_T = function(name){
			var ret = [];
			for(var i=0; i<arguments.length;i++)
				ret.push($_.copy(obj.getElementsByTagName(arguments[i])))
			return ret[1] ? ret:ret[0];
		}
	}
}

		
		
		
		if(!$_.ie){
$_.$_DOM.$_.CSS = {
	init : function(obj){
		obj.CSS = function(o){
			if(typeof o != 'string'){
				for(var i in o)
					obj.style[i] = o[i];
				return obj;
			}else{
				return obj.style[o];
			}
		}
		obj.CSSSave = function(o){
			obj.$_style = {};
			for(var i in o){
				obj.$_style[i] = o[i];
			}
			return obj;
		}
		obj.CSSLoad = function(){
			for(var i in obj.$_style){
				obj.style[i] = obj.$_style[i];
			}
			return obj;
		}
	}
}

}else{

$_.$_DOM.$_.CSS = {

	init : function(obj){
		obj.CSS = function(o){
			if(typeof o != 'string'){
				for(var i in o){
					
					if('opacity' == i){
						if(isNaN(o[i])) continue;
						var oAlpha = obj.filters['DXImageTransform.Microsoft.alpha'] || obj.filters.alpha || 0;
						if (oAlpha){ 
							oAlpha.opacity = o[i]*100;
						}else{ 
							obj.style.filter = "progid:DXImageTransform.Microsoft.Alpha(opacity="+o[i]*100+")";
						//alert( obj.style.filter)
						}
						obj.style.zoom = obj.style.zoom || 1;
					}else{
						obj.style[i] = o[i];
					}
				}
				return obj;
			}else{
				if(o != 'opacity')
					return obj.style[o];
				var m = obj.style.filter.match(/opacity=([0-9]+)/);
				if(m){
					return m[1]/100;
				}
			}
		}
		obj.CSSSave = function(o){
			obj.$_style = {};
			for(var i in o){
				obj.$_style[i] = o[i];
			}
			return obj;
		}
		obj.CSSLoad = function(){
			for(var i in obj.$_style){
				obj.style[i] = obj.$_style[i];
			}
			return obj;
		}
	}
}



}
$_.$_DOM.$_$_.CSS = {
	init : function(obj){
		obj.CSS = function(o){
			for(var k=obj.length-1; k>=0; k--){
				obj[k].CSS(o);
			}
			return obj;
		}
	}
}



		
		/**
 * A class to parse color values
 * @author Stoyan Stefanov <sstoo@gmail.com>
 * @link   http://www.phpied.com/rgb-color-parser-in-javascript/
 * @license Use it if you like it
 */
function RGBColor(color_string)
{
    this.ok = false;

    // strip any leading #
    if (color_string.charAt(0) == '#') { // remove # if any
        color_string = color_string.substr(1,6);
    }

    color_string = color_string.replace(/ /g,'');
    color_string = color_string.toLowerCase();

    // before getting into regexps, try simple matches
    // and overwrite the input
    var simple_colors = {
        aliceblue: 'f0f8ff',
        antiquewhite: 'faebd7',
        aqua: '00ffff',
        aquamarine: '7fffd4',
        azure: 'f0ffff',
        beige: 'f5f5dc',
        bisque: 'ffe4c4',
        black: '000000',
        blanchedalmond: 'ffebcd',
        blue: '0000ff',
        blueviolet: '8a2be2',
        brown: 'a52a2a',
        burlywood: 'deb887',
        cadetblue: '5f9ea0',
        chartreuse: '7fff00',
        chocolate: 'd2691e',
        coral: 'ff7f50',
        cornflowerblue: '6495ed',
        cornsilk: 'fff8dc',
        crimson: 'dc143c',
        cyan: '00ffff',
        darkblue: '00008b',
        darkcyan: '008b8b',
        darkgoldenrod: 'b8860b',
        darkgray: 'a9a9a9',
        darkgreen: '006400',
        darkkhaki: 'bdb76b',
        darkmagenta: '8b008b',
        darkolivegreen: '556b2f',
        darkorange: 'ff8c00',
        darkorchid: '9932cc',
        darkred: '8b0000',
        darksalmon: 'e9967a',
        darkseagreen: '8fbc8f',
        darkslateblue: '483d8b',
        darkslategray: '2f4f4f',
        darkturquoise: '00ced1',
        darkviolet: '9400d3',
        deeppink: 'ff1493',
        deepskyblue: '00bfff',
        dimgray: '696969',
        dodgerblue: '1e90ff',
        feldspar: 'd19275',
        firebrick: 'b22222',
        floralwhite: 'fffaf0',
        forestgreen: '228b22',
        fuchsia: 'ff00ff',
        gainsboro: 'dcdcdc',
        ghostwhite: 'f8f8ff',
        gold: 'ffd700',
        goldenrod: 'daa520',
        gray: '808080',
        green: '008000',
        greenyellow: 'adff2f',
        honeydew: 'f0fff0',
        hotpink: 'ff69b4',
        indianred : 'cd5c5c',
        indigo : '4b0082',
        ivory: 'fffff0',
        khaki: 'f0e68c',
        lavender: 'e6e6fa',
        lavenderblush: 'fff0f5',
        lawngreen: '7cfc00',
        lemonchiffon: 'fffacd',
        lightblue: 'add8e6',
        lightcoral: 'f08080',
        lightcyan: 'e0ffff',
        lightgoldenrodyellow: 'fafad2',
        lightgrey: 'd3d3d3',
        lightgreen: '90ee90',
        lightpink: 'ffb6c1',
        lightsalmon: 'ffa07a',
        lightseagreen: '20b2aa',
        lightskyblue: '87cefa',
        lightslateblue: '8470ff',
        lightslategray: '778899',
        lightsteelblue: 'b0c4de',
        lightyellow: 'ffffe0',
        lime: '00ff00',
        limegreen: '32cd32',
        linen: 'faf0e6',
        magenta: 'ff00ff',
        maroon: '800000',
        mediumaquamarine: '66cdaa',
        mediumblue: '0000cd',
        mediumorchid: 'ba55d3',
        mediumpurple: '9370d8',
        mediumseagreen: '3cb371',
        mediumslateblue: '7b68ee',
        mediumspringgreen: '00fa9a',
        mediumturquoise: '48d1cc',
        mediumvioletred: 'c71585',
        midnightblue: '191970',
        mintcream: 'f5fffa',
        mistyrose: 'ffe4e1',
        moccasin: 'ffe4b5',
        navajowhite: 'ffdead',
        navy: '000080',
        oldlace: 'fdf5e6',
        olive: '808000',
        olivedrab: '6b8e23',
        orange: 'ffa500',
        orangered: 'ff4500',
        orchid: 'da70d6',
        palegoldenrod: 'eee8aa',
        palegreen: '98fb98',
        paleturquoise: 'afeeee',
        palevioletred: 'd87093',
        papayawhip: 'ffefd5',
        peachpuff: 'ffdab9',
        peru: 'cd853f',
        pink: 'ffc0cb',
        plum: 'dda0dd',
        powderblue: 'b0e0e6',
        purple: '800080',
        red: 'ff0000',
        rosybrown: 'bc8f8f',
        royalblue: '4169e1',
        saddlebrown: '8b4513',
        salmon: 'fa8072',
        sandybrown: 'f4a460',
        seagreen: '2e8b57',
        seashell: 'fff5ee',
        sienna: 'a0522d',
        silver: 'c0c0c0',
        skyblue: '87ceeb',
        slateblue: '6a5acd',
        slategray: '708090',
        snow: 'fffafa',
        springgreen: '00ff7f',
        steelblue: '4682b4',
        tan: 'd2b48c',
        teal: '008080',
        thistle: 'd8bfd8',
        tomato: 'ff6347',
        turquoise: '40e0d0',
        violet: 'ee82ee',
        violetred: 'd02090',
        wheat: 'f5deb3',
        white: 'ffffff',
        whitesmoke: 'f5f5f5',
        yellow: 'ffff00',
        yellowgreen: '9acd32'
    };
    for (var key in simple_colors) {
        if (color_string == key) {
            color_string = simple_colors[key];
        }
    }
    // emd of simple type-in colors

    // array of color definition objects
    var color_defs = [
        {
            re: /^rgb\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3})\)$/,
            example: ['rgb(123, 234, 45)', 'rgb(255,234,245)'],
            process: function (bits){
                return [
                    parseInt(bits[1]),
                    parseInt(bits[2]),
                    parseInt(bits[3])
                ];
            }
        },
        {
            re: /^(\w{2})(\w{2})(\w{2})$/,
            example: ['#00ff00', '336699'],
            process: function (bits){
                return [
                    parseInt(bits[1], 16),
                    parseInt(bits[2], 16),
                    parseInt(bits[3], 16)
                ];
            }
        },
        {
            re: /^(\w{1})(\w{1})(\w{1})$/,
            example: ['#fb0', 'f0f'],
            process: function (bits){
                return [
                    parseInt(bits[1] + bits[1], 16),
                    parseInt(bits[2] + bits[2], 16),
                    parseInt(bits[3] + bits[3], 16)
                ];
            }
        }
    ];

    // search through the definitions to find a match
    for (var i = 0; i < color_defs.length; i++) {
        var re = color_defs[i].re;
        var processor = color_defs[i].process;
        var bits = re.exec(color_string);
        if (bits) {
            channels = processor(bits);
            this.r = channels[0];
            this.g = channels[1];
            this.b = channels[2];
            this.ok = true;
        }

    }

    // validate/cleanup values
    this.r = (this.r < 0 || isNaN(this.r)) ? 0 : ((this.r > 255) ? 255 : this.r);
    this.g = (this.g < 0 || isNaN(this.g)) ? 0 : ((this.g > 255) ? 255 : this.g);
    this.b = (this.b < 0 || isNaN(this.b)) ? 0 : ((this.b > 255) ? 255 : this.b);

    // some getters
    this.toRGB = function () {
        return 'rgb(' + this.r + ', ' + this.g + ', ' + this.b + ')';
    }
    this.toHex = function () {
        var r = this.r.toString(16);
        var g = this.g.toString(16);
        var b = this.b.toString(16);
        if (r.length == 1) r = '0' + r;
        if (g.length == 1) g = '0' + g;
        if (b.length == 1) b = '0' + b;
        return '#' + r + g + b;
    }

}



		
		$_.__.Mutation = function(el){
	this.element = el;
	this.FPS = 30;
	this.duration = 0;
	this.framesCount = 0;
	this.movies = [];
	this.sequence = [];
}
$_.__.Mutation.prototype.set = function(from, to, frames){
	
	var movies = [];
	for(var i in from){
		var muta = new $_.__.Mutagen();
		if(from[i] == 'auto'){
			if(i == 'height') from[i] = this.element.offsetHeight + 'px'
		}
		if(muta['__'+i]){
			if(typeof to[i]!== 'undefined'){
				muta['__'+i](from[i], to[i], frames);
				movies.push(muta.movie);
			}
		}else{
			if(typeof to[i] !== 'undefined'){ 
				muta.__Number.apply(muta, [i, from[i], to[i], frames]);
				movies.push(muta.movie);
			}
		}
	}
	this.duration += frames*1000/this.FPS
	this.sequence.push(movies);
}
$_.__.Mutation.prototype.clear = function(){
	this.movies = [];
	this.sequence = [];
	this.duration = 0;
}

/* We shoud calculate intervals */

$_.__.Mutation.prototype.play = function(){
	if(this.paused || this.element.mutating) return;
	this.element.mutating = 1;
	
	var mv = [];
	
	for(var z =0; this.movies = this.sequence[z];z++){
	// For each sequence step
		
		var length = this.movies[0].length;
		for(var i=0; i< length; i++){
			// for each steps as parallel
			var ml = this.movies.length;
			var obj = {};
			for(var k=0; k< ml;k++){
				// foreach parallel property
				$_.extend(obj, this.movies[k][i])
			}
			mv[mv.length] = obj;
		}
	}
	var dur = mv.length/this.FPS;
	var intV = Math.ceil(dur*1000/mv.length) ;
	this.currentIndex = 0;

	this.intId = setInterval(function(mv,obj){
		return function(){
		if(!mv[obj.currentIndex]){
				obj.element.mutating = 0;
				clearInterval(obj.intId)
				obj.currentIndex = 0;
				if(obj.isCyclic) obj.play();
				if(obj.onPlay)obj.onPlay();
				return;
			}
			//console.log(mv[obj.currentIndex])
			obj.element.CSS(mv[obj.currentIndex])
			obj.currentIndex += 1;
		}
	}(mv,this), intV)
	
}

$_.__.Mutation.prototype._play = function(){
	if(this.paused || this.element.mutating) return;
	this.element.mutating = 1;
	this.funcs = [];
	var offset=0;
	var self = this;
	if(this.onPlay){setTimeout(function(){self.onPlay()}, Math.ceil(this.duration));}
	
	setTimeout(function(){self.element.mutating = 0}, Math.ceil(this.duration));
	var prevStyle = null;
	var interval = Math.ceil(1000/this.FPS);
	for(var z =0; this.movies = this.sequence[z];z++){
		var length = this.movies[0].length;
		for(var i=0; i< length; i++){
			var ml = this.movies.length;
			for(var k=0; k< ml;k++){
				var style = this.movies[k][i]
				//console.log(style);
				if(prevStyle != style){
					var func = function(obj, style){
						return function(){
							obj.CSS(style);
						}
					}(this.element, style);
					this.funcs.push(func)
					setTimeout(func, offset + interval*i);
				}
				var prevStyle = style;
			}
		}
		offset += interval*i;
	}
	if(this.isCyclic) setTimeout(function(){self.play()}, offset);
}
$_.__.Mutation.prototype.rewind = function(){
	if(this.paused || this.element.mutating) return;
	this.element.mutating = 1;
	var offset=0;
	var self = this;
	if(this.onRewind) setTimeout(function(){self.onRewind()}, Math.ceil(this.duration));
	setTimeout(function(){self.element.mutating = 0}, Math.ceil(this.duration));
	var interval = Math.ceil(this.duration/this.funcs.length);
	var l = this.funcs.length
	for(var z = 0;this.funcs[z];z++){
		setTimeout(this.funcs[z], interval*(l-z));
	}
}
$_.__.Mutation.prototype.pause = function(){
	this.paused = true;
}
$_.__.Mutation.prototype.stop = function(){
	clearInterval(this.intId );
	this.element.mutating = 0;
}
$_.__.Mutation.prototype.unpause = function(){
	this.paused = false;
}


$_.__.Mutagen = function(){
	this.movie = [];
}

$_.__.Mutagen.prototype.__backgroundColor = function(from, to, frames){
	this.__Color.apply(this, ['backgroundColor', from, to, frames]);
}
$_.__.Mutagen.prototype.__borderColor = function(from, to, frames){
	this.__Color.apply(this, ['borderColor', from, to, frames]);
}
$_.__.Mutagen.prototype.__color = function(from, to, frames){
	this.__Color.apply(this, ['color', from, to, frames]);
}

$_.__.Mutagen.prototype.__Color = function(prop, from, to, frames){
	var f = new RGBColor(from);
	var t =  new RGBColor(to);
	var res =  new RGBColor("red");

	var deltaR = (t.r-f.r)/frames;
	var deltaG = (t.g-f.g)/frames;
	var deltaB = (t.b-f.b)/frames;

	var r = f.r;
	var g = f.g;
	var b = f.b;
	var str = '';
	for(var i=0;i<frames;i++){
		r += deltaR;
		g += deltaG;
		b += deltaB;
		res.r = Math.round(r)
		res.g = Math.round(g)
		res.b = Math.round(b)
		var obj = {};
		obj[prop] =  res.toHex();
		this.movie.push(obj);
	}
}

$_.__.Mutagen.prototype.__eNumber = function(prop, from, to, frames){
	var pref = from.toString().replace(/[\-0-9]+/,'');
	to = parseFloat(to);
	from = parseFloat(from);
	var delta = (to-from)/frames;
	var h = from;
	for(var i=0;i<frames;i++){
		h += delta;
		var obj = {};
		obj[prop] = Math.round(h)+pref;
		this.movie.push(obj);
	}
}
$_.__.Mutagen.prototype.__Number = function(prop, from, to, frames){
	var pref = from.toString().replace(/[\-0-9]+/,'');
	to = parseFloat(to);
	from = parseFloat(from);
	var delta = (to-from)/frames;
	var h = from;
	
	var roundDelta = pref == 'px' ? 1:100;
	
	for(var i=0;i<frames;i++){
		h += delta;
		var obj = {};
		obj[prop] = Math.round(h*roundDelta)/roundDelta+pref;
		this.movie.push(obj);
	}
}

$_.$_DOM.$_.Mutation = {
	init:function(el){
		el.mutate = function(){
			el.mutation = new $_.__.Mutation(el);
			var l = arguments.length-1;
			if(typeof arguments[l] == 'function'){
				el.mutation.onPlay = function(f){return function(){f()}}(arguments[l])
				l --;
			}
			var frames = Math.round(arguments[l] / (l-1));
			for(var i=0; i<l-1;i++){
				var f = arguments[i];
				var t = arguments[i+1]
				el.mutation.set(f,t,frames);
			}
			el.mutation.play();
		}
		el.demutate = function(f){
			if(typeof f == 'function') el.mutation.onRewind = function(){f()};
			el.mutation.rewind();
		}
	}
}

$_.$_DOM.$_$_.Mutation = { //Mutating family
	init:function(el){
		el.mutate = function(){
			var l2 = el.length;
			var mutas = [];
			var l = arguments.length-1;
			
			if(typeof arguments[l] == 'function'){
				var onPlay = function(f){return function(){f()}}(arguments[l])
				l --;
			}
			var frames = Math.round(arguments[l] / (l-1));
			
			for(var k=0; k<l2; k++){
				el[k].mutation = new $_.__.Mutation(el[k]);
				for(var i=0; i<l-1;i++){
					var f = arguments[i];
					var t = arguments[i+1]
					el[k].mutation.set(f,t,frames);
				}
				if(k == (l2-1)) el[k].mutation.onPlay = onPlay
				mutas[k] = el[k].mutation;
			}
			for(var i=0; i<l2; i++){
				mutas[i].play();
			}
		}
		el.demutate = function(f){
			var l2 = el.length;
			for(var k=0; k<l2; k++){
				
				if(typeof f == 'function' && (k==l2-1)){
					el[k].mutation.onRewind = function(){f()}
				}
				el[k].mutation.rewind()
			}
			
		}
	}
}

		
		if(!$_.ie){
$_.$_DOM.$_.CSSCoords = {
	init : function(el){
		el.getXY = function(){
			if (el.parentNode === null || !el.offsetHeight) return {x:0, y:0};
			var parent = null;
			var pos = {x:0, y:0};
			var box;
			if ($_.gecko) { // gecko
				if( el.getBoundingClientRect){
					var box = el.getBoundingClientRect();
					var scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
					var scrollLeft = document.documentElement.scrollLeft || document.body.scrollLeft;
					return {x:(box.left + scrollLeft), y:(box.top + scrollTop)};
				}else{
					box = document.getBoxObjectFor(el);
					pos = {x:box.x, y:box.y};
				}
			}else { // safari/opera
				pos = {x:el.offsetLeft, y:el.offsetTop};
				parent = el.offsetParent;
				if (parent != el) {
					while (parent) {
						pos.x += parent.offsetLeft;
						pos.y += parent.offsetTop;
						parent = parent.offsetParent;
					}
				}
				if ($_.opera || ( $_.safari && el.style.position == 'absolute' )) {
					pos.y += document.body.offsetTop;
				}
			}
			if (el.parentNode) { parent = el.parentNode; }
			else { parent = null; }
			return pos;
			while (parent && parent.tagName != 'BODY' && parent.tagName != 'HTML') {
				pos.x -= parent.scrollLeft;
				parent = parent.parentNode || null;
			}
			return pos;
		}
	}
}

}else{

$_.$_DOM.$_.CSSCoords = {
	init : function(el){
		el.getXY = function() {
			if (el.parentNode === null || !el.offsetHeight) return {x:0, y:0};
			var box = el.getBoundingClientRect();
			var scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
			var scrollLeft = document.documentElement.scrollLeft || document.body.scrollLeft;
			return {x:(box.left + scrollLeft), y:(box.top + scrollTop)};
		}
	}
}



}
$_.$_DOM.$_$_.CSSCoords = {
	init : function(obj){
		obj.getXY = function(){
			var l = obj.length;
			var out = [];
			for(var k=0; k<l; k++){
				out[out.length] = obj[k].getXY();
			}
			return out;
		}
	}
}



		
		
$_.$_STR.$_.String = {
	init : function(str){
		str.truncate = function(len){
			return str.length > len ? str.substr(0,len)+'...' : str;
		}
		str.splitByWords = function(num){
			if(!num) num=20;
			return str.replace(/[a-z0-9\' ]+/ig, function(s){
				return s.length > 20 ? s.split('').join('<WBR>&shy;') : s;
			})
		}
		
		str.UTF8Encode = function(){
			string = str.replace(/\r\n/g,"\n");
			var utftext = "";
			for (var n = 0; n < string.length; n++) {
				var c = string.charCodeAt(n);
				if (c < 128) {
					utftext += String.fromCharCode(c);
				}
				else if((c > 127) && (c < 2048)) {
					utftext += String.fromCharCode((c >> 6) | 192);
					utftext += String.fromCharCode((c & 63) | 128);
				}
				else {
					utftext += String.fromCharCode((c >> 12) | 224);
					utftext += String.fromCharCode(((c >> 6) & 63) | 128);
					utftext += String.fromCharCode((c & 63) | 128);
				}
			}
			return utftext;
		}
		var crc = 0;
		str.CRC32 = function(){
			str = str.UTF8Encode();
			var table = "00000000 77073096 EE0E612C 990951BA 076DC419 706AF48F E963A535 9E6495A3 0EDB8832 79DCB8A4 E0D5E91E 97D2D988 09B64C2B 7EB17CBD E7B82D07 90BF1D91 1DB71064 6AB020F2 F3B97148 84BE41DE 1ADAD47D 6DDDE4EB F4D4B551 83D385C7 136C9856 646BA8C0 FD62F97A 8A65C9EC 14015C4F 63066CD9 FA0F3D63 8D080DF5 3B6E20C8 4C69105E D56041E4 A2677172 3C03E4D1 4B04D447 D20D85FD A50AB56B 35B5A8FA 42B2986C DBBBC9D6 ACBCF940 32D86CE3 45DF5C75 DCD60DCF ABD13D59 26D930AC 51DE003A C8D75180 BFD06116 21B4F4B5 56B3C423 CFBA9599 B8BDA50F 2802B89E 5F058808 C60CD9B2 B10BE924 2F6F7C87 58684C11 C1611DAB B6662D3D 76DC4190 01DB7106 98D220BC EFD5102A 71B18589 06B6B51F 9FBFE4A5 E8B8D433 7807C9A2 0F00F934 9609A88E E10E9818 7F6A0DBB 086D3D2D 91646C97 E6635C01 6B6B51F4 1C6C6162 856530D8 F262004E 6C0695ED 1B01A57B 8208F4C1 F50FC457 65B0D9C6 12B7E950 8BBEB8EA FCB9887C 62DD1DDF 15DA2D49 8CD37CF3 FBD44C65 4DB26158 3AB551CE A3BC0074 D4BB30E2 4ADFA541 3DD895D7 A4D1C46D D3D6F4FB 4369E96A 346ED9FC AD678846 DA60B8D0 44042D73 33031DE5 AA0A4C5F DD0D7CC9 5005713C 270241AA BE0B1010 C90C2086 5768B525 206F85B3 B966D409 CE61E49F 5EDEF90E 29D9C998 B0D09822 C7D7A8B4 59B33D17 2EB40D81 B7BD5C3B C0BA6CAD EDB88320 9ABFB3B6 03B6E20C 74B1D29A EAD54739 9DD277AF 04DB2615 73DC1683 E3630B12 94643B84 0D6D6A3E 7A6A5AA8 E40ECF0B 9309FF9D 0A00AE27 7D079EB1 F00F9344 8708A3D2 1E01F268 6906C2FE F762575D 806567CB 196C3671 6E6B06E7 FED41B76 89D32BE0 10DA7A5A 67DD4ACC F9B9DF6F 8EBEEFF9 17B7BE43 60B08ED5 D6D6A3E8 A1D1937E 38D8C2C4 4FDFF252 D1BB67F1 A6BC5767 3FB506DD 48B2364B D80D2BDA AF0A1B4C 36034AF6 41047A60 DF60EFC3 A867DF55 316E8EEF 4669BE79 CB61B38C BC66831A 256FD2A0 5268E236 CC0C7795 BB0B4703 220216B9 5505262F C5BA3BBE B2BD0B28 2BB45A92 5CB36A04 C2D7FFA7 B5D0CF31 2CD99E8B 5BDEAE1D 9B64C2B0 EC63F226 756AA39C 026D930A 9C0906A9 EB0E363F 72076785 05005713 95BF4A82 E2B87A14 7BB12BAE 0CB61B38 92D28E9B E5D5BE0D 7CDCEFB7 0BDBDF21 86D3D2D4 F1D4E242 68DDB3F8 1FDA836E 81BE16CD F6B9265B 6FB077E1 18B74777 88085AE6 FF0F6A70 66063BCA 11010B5C 8F659EFF F862AE69 616BFFD3 166CCF45 A00AE278 D70DD2EE 4E048354 3903B3C2 A7672661 D06016F7 4969474D 3E6E77DB AED16A4A D9D65ADC 40DF0B66 37D83BF0 A9BCAE53 DEBB9EC5 47B2CF7F 30B5FFE9 BDBDF21C CABAC28A 53B39330 24B4A3A6 BAD03605 CDD70693 54DE5729 23D967BF B3667A2E C4614AB8 5D681B02 2A6F2B94 B40BBE37 C30C8EA1 5A05DF1B 2D02EF8D";
			//if (typeof(str.crc) == "undefined") {str.crc = 0; }
			var x = 0;
			var y = 0;
			crc = 0;
			crc = crc ^ (-1);
			var iTop = str.length;
			for( var i = 0; i < iTop; i++ ) {
				
				y = ( crc ^ str.charCodeAt( i ) ) & 0xFF;
				x = "0x" + table.substr( y * 9, 8 );
				crc = ( crc >>> 8 ) ^ x;
			}
			return crc ^ (-1);
		}
		
		return str;
	}
}
$_.$_STR.$_$_.String = {
	init : function(obj){
		obj.truncate = function(o){
			var out = [];
			for(var k=obj.length-1; k>=0; k--){
				out[k] = obj[k].truncate(o);
			}
			return out;
		}
		obj.splitByWords = function(){
			var out = [];
			for(var k=obj.length-1; k>=0; k--){
				out[k] = obj[k].splitByWords();
			}
			return out;
		}
		obj.CRC32 = function(){
			var out = [];
			for(var k=obj.length-1; k>=0; k--){
				out[k] = obj[k].CRC32();
			}
			return out;
		}			
		return obj;	
	}
}

		
		$_.$_DOM.$_.Collection = $_.$_STR.$_.Collection = $_.$_OBJ.$_.Collection = $_.$_NUMBER.$_.Collection = {
	init : function(el){
		el.apply = function(cbf){
			cbf(el)
		}
		return el;
	}
}
Array.prototype.$_ = function(){return this;}
Array.prototype.apply = function(cbf){
	var l = this.length;
	for(var i=0;i<l;i++)
		cbf(this[i])
	return this;
}

		
$_.$_STR.$_.DOMAccess  ={
	accepts: function(arg){
		var c = arg.charAt(0);
		return (c=='#' || c == '.' || c == '~' || c=='@' || c=='>');
	},
	init: function(str){

		var parts = str.match(/[#.~@][a-z0-9_\-]+/ig)

		if(parts.length >= 2){
			var arrs = [];
			for(var i=0; i<parts.length; i++){
				arrs[arrs.length]= $_(parts[i]);
			}
			return $_(arrs[0].intersects.apply(arrs[0], arrs.slice(1)));
		}
		
		switch(str.charAt(0)){
			case '#':
				var el = !$_.__[str] ? $_(document.getElementById(str.substr(1))):$_.__[str];
			break;

			case '@':
				var el = $_(document.getElementsByName(str.substr(1)))
			break
			case '~':
				var s = str.substr(1).toLowerCase();
				switch(s){
					case 'checkbox':
					case 'radio':
					case 'password':
					case 'image':
					case 'text':
						var els = $_('~input');
						var el = [];
						for(var j=0;j<els.length;j++)
							if(els[j].type && els[j].type == s)
								el[el.length] = els[j]
						el = $_(el)		
					break;
					default:
						var el = $_(document.getElementsByTagName(s))
				}		
			break;
			case '.':
				var classElements = new Array();
				var els = document.getElementsByTagName('*');
				var elsLen = els.length;
				var pattern = new RegExp('(^|\\s)'+str.substr(1)+'(\\s|$)');
				for (i = 0, j = 0; i < elsLen; i++) {
					if ( pattern.test(els[i].className) ) {
						classElements[j] = els[i];
						j++;
					}
				}
				var el = $_(classElements);
			break;
		}
		
		return el;
	}
}
$_.$_STR.$_$_.DOMAccess  ={
	init: function(arr){
		var out = [];
		for(var i=0; i<arr.length; i++){
			out[out.length] = $_.$_STR.$_.DOMAccess.init(arr[i])
		}
		return out;
	}
}

$_.$_DOM.$_.DOMAccess = {
	init : function(el){
		el.$_ = function(str){
			if(arguments.length > 1){
				var all = []
				for(var i=0; i<arguments.length; i++){
					all.push(el.$_(arguments[i]));
				}
				return $_(all)
			}
			var parts = str.match(/[#.~@>][a-z0-9_\-\*]+/ig)
			if(parts.length >= 2){
				var arrs = [];
				for(var i=0; i<parts.length; i++){
					arrs[arrs.length]= $_(parts[i]);
				}
				return $_(arrs[0].intersects.apply(arrs[0], arrs.slice(1)));
			}
			
			switch(str.charAt(0)){
				
				case '~': //tagname
					var s = str.substr(1).toLowerCase();
					switch(s){
						case 'checkbox':
						case 'radio':
						case 'password':
						case 'image':
						case 'text':
							var els = el.$_('~input');
							var coll = [];
							for(var j=0;j<els.length;j++)
								if(els[j].type && els[j].type == s)
									coll[coll.length] = els[j]
							coll = $_(coll)		
						break;
						default:
							var coll = $_(el.getElementsByTagName(s))
					}	
				break;
				case '>':
					var coll = [];
					
					var els = el.childNodes;
					for(var i=els.length-1; i>=0; i--){
						if(els[i].tagName) coll.push(els[i])
					}
					coll.reverse()
				break;
				case '@': //name
					var coll = (function(searchName,node,tag) {
						var nameElements = new Array();
						if ( node == null )
								node = document;
						if ( tag == null )
							   tag = '*';
						var els = node.getElementsByTagName(tag);
						var elsLen = els.length;
					
						var pattern = new RegExp("(^|\\s)"+searchName+"(\\s|$)");
						for (i = 0, j = 0; i < elsLen; i++) {
							   if ( pattern.test(els[i].name) ) {
									nameElements[j] = els[i];
								   j++;
							  }
						}
						return nameElements;
					})(str.substr(1), el, null)
				break;
				case '.': //classname
					var coll = (function(searchClass,node,tag) {
						var classElements = new Array();
						if ( node == null )
								node = document;
						if ( tag == null )
							   tag = '*';
						var els = node.getElementsByTagName(tag);
						var elsLen = els.length;
					
						var pattern = new RegExp("(^|\\s)"+searchClass+"(\\s|$)");
						for (i = 0, j = 0; i < elsLen; i++) {
							   if ( pattern.test(els[i].className) ) {
									classElements[j] = els[i];
								   j++;
							  }
						}
						return classElements;
					})(str.substr(1), el, null)
				break;			
			}
			return $_(coll);
		}
		el.intersects = function(el2){
			for(var i=0; i<el2.length; i++)
				if(el == el2[i]) return el;
			return null
		}
	}
}
$_.$_DOM.$_$_.DOMAccess = {
	init : function(arr){
		arr.$_ = function(str){
			var out = [];
			for(var i=0; i<arr.length;i++){
				out[out.length] = arr[i].$_.apply(arr[i], arguments)
			}
			return $_(out);
		}
		arr.intersects = Array.prototype.intersects
	}
}

		
		$_.$_STR.$_.XOR = {
	char_escaped : "%00%01%02%03%04%05%06%07%08%09%0A%0B%0C%0D%0E%0F%10%11%12%13%14%15%16%17%18%19%1A%1B%1C%1D%1E%1F%20%21%22%23%24%25%26%27%28%29%2A%2B%2C%2D%2E%2F%30%31%32%33%34%35%36%37%38%39%3A%3B%3C%3D%3E%3F%40%41%42%43%44%45%46%47%48%49%4A%4B%4C%4D%4E%4F%50%51%52%53%54%55%56%57%58%59%5A%5B%5C%5D%5E%5F%60%61%62%63%64%65%66%67%68%69%6A%6B%6C%6D%6E%6F%70%71%72%73%74%75%76%77%78%79%7A%7B%7C%7D%7E%7F%80%81%82%83%84%85%86%87%88%89%8A%8B%8C%8D%8E%8F%90%91%92%93%94%95%96%97%98%99%9A%9B%9C%9D%9E%9F%A0%A1%A2%A3%A4%A5%A6%A7%A8%A9%AA%AB%AC%AD%AE%AF%B0%B1%B2%B3%B4%B5%B6%B7%B8%B9%BA%BB%BC%BD%BE%BF%C0%C1%C2%C3%C4%C5%C6%C7%C8%C9%CA%CB%CC%CD%CE%CF%D0%D1%D2%D3%D4%D5%D6%D7%D8%D9%DA%DB%DC%DD%DE%DF%E0%E1%E2%E3%E4%E5%E6%E7%E8%E9%EA%EB%EC%ED%EE%EF%F0%F1%F2%F3%F4%F5%F6%F7%F8%F9%FA%FB%FC%FD%FE%FF",
	char_all : unescape( this.char_escaped ),
	bound: function( min_val, value, max_val ){
	   if( value < min_val ) {
		  value = min_val;
	   }
	   if( value > max_val ) {
		  value = max_val;
	   }
	   return value;
	},
	aton: function( str, index ){  // Convert a character to a number.  The range is 0x00 to 0xFF inclusive.
	   // Revision 1.00, becd.
	   index = this.bound( 0, index, str.length-1 );
	   return this.char_all.indexOf( str.charAt( index ), 0 );
	},
	
	ntoa: function ( index ){
		index = this.bound( 0, index, 0xFF );
		return this.char_all.charAt( index );
	},
	flag : false,
	init : function(str){
		
		if(!this.flag){
			flag = 1;
			this.char_all = unescape( this.char_escaped );
		}
		str.XOREncode = function(ptr){
			return escape(str.XOR(ptr))
		}
		str.XORDecode = function(ptr){
			return $_(str).XOR(ptr, 1)
		}		
		str.XOR = function(ptr, safe){
			if(safe) str = unescape(str);
			var ii = 0;    // Data index.
			var jj = 0;    // Pattern index.
			var result = "";

			if( ptr == null || ptr == "" || ptr.length <= 0 ) {
				ptr = "simple_xor_pattern";
			}

			for( ii = 0; ii < str.length; ii++ ) {
				if( jj >= ptr.length ){ 
			 		jj = 0;
				}
				result += $_.$_STR.$_.XOR.ntoa( $_.$_STR.$_.XOR.aton( str, ii ) ^ $_.$_STR.$_.XOR.aton( ptr, jj++ ));
			}
			return result;	
		}
		return str;
		
	}
}







		
		$_.$_DOM.$_.Events = {
	__ : [],
	__mkEvt : function(obj, type, fn, ie){
		var evt = $_({
			id : Math.round(Math.random() * 10000),
			type : type, 
			handler : fn,
			element : obj
		})
		if(!ie) obj.$__event[evt.id] = evt;
		return evt;
	},
	__rmEvt : function(obj, id){
		if(obj.$__event[id]){
			delete(obj.$__event[id]);
			return true;
		}else
			return false;
	},	
	
	init : function(str){
		var mkf = function(f){return function(e){
			if(!e) e = event;
			if(typeof(e.target) == 'undefined'){
				e.target = e.srcElement;
			}
			return f(e);
		}}
		
	}
}
$_.$_DOM.$_$_.Events = {
	init : function(str){
		str.attach = function(type, fn){
			// Attach same event to multiple instances
			var out = [];
			for(var k=str.length-1;k>=0;k--){
				out.push(str[k].attach(type, fn))
			}
			return out;	
		}
		
		str.detach = function(){
			// Detach specified events
			var evts = $_.copy(arguments);
			str.apply(function(el){ el.detach(evts)})
			return str;	
		}
		
		var mkf = function(f){return function(e){
			if(!e) e = event;
			console.log(e.target)
			if(typeof(e.target) == 'undefined'){
				e.target = e.srcElement;
			}
			return f(e);
		}}
	}
}

$_.$_DOM.$_.EventsW3C = {
	accepts : function(){
		return !$_.ie;
	},

	init : function(el){
		el.$__event = el.$__event || {};
		el.attach = function(type, fn){
			// Attach event
			el.addEventListener(type, fn, false);
			return $_.$_DOM.$_.Events.__mkEvt(el,type,fn);
		}
		el.detach = function(ev){
			
			switch(typeof ev ){
				case 'string':
					for(var i in el.$__event){
						if(el.$__event[i].type && el.$__event[i].type== ev)
							el.detach(el.$__event[i])
					}
				break;	
				case 'undefined':
					
					for(var i in el.$__event){
						if(el.$__event[i].type)
							el.detach(el.$__event[i])
					}
					return true;
				break;
				default:
					if(typeof ev.length != 'undefined'){
						
						if(!ev.length){
							return el.detach()
						}else if(ev instanceof String)
							return el.detach(ev+'')
							
						return ev.apply(function(ev){el.detach(ev)});
					}else
						el.removeEventListener(ev.type, ev.handler, false);
			}
			return $_.$_DOM.$_.Events.__rmEvt(el, ev.id);
		}
	}
}
$_.$_DOM.$_.EventsIE = {
	accepts : function(){
		return $_.ie;
	},
	__attachW3C : function(e){
		e.stopPropagation = function(){
			e.cancelBubble = 1;
		}
		e.preventDefault = function(){
	        e.returnValue = false
		}
		e.target = e.srcElement;
		return e;
	},
	__run : function(el, type){
		var l = el.$__event.queue[type].length;
		for(var i =0; i<l; i++){
			var id = el.$__event.queue[type][i]
			if(el.$__event[type][id]){
				el.$__event[type][id].element.__dorun = el.$__event[type][id].handler;
				el.$__event[type][id].element.__dorun($_.$_DOM.$_.EventsIE.__attachW3C(event))
				el.$__event[type][id].element.__dorun = null;
			}
		}
	},
	init : function(el){
		el.$__event = el.$__event || {queue:{}};
		el.attach = function(type, fn){
			if(typeof el.$__event[type] == 'undefined'){
				el.attachEvent("on" + type, 
					function(el, type){
						return function(){$_.$_DOM.$_.EventsIE.__run(el, type)}
					}(el, type)
				);
				el.$__event[type] =  {};
			}
			el.$__event.queue[type] = el.$__event.queue[type] || [];
			var evt = $_.$_DOM.$_.Events.__mkEvt(el,type,fn,true);
			el.$__event[type][evt.id] = evt;
			el.$__event.queue[type].push(evt.id);
			return evt;
		}
		el.detach = function(ev){
			switch(typeof ev){
				case 'string':
					if(el.$__event && el.$__event.queue && el.$__event.queue[ev])
						el.$__event.queue[ev].length = 0;
				break;
				case 'undefined':
					for(var i in el.$__event.queue){
						if(typeof i == 'string' && el.$__event.queue[i].length)	
							el.$__event.queue[i].length = 0;
					}
				break;		
				default:
					if(typeof ev.length != 'undefined'){
						if(!ev.length){
							return el.detach()
						}else if(ev instanceof String)
							return el.detach(ev+'')
							
						return ev.apply(function(ev){el.detach(ev)});
					}
					for(var i=el.$__event.queue[ev.type].length-1; i<=0; i--){
						if(el.$__event.queue[ev.type][i] == ev.id){
							var index = i; 
							break;
						}
					}
					if(typeof index !== 'undefined'){
						el.$__event.queue[ev.type].splice(index,1);
						delete(el.$__event[ev.type][ev.id]);
					}
					el.$__event.queue[ev.type][ev.id] = ev;
			}
		}
	}
}





$_.$_DOM.$_.EventsEmulate = {
	EvTypes : ['click|mouseup|mousedown|mouseover|mouseout', 'keyup|keydown|keypress', 'change|focus|blur'],

		
	init : function(obj){
		obj.emulate = function(type, event){
			
			if(!$_.ie){
				var evType = ['MouseEvents', 'KeyEvents', 'HTMLEvents'][$_.$_DOM.$_.EventsEmulate.EvTypes.search(function(el){
					return el.search(type) != -1
				})]
				if(!evType) return false;
				var e = document.createEvent(evType);
				e.initEvent(type, true, true);
				obj.dispatchEvent(e);
			}else{
				obj.fireEvent('on'+type)
			}
		}
	}
}
$_.$_DOM.$_$_.EventsEmulate = {
	init : function(obj){
		obj.emulate = function(type, event){
			for(var k=0;k<obj.length;k++){
				obj[k].emulate(type, event)
			}
		}
	}
}



$_.$_OBJ.$_.Events = {
	accepts : function(el){
		return el.element && el.type && el.id;
	},
	init : function(el){
		el.detach = function(){
			el.element.detach(el)
		}
	}
}

$_.$_OBJ.$_$_.Events = {
	init : function(el){
		el.detach = function(){
			for(var i=0;i<el.length; i++)
				el[i].element.detach(el);
		}
	}
}

		
		
Visio = {
	$_ : {}
}




Visio.$_.__ = {
	init : function(el, type){
		/* There is wonderful place to create repo in element */
		
		
		el.$_.visio.__conf = el.$_.visio.__conf || {
			id : el.id || 'visio'+Math.round(Math.random() * 10000),
			width : el.offsetWidth,
			height : el.offsetHeight
		};
		
		el.$_.visio.conf = function(){
			if(typeof arguments[0] == 'string'){
				var key = arguments[0];
			
				if(typeof el.$_.visio.__conf[key] == 'function' && typeof arguments[1] == 'undefined'){
					return el.$_.visio.__conf[key]();
				}else
					return el.$_.visio.__conf[key]
			}else{
				var conf = arguments[0];
				var key = null;
				
				for(key in conf){
					
						if(typeof el.$_.visio.__conf['_'+key] != 'function')
							el.$_.visio.__conf[key] = conf[key];
						else{
							if(typeof el.$_.visio.__conf[key] != 'function' || typeof conf[key]=='function'){
								el.$_.visio.__conf[key] = el.$_.visio.__conf['_'+key](conf[key]);	
							}else
								el.$_.visio.__conf['_'+key](conf[key]);	
						}
				}	
				return el;
			}
		}
		
		el.$_.visio[type] = {};
	},
	
	postprocess : function(el){

		if(el.$_.visio.conf('onclick', true)){
			el.onclick = el.$_.visio.conf('onclick', true);
		}
		if(el.$_.visio.conf('onmousemove', true)){
			el.onmousemove = el.$_.visio.conf('onmousemove', true);
		}
		if(el.$_.visio.conf('onmouseover', true)){
			el.onmouseover = el.$_.visio.conf('onmouseover', true);
		}
		if(el.$_.visio.conf('onmouseout', true)){
			el.onmouseout = el.$_.visio.conf('onmouseout', true);
		}						
		return el;
	}
}

Visio.$_.bubble = {
	init : function(el){
	/*	Visio.$_.__.init(el, 'bubble'); */

		el = Visio.$_.image.init(el)

		el.onmouseover = this.__movH(el)
		el.onmouseout = this.__mouH(el)
		el.$_.visio.conf({
			newHeight : function(){return el.$_.visio.conf('height')*this.zoom},
			newWidth : function(){return el.$_.visio.conf('width')*this.zoom},
			zoom : 1.5,
			speed : 10
		})
		return el;
	},
	
	postprocess : function(el){
		return Visio.$_.__.postprocess(el)	
	},
	
	__movH : function(el){
		return function(){
			if(!el.$_.visio.conf('iWidth')){
				el.$_.visio.conf(
					{
						iWidth :el.offsetWidth,
						iHeight : el.offsetHeight
					}
				)
			}
			el.mutate(
				{height:el.$_.visio.conf('height')+'px', width:el.$_.visio.conf('width')+'px'}, 
				{height:el.$_.visio.conf('newHeight')+'px', width:el.$_.visio.conf('newWidth')+'px'},
				el.$_.visio.conf('speed')
			)
		}
	},
	__mouH : function(el){
		return function(){
			//el.$_.visio.conf({})
			el.mutation.stop()
			el.mutate(
				{height:el.offsetHeight+'px', width:el.offsetWidth+'px'},
				{height:el.$_.visio.conf('iHeight')+'px', width:el.$_.visio.conf('iWidth')+'px'}, 
				
				el.$_.visio.conf('speed')
			)
		}
	}
}






Visio.Factory = function(el, type, opts){
	if(Visio.$_[type]){
		el.$_.visio = {
			type : type,
			path : []
		}
		return Visio.$_[type].init(el);
	}
}

Visio.getType = function(el){
	var pattern = /visio-([a-z\-]+)/;
	var m = null;
	if(m = el.className.match(pattern) ){
		return m[1];
	}else{
		return false;
	}
}

$_.$_DOM.$_.Visio = {
	init : function(el){

		el.visio = function(vops){
			// Scan for visio targets
			var coll = el.getElementsByTagName('*'); var l = coll.length;
			
			var t = null;
			for (i = 0, j = 0; i < l; i++) {
				if ( t = Visio.getType(coll[i]) ) {
					if( Visio.Factory($_(coll[i]), t)){
						// We should now attach full path to top
						var p = coll[i];
						coll[i].$_.visio.path.push(t)
						while(el != p){
							p = p.parentNode;
							if(t = Visio.getType(p)){
								coll[i].$_.visio.path.push(t)
							}
						}
						
						coll[i].$_.visio.path.reverse()
						/* Apply cfg if match */
					
						if(vops[coll[i].$_.visio.path.join('/')]){
							// Matched
							var cfg = vops[coll[i].$_.visio.path.join('/')]
							coll[i].$_.visio.conf(cfg);
						}
						if(Visio.$_[Visio.getType(coll[i])].postprocess)
							Visio.$_[Visio.getType(coll[i])].postprocess(coll[i])
					}
				}
			}
		}
		
		el.vis = function(vops){
			m = el.className.match(/visio-([a-z\-]+)/);

			var vType = m ? m[1] : null ;
			el.visio(vops);
			return vType ? Visio.Factory(el, vType) : el;
		}
	}
}

		
Visio.$_.blinker = {
	init : function(el){
		Visio.$_.__.init(el, 'blinker');
		el.$_.visio.conf({
			speed : 10
		})
		el.changeHTML = function(html){
			if(this.mutation)this.mutation.stop();
			
			this.mutate(
				{opacity:(parseFloat(el.CSS('opacity')))},
				{opacity:0},
				el.$_.visio.conf('speed'),
				function(){
					el.innerHTML = html;
					el.mutate(
						{opacity:0},
						{opacity:1},
						el.$_.visio.conf('speed')
					);
				}
			)
		};
		el.blinkDown  = function(func){
			func = func || function(){}
			this.mutate(
				{opacity:1},
				{opacity:0},
				el.$_.visio.conf('speed'),
				func
			)
		};
		el.blinkUp  = function(func){
			func = func || function(){}
			this.mutate(
				{opacity:0},
				{opacity:1},
				el.$_.visio.conf('speed'),
				func
			)
		}		
		
		return el;
	}
}



Visio.$_.hint = {
	init : function(el){
		Visio.$_.__.init(el, 'hint');
		el.changeHTML = function(html){
			el.innerHTML = html;
		},
		el.show  = function(){
			el.style.display = 'block';
		},
		el.hide  = function(){
			el.style.display = 'none';
		},
		el.moveTo = function(e){
			e = e || event;
			var x = e.clientX + (window.pageXOffset || document.documentElement.scrollLeft || document.body.scrollLeft || 0);
			var y = e.clientY + (window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0);
			el.style.top = y+5+'px'
			el.style.left = x+5+'px'
		}		
		
		return el;
	}
}


Visio.$_.block = {
	postprocess : function(el){
		return Visio.$_.__.postprocess(el)	
	},
	init : function(el){
		Visio.$_.__.init(el, 'block');
		return el;
	}
	
}

Visio.$_.image = {
	init : function(el){
		Visio.$_.__.init(el, 'image');
		el.$_.visio.conf({
			
			width : function(){return el.offsetWidth},
			height : function(){return el.offsetHeight},	
			
			aspectRatio : el.offsetHeight ? (el.offsetWidth/el.offsetHeight) : 1,
				
			_width : function(w){el._width(w)},
			_height : function(w){el._height(w)}

		})
		
		el._width = function(w){
			this.style.width = parseInt(w)+'px';
			this.style.height = w*el.$_.visio.conf('aspectRatio')+'px';
			return w;
		}
		el._height = function(h){
			this.style.height = h+'px';
			this.style.width = h*el.$_.visio.conf('aspectRatio')+'px';
			return h;
		}		
		return el;
	}
}

Visio.$_.frame = {
	init : function(el){
		Visio.$_.__.init(el, 'frame');
		
		el.$_.visio.conf({
			speed : 10
		})
		
		el.$_.visio.blockXIndex = 0;
		el.fc = $_(el.getElementsByTagName('*')[0])
		
		
		var coll= el.fc.$_('>*')
		
		el.currentFrame = coll[el.$_.visio.blockXIndex]
		/* hang ids */
		for(var i=0; i<coll.length; i++){
			coll[i]._uid = i;
		}
		
		
		el.moveLeft = this.__moveLeft(el);
		el.moveRight = this.__moveRight(el);
		el.moveTo = this.__moveTo(el);		
		return el;
	},
	
	postprocess : function(el){
		return Visio.$_.__.postprocess(el)	
	},
	
	__moveLeft : function(el){return function(f){
			if(el.$_.visio.moving) return;
			f = function(f,el){
				return function(){
					el.$_.visio.moving = false;
					if(f){
						f()
					}
						
				}
			}(f,el)
			
				
			
			if(typeof el.fc.$_('>*')[el.$_.visio.blockXIndex+1] == 'undefined'){
				// Last left
				var cx = el.fc.$_('>*');
				el.$_.visio.blockXIndex = cx.length-2; 
				
				// mov right
				el.fc.style.left = parseInt(el.fc.style.left) + cx[0].offsetWidth + 'px'
				el.fc.appendChild(cx[0])
			}
			el.$_.visio.moving = true;
			
			el.currentFrame = el.fc.$_('>*')[el.$_.visio.blockXIndex+1]
			var delta = el.currentFrame.offsetWidth
			el.$_.visio.blockXIndex ++;
			// Moves frames content left to it's margin 
			el.fc.mutate(
				{left:el.fc.style.left || "0px"},
				{left:parseInt(el.fc.style.left || "0") - delta  + 'px'},
				el.$_.visio.conf('speed'),
				f
			)
		}
	},
	__moveRight : function(el){return function(f){
		if(el.$_.visio.moving) return;
			f = function(f,el){
				return function(){
					el.$_.visio.moving = false;
					if(f){
						f()
					}
						
				}
			}(f,el)
		
		
			// Moves frames content left to it's margin 
			f = f || function(){}
			if(typeof el.fc.$_('>*')[el.$_.visio.blockXIndex-1] == 'undefined'){
				// Last right
				el.$_.visio.blockXIndex = 1 
				var cx = el.fc.$_('>*');
				// mov right
				
				el.fc.style.left = -(cx[0].offsetWidth) + 'px'
				el.fc.insertBefore(cx[cx.length - 1], cx[0] )
				
			};
			el.$_.visio.moving = true;
			el.currentFrame = el.fc.$_('>*')[el.$_.visio.blockXIndex-1]
			var delta = el.currentFrame.offsetWidth
			
			el.$_.visio.blockXIndex --;
			el.fc.mutate(
				{left:el.fc.style.left || "0px"},
				{left:parseInt(el.fc.style.left || "0") + delta + 'px'},
				el.$_.visio.conf('speed'),
				f
			)
		}
	},	
	__moveTo : function(el){
		return function(uid,f){
			if(isNaN(uid)) uid = 0;
			if(uid == el.currentFrame._uid) return;
			// Move left until uid found
			el.moveLeft(function(uid,f){
					return function(){
						if(el.currentFrame._uid != uid){
							el.moveTo(uid,f)
						}else{
							if(f)f()
						}
					}
				}(uid,f))
				return true;
		}
	}
	
}


		
