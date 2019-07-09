# The C2B (Customer To Business) API
This API enables Paybill and Buy Goods merchants to integrate to M-Pesa and receive real time payments notifications. A user pays through the traditional payment process (i.e goes to M-Pesa menu on their phone and makes the payment to your shortcode). The transaction details are then sent to your app.

This could come in handy and work as a backup to STK push, should the prompt fail, either because the user has not enabled their SIM, or it timed out before they saw it. In this case you should display appropriate instructions for the user to make this payment, making sure to supply your shortcode, and account number (for Paybills).

## Import Class With Namespace
Import the class namespace into your class or app to make it available for use.

```php
use Osen\Mpesa\C2B;
```

## Instantiating The Class
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

## The Validation/Confirmation URLs
Whenever M-Pesa receives a transaction on your shortcode, a validation request is sent to the validation URL registered above. M-Pesa completes or cancels the transaction depending on the validation response it receives.

These URLs must be HTTPS in production. Validation is an optional feature that needs to be activated on M-Pesa, the owner of the shortcode needs to make this request for activation. This can be done by sending an email to [apisupport@safaricom.co.ke](mailto:apisupport@safaricom.co.ke), or through a chat on the [developer portal](https://developer.safaricom.co.ke).

###  Register Validation/Confirmation URLs
Simply call the `register` method of the `C2B` class, optionally passing a callback function to process the response from M-PESA. If no callback URL is supplied, the method will return an array of the response from M-PESA.

```php
C2B::register();
```

or

```php
C2B::register(function($response){
  // Do something with $response, like echo $response['ResponseDescription']
});
```

## Process the Payment
If you return a success response at the validation endpoint. a confirmation request of the transaction is sent by M-Pesa to the confirmation URL. The transaction data is sent with this request, and you can use the `confirm` method of the `C2B` class to save this information, then return true. The method allows you to pass a callback function to process the data received.
```php
C2B::confirm();
```

or

```php
C2B::confirm(function($response){
  // Process $response
  // $TransactionType    = $response['TransactionType'];
  // $TransID            = $response['TransID'];
  // $TransTime          = $response['TransTime'];
  // $TransAmount        = $response['TransAmount'];
  // $BusinessShortCode  = $response['BusinessShortCode'];
  // $BillRefNumber      = $response['BillRefNumber'];
  // $InvoiceNumber      = $response['InvoiceNumber'];
  // $OrgAccountBalance  = $response['OrgAccountBalance'];
  // $ThirdPartyTransID  = $response['ThirdPartyTransID'];
  // $MSISDN             = $response['MSISDN'];
  // $FirstName          = $response['FirstName'];
  // $MiddleName         = $response['MiddleName'];
  // $LastName           = $response['LastName'];
  return true;
});
```

See [the README](README.md) for making and processing payment requests.