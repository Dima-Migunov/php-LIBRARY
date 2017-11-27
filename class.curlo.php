<?php
class CURLO{
	public static $version	= '2015-11-04';

	public static $agent				= 'Mozilla/5.0 (Windows; I; Windows NT 5.1; en-GB; rv:1.9.2.13) Gecko/20100101 Firefox/20.0';
	public static $get_header		= FALSE;
	public static $follow				= FALSE;
	public static $referer			= 'http://google.com/';
	public static $time2connect	= 30;
	public static $timeout			= 30;
	public static $cookiefile		= '/tmp/cookie.txt';

	public static function send( $url, $data=NULL ){
		touch( self::$cookiefile );
		
		$curl = curl_init ();
		
		curl_setopt( $curl, CURLOPT_URL, $url );
		curl_setopt( $curl, CURLOPT_HEADER, self::$get_header );
		curl_setopt( $curl, CURLOPT_USERAGENT, self::$agent );
		curl_setopt( $curl, CURLOPT_AUTOREFERER, true );
		curl_setopt( $curl, CURLOPT_FAILONERROR, true );
		curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, self::$follow );
		curl_setopt( $curl, CURLOPT_MAXREDIRS, 5 );
		curl_setopt( $curl, CURLOPT_REFERER, self::$referer );
		curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, self::$time2connect );
		curl_setopt( $curl, CURLOPT_TIMEOUT, self::$timeout );
		curl_setopt( $curl, CURLOPT_COOKIEFILE, self::$cookiefile );
		curl_setopt( $curl, CURLOPT_COOKIEJAR, self::$cookiefile );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 0 );
		
		if ( $data ){
			curl_setopt ( $curl, CURLOPT_POST, 1 );
			curl_setopt ( $curl, CURLOPT_POSTFIELDS, $data );
		}
		
		curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 );
		
		$result = curl_exec ( $curl);
		
		curl_close ( $curl );
		
		return $result;
	}

	public static function get( $url, $get_header=NULL ){
		if ( $get_header !== NULL ){
			self::$get_header = $get_header;
		}
		
		return self::send( $url );
	}

	public static function post( $url, $data, $get_header=NULL ){
		if( ! $data ){
			return '';
		}
		
		$data	= self::prepareData( $data );
		
		if ( $get_header !== null ){
			self::$get_header = $get_header;
		}
		
		return self::send( $url, $data );
	}
	
	protected static function prepareData( $data ){
		if( !is_array( $data ) ){
			return $data;
		}
		
		$str	= [];
		
		foreach ( $data as $key=>$value ){
			$str[]	= $key . '=' . $value;
		}
		
		$str	= implode( '&', $str );
		
		return $str;
	}
}
