# PHP Request Signer

`arthens/request-signer` is a PHP library to sign and validate requests.

The implementation is inspired by the [AWS Rest Authentication tutorial](http://s3.amazonaws.com/doc/s3-developer-guide/RESTAuthentication.html).

[![Build Status](https://travis-ci.org/arthens/request-signer.svg?branch=master)](https://travis-ci.org/arthens/request-signer)

## Installation

Add `arthens/request-signer` to your `composer.json`.

You can also download `request-signer` directly, as it doesn't have any dependency.

## Usage

```php
// Create a new Signer
$signer = new \Arthens\RequesSigner\Signer('here-your-secret-key');

// Generate a new url-friendly signature
$signature = $signer->sign('GET', '/news');

// Verify a signature
if (!$signer->verify('here-the-signature-from-request', 'GET', '/news')) {
    throw new \Exception('Invalid signature);
}
```
