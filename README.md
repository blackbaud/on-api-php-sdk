[![Build Status](https://travis-ci.org/blackbaud/on-api-php-sdk.svg?branch=master)](https://travis-ci.org/blackbaud/on-api-php-sdk)

## Features

* Provides easy-to-use HTTP clients for all supported ON API methods and authentication
  protocols.
* Currently built on Curl
* Provides authentication and simple token management

## Getting Started

1. **Have a Web Service API user** – Before you begin, you need to
   sign up for the onSDK and have a Manager account created.
1. **Minimum requirements** – To run the SDK, your system will need to meet the
   [minimum requirements][docs-requirements], including having **PHP >= 5.5**
   compiled with the cURL extension and cURL 7.16.2+ compiled with a TLS
   backend (e.g., NSS or OpenSSL).
1. **Install the SDK** – Using [Composer] is the recommended way to install the
   Blackbaud onSDK for PHP. The SDK is available via [Packagist] under the
   [`blackbaud/onsdk`][install-packagist] package. 

## Quick Examples

### Authenticate with API

```php
<?php
// Require the Composer autoloader.
require 'vendor/autoload.php';

use Blackbaud\onSDK\onApiClient;

// Instantiate a Blackbaud Client.
$bb = new onApiClient('SchoolUrl','Username','Password');
```

### Get User Info

```php
<?php
// get info about the current user account using the SDK.
try {
    $bb->get_current_user();
    print_r($bb);
} catch (Exception $e) {
    echo "There was an error getting user info.\n";
}
```



[install-packagist]: https://packagist.org/packages/blackbaud/onsdk
[composer]: http://getcomposer.org
[packagist]: http://packagist.org
