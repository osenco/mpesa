# Mpesa PHP SDK
Intuitive, Dynamic Mpesa PHP SDK
Supported APIs include STK, C2B, B2C, B2B, as well as balance and status check, and reversal.

## Installation
Install via composer by typing in your terminal

```bash
composer require osenco/mpesa
```

For Laravel Usesrs, there is a detailed guide [here](LARAVEL.MD) as well as a sample [controller.php](examples/MpesaController.php)

### Import Class Namespace
Import the class namespace into your class or app to make it available for use. Replace STK with your API of choice
```php
use Osen\Mpesa\STK;
```

## Instantiating The Class 
```php
STK::init(
    array(
        'env'               => 'sandbox',
        'type'              => 4,
        'shortcode'         => '173527',
        'honumber'          => '173527',
        'key'               => 'Your Consumer Key',
        'secret'            => 'Your Consumer Secret',
        'username'          => '',
        'passkey'           => 'Your Online Passkey',
        'validation_url'    => url('mpesa/validate'),
        'confirmation_url'  => url('mpesa/confirm'),
        'callback_url'      => url('mpesa/reconcile'),
        'timeout_url'       => url('mpesa/timeout'),
    )
);
```

## Making A Payment Request
Wrap your request in a try catch to ensure proper error handling

```php
try {
    return $res = STK($phone, $amount, $reference);
} catch (\Throwable $th) {
    return $th
}
```
## Helper Functions

You can use our helper functions for shorter code
```php
$config = array(
    'env'               => 'sandbox',
    'type'              => 4, // or 1, 2
    'shortcode'         => '173527',
    'honumber'          => '173527',
    'key'               => 'Your Consumer Key',
    'secret'            => 'Your Consumer Secret',
    'username'          => '',
    'passkey'           => 'Your Online Passkey',
    'validation_url'    => url('mpesa/validate'),
    'confirmation_url'  => url('mpesa/confirm'),
    'callback_url'      => url('mpesa/reconcile'),
    'timeout_url'       => url('mpesa/timeout'),
);
setup_mpesa($config);
```
```php
stk_push($phone, $amount, $reference);
```
```php
c2b_request($phone, $amount, $reference);
```
```php
b2c_request
```
```php
b2b_request
```