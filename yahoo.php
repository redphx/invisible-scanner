<?php
ob_start();
error_reporting(FALSE);
define('AKDKGK',true);
set_time_limit(20);

$referer = strtolower($_SERVER['HTTP_REFERER']);
$p = parse_url($referer);
if (strstr($p['path'],'./')) die();

$leech = false;

if (!$_SERVER['REMOTE_ADDR'] || !$_SERVER['HTTP_USER_AGENT'] || strpos($referer,'vngrabber.com/') === false) $leech = true;

if ($_GET['avatar'] == 3) {
	header("Location: avatar.gif");
	exit();
}

if ($_GET['avatar'] && $_GET['id']) {
	
	$id = stripslashes(trim(urldecode($_GET['id'])));
	$time = time();
	$time = floor($time / 10000) * 10000;
	$url = 'http://img.msg.yahoo.com/avatar.php?yids='.$id.'&r='.$time;
	
	header('Location: '.$url);
	exit();
}

//if ($leech) die();

include_once('new.php');