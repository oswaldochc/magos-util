<?php
namespace Magos\Util;
class Report
{
	protected static $_appmagos = '/var/www/html/az/module';
	protected static $_appbasepath = '/var/www/html';

	public static function getBridgeMagosVersion($phpbridgeversion) {
		return self::$_appmagos .'/Bridge/'.$phpbridgeversion.'/Java.inc';
		// return 'http://localhost:8082/JavaBridge/java/Java.inc';
	}
	public static function getJavaHost() {
		return "localhost:8082";
	}
	public static function getUser() {
		return 'user';
	}
	public static function getPassword() {
		return 'pass';
	}
	public static function getDriver() {
		return 'org.postgresql.Driver';
	}
    public static function getConexion() {
		$domain = $_SERVER['HTTP_HOST'];
		return 'jdbc:postgresql://'.(($domain == '172.30.92.240') ? '172.30.92.228:5434' : '192.168.0.110:5433').'/base';
	}
	public static function getBasePath(){
		return self::$_appbasepath;
	}
	public static function getHttpdTmp(){
		return '/httpdtmp';
	}
}
