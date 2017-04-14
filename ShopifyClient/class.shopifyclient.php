<?php
require dirname( __FILE__ ) . '/class.shopifycore.php';

class ShopifyClient extends ShopifyCore{
  public function __construct( $shop_domain, $token, $api_key, $secret ) {
    parent::__construct( $shop_domain, $token, $api_key, $secret );
  }
}