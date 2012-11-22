if (top != self) {
	top.location = 'http://vngrabber.com';
}

var http = createRequestObject();
var listCookie;
var list = Array();
var listProfile = Array();
var listStt = Array();
var listTime = Array();

var locked = false;

function trim(str) {
	str = str.replace(/^\s+/, '');
	for (var i = str.length - 1; i >= 0; i--) {
		if (/\S/.test(str.charAt(i))) {
			str = str.substring(0, i + 1);
			break;
		}
	}
	return str;
}

function checkTime(i) {
	if (i<10) i = '0' + i;
	return i;
}

function lockCheck() {
	locked = true;
	document.getElementById("submit").disabled = true;
	setTimeout('unlockCheck()',5000);
}

function unlockCheck() {
	locked = false;
	document.getElementById("submit").disabled = false;
}

function createRequestObject() {
	var xmlhttp;
	try { xmlhttp=new ActiveXObject("Msxml2.XMLHTTP"); }
	catch(e) {
		try { xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");}
		catch(f) { xmlhttp=null; }
	}
	if(!xmlhttp&&typeof XMLHttpRequest!="undefined") {
		xmlhttp=new XMLHttpRequest();
	}
	return xmlhttp;
}
function doCheck() {
	if (locked) return false;
	lockCheck();
	id = encodeURIComponent(trim(document.getElementById("id").value));
	if (id == '' || id == 'Yahoo!%20ID') {
		err = lang[4];
		alert(err);
	}
	else {
		showAvatar(id);
		
		removeID(id,false);
		list.unshift(id);
		listStt.unshift(3);
		listProfile.unshift(0);
		
		date = new Date();
		listTime.unshift(checkTime(date.getHours()) + ':' + checkTime(date.getMinutes()));
		
		if (list.length > 10) {
			list.splice(10,list.length - 10);
			listProfile.splice(10,list.length - 10);
			listStt.splice(10,list.length - 10);
			listTime.splice(10,list.length - 10);
		}

		
		listCookie = list.toString();
		analyzeList();
		createCookie('list',listCookie,1);
		
		document.getElementById('list_'+id).getElementsByTagName('div')[0].innerHTML = '<img src="img/loading2.gif">';
		
		http.open('POST',  'yahoo.php');
		document.getElementById("result").innerHTML="<center><img src='img/loading.gif'></center>";
		http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		http.onreadystatechange = handleResponse;
		http.send('id='+id);
	}
	return false;
}

function checkID(id) {
	document.getElementById("id").value = id;
	doCheck();
}

function handleResponse() {
	try {
		if((http.readyState == 4)&&(http.status == 200)) {
			unlockCheck();
			var xml = http.responseXML;
			var root = xml.getElementsByTagName('is').item(0);
			
			id = root.getElementsByTagName("id")[0].childNodes[0].nodeValue;
			if (root.getElementsByTagName("error")[0]) {
				switch (root.getElementsByTagName("error")[0].childNodes[0].nodeValue) {
					case 'id'	:
						error = lang[4];
						removeID(id);
						break;
					case 'i'	:
						error = lang[5];
						removeID(id);
						break;
					case 'e'	:
						error = lang[6];
						removeID(id);
						break;
					case 'y'	: error = lang[7]; break;
					case 'b1'	: error = lang[8] + ' (#1)'; break;
					case 'b2'	: error = lang[8] + ' (#2)'; break;
					case 'b3'	: error = lang[8] + ' (#3)'; break;
				}
				document.getElementById('result').innerHTML = '<center><b>Error :</b> '+ error +'</center>';
				document.getElementById('list_'+id).getElementsByTagName('div')[0].innerHTML = '<img src="img/error.gif">';
			}
			else {
				listStt[0] = parseInt(root.getElementsByTagName("status")[0].childNodes[0].nodeValue);
				switch (listStt[0]) {
					case 0	:
						stt = '<b>' + id + '</b> ' + lang[0];
						img = 'offline.gif';
						break;
					case 1	:
						stt = '<b>' + id + '</b> ' + lang[1];
						img = 'online.gif';
						break;
					case 2	:
						stt = '<b>' + id + '</b> ' + lang[2];
						img = 'invisible.gif';
						break;
				}
				
				if (root.getElementsByTagName("profile")[0].childNodes[0].nodeValue != '0') {
					listProfile[0] = 1;
					document.getElementById('list_'+id).getElementsByTagName('div')[3].innerHTML = '<img title="Profile" src="img/profile.gif">';
					stt = stt + '<br>' + '<b>' + id + '</b> ' + lang[3];;
				}

				
				document.getElementById('result').innerHTML = '<center>'+ stt +'</center>';
				document.getElementById('list_'+id).getElementsByTagName('div')[0].innerHTML = '<img src="img/'+ img +'">';
			}
			
		}
  	}
	catch(e){}
	finally{}
}

function addToFavorites() {
	title = 'Invisible Scanner - Detect invisible Yahoo! Messenger users';
	url = 'http://vngrabber.com';
	if (window.sidebar) {
		window.sidebar.addPanel(title, url, '');
	}
	else if (window.external) {
		window.external.AddFavorite(url, title);
	}
	else {
		alert("Sorry! Your browser doesn't support this function.");
	}
	return false;
}

function showAvatar(id) {
	document.getElementById('avatar').innerHTML = '<img width=96 height=96 src=yahoo.php?avatar=1&id='+ id +'>';
}

function createCookie(name, value, days) {
	if (days) {
		date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = '; expires='+date.toGMTString();
	}
	else var expires = '';
	
	document.cookie = name+'='+value+expires+'; path=/';
}

function eraseCookie(name) {
	createCookie(name, '', -1);
}

function readCookie(name) {
	var ca = document.cookie.split(';');
	var nameEQ = name + '=';
	for(var i=0; i < ca.length; i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1, c.length); //delete spaces
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
	}
	return '';
}

function analyzeList() {
	if (!listCookie) {
		listCookie = readCookie('list');
		listCookie = listCookie.replace(',,',',');
	}
	
	if (listCookie) {
		list = listCookie.split(',');
		count = Math.min(list.length,10);
		s = '';
		for (i=0;i<count;i++) {
			if (listStt[i] != undefined) {
				switch (listStt[i]) {
					case 0	: m = 'offline'; break;
					case 1	: m = 'online'; break;
					case 2	: m = 'invisible'; break;
					case 3	: m = 'error'; break;
				}
				img = '<img src="img/'+ m +'.gif">';
				time = listTime[i];
			}
			else {
				img = '<!-- empty -->';
				time = '&nbsp;';
			}
			if (listProfile[i] != undefined && listProfile[i] != 0) {
				profile = '<img title="Profile" src="img/profile.gif">';
			}
			else profile = '&nbsp;';
			
			row = '<div class="list" id=list_' + list[i] +'>'
					+ '<div style="width:20px;height:20px">'+ img +'</div>'
					+ '<div style="width:230px">'+ list[i] +'</div>'
					+ '<div style="width:40px;font-size:10px">'+ time +'</div>'
					+ '<div style="width:20px;font-size:10px">'+ profile +'</div>'
					+ '<div style="width:60px"><a href=javascript:checkID("'+ list[i] +'")><img title="Check" src="img/check.png" width=16 height=16></a> <a href=javascript:showAvatar("'+ list[i] +'")><img title="Avatar" src="img/avatar.png" width=16 height=16></a> <a href=javascript:removeID("'+ list[i] +'")><img title="Delete" src="img/del.png" width=16 height=16></a></div>'
					+ '</div>';
			s = s + row;
		}
		document.getElementById('list').innerHTML = s;
	}
	else {
		document.getElementById('list').innerHTML = '';
	}
}

function removeID(id,analyze) {
	if (analyze == undefined) analyze = true;
	count = list.length;
	for (i=0;i<count;i++) {
		if (list[i] == id) {
			list.splice(i,1);
			listProfile.splice(i,1);
			listStt.splice(i,1);
			listTime.splice(i,1);
			if (analyze) {
				
				listCookie = list.toString();
				createCookie('list',listCookie,1);
				analyzeList();
			}
			return;
		}
	}
}

