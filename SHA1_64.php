<?php
//if (!defined('AKDKGK')) die();
class SHA1Library {
	var $H0 = 0x67452301;
	var $H1 = 0xEFCDAB89;
	var $H2 = 0x98BADCFE;
	var $H3 = 0x10325476;
	var $H4 = 0xC3D2E1F0;
	var $count = array(0, 0);
	var $length = 0;
	var $input = array();
	
	function SHA1Library($inp) {
		$this->update($inp);
	}
	
	function _rotateLeft($x, $n) {
		return ( $x << $n | $x >> (32-$n) );
	}
	
	function _long2bytesBigEndian($n, $blocksize = 0) {
		$s = '';
		//echo $n.' - ';
		while ($n > 0) {
			$z = eval('return ('.$n.'&4294967295);');
			$s = pack('N', $z).$s;
			$n = $n >> 32;
		}
		
		for ($i=0;$i<strlen($s);$i++) {
			if ($s[$i] != chr(0x00)) {
				break;
			}
		}
		$s = substr($s,$i);
		//echo fmod(strlen($s),$blocksize).'<br>';
		if (($blocksize > 0) && fmod(strlen($s),$blocksize)) {
			//echo ($blocksize - fmod(strlen($s),$blocksize)).'<br>';
			//$s = chr(($blocksize - fmod(strlen($s),$blocksize)) * 0x00)) . $s;
			$s = chr(0x00).$s;
			
		}
		//echo $m.' '.$blocksize.' '.md5($s).'<br>';
		return $s;
	}
	
	function _bytelist2longBigEndian($list){

		$imax = count($list)/4;
		$j = 0;
		$i = 0;
		while ($i < $imax) {
			//$hl[$i] = eval('return ('.ord($list[$j]).' << 24) | ('.ord($list[$j+1]).' << 16) | ('.ord($list[$j+2]).' << 8) | ('.ord($list[$j+3]).');');
			$hl[$i] = ord($list[$j]) << 24 | ord($list[$j+1]) << 16 | ord($list[$j+2]) << 8 | ord($list[$j+3]);
			$i = $i + 1;
			$j = $j + 4;
		}
		return $hl;
	}

	function _transform($W) {
		$n = 1;
		for ($t=16;$t<80;$t++){
			$q = $W[$t-3] ^ $W[$t-8] ^ $W[$t-14] ^ $W[$t-16];
			$q = $this->_rotateLeft($q,$n) & 4294967295;
			//echo cal($q).'<br>';
			array_push($W, $q);
		}
		
		//echo '----------<br>';
		$A = $this->H0;
		$B = $this->H1;
		$C = $this->H2;
		$D = $this->H3;
		$E = $this->H4;
		
		/*
		echo $A.'<br>';
		echo $B.'<br>';
		echo $C.'<br>';
		echo $D.'<br>';
		echo $E.'<br>-----<br>';
		*/
		
		$K = array(
			0x5A827999, # ( 0 <= t <= 19)
			0x6ED9EBA1, # (20 <= t <= 39)
			0x8F1BBCDC, # (40 <= t <= 59)
			0xCA62C1D6  # (60 <= t <= 79)
		);
		
		for ($t=0;$t<20;$t++) {
			$TEMP = $this->_rotateLeft($A, 5) + ( $B & $C |~ $B & $D ) + $E + $W[$t] + $K[0];
			$E = $D;
			$D = $C;
			$C = $this->_rotateLeft($B, 30) & 4294967295;
			$B = $A;
			$A = $TEMP & 4294967295;
			//list($C,$A) = cal($C.','.$A);
		}
	
		for ($t=20;$t<40;$t++) {
			$TEMP = $this->_rotateLeft($A, 5) + ( $B ^ $C ^ $D ) + $E + $W[$t] + $K[1];
			$E = $D;
			$D = $C;
			$C = $this->_rotateLeft($B, 30) & 4294967295;
			$B = $A;
			$A = $TEMP & 4294967295;
			//list($C,$A) = cal($C.','.$A);
		}
		for ($t=40;$t<60;$t++) {
			$TEMP = $this->_rotateLeft($A, 5) + (( $B & $C ) | ( $B & $D ) | ( $C & $D ) ) + $E + $W[$t] + $K[2];
			$E = $D;
			$D = $C;
			$C = $this->_rotateLeft($B, 30) & 4294967295;
			$B = $A;
			$A = $TEMP & 4294967295;
			//list($C,$A) = cal($C.','.$A);
		}
		for ($t=60;$t<80;$t++) {
			$TEMP = $this->_rotateLeft($A, 5) + ( $B ^ $C ^ $D ) + $E + $W[$t] + $K[3];
			$E = $D;
			$D = $C;
			$C = $this->_rotateLeft($B, 30) & 4294967295;
			$B = $A;
			$A = $TEMP & 4294967295;
			//list($C,$A) = cal($C.','.$A);
		}
		
		
		
		$this->H0 = ( $this->H0 + $A ) & 4294967295;
		$this->H1 = ( $this->H1 + $B ) & 4294967295;
		$this->H2 = ( $this->H2 + $C ) & 4294967295;
		$this->H3 = ( $this->H3 + $D ) & 4294967295;
		$this->H4 = ( $this->H4 + $E ) & 4294967295;
		
		//list($this->H0,$this->H1,$this->H2,$this->H3,$this->H4) = cal($this->H0.','.$this->H1.','.$this->H2.','.$this->H3.','.$this->H4);
		
		/*
		echo $this->H0.'<br>';
		echo $this->H1.'<br>';
		echo $this->H2.'<br>';
		echo $this->H3.'<br>';
		echo $this->H4.'<br>';
		*/
	}
	
	function update($inBuf) {
		$leninBuf = strlen($inBuf);
		$index = ($this->count[1] >> 3) & 0x3F;
		
		$this->count[1] = $this->count[1] + ($leninBuf << 3);
		if ($this->count[1] < ($leninBuf << 3)) {
			$this->count[0] = $this->count[0] + 1;
		}
		$this->count[0] = $this->count[0] + ($leninBuf >> 29);
		

		$partLen = 64 - $index;

		if ($leninBuf >= $partLen) {
			$a = str_split(substr($inBuf,0,$partLen));
			$this->input = array_slice($this->input,$index);
			$this->input = array_merge($this->input,$a);
			//print_r($this->input);
			$this->_transform($this->_bytelist2longBigEndian($this->input));
			$i = $partLen;
			if (($i + 63) < $leninBuf) {
				while (($i + 63) < $leninBuf) {
					$a = str_split(substr($inBuf,$i,64));
					$this->_transform($this->_bytelist2longBigEndian($a));
					$i = $i + 64;
				}
			}
			else $this->input = str_split(substr($inBuf,$i,$leninBuf-$i));
		}
		else {
			$i = 0;
			$a = str_split($inBuf);
			$this->input = array_merge($this->input,$a);
		}
	}
	
	function digest() {
		$H0 = $this->H0;
		$H1 = $this->H1;
		$H2 = $this->H2;
		$H3 = $this->H3;
		$H4 = $this->H4;
		
		$input = $this->input;
		$count = $this->count;

		$index = ($this->count[1] >> 3) & 0x3f;
		if ($index < 56) {
			$padLen = 56 - $index;
		}
		else {
			$padLen = 120 - $index;
		}
		$padding = chr(0x80);
		for ($i=0;$i<$padLen-1;$i++) $padding .= chr(0x00);
		
		
		$this->update($padding);
		
		# Append length (before padding).
		$s = implode('',$this->input);
		
		$a = str_split(substr($s,0,56));
		$bits = array_merge($this->_bytelist2longBigEndian($a),$count);
		
		$this->_transform($bits);
		/*
		echo $this->H0.'<br>';
		echo $this->H1.'<br>';
		echo $this->H2.'<br>';
		echo $this->H3.'<br>';
		echo $this->H4.'<br>';
		echo '-------<br>';
		*/
		# Store state in digest.
		$digest = $this->_long2bytesBigEndian($this->H0, 4) . $this->_long2bytesBigEndian($this->H1, 4) . $this->_long2bytesBigEndian($this->H2, 4) . $this->_long2bytesBigEndian($this->H3, 4) . $this->_long2bytesBigEndian($this->H4, 4);
		
		$this->H0 = $H0;
		$this->H1 = $H1;
		$this->H2 = $H2;
		$this->H3 = $H3;
		$this->H4 = $H4;
		$this->input = $input;
		$this->count = $count;
		return $digest;
	}
}
?>