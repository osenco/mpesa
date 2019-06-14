# The C2B (Customer To Business) API

### Instantiating The Class

Remember to add the Mpesa web portal username when setting up the class. 

````php
C2B::init(
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
````

## Register Validation/Confirmation URLs
```php
C2B::register();
```

## Process the Payment
```php
C2B::reconcile();
```

See [the README](README.md) for making and processing payment requests.
