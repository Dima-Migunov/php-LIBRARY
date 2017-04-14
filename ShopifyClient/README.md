# shopify.php

Lightweight multi-paradigm PHP (JSON) client for the [Shopify API](http://api.shopify.com/).


## Requirements

* PHP 4 with [cURL support](http://php.net/manual/en/book.curl.php).
* Only compatible OAuth Shopify Apps.  For Legacy Authentication: [Use an old version of ohShopify.php](https://github.com/cmcdonaldca/ohShopify.php/blob/7ee7a344ca83518a0560ba585d4f8deab65bf5cd/shopify.php)


## Getting Started

Basic needs for authorization and redirecting

```php

<?php
require 'class.shopify.php';

// if they posted the form with the shop name
$shop = $_POST['shop'];

if ( !$shop ) {
  $shop = $_GET['shop'];
}

Shopify::init( $shop, $_SESSION['shopify_token'], $SHOPIFY_API_KEY, $SHOPIFY_SECRET );

/* Define requested scope (access rights) - checkout https://docs.shopify.com/api/authentication/oauth#scopes 	 */
define( 'SHOPIFY_SCOPE', 'place here your scope' ); //eg: define('SHOPIFY_SCOPE','read_orders,write_orders');

if ( $shop && isset( $_GET['code'] ) ) { // if the code param has been sent to this page... we are in Step 2
  // Step 2: do a form POST to get the access token
  session_unset();

  // Now, request the token and store it in your session.
  $_SESSION['token'] = Shopify::client()->getAccessToken( $_GET['code'] );

  if ( $_SESSION['token'] != '' ) {
    $_SESSION['shop'] = $shop;
  }

  header( 'Location: index.php' );
  exit;
}

if ( $shop ) {
  // Step 1: get the shopname from the user and redirect the user to the
  // shopify authorization page where they can choose to authorize this app

  // get the URL to the current page
  $pageURL = 'http';

  if ( 'on' == $_SERVER['HTTPS'] ) {
    $pageURL .= 's';
  }

  $pageURL .= '://' . $_SERVER['SERVER_NAME'];

  if ( '80' == $_SERVER['SERVER_PORT'] ) {
    $pageURL .= $_SERVER['SCRIPT_NAME'];
  }
  else {
    $pageURL .= ':' . $_SERVER['SERVER_PORT'] . $_SERVER['SCRIPT_NAME'];
  }

  // redirect to authorize url
  header( 'Location: ' . Shopify::client()->getAuthorizeUrl( SHOPIFY_SCOPE, $pageURL ) );
  exit;
}

// first time to the page, show the form below
?>
<p>Install this app in a shop to get access to its private admin data.</p> 

<p style="padding-bottom: 1em;">
  <span class="hint">Don&rsquo;t have a shop to install your app in handy? <a href="https://app.shopify.com/services/partners/api_clients/test_shops">Create a test shop.</a></span>
</p> 

<form action="" method="post">
  <label for='shop'><strong>The URL of the Shop</strong> 
    <span class="hint">(enter it exactly like this: myshop.myshopify.com)</span> 
  </label> 
  <p> 
    <input id="shop" name="shop" size="45" type="text" value="" /> 
    <input name="commit" type="submit" value="Install" /> 
  </p> 
</form>

```

Once you have authorized and stored the token in the session, you can make API calls

Making API calls:

```php
<?php

require 'class.shopify.php';

$shop = $_POST['shop'];

if ( !$shop ) {
  $shop = $_GET['shop'];
}

Shopify::init( $shop, $_SESSION['shopify_token'], $SHOPIFY_API_KEY, $SHOPIFY_SECRET );

try {
  // Get all products
  $products = Shopify::client()->call( 'GET', '/admin/products.json',
                                        array( 'published_status' => 'published' ) );

  // Create a new recurring charge
  $charge = array(
    'recurring_application_charge' => array(
      'price'      => 10.0,
      'name'       => 'Super Duper Plan',
      'return_url' => 'http://super-duper.shopifyapps.com',
      'test'       => TRUE
    )
  );

  try {
    $recurring_application_charge = Shopify::client()->call( 'POST',
                                                              '/admin/recurring_application_charges.json',
                                                              $charge );

    // API call limit helpers
    echo Shopify::client()->callsMade(); // 2
    echo Shopify::client()->callsLeft(); // 498
    echo Shopify::client()->callLimit(); // 500
  }
  catch ( ShopifyApiException $e ) {
    // If you're here, either HTTP status code was >= 400 or response contained the key 'errors'
  }
}
catch ( ShopifyApiException $e ) {
  /*
    $e->getMethod() -> http method (GET, POST, PUT, DELETE)
    $e->getPath() -> path of failing request
    $e->getResponseHeaders() -> actually response headers from failing request
    $e->getResponse() -> curl response object
    $e->getParams() -> optional data that may have been passed that caused the failure

   */
}
catch ( ShopifyCurlException $e ) {
  // $e->getMessage() returns value of curl_errno() and $e->getCode() returns value of curl_ error()
}
?>
```

When receiving requests from the Shopify API, validate the signature value:

```php
<?php
  Shopify::init( $_GET['shop'], '', $SHOPIFY_API_KEY, $SHOPIFY_SECRET );

  if( !Shopify::client()->validateSignature( $_GET ) ){
    die('Error: invalid signature.');
  }

?>

or you can by another

<?php

  $sc = new ShopifyClient( $_GET['shop'], '', $SHOPIFY_API_KEY, $SHOPIFY_SECRET );

  if( !$sc->validateSignature( $_GET ) ){
    die('Error: invalid signature.');
  }

?>
```

