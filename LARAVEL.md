# Sample Controller For Laravel
## The Mpesa Controller
We will need a controller to handle MPesa Transactions and save them to a database table of your choice. See [this example](examples/MpesaController.php) for sample code.

```bash
php artisan make:controller MpesaController
```

or create a file called `MpesaController.php` in the `app/Http/Controllers` and copy the contents of the [sample controller](examples/MpesaController.php) into the newl created file.

### Import Class With Namespace
Put this code at the top of the controller to make the M-PESA class available for use.

```php
use Osen\Mpesa\STK;
```

### Instantiating The Class
In your controller"s constructor, instantiate the Mpesa API class you want to use by passing configuration options like below: 

```php
STK::init(
  array(
    "env"               => "sandbox",
    "type"              => 4,
    "shortcode"         => "174379",
    "headoffice"        => "174379",
    "key"               => "Your Consumer Key",
    "secret"            => "Your Consumer Secret",
    "passkey"           => "Your Online Passkey",
    "validation_url"    => url("lipwa/validate"),
    "confirmation_url"  => url("lipwa/confirm"),
    "callback_url"      => url("lipwa/reconcile"),
    "results_url"       => url("lipwa/results"),
    "timeout_url"       => url("lipwa/timeout"),
  )
);
```

## Routing and Endpoints
You can set your Laravel routes so as to create endpoints for interaction between Mpesa and your Laravel installation. Remember to call the respective actions (Mpesa methods) inside your controller methods.

```php
Route::prefix("lipwa")->group(function ()
{
  Route::any("pay", "MpesaController@pay");
  Route::any("validate", "MpesaController@validation");
  Route::any("confirm", "MpesaController@confirmation");
  Route::any("results", "MpesaController@results");
  Route::any("register", "MpesaController@register");
  Route::any("timeout", "MpesaController@timeout");
  Route::any("reconcile", "MpesaController@reconcile");
  Route::any("reverse", "MpesaController@reverse");
  Route::any("status", "MpesaController@status");
});
```

### CSRF verification
Remember to add `lipwa/*` to the `$except` array in `app/Http/Middleware/VerifyCsrfToken.php` to whitelist your endpoints so they can receive data from Mpesa.

See [the README](README.md) for making and processing payment requests.
