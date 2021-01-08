# Sample Controller For Laravel
## The M-PESA Controller
We will need a controller to handle MPesa Transactions and save them to a database table of your choice. See [this example](examples/MpesaController.php) for sample code.

```bash
php artisan make:controller MpesaController
```

or create a file called `MpesaController.php` in the `app/Http/Controllers` and copy the contents of the [sample controller](examples/MpesaController.php) into the newl created file.

### Import Class With Namespace
Put this code at the top of the controller (in the class imports) to make the M-PESA class available for use.

```php
use Osen\Mpesa\C2B;
```

### Instantiating The Class
In your controller"s constructor, instantiate the M-PESA API class you want to use by passing configuration options like below: 

```php
C2B::init(
  array(
    "env"               => "sandbox",
    "type"              => 4,
    "shortcode"         => "174379",
    "headoffice"        => "174379",
    "key"               => "Your Consumer Key",
    "secret"            => "Your Consumer Secret",
    "passkey"           => "Your Online Passkey",
    "validation_url"    => url("api/lipwa/validate"),
    "confirmation_url"  => url("api/lipwa/confirm"),
    "callback_url"      => url("api/lipwa/reconcile"),
    "results_url"       => url("api/lipwa/results"),
    "timeout_url"       => url("api/lipwa/timeout"),
  )
);
```

## Routing and Endpoints
You can set your Laravel routes so as to create endpoints for interaction between M-PESA and your Laravel installation. Remember to call the respective actions (M-PESA methods) inside your controller methods.

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
Remember to add `api/lipwa/*` to the `$except` array in `app/Http/Middleware/VerifyCsrfToken.php` to whitelist your endpoints so they can receive data from M-PESA.

See [the README](README.md) for making and processing payment requests.
