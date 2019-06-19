# Sample Controller For Laravel

## The Mpesa Controller

We will need a controller to handle MPesa Transactions and save them to a database table of your choice. See [this example](examples/MpesaController.php) for sample code.

```bash
php artisan make:controller MpesaController
```

### Import Class With Namespace
```php
use Osen\Mpesa\STK;
```

### Instantiating The Class

In your controller's constructor, instantiate the Mpesa API class you want to use by passing configuration options like below: 

```php
STK::init(
  array(
    'env'               => 'sandbox',
    'type'              => 4,
    'shortcode'         => '174379',
    'headoffice'        => '174379',
    'key'               => 'Your Consumer Key',
    'secret'            => 'Your Consumer Secret',
    'passkey'           => 'Your Online Passkey',
    'validation_url'    => url('mpesa/validate'),
    'confirmation_url'  => url('mpesa/confirm'),
    'callback_url'      => url('mpesa/reconcile'),
    'results_url'       => url('mpesa/results'),
    'timeout_url'       => url('mpesa/timeout'),
  )
);
```

## Routing and Endpoints

You can set your laravel routes so as to create endpoints for interaction between Mpesa and your Laravel Installation. Remember to call the respective actions (Mpesa methods) inside your controller methods.

```php
Route::prefix('mpesa')->group(
  function ()
  {
    Route::any('pay', 'MpesaController@pay');
    Route::any('validate', 'MpesaController@validation');
    Route::any('confirm', 'MpesaController@confirmation');
    Route::any('results', 'MpesaController@results');
    Route::any('timeout', 'MpesaController@timeout');
    Route::any('reconcile', 'MpesaController@reconcile');
  }
);
```

### CSRF verification
Remember to add `mpesa/*` to the `$except` array in `app/Http/Middleware/VerifyCsrfToken.php` to whitelist your endpoints so they can receive data from Mpesa.


See [the README](README.md) for making and processing payment requests.
