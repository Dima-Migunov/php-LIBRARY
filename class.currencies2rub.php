<?php
namespace dmitri;

class Currencies2RUB {

    public static $cache      = FALSE;
    public static $cache_time = 24; // in hours
    public static $cache_file = 'currencies.txt';
    protected static $data;

    public static function get( $cur = 'USD', $amount = 1 )
    {
        $data = array();

        self::listing();

        $data = self::$data['rates'];

        if ( 'USD' == $cur ) {
            $data['result'] = $data['RUB'] * $amount;
            return $data;
        }

        foreach ( self::$data['rates'] as $code => $val ) {
            if ( $code != $cur ) {
                continue;
            }

            $data['result'] = $amount * self::$data['RUB'] / $val;
            return $data;
        }

        return FALSE;
    }

    public static function listing()
    {
        $cache = FALSE;

        if ( self::$cache && file_exists( self::$cache_file ) ) {
            $dtime      = time() - filectime( self::$cache_file );
            $cache_time = self::$cache_time * 3600;

            if ( $dtime <= $cache_time ) {
                $data       = file_get_contents( self::$cache_file );
                self::$data = json_decode( $data, TRUE );
                return TRUE;
            }
        }

        $data       = self::getNewCurrencies();
        self::$data = json_decode( $data, TRUE );

        if ( self::$cache ) {
            file_put_contents( self::$cache_file, $data );
        }
    }

    protected static function getNewCurrencies()
    {
        $data = file_get_contents( 'http://api.fixer.io/latest?base=USD' );
        return $data;
    }

}

