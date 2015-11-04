<?php
class AntiHacker{
	public static $version	= '2015-11-04';

	public static function start(){
		self::replace_hackword( $_POST );
		self::replace_hackword( $_GET );
		self::replace_hackword( $_COOKIE );
	}

	private static function replace_hackword( &$ar ){
		foreach ( $ar as $key => $vv ){

			if( is_array( $vv ) ){
				self::replace_hackword( $vv );
				return TRUE;
			}
			
			$key	= self::hackstring( $key );
			$vv		= self::hackstring( $vv );
			
			$ar[$key] = strip_tags( $vv );
		}
	}

	private static function hackstring( $stoka ){
		$stoka = preg_replace('%&\s*\{[^}]*(\}\s*;?|$)%', '', $stoka);
		
		if(!get_magic_quotes_gpc()) {
			// replace "\" => "\\"
			$stoka = addslashes( $stoka );
		}
		
		$stoka = htmlentities( $stoka, ENT_QUOTES, 'UTF-8' );
		return $stoka;
	}
}
