# Mpesa PHP SDK
Intuitive, Dynamic Mpesa PHP SDK
Supported APIs include STK, C2B, B2C, B2B, as well as balance and status check, and reversal.

## Installation
Install via composer by typing in your terminal

```bash
composer require osenco/mpesa
```

## Instantiating The Class
```php
STK::init(
    array(
        'env'               => 'sandbox',
        'type'              => 4,
        'shortcode'         => '173527',
        'honumber'          => '173527',
        'key'               => Setting::mpesa('key'),
        'secret'            => Setting::mpesa('secret'),
        'username'          => '',
        'passkey'           => Setting::mpesa('passkey'),
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
`setup_mpesa`
`stk_push`
`c2b_request
`b2c_request`
`b2b_request`