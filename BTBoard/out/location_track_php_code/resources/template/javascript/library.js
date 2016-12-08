var net=new Object();

net.READY_STATE_UNINITIALIZED=0;
net.READY_STATE_LOADING=1;
net.READY_STATE_LOADED=2;
net.READY_STATE_INTERACTIVE=3;
net.READY_STATE_COMPLETE=4;

/*--- content loader object for cross-browser requests ---*/
net.ContentLoader = function(url,onload,onerror,method,params,contentType){
	this.req = null;
	this.onload=onload;
	this.onerror=(onerror) ? onerror : this.defaultError;
	this.loadXMLDoc(url,method,params,contentType);
}

net.ContentLoader.prototype = {
loadXMLDoc:function(url,method,params,contentType) {

		if (!method) {
			method = "GET";
		}

		if (!contentType && method == "POST") {
			contentType = 'application/x-www-form-urlencoded';
		}

		if (window.XMLHttpRequest) {
			this.req = new XMLHttpRequest();
		} else if (window.ActiveXObject) {
			this.req = new ActiveXObject("Microsoft.XMLHTTP");
		}

		if (this.req) {
			try {
				var loader = this;
				this.req.onreadystatechange = function() {
					loader.onReadyState.call(loader);
				}
				this.req.open(method, url, true);
				if (contentType) {
					this.req.setRequestHeader('Content-Type', contentType);
				}
				this.req.send(params);
			} catch (err) {
				this.onerror.call(this);
			}
		}

	},

	onReadyState:function() {

		var req = this.req;
		var ready = req.readyState;

		if (ready > 1) {

			if (ready == net.READY_STATE_COMPLETE) {
				var httpStatus = req.status;
				if (httpStatus == 200 || httpStatus == 0) {
					//alert(this.req.responseText);
					this.onload.call(this);
				}else{
					this.onerror.call(this);
				}
			}
		}

	},

	defaultError:function() {

		alert("error fetching data!"
			+ "\n\nreadyState:" + this.req.readyState
			+ "\nstatus: " + this.req.status
			+ "\nheaders: " + this.req.getAllResponseHeaders()
			+ "\ndata:\n" + this.req.responseText
		);

	}

}

/*

var geturl = baseurl + "&id=" + encodeURI(aaaaa) + "&nocache=" + new Date().getTime();
var loader = new net.ContentLoader(geturl, requestprocess);

function requestprocess() {
	eval(this.req.responseText);
}

*/

//Get element by id shorthand
function $(element) {
	var elementobj = document.getElementById(element);
	return elementobj;
}

//Get last index in array
function getlastindex(array) {
	//Get last index
	var lastindex = undefined;
	for (var i in array) {
		lastindex = i;
	}
	return lastindex;
}

//List variables in array
function listelement(ele) {
	var data = "";
	for (i in ele) {
		data += i + " => " + ele[i] + "\n";
	}
	alert(data);
}

//In array
function inarray(value, array) {
	for (var i=0; i < array.length; i++) {
		if (array[i] == value) {
			return true;
		}
	}
	return false

}

//Init 'Unobtrusive' JavaScript
//addEvent(window, "load", function() {
//});

//$("example").onclick = function() {

//Add onload event handler
//addEvent(window, "load", aaaa);
function addEvent(obj, evType, fn){
	if (obj.addEventListener){
		obj.addEventListener(evType, fn, false);
		return true;
	} else if (obj.attachEvent){
		var r = obj.attachEvent("on"+evType, fn);
		return r;
	} else {
		return false;
	}
}

//If is numberic
function isnumeric(number, incdec) {

	var validchar = "0123456789";
	validchar += (incdec) ? "." : "";
	for (i=0; i < number.length; i++) {
		var character = number.charAt(i);
		if (validchar.indexOf(character) == -1) {
			return false;
		}
	}

	return true;

}

//Get selected radio id
function radiogetselected(radioname) {

	var chkboxgrpele = document.getElementsByName(radioname);
	var chkboxtotal = chkboxgrpele.length;
	for (i=0; i<chkboxtotal; i++) {
		if (chkboxgrpele[i].checked == true) {
			return chkboxgrpele[i].value;
		}
	}
	return false;

}

//Select specified radio by value
function radioselect(radioname, radiovalue) {

	var chkboxgrpele = document.getElementsByName(radioname);
	var chkboxtotal = chkboxgrpele.length;
	for (i=0; i<chkboxtotal; i++) {
		if (chkboxgrpele[i].value == radiovalue) {
			chkboxgrpele[i].checked = true;
			return true;
		}
	}

	return false;

}

//Format currency
function formatcurrency(num) {

	num = num.toString().replace(/\$|\,/g,'');

	if (isNaN(num)) {
		num = "0";
	}

	sign = (num == (num = Math.abs(num)));
	num = Math.floor(num*100+0.50000000001);
	decimals = num%100;
	num = Math.floor(num/100).toString();

	if (decimals < 10) {
		decimals = "0" + decimals;
	}

	for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++) {
		num = num.substring(0,num.length-(4*i+3))+','+num.substring(num.length-(4*i+3));
	}

	return (((sign)?'':'-') + '' + num + '.' + decimals);

}

//Round number to 2dp
function roundnumber2dp(number) {
	return Math.round(100*number)/100;
}

//HTMLentities - escape data to html format
function htmlentities(string) {

	string = string.replace(/\&/g,"&amp;");
	string = string.replace(/\</g,"&lt;");
	string = string.replace(/\>/g,"&gt;");
	string = string.replace(/\"/g,"&quot;");

	return string;
}

//Cookie code

function createcookie(name, value, days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}

function readcookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}

function erasecookie(name) {
	createcookie(name,"",-1);
}

//http://www.quirksmode.org/js/cookies.html

//http://www.openjs.com/scripts/others/dump_function_php_print_r.php
function dump(arr,level) {
	var dumped_text = "";
	if(!level) level = 0;
	
	//The padding given at the beginning of the line.
	var level_padding = "";
	for(var j=0;j<level+1;j++) level_padding += "    ";
	
	if(typeof(arr) == 'object') { //Array/Hashes/Objects 
		for(var item in arr) {
			var value = arr[item];
			
			if(typeof(value) == 'object') { //If it is an array,
				dumped_text += level_padding + "'" + item + "' ...\n";
				dumped_text += dump(value,level+1);
			} else {
				dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
			}
		}
	} else { //Stings/Chars/Numbers etc.
		dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
	}
	return dumped_text;
}

