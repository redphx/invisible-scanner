<?php
error_reporting(FALSE);

include_once('tables.php');

class YMSG extends YMSG_Tables {
	var $fp;
	var $connected = false;
	var $id,$pwd;	// Y!ID & Password
	var $victim;
	
	var $MySQL_Host = 'localhost';
	var $MySQL_User = 'root';
	var $MySQL_Password = '';
	
	var $OPERATORS_LOOKUP = '+|&%/*^-';
	var $ALPHANUM_LOOKUP = 'qzec2tb3um1olpar8whx4dfgijknsvy5';
	var $ENCODE1_LOOKUP = 'FBZDWAGHrJTLMNOPpRSKUVEXYChImkwQ';
	var $ENCODE2_LOOKUP = 'F0E1D2C3B4A59687abcdefghijklmnop';
	var $ENCODE3_LOOKUP = ',;';
	
	var $countSQL = true;
	var $total = 0;
	
	var $system; // 32-bit or 64-bit
	
	var $status;
	
	function YMSG($id,$pwd,$victim) {
		$this->id = $id;
		$this->pwd = $pwd;
		$this->victim = $victim;
		
		$this->checkSystem();
		
		//$this->fp = fsockopen('scs.msg.yahoo.com', 5050, $errno, $errstr, 3);
		$this->fp = fsockopen('tcp://scs.msg.yahoo.com', 5050, $errno, $errstr, 3);
		if (!$this->fp) {
			$this->returnStatus('yahoo');
			return;
		}
		$this->connected = true;
		$this->listener();
	}
	
	function disconnect() {
		if ($this->fp) {
			fclose($this->fp);
		}
	}
	
	function returnStatus($stt) {
		$this->status = $stt;
		$this->connected = false;
		$this->disconnect();
	}
	
	function getStatus() {
		return $this->status;
	}
	
	function listener() {
		$this->SendPacket('Auth',array($this->id));
		
		while ($this->connected) {
			//stream_set_timeout($this->fp,1);
			$t = 0;
			do {
				$header = fread($this->fp,20);
				if (!$header && $t++ == 10) {
					$this->returnStatus('busy');
					return;
				}
			}
			while (substr($header,0,4) != 'YMSG');
			
			$pheader = $this->parseHeader($header);
			$sz = 0;
			$data = '';
			while ($sz < $pheader[1]) {
				$data .= fread($this->fp,$pheader[1] - $sz);
				$sz = strlen($data);
			}
			$svc = $pheader[2];
			
			$p = explode(SP,$data);
			//$this->SendPacket('PM',array($this->id,$this->victim,'asdasd'));

			//$svc = ord(substr($c,11,1));
			switch ($svc) {
				case SERVICE_AUTH :
					list($res_1,$res_2) = $this->getStr($this->id,$this->pwd,$p[3]);
					$this->SendPacket('AuthResp',array($this->id,$res_1,$res_2));
					break;
				case SERVICE_LIST :
					$this->SendPacket('PM6',array($this->id,$this->victim));
					break;
				case SERVICE_PM6 :
					if (strpos($data,'198'.SP.'1'.SP.'197') !== false) {
						$this->SendPacket('P',array($this->id,$this->victim));
						$this->SendPacket('A',array($this->id,$this->victim));
						$this->SendPacket('P2P',array($this->id,$this->victim));
					}
					elseif (strpos($data,'198'.SP.'0'.SP.'197') !== false) {
						$this->returnStatus('invisible');
					}
					elseif (strpos($data,'66'.SP.'1'.SP) !== false) {
						$this->returnStatus('offline');
					}
					break;
				case SERVICE_P :
					if (strpos($data,'66'.SP.'1'.SP) !== false) {
						$this->returnStatus('offline');
					}
					break;
				case SERVICE_A :
					if (strpos($data,'13'.SP.'2'.SP) !== false || strpos($data,'13'.SP.'1'.SP) !== false) {
						$this->returnStatus('invisible');
					}
					break;
				case SERVICE_P2P :
					$this->returnStatus('invisible');
					break;
			}
		}
		$this->disconnect();
	}
	
	function toInt($s) {
		$c = 0;
		$len = strlen($s);
		for ($i=0;$i<$len;$i++) {
			$c = $c << 8;
			$c += ord($s[$i]);
		}
		return $c;
	}
	
	function parseHeader($h) {
		if (substr($h,0,4) == 'YMSG' && strlen($h) == 20) {
			return array(
				#toint(h[4:8]),
				$this->toInt($h[5]),	#version
				#
				$this->toInt(substr($h,8,2)), #length
				$this->toInt(substr($h,10,2)),#service id
				//$this->toInt(substr($h,12,4)),#status
				//$this->toInt(substr($h,16,4)) #session id
			);
		}
	}
	
	function checkSystem() {
		if (PHP_INT_MAX == 0x80000000 - 1) {
			$this->system = 32;
		}
		elseif (PHP_INT_MAX >= 0x8000000000000000 - 1) {
			$this->system = 64;
		}
		else die('Unsupported System');
	}
	/*
	function Login($id, $pwd) {
		$this->id = $id;
		$this->pwd = $pwd;
		
		$this->SendPacket('PreLogin',array($this->id));
		$stream = $this->getStream($this->fp);
		
		if ($stream[11] == 'W') {
			$Key = substr($stream,16,4);
			$e = explode(SP,$stream);
			$challenge = $e[3];

			//$challenge = '1+y+w+u|v&j^l+(q%n&t-m*t*h&(z%m)/o^d/t%i*t|p-(1^3&k)|y*k%(o+s*8^i%f&8))';
			list($res_1,$res_2) = $this->getStr($this->id,$this->pwd,$challenge);
			
			//echo $res_1.'<br>'.$res_2;
			//echo '<br>'.$this->total;
			//exit();
			
			$this->SendPacket('Login',array($this->id,$res_1,$res_2));
			$stream = $this->getStream($this->fp);
			if (strpos($stream,'domain=') === false) {
				return 'Info';
			}
		}
		else {
			$this->Logout();
			return 'Login';
		}
	}
	
	function Logout() {
		if ($this->fp) {
			fclose($this->fp);
		}
	}
	
	// Get Stream Contents
	function getStream($fp,$seconds = 0,$microseconds = 600000) {
		usleep(100000);
		stream_set_timeout($fp,$seconds,$microseconds);
		$contents = stream_get_contents($fp);

		return $contents;
	}
	*/
	// Send Packet
	function SendPacket($t,$a = array()) {
		switch ($t) {
			case 'Auth':
				$data = $this->Packet(57, array(1,$a[0]));
				break;
			case 'AuthResp':
				//$data = $this->Packet(54, array(6,$a[1],96,$a[2],0,$a[0],2,$a[0],192,"-1",1,$a[0],135,"6,0,0,0000",148,360));
				$data = $this->Packet(54, array(6,$a[1],96,$a[2],0,$a[0],2,$a[0],192,"-1",1,$a[0],135,"6,0,0,0000",148,360));
				break;
			case 'P':
				$data = $this->Packet('C1',array(1,$a[0],5,$a[1],206,2));
				//$data = $data.$data.$data;
				break;
			case 'A':
				$data = $this->Packet('BE', array(1,$a[0],5,$a[1],13,1));
				break;
			case 'P2P':
				$data = $this->Packet('4D',array(49,'IMVIRONMENT',1,$a[0],14,'null',13,4,5,$a[1],63,';0',64,0));
				break;
			case 'PM6':
				$data = $this->Packet('C0',array(1,$a[0],5,$a[1]));
				break;
			case 'PM':
				$data = $this->Packet('06',array(1,$a[0],5,$a[1],241,100,14,$a[2],97,1,63,';0',64,0,206,0));
				//$data = $this->Packet('06',array(1,$a[0],5,$a[1],14,$a[2],97,1,63,';0',64,0,206,2));
				break;
			/*
			case 'PM':
				// ID VICTIM MSG
				$data = $this->Packet('D4', array(1,$a[0],5,$a[1],13,1)).$this->Packet('06', array(1,$a[0],5,$a[1],14,$a[2],97,1,63,';0',64,0,206,2), chr(0x5A).chr(0x55).chr(0xAA).chr(0x55));
				break;
			/*
			case 'VoiceChat':
				$data = $this->Packet('4A', array(1,$a[0],5,$a[1],57,'',13,1));
				break;
			case 'Avatar':
				$data = $this->Packet('BE', array(1,$a[0],5,$a[1],13,1));
				break;
			case 'Doodle_1':
				$data = $this->Packet('4D',array(49,'IMVIRONMENT',1,$a[0],14,'null',13,4,5,$a[1],63,'doodle;107',64,1));
				break;
			case 'Doodle_2':
				$data = $this->Packet('4D',array(49,'IMVIRONMENT',1,$a[0],14,'',13,0,5,$a[1],63,'doodle;107',64,0));
				break;
			case 'Doodle_3':
				$data = $this->Packet('4D',array(49,'IMVIRONMENT',1,$a[0],14,'null',13,5,5,$a[1],63,'doodle;107',64,1));
				break;
			*/
		}
		fwrite($this->fp,$data);
	}
	
	// Generate Packet
	function Packet($PackType, $Pack, $Status = '') {
		global $Key;
		if ($Key == '') $Key = $this->string(4,0);
		$Pack = implode(SP,$Pack);
		$Pack .= SP;
		
		if (!$Status) $Status = $this->string(4,0);
		$Packet = 'YMSG'.chr(0).chr(15).$this->string(2,0).chr(intval((strlen($Pack) / 256))).chr(fmod(strlen($Pack),256)).chr(0).chr(hexdec($PackType)).$Status.$Key.$Pack;
		return $Packet;
	}

	function string($n,$c) {
		$s = '';
		for ($i=0;$i<$n;$i++) {
			if (gettype($c) == 'integer') $s .= chr($c);
			else $s .= $c;
		}
		return $s;
	}

/*
LOGIN FUNCTIONS
*/

	function isOperator($c) {
		if (strpos($this->OPERATORS_LOOKUP,$c) !== false) return true;
		return false;
	}
	
	function cal($s) {
		if ($this->countSQL) {
			$this->total++;
		}
		$queryResult = mysql_query('SELECT '.$s) or die(mysql_error());
		$queryContent = mysql_fetch_row($queryResult) or die(mysql_error());
		//mysql_free_result($queryResult);
		if (count($queryContent) > 1) return $queryContent;
		return $queryContent[0];
	}

	
	function getStr($id,$password,$challenge) {
		mysql_connect($this->MySQL_Host,$this->MySQL_User,$this->MySQL_Password) or die(mysql_error());
		$magic = array();
		$cnt = 0;
		$len = strlen($challenge);
		for($i=0;$i<$len;$i++) {
			$c = $challenge[$i];
			if(preg_match('/[a-zA-Z]/',$c) || preg_match('/[0-9]/',$c)) {
				$operand = strpos($this->ALPHANUM_LOOKUP,$c) << 3;		// 0-31, shifted to high 5 bits
			}
			elseif ($this->isOperator($c)) {
				$a = strpos($this->OPERATORS_LOOKUP,$c);		// 0-7
				$magic[$cnt]=($operand|$a) & 0xff;				// Mask with operand
				$cnt++;
			}
		}
		$magic_len = count($magic);
		
		for($i=$magic_len-1;$i>0;$i--) {
			$a = $magic[$i-1];
			$b = $magic[$i];
			$a *= 0xcd;
			$a &= 0xff;
			$a ^= $b;
			$magic[$i] = $a;
		}
		
		$cnt = 1;
		$x = 0;
		$comparison = array();
		
		while (($cnt < $magic_len) && ($x < 20)) {
			$bl = 0;
			$cl = $magic[$cnt++];
			if ($cl > 0x7f) {
				if ($cl < 0xe0) $bl = $cl = ($cl & 0x1f) << 6;
				else {
					$bl = $magic[$cnt++];
					$cl = ($cl & 0x0f) << 6;
					$bl = (($bl & 0x3f) + $cl) << 6;
				}
				$cl = $magic[$cnt++];
				$bl = ($cl & 0x3f) + $bl;
			}
			else $bl = $cl;
			
			$comparison[$x++] = chr(($bl & 0xff00) >> 8);
			$comparison[$x++] = chr($bl & 0xff);
		}
		
		$comparison = implode('',$comparison);
	
		$binLookup = substr($comparison,0,4);
		$com = substr($comparison,4);
		for($i=0;$i<0xffff;$i++) {
			for($j=0;$j<5;$j++) {
				$binLookup[4] = chr($i&0xff);
				$binLookup[5] = chr(($i>>8)&0xff);
				$binLookup[6] = chr($j);
				$result = md5($binLookup,true);
				if($result == $com) {
					$depth = $i;
					$table = $j;
					$i = 0xffff;
					$j = 5;	// Exit loops
				}
			}
		}
		
		if ($this->system == 32) {
			$x = $this->cal(ord($comparison[3]).'<<24|'.ord($comparison[2]).'<<16|'.ord($comparison[1]).'<<8|'.ord($comparison[0]));
		}
		else {
			$x = ord($comparison[3]) << 24 | ord($comparison[2]) << 16 | ord($comparison[1]) << 8 | ord($comparison[0]);
		}
		
		$this->YMSG_Tables();
		
		$x = $this->yahoo_xfrm($table,$depth,$x);
		$x = $this->yahoo_xfrm($table,$depth,$x);
		
		if ($this->system == 32) {
			$magic_key_char = chr($this->cal($x.' & 255')) . chr($this->cal($x.' >> 8 & 255')) . chr($this->cal($x.' >> 16 & 255')) . chr($this->cal($x.' >> 24 & 255'));
		}
		else {
			$magic_key_char = chr($x & 255) . chr($x >> 8 & 255) . chr($x >> 16 & 255) . chr($x >> 24 & 255);
		}
		$crypt_result = crypt($password, '$1$_2S43d5f');
		return array(
			$this->finalstep($password, $magic_key_char, $table), 
			$this->finalstep($crypt_result, $magic_key_char, $table)
		);
	}
	
	function finalxor($hash, $mask) {
		$result = '';
		for ($i=0;$i<strlen($hash);$i++) {
			$result .= chr(ord($hash[$i]) ^ $mask);
		}
		for ($i=0;$i<64-strlen($hash);$i++) {
			$result .= chr($mask);
		}
		return $result;
	}

	
	function finalstep($input, $magic_key_char, $table) {
		$hash = base64_encode(md5($input,true));
		$hash = strtr($hash, '+/=', '._-');
		if ($table >= 3) {
			if ($this->system == 32) {
				include_once('SHA1_32.php');
				$sha1 = new SHA1Library();
				$sha1->ex = true;
				$digest1 = $sha1->str_sha1($this->finalxor($hash, 0x36).$magic_key_char);
			}
			else {
				include_once('SHA1_64.php');
				$sha1 = new SHA1Library($this->finalxor($hash, 0x36).$magic_key_char);
				$sha1->count[1] = $sha1->count[1] - 1;
				$digest1 = $sha1->digest();
			}
		}
		else {
			$digest1 = sha1($this->finalxor($hash, 0x36).$magic_key_char,TRUE);
		}

		$digest2 = sha1($this->finalxor($hash, 0x5c).$digest1,TRUE);

		$result = '';
		for ($i=0;$i<10;$i++) {
			$val = (ord($digest2[$i * 2]) << 8) + ord($digest2[$i*2+1]);
	
			$result .= $this->ENCODE1_LOOKUP[($val >> 0x0b) & 0x1f] . '=';
			$result .= $this->ENCODE2_LOOKUP[($val >> 0x06) & 0x1f];
			$result .= $this->ENCODE2_LOOKUP[($val >> 0x01) & 0x1f];
			$result .= $this->ENCODE3_LOOKUP[$val & 0x01];
		}
		return $result;
	}
}
?>