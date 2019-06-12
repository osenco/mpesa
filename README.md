# Mpesa PHP SDK
Intuitive, Dynamic Mpesa PHP SDK
Supported APIs include STK, C2B, B2C, B2B, as well as balance and status check, and reversal.

## Installation
Install via composer by typing in your terminal

```bash
composer require osenco/mpesa
```

For Laravel Usesrs, there is a detailed guide [here](LARAVEL.md) as well as a sample [controller.php](examples/MpesaController.php)

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
/**
 * Define your configuration options and pass them as the argument
*/
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

To make a STK Prompt request, pass the user's phone number, the amount due, and an optional reference(shows up on the user's phone) respectively
```php
/**
 * @param $phone Phone Number (starting with country code e.g 254)
*/
stk_push($phone, $amount, $reference);
```

To process c2b transactions, call the function as follows, passing the user's phone number, the amount due, and an optional reference respectively
```php
/**
 * @param $phone Phone Number (starting with country code e.g 254)
*/
c2b_request($phone, $amount, $reference);
```

To send funds to a customer
```php
/**
 * @param $phone Phone Number
*/
b2c_request();
```

Transfer funds between one business to another
```php
/**
 * Transfer funds between one business to another
*/
b2b_request();
```