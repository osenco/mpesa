# The B2C (Business To Customer) API

### Instantiating The Class

Remember to add the Mpesa web portal username when setting up the class. 

````php
B2C::init(
  array(
    'env'               => 'sandbox',
    'type'              => 4,
    'shortcode'         => '174379',
    'key'               => 'Your Consumer Key',
    'secret'            => 'Your Consumer Secret',
    'username'          => '',
    'password'          => '',
    'passkey'           => 'Your Online Passkey',
    'results_url'       => url('mpesa/results'),
    'timeout_url'       => url('mpesa/timeout'),
  )
);
````
## Make payment
```php
B2C::send(function($response)
{
    $ConversationID             = $response["ConversationID"];
    $OriginatorConversationID   = $response["OriginatorConversationID"];
    $ResponseCode               = $response["ResponseCode"];
    $ResponseDescription        = $response["ResponseDescription"];
});
```

## Register Validation/Confirmation URLs
```php
B2C::register();
```

## Process the Payment
```php
B2C::reconcile();
```

See [the README](README.md) for making and processing payment requests.
