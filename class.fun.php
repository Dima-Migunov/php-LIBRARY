<?php
// Use like this:

//class FUN{
//  use FunCore;
//}


trait FunCore {
	static $version	= '2016-10-06';

	// Checkers
	static function checkEmail( $email ) {
		return preg_match( '/^[\.A-z0-9_\-\+]+[@][A-z0-9_\-]+([.][A-z0-9_\-]+)+[A-z]{1,4}$/', $email );
	}
	
	static function isValidEmail( $email ){
		return self::checkEmail( $email );
	}

	// Converters
	static function only09az( $str ) {
		return preg_replace( '#([^0-9A-z])#Usi', '', $str );
	}

	// Object to Array
	static function object2array( $object ) {
		return @json_decode( @json_encode( $object ), TRUE );
	}
	
	static function objectToArray( $object ){
		return object2array( $object );
	}

	// XML to Array
	static function xml2array( $xml ) {
		$xml = simplexml_load_string( $xml, 'SimpleXMLElement', LIBXML_NOCDATA );
		return self::object2array( $xml );
	}
	
	static function xmlToArray( $xml ){
		return xml2array( $xml );
	}

	// Convert link in text into hyperlink
	static function url2link( $text, $class=NULL, $prefix=NULL, $postfix=NULL ) {
		$re = '#((?:https?|ftps?)://[0-9a-zA-Z\.\-\_]+(/\S*)?)#msi';
		
		if ( $class ){
			$class = " class=\"$class\"";
		}
		
		$text = preg_replace( $re, '<a href="$1"' . $class . '>' . $prefix . '$1' . $postfix . '</a>', $text );
		
		return $text;
	}

	// Translit for Cyrillic
	static function translit( $stroka ) {
		$converter = array(
			'а' => 'a', 'б' => 'b', 'в' => 'v',
			'г' => 'g', 'д' => 'd', 'е' => 'e',
			'ё' => 'e', 'ж' => 'zh', 'з' => 'z',
			'и' => 'i', 'й' => 'y', 'к' => 'k',
			'л' => 'l', 'м' => 'm', 'н' => 'n',
			'о' => 'o', 'п' => 'p', 'р' => 'r',
			'с' => 's', 'т' => 't', 'у' => 'u',
			'ф' => 'f', 'х' => 'h', 'ц' => 'c',
			'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
			'ь' => "'", 'ы' => 'y', 'ъ' => "'",
			'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
			'А' => 'A', 'Б' => 'B', 'В' => 'V',
			'Г' => 'G', 'Д' => 'D', 'Е' => 'E',
			'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z',
			'И' => 'I', 'Й' => 'Y', 'К' => 'K',
			'Л' => 'L', 'М' => 'M', 'Н' => 'N',
			'О' => 'O', 'П' => 'P', 'Р' => 'R',
			'С' => 'S', 'Т' => 'T', 'У' => 'U',
			'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
			'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch',
			'Ь' => "'", 'Ы' => 'Y', 'Ъ' => "'",
			'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',
		);
		
		return strtr( $stroka, $converter );
	}

	static function urlTranslit( $title, $end=NULL ) {
		if ( !preg_match( '/[^A-Za-z0-9_\-]/', $title ) ){
			return $title;
		}

		$title = trim( $title );
		
		// if text contains few sentences, take first sentence.
		if ( preg_match( '#^\.?([^\.]*)#ui', $title, $matches ) && mb_strlen( $matches[1] ) > 5 ){
			$title = $matches[1];
		}

		$title = self::translit( $title );
		$title = str_ireplace( array( '-', ' ' ), '_', $title );
		$title = preg_replace( "#[^A-Za-z0-9_\-]#ui", '', $title );

		if ( $end ){
			$title .= "-" . self::urlTranslit( $end );
		}

		return $title;
	}

	// convert number to qwerty code
	static function q36encode( $A, $q = '' ) {
		if ( !is_numeric( $A ) ){
			$A = 0;
		}
		
		$qwerty = array( 'q', 'w', 'e', 'r', 't', 'y', 'u', 'i', 'o', 'p', 'a',
			's', 'd', 'f', 'g', 'h', 'j', 'k', 'l', '1', '2', '3', '4', '5', '6',
			'7', '8', '9', '0', 'z', 'x', 'c', 'v', 'b', 'n', 'm' );
		
		if ( $A < 36 ) {
			$q = $qwerty[$A] . $q;
			return $q;
		}
		
		$Q = floor( $A / 36 );
		$q = $qwerty[ $A - $Q * 36 ] . $q;
		
		return self::q36encode( $Q, $q );
	}

	// convert qwerty code to number
	static function q36decode( $code ) {
		$qwerty = array( 'q', 'w', 'e', 'r', 't', 'y', 'u', 'i', 'o', 'p', 'a',
			's', 'd', 'f', 'g', 'h', 'j', 'k', 'l', '1', '2', '3', '4', '5', '6',
			'7', '8', '9', '0', 'z', 'x', 'c', 'v', 'b', 'n', 'm' );

		$number	= 0;
		$code		= preg_replace( "#([^0-9A-z])#Usi", '', $code );
		$length	= strlen( $code ) - 1;
		
		if ( $length < 0 ){
			return 0;
		}

		$code = preg_split( '//', $code, -1, PREG_SPLIT_NO_EMPTY );
		
		for ( $i = 0; $i < $length; $i++ ) {
			$number += array_search( $code[$i], $qwerty ) * pow( 36, $length - $i );
		}
		
		$number += array_search( $code[$length], $qwerty );
		
		return $number;
	}

	// OUTPUT
	static function trace( $arr, $display = true ) {
		$arr = print_r( $arr, true );
		
		if ( $display ){
			echo '<pre>' . $arr . '</pre>';
		}
		
		return $arr;
	}

	/**
	 * Return unicode char by its code
	 *
	 * @param int $u
	 * @return char
	 */
	static function mb_chr( $u ) {
		return mb_convert_encoding( '&#' . intval( $u ) . ';', 'UTF-8', 'HTML-ENTITIES' );
	}

	/**
	 * Return code by it's unicode char
	 *
	 * @param char $char
	 * @return int
	 */
	static function mb_ord( $char ) {
		return hexdec( bin2hex( $char ) );
	}

	/**
	 * Return trimed string for unicode
	 *
	 */
	static function mb_trim( $str ) {
		return preg_replace( "#(^\s+|\s+$)#us", '', $str );
	}

	// output in JSON
	static function echoJSON( $data, $exit=TRUE ) {
		header( 'Content-type: application/json' );
    $data = json_encode( $data );
    
    if( $exit ) exit( $data );
		
    echo $data;
	}

	// output in TEXT
	static function echoText( $data ) {
		header( 'Content-type: text/plain' );
		echo $data;
	}

	// ACTIONS
	static function send_mail( $from, $to, $subject, $body ) {
		$headers = '';
		$headers .= "From: $from\n";
		$headers .= "Reply-to: $from\n";
		$headers .= "Return-Path: $from\n";
		$headers .= "Message-ID: <" . md5( uniqid( time() ) ) . "@" . $_SERVER['SERVER_NAME'] . ">\n";
		$headers .= "MIME-Version: 1.0\n";
		$headers .= "Date: " . date( 'r', time() ) . "\n";

		mail( $to, $subject, $body, $headers );
	}

	static function redirect( $url='/', $permanent=FALSE ) {
    if( $permanent ){
      header("HTTP/1.1 301 Moved Permanently");
    }
    
		header( 'Location: ' . $url, FALSE );
		exit;
	}

	// create Cookie
	static function createCookie( $nameCook, $value, $period ) {
		$host = $_SERVER['HTTP_HOST'];
		
		if ( $_SERVER['HTTP_X_FORWARDED_HOST'] ){
			$host = $_SERVER['HTTP_X_FORWARDED_HOST'];
		}

		$matches	= array();
		$host			= str_ireplace( 'www.', '', $host );
		
		preg_match_all( "#(\.)#", $host, $matches );
		
		if ( 1== count( $matches[1] ) ){
			$host = '.' . $host;
		}

		setcookie( $nameCook, $value, time() + $period, '/', $host );
		$_COOKIE[$nameCook] = $value;
	}

	// delete Cookie
	static function deleteCookie( $nameCook ) {
		unset( $_COOKIE[$nameCook] );
		
		$host = $_SERVER['HTTP_HOST'];
		
		if ( $_SERVER['HTTP_X_FORWARDED_HOST'] ){
			$host = $_SERVER['HTTP_X_FORWARDED_HOST'];
		}
		
		setcookie( $nameCook, sha1( date( 'r' ) ), time() - 3600 * 24 * 14, '/', $host );
	}

	// cut text
	static function text_substr( $text, $maxlen, $ost='...' ) {
		$text	= trim( $text );
		$len	= mb_strlen( $text, 'UTF-8' );
		
		if ( $len <= $maxlen ){
			$ost = '';
		}
		
		$text = mb_substr( $text, 0, $maxlen, 'UTF-8' ) . $ost;
		return $text;
	}

	// cut HTML text
	static function html_substr( $html, $maxlen, $ost='...' ) {
		$html	= trim( $html );
		$len	= mb_strlen( $html, 'UTF-8' );
		
		if ( $len <= $maxlen ){
			$ost = '';
		}
		
		$html = mb_substr( $html, 0, $maxlen, 'UTF-8' );
		$html = preg_replace( '#</?$#uU', '', $html ) . $ost;
		
		return self::html_repair( $html );
	}

	// repair HTML text
	static function html_repair( $html ) {
		$html = "<html>$html</html>";

		$config	= array( 'show-body-only' => TRUE, 'output-html' => TRUE );
		$tidy		= tidy_parse_string( $html, $config, 'UTF8' );
		
		$tidy->cleanRepair();
		
		$html = $tidy->value;
		return $html;
	}

	// unique row in 41 symbols (32 symbols if $sha1=FALSE)
	static function idhash( $secret='', $sha1=TRUE ) {
		$secret .= date( 'r' ) . uniqid( strval( mt_rand() ), TRUE );

		if ( $sha1 ){
			return sha1( $secret );
		}

		return md5( $secret );
	}

	static function passwordGenerator( $length=8, $any_symbols=TRUE ) {
    if( ! $any_symbols ){
			$password = self::idhash( NULL, TRUE );
  		$password = substr( $password, 0, $length );
      return $password;
    }
    
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?';
    $password     = str_shuffle( $chars );
    $chars_length = strlen( $chars );
    $buffer       = [];

    for( $i=0; $i<$length; $i++ ){
      $n  = rand( 1, $chars_length ) - 1;
      $buffer[] = $chars[ $n ];
    }

    return implode( '', $buffer );
	}

	// age of file in seconds
	static function ageFile( $filename ) {
		if ( !file_exists( $filename ) ){
			return FALSE;
		}
		
		$age = time() - filectime( $filename );
		return $age;
	}

	static function deleteFile( $filename ) {
		if ( !file_exists( $filename ) ){
			return FALSE;
		}
		
		unlink( $filename );
		return TRUE;
	}

	// my host http://www.domain.com
	static function myHost() {
		$host = 'http';

		if ( $_SERVER['HTTPS'] && $_SERVER['HTTPS'] != 'off' ) {
			$host .= 's';
		}

		$host .= '://' . $_SERVER['HTTP_HOST'];
		return $host;
	}

	// maxExecutionTime into UNLIMITED
	static function phpExecutionTimeUnlimited() {
		set_time_limit( 0 );
	}

	// DISPLAYS COMMENT POST TIME AS "1 year, 1 week ago" or "5 minutes, 7 seconds ago", etc...
	static function timeAgo( $date, $granularity=2 ) {
		if( is_string( $date ) ){
			$date	= strtotime( $date );
		}
		
		$difference	= time() - $date;
		
		$periods = array(
			'decade'	=> 315360000,
			'year'		=> 31536000,
			'month'		=> 2628000,
			'week'		=> 604800,
			'day'			=> 86400,
			'hour'		=> 3600,
			'minute'	=> 60,
			'second'	=> 1
		);

		foreach ( $periods as $key => $value ) {
			if ( $difference >= $value ) {
				$time = floor( $difference / $value );
				$difference %= $value;
				$retval .= ($retval ? ' ' : '') . $time . ' ';
				$retval .= (($time > 1) ? $key . 's' : $key);
				$granularity--;
			}
			
			if ( '0' == $granularity ) {
				break;
			}
		}
		
		return ' posted ' . $retval . ' ago';
	}
	
	static function headerNoCache(){
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
	}
	
	// Function to get the client IP address
	static function get_client_ip() {
		if ( $_SERVER['HTTP_CLIENT_IP'] ){
			return $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
		}
		
		if( $_SERVER['HTTP_X_FORWARDED_FOR'] ){
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		
		if( $_SERVER['HTTP_X_FORWARDED'] ){
			return $_SERVER['HTTP_X_FORWARDED'];
		}
		
		if( $_SERVER['HTTP_FORWARDED_FOR'] ){
			return $_SERVER['HTTP_FORWARDED_FOR'];
		}
		
		if( $_SERVER['HTTP_FORWARDED'] ){
			return $_SERVER['HTTP_FORWARDED'];
		}
		
		if( $_SERVER['REMOTE_ADDR'] ){
			return $_SERVER['REMOTE_ADDR'];
		}
		
		return NULL;
	}

	public static function dateMysqlToUnix( $mysqldate ){
		if ( is_numeric( $mysqldate ) ){
			return $mysqldate;
		}
		
		return strtotime( $mysqldate );
	}
	
	public static function dateUnixToMysql( $unixdate ){
		if ( !$unixdate ){
			$unixdate = time();
		}
		
		if ( !is_numeric( $unixdate ) ){
			return $unixdate;
		}
		
		return date( 'Y-m-d H:i:s', $unixdate );
	}
  
  function time_elapsed_string( $datetime, $full=FALSE ) {
    $now  = new DateTime;
    $ago  = new DateTime ($datetime );
    $diff = $now->diff( $ago );

    $diff->w = floor( $diff->d / 7 );
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    
    foreach ($string as $k => &$v) {
        if ( $diff->$k ) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            continue;
        }
        
        unset($string[$k]);
    }

    if ( !$full ){
      $string = array_slice($string, 0, 1);
    }
    
    if( ! $string ){
      return 'just now';
    }
    
    return implode(', ', $string) . ' ago';
  }
  
  public static function niceNumber( $number, $russian=TRUE ){
    // first strip any formatting;
    $number = ( 0+ str_replace( ',', '', $number ) );

    // is this a number?
    if ( ! is_numeric( $number ) ){
      return FALSE;
    }

    $words  = array(
      'trillion'  => 'триллионов',
      'billion'   => 'миллиардов',
      'million'   => 'миллионов',
      'thousand'  => 'тысяч'
    );
    
    // now filter it;
    if ( $number > 1000000000000 ){
      $nice_word  = 'trillion';
      
      if( $russian ){
        $nice_word  = $words[ $nice_word ];
      }
      
      return round( ( $number/1000000000000 ), 2 ) . ' ' . $nice_word;
    }
    
    if ( $number > 1000000000 ){
      $nice_word  = 'billion';
      
      if( $russian ){
        $nice_word  = $words[ $nice_word ];
      }
      
      return round( ( $number/1000000000 ), 2 ) . ' ' . $nice_word;
    }
    
    if ( $number > 1000000 ){
      $nice_word  = 'million';
      
      if( $russian ){
        $nice_word  = $words[ $nice_word ];
      }
      
      return round( ( $number/1000000 ), 2 ) . ' ' . $nice_word;
    }
    
    if ( $number > 1000 ){
      $nice_word  = 'thousand';
      
      if( $russian ){
        $nice_word  = $words[ $nice_word ];
      }
      
      return round( ( $number/1000 ), 2 ) . ' ' . $nice_word;
    }

    return number_format( $number );
  }
  
  public static function test(){
    echo self::passwordGenerator( 8 );
  }
}
