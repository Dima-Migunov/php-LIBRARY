<?php
class CURLO{
	const VERSION	= '2018-04-02';

	private static
    $agent  = 'Mozilla/5.0 (Windows; I; Windows NT 5.1; en-GB; rv:1.9.2.13) Gecko/20100101 Firefox/20.0',
    $need_header	= FALSE,
    $follow				= FALSE,
    $referer			= 'http://google.com/',
    $time2connect = 30,
    $timeout			= 30,
    $cookiefile		= '/tmp/cookie.txt';

	public static function send( $url, $data=NULL ){
		touch( self::$cookiefile );
		
		$curl = curl_init ();
		
		curl_setopt( $curl, CURLOPT_URL, $url );
		curl_setopt( $curl, CURLOPT_HEADER, self::$need_header );
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

	public static function get( $url, $need_header=FALSE ){
    self::$need_header = $need_header;
		
		return self::send( $url );
	}

	public static function post( $url, $data, $need_header=FALSE ){
		if( ! $data ){
			return '';
		}
		
		$data	= self::prepareData( $data );
		
    self::$need_header = $need_header;
		
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
  
  public static function set_cookie_file( $absolute_filename ){
    self::$cookiefile = $absolute_filename;

    if( file_exists( $absolute_filename ) ){
      unlink( $absolute_filename );
    }
  }
  
  public static function set_timeout( $timeout=30 ){
    if( is_numeric( $timeout ) ){
      self::$timeout  = $timeout;
    }
  }
  
  public static function need_header( $need_header=TRUE ){
    self::$need_header  = $need_header;
  }
  
  public static function follow_redirect( $follow=TRUE ){
    self::$follow = $follow;
  }
  
  public static function set_referer( $referer ){
    self::$referer  = $referer;
  }
  
  public static function set_agent( $agent ){
    self::$agent  = $agent;
  }
  
  public static function set_time2connect( $time_connect=30 ){
    if( is_numeric( $time_connect ) ){
      self::$time2connect = intval( $time_connect );
    }
  }
}
