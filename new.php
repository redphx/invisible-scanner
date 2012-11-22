<?php
//error_reporting(E_ALL);
//session_start();

if (!$_SESSION['yahoo_lang'] || !in_array($_SESSION['yahoo_lang'],array('vn','en'))) {
	$_SESSION['yahoo_lang'] = 'en';
}

mt_srand ((double)microtime() * 1000000);
$x = rand(0,400);
$id = 'jdhfkxjdhfkjxdhfkxduryxjdf'.$x; // select random bot
$pwd = 'redphx'; // bot's password

$victim = stripslashes(strtolower(trim(urldecode($_POST['id']))));
$victim = explode('@yahoo.com',$victim);
$victim = $victim[0];
$victim = strip_tags($victim);

include_once('consts.php');
include_once('ymsg.php');
include_once('lang.php');

$lang = $langArr[$_SESSION['yahoo_lang']];

function checkProfile($id) {
	define('NL',"\r\n");
	$id = urlencode($id);
	
	$config = array(
		'POST'			=>	true,
		'POSTFIELDS'	=>	'yid='.$id,
	);
	
	$contents = getContents('http://cn.webmessenger.yahoo.com/platform/icon.php', $config);
	
	if ($contents && strpos($contents,'f3.yahoofs.com') !== false) return false;
	else {
		$config = array(
			'HEADER'	=> true,
			'NOBODY'	=> true,
		);
		
		$contents = getContents('http://lookup.avatars.yahoo.com/wimages?yid='.$id.'&size=medium&type=jpg', $config);
		
		if ($contents && strpos($contents,'img.avatars.yahoo.com')) return false;
	}
	return true;
}

function isExistsID($id) {
	$id = urlencode($id);
	
	$config = array(
		'HEADER'	=> true,
		'NOBODY'	=> true,
	);
	
	$contents = getContents('http://manage.members.yahoo.com/index_deny.html?id='.$id, $config);
	
	if (!strstr($contents,'error.html')) {
		return true;
	}
	return false;
}

function showResult($txt) {
	global $victim,$ymsg;

	header('Content-Type: text/xml');
	
	$xml = '<?xml version="1.0" ?><is>';
	$xml .= '<id>'.htmlentities($victim).'</id>';
	if (in_array($txt,array('id','yahoo','exists','invalid','busy','forbid'))) {
		$xml .= '<error>';
		switch ($txt) {
			case 'id'		: $xml .= 'id'; break;
			case 'invalid'	: $xml .= 'i'; break;
			case 'exists'	: $xml .= 'e'; break;
			case 'yahoo'	: $xml .= 'y'; break;
			case 'busy'		: $xml .= 'b1'; break;
			case 'forbid'	: $xml .= 'b'.rand(1,3); break;
		}
		$xml .= '</error>';
	}
	else {
		$isProfile = checkProfile($victim);
		
		$xml .= '<status>';
		switch ($txt) {
			case 'offline'		: $xml .= 0; break;
			case 'online'		: $xml .= 1; break;
			case 'invisible'	: $xml .= 2; break;
			
		}
		$xml .= '</status>';
		
		if ($isProfile) $xml .= '<profile>1</profile>';
		else $xml .= '<profile>0</profile>';
	}
	$xml .= '</is>';
	die($xml);
}

function encode($s) {
	$a = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ+/=';
	$b = 'a/HxT86=CUMbJ3D12Sc9I7ZiXhfQPmnwVsApuqLvdYzWRl0tN+yKFeGg4jEOrok5B';
	$s = strrev(strtr(base64_encode($s),$b,$a));
	return base64_encode($s);
}

function logID($id) {
	$date = date('ymd');
	$f = fopen('logs/log_'.$date.'_'.substr(md5($date.'hehe'),0,5).'_'.$_SESSION['yahoo_lang'].'.txt','a');
	flock($f,2);
	fwrite($f,encode($id)."\r\n");
	flock($f,1);
	fclose($f);
}

function validateID($id) {
	if (strlen($id) <= 64 && preg_match('/^[a-zA-Z0-9\-\_\.\@\ ]+$/',$id)) return true;
	return false;
}


function getContents($url,$config = array()) {
	$c = curl_init();
	curl_setopt($c, CURLOPT_URL, $url);
	curl_setopt($c, CURLOPT_NOPROGRESS, TRUE);
	curl_setopt($c, CURLOPT_NOSIGNAL, TRUE);
	curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 3);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($c, CURLOPT_ENCODING, '');
	
	//if (!$config['TIMEOUT']) curl_setopt($c, CURLOPT_TIMEOUT, 30);
	//else curl_setopt($c, CURLOPT_TIMEOUT, $config['TIMEOUT']);
	if ($config['HEADER']) curl_setopt($c, CURLOPT_HEADER, TRUE);
	if ($config['NOBODY']) curl_setopt($c, CURLOPT_NOBODY, TRUE);
	curl_setopt($c, CURLOPT_USERAGENT, 'Windows-Media-Player/10.00.00.4036');
	//else curl_setopt($c, CURLOPT_USERAGENT, $config['USERAGENT']);
	//if ($config['REFERER']) curl_setopt($c, CURLOPT_REFERER, $config['REFERER']);
	if ($config['CUSTOMREQUEST']) curl_setopt($c, CURLOPT_CUSTOMREQUEST, $config['CUSTOMREQUEST']);
	if ($config['POST']) curl_setopt($c, CURLOPT_POST, TRUE);
	if ($config['POSTFIELDS']) curl_setopt($c, CURLOPT_POSTFIELDS, $config['POSTFIELDS']);
	//if ($config['HTTPHEADER']) curl_setopt($c, CURLOPT_HTTPHEADER, $config['HTTPHEADER']);
	
	//if ($config['COOKIE']) curl_setopt($c, CURLOPT_COOKIE, $config['COOKIE']);
	$contents = curl_exec($c);
	curl_close($c);
	return $contents;
}

if (!validateID($victim)) showResult('invalid');


$special_list = array(
	// ME
	'redphoenix89'
);

if (in_array($victim,$special_list)) showResult('forbid');

$c = getContents('http://opi.yahoo.com/online?u='.(urlencode($victim)).'&m=t&t=1');
if ($c == '00' && !isExistsID($victim)) showResult('exists');

logID($victim);

if ($c == '01') showResult('online');


$ymsg =& new YMSG($id,$pwd,$victim);
showResult($ymsg->getStatus());