<?php

function profileLang($profile = false) {
	global $langArr,$victim;
	$c = $_SESSION['yahoo_lang'];
	if ($c == 'vn') {
		if ($profile === false)
			showResult($langArr[$c]['invisible'].'<br><b>'.$victim.'</b> là nick thật');
		else
			showResult($langArr[$c]['invisible'].'<br><b>'.$victim.'</b> là Profile ( nick ảo ) của <b>'.$profile.'</b>');
	}
	elseif ($c == 'en') {
		if ($profile === false)
			showResult($langArr[$c]['invisible'].'<br><b>'.$victim.'</b> is a Main ID ( not Profile )');
		else
			showResult($langArr[$c]['invisible'].'<br><b>'.$victim.'</b> is a Profile of <b>'.$profile.'</b>');
	}
}

$langArr = array(
	'vn'	=> array(
		//'notice'	=>	'<b>Chú ý</b> : Vì một vài lý do nên nếu kết quả cho <b>OFFLINE</b> thì bạn nên thử lại để có kết quả chính xác nhất (nếu kết quả đã <b>ONLINE</b> thì không cần thực hiện thao tác này)',
		'info'		=>	'Kiểm tra ẩn nick trong Yahoo! Messenger',
		'online'	=>	'hiện đang <img src=\'img/online.gif\' width=\'16\' height=\'16\' alt=\'Online\' /> <b>ONLINE</b>',
		'offline'	=>	'hiện đang <img src=\'img/offline.gif\' width=\'14\' height=\'14\' alt=\'Offline\' /> <b>OFFLINE</b>',
		'invisible'	=>	'hiện đang <img src=\'img/invisible.gif\' width=\'16\' height=\'16\' alt=\'Invisible\' /> <b>INVISIBLE</b>',
		'profile'	=>	'là Profile ( nick ảo )',
		'busy'		=>	'Server đang bận. Xin hãy thử lại',
		'yahoo'		=>	'Không thể kết nối với Server',
		'id'		=>	'Bạn chưa nhập Yahoo! ID',
		'forbid'	=>	'Không thể check nick này :-P',
		'exists'	=>	'Yahoo! ID này không tồn tại',
		'invalid'	=>	'Y!ID không hợp lệ',
	),
	'en'	=>	array(
		//'notice'	=>	'<b>Notice</b> : For some reasons, if the result shows <b>OFFLINE</b>, please try again for more accurate results (if the result shows <b>ONLINE</b>, you don\'t have to repeat the scanning)',
		'info'		=>	'Detect invisible Yahoo! Messenger users',
		'online'	=>	'is now <img src=\'img/online.gif\' width=\'16\' height=\'16\' alt=\'Online\' /> <b>ONLINE</b>',
		'offline'	=>	'is now <img src=\'img/offline.gif\' width=\'14\' height=\'14\' alt=\'Offline\' /> <b>OFFLINE</b>',
		'invisible'	=>	'is now <img src=\'img/invisible.gif\' width=\'16\' height=\'16\' alt=\'Invisible\' /> <b>INVISIBLE</b>',
		'profile'	=>	'is Profile ( not Main ID )',
		'busy'		=>	'Server is busy. Please try again',
		'yahoo'		=>	'Cannot connect to Server',
		'id'		=>	'Please enter Yahoo! ID',
		'forbid'	=>	'Server is busy. Please try again',
		'exists'	=>	'This Yahoo! ID isn\'t exists',
		'invalid'	=>	'Invalid Y!ID',
	),
);