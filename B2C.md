# The B2C (Business To Customer) API

### Instantiating The Class

Remember to add the Mpesa web portal username when setting up the class. 

````php
B2C::init(
  array(
    'env'               => 'sandbox',
    'type'              => 4,
    'shortcode'         => '174379',
    'headoffice'        => '174379',
    'key'               => 'Your Consumer Key',
    'secret'            => 'Your Consumer Secret',
    'username'          => '',
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
B2C::register();
```

## Process the Payment
```php
B2C::reconcile();
```

See [the README](README.md) for making and processing payment requests.
