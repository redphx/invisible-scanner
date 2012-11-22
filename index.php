<?php

//ymsgr:displayimage?pic=http://blog.messenger.yahoo.com/images/displayimages/diwali1.jpg

error_reporting(FALSE);
if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip'))
	ob_start('ob_gzhandler');
else ob_start();


$referer = strtolower($_SERVER['HTTP_REFERER']);
if (strstr($p['path'],'./')) die();
$p = parse_url($referer);
if ($referer && ($p['host'] != 'www.vngrabber.com' || $p['host'] != 'vngrabber.com')) {
	$f = fopen('referer_'.date('ymd').'.txt','a');
	flock($f,2);
	fwrite($f,$_SERVER['HTTP_REFERER']."\r\n");
	flock($f,1);
	fclose($f);
}


define('AKDKGK',true);
session_start();

if (in_array($_GET['lang'],array('vn','en'))) {
	$_SESSION['yahoo_lang'] = $_GET['lang'];
	//setcookie('yahoo_lang',$_GET['lang'],time()+3600*24*365,'','vngrabber.com');
	header('Location: ./');
	exit();
}

include('lang.php');

if (!$_SESSION['yahoo_lang'] || !in_array($_SESSION['yahoo_lang'],array('vn','en'))) {
	setcookie('yahoo_lang','vn',time()+3600*24*365,'','vngrabber.com');
	$_SESSION['yahoo_lang'] = 'en';
}

$lang = $langArr[$_SESSION['yahoo_lang']];
$notice = $lang['notice'];

$id = stripslashes(strtolower(trim(urldecode($_GET['id']))));
$is_search = ($id)?true:false;
$is_search = false;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Invisible Scanner - <?php echo $lang['info']; ?> - RedPhoenix89</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="classification" content="Internet Tool" />
	<meta name="copyright" content="Yahoo.4tvn.com" />
	<meta name="programmer" content="RedPhoenix89" />
	<meta name="description" content="Invisible Scanner,Find invisible People in Yahoo! Messenger,Detect invisible in Yahoo! Messenger,Invisible Detector,RedPhoenix89" />
	<meta name="keywords" content="invisible scanner,yahoo messenger invisible hack,yahoo messenger,emoticon,invisible,scanner,invisible finder,detect invisible,an nick,kiem tra,status checker,offline,online,buddy checker,find,checks,scan," />
	<meta name="revisit-after" content="2 days" />
	<meta name="robots" content="index,follow,all" />
	<link rel="shortcut icon" type="image/ico" href="favicon.gif" />
	<link media="all" href="style.css" type="text/css" rel="stylesheet" />
</head>
<body>
<div class="page-container">
	<div class="scanner">
		<div class="box">
			<div class="lang"><a href="index.php?lang=vn" title="Tiếng Việt"><img alt="Tiếng Việt" width="16" height="11" src="img/vn.gif" /></a> <a title="English" href="index.php?lang=en"><img alt="English" width="16" height="11" src="img/en.gif" /></a></div>
			<div class="header"><h1>Invisible Scanner<br /><span class="info"><?php echo $lang['info']; ?></span></h1></div>
			<div style="padding:1px">
				<div class="avatar" id="avatar"><img width="96" height="96" src="img/avatar.gif" alt="Avatar" /></div>
				<div class="form">
					<form onsubmit="return doCheck();" action="" method="get">
					<div>
						<input class="id" id="id" type="text" value="Yahoo! ID" onclick="if (this.value == 'Yahoo! ID') this.value='';" />
						<input class="submit" type="submit" value="Check" id="submit" />
					</div>
					</form>
					<div class="result" id="result">&nbsp;</div>
				</div>
				<div id="list" style="clear:both;min-height:40px;"></div>
				<script type="text/javascript">
				<!--
				var lang = Array(
					"<?php echo $lang['offline']; ?>",
					"<?php echo $lang['online']; ?>",
					"<?php echo $lang['invisible']; ?>",
					"<?php echo $lang['profile']; ?>",
					"<?php echo $lang['id']; ?>",
					"<?php echo $lang['invalid']; ?>",
					"<?php echo $lang['exists']; ?>",
					"<?php echo $lang['yahoo']; ?>",
					"<?php echo $lang['busy']; ?>"
				);
				-->
				</script>
				<script src="js.js" type="text/javascript"></script>
				<!--<script src="../bar/bar.js" type="text/javascript"></script>-->
				<script type="text/javascript">analyzeList();</script>
			</div>
			<div class="firefox">Best view with <b>
			<script type="text/javascript">
			<!--
				google_ad_client = "pub-3168146600363329";
				google_ad_slot = "2536682797";
				google_ad_output = "textlink";
				google_ad_format = "ref_text";
				google_cpa_choice = ""; // on file
			-->
			</script>
			<script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"></script></b>, <b>1024x768</b> resolution</div>
			<div class="footer"><b><a href="http://huhiho.com">huhiho.com</a></b><br />RedPhoenix89</div>
		</div>
	</div>
	<div class="ads">
		<div class="box">
			<script type="text/javascript">
			<!--
				google_ad_client = "pub-3168146600363329";
				google_ad_slot = "3251556542";
				google_ad_width = 336;
				google_ad_height = 280;
			-->
			</script>
			<script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"></script>
		</div>
	</div>
</div>
</body>
</html>