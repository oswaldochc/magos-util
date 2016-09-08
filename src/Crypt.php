<?php
namespace Magos\Util;

class Crypt
{
	public static function encrypt($string, $key='') {
		if ($key == ''){
			$key = 'aGFja2VyOnA0c3N3MHJk';
		}
		return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $string, MCRYPT_MODE_CBC, md5(md5($key))));
	}

	public static function desencrypt($string, $key='') {
		if ($key == ''){
			$key = 'aGFja2VyOnA0c3N3MHJk';
		}
		return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($string), MCRYPT_MODE_CBC, md5(md5($key))), "\0");
	}

	public static function getIpUser() {
		$ip = "";
		if(isset($_SERVER)) {
			if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
				$ip=$_SERVER['HTTP_CLIENT_IP'];
			} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
			} else {
				$ip=$_SERVER['REMOTE_ADDR'];
			}
		} else {
			if ( getenv( 'HTTP_CLIENT_IP' ) ) {
				$ip = getenv( 'HTTP_CLIENT_IP' );
			} elseif ( getenv( 'HTTP_X_FORWARDED_FOR' ) ) {
				$ip = getenv( 'HTTP_X_FORWARDED_FOR' );
			} else {
				$ip = getenv( 'REMOTE_ADDR' );
			}
		}
		if(strstr($ip,',')) {
			$ip = array_shift(explode(',',$ip));
		}
		return $ip;
	}
}
