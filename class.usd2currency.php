<?php
class USD2Currency{
	public static $cache      = FALSE;
	public static $cache_time = 24; // in hours
	public static $cache_file = 'currencies.txt';
	protected static $data;
	
	public static function get( $cur='EUR', $amount=1 ){
		$data = array();
    
		self::listing();
    
    $data = self::$data['rates'];
    
    if( 'USD' == $cur ){
      $data['result'] = $amount;
      return $data;
    }
    
    foreach ( self::$data['rates'] as $code=>$val ){
      if( $code != $cur ){
        continue;
      }

      $data['result'] = $amount * $val;
      return $data;
		}
		
    return FALSE;
	}
	
	public static function listing(){
		$data = self::getNewCurrencies();
		self::$data = json_decode( $data, TRUE );
	}
	
	protected static function getNewCurrencies(){
    $data = self::getCacheData();
    
    if( $data ){
      return $data;
    }

    $data  = file_get_contents( 'https://api.fixer.io/latest?base=USD' );

    if ( self::$cache ){
      file_put_contents( self::$cache_file, $data );
    }

    return $data;
	}
  
  protected static function getCacheData(){
    if ( ! self::$cache || ! file_exists( self::$cache_file ) ){
      return NULL;
    }
    
    $dtime      = time() - filectime( self::$cache_file );
    $cache_time = self::$cache_time * 3600;

    if( $dtime > $cache_time ){
      return NULL;
    }
    
    $data = file_get_contents( self::$cache_file );

    if( $data ){
      return $data;
    }
    
    return NULL;
  }
}
