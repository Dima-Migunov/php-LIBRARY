<?php
// previous version https://github.com/cmcdonaldca/ohShopify.php
// Lightweight object-oriented PHP (JSON) client for the Shopify API

class Shopify{
  public  static $version	= '2017-04-14';

  private static $instance  = NULL;
  private static $SHOP_DOMAIN, $TOKEN, $API_KEY, $SECRET;

  public static function init( $shop_domain, $token, $api_key, $secret ){
    self::$SHOP_DOMAIN  = $shop_domain;
    self::$TOKEN        = $token;
    self::$API_KEY      = $api_key;
    self::$SECRET       = $secret;
    
    $dir  = dirname( __FILE__ );
    require $dir . '/class.shopifyclient.php';
  }
  
  public static function client(){
    if( !self::$SHOP_DOMAIN ){
      return NULL;
    }
    
    if( !self::$instance ){
      self::$instance = new ShopifyClient( $shop_domain, $token, $api_key, $secret );
    }
    
    return self::$instance;
  }
}
