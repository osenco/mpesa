# Sample Controller For Laravel

## The Mpesa Controller

We will need a controller to handle MPesa Transactions and save them to a database table of your choice. See examples/MpesaController.php for sample code

```bash
php artisan make:controller MpesaController
```

### Import Class Namespace
```php
use Osen\Mpesa\STK;
```

### Instantiating The Class

In your controller's constructor, instantiate the Mpesa API class you want to use by passing configuration options like below: 

````php
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
````

### Making A Payment Request
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

## Routing and Endpoints

You can set your laravel routes so as to create endpoints for interaction between Mpesa and your Laravel Installation

```php
Route::prefix('mpesa')->group(function ()
{
  Route::get('pay', 'MpesaController@pay');
  Route::get('validate', 'MpesaController@validation');
  Route::get('confirm', 'MpesaController@confirmation');
});
```

Note that you need to call the API validation and confirmation methods (see examples/MpesaController.php)