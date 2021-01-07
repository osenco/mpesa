# The B2C (Business To Customer) API

### Instantiating The Class

Remember to add the M-PESA web portal username and password for a user with `B2C ORG API Initiator` role when setting up the class. 

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
    'results_url'       => url('tuma/results'),
    'timeout_url'       => url('tuma/timeout'),
  )
);
````
## Make payment
```php
B2C::send($phone, $amount, $command, $remarks, $occassion, function($response)
{
  $ConversationID             = $response["ConversationID"];
  $OriginatorConversationID   = $response["OriginatorConversationID"];
  $ResponseCode               = $response["ResponseCode"];
  $ResponseDescription        = $response["ResponseDescription"];

  // TIP: Save $OriginatorConversationID in the database, and use it as a key for update
});
```

## Process the Payment
```php
B2C::reconcile(function($response)
{
  $Result                              = $response["Result"];
  $ResultType                          = $Result["ResultType"];
  $ResultCode                          = $Result["ResultCode"];
  $ResultDesc                          = $Result["ResultDesc"];
  $OriginatorConversationID            = $Result["OriginatorConversationID"];
  $ConversationID                      = $Result["ConversationID"];
  $TransactionID                       = $Result["TransactionID"];
  $ResultParameters                    = $Result["ResultParameters"];
  $ResultParameter                     = $Result["ResultParameters"]["ResultParameter"];
  $TransactionAmount                   = $ResultParameter[0]["Value"];
  $TransactionReceipt                  = $ResultParameter[1]["Value"];
  $B2CWorkingAccountAvailableFunds     = $ResultParameter[2]["Value"];
  $B2CUtilityAccountAvailableFunds     = $ResultParameter[3]["Value"];
  $TransactionCompletedDateTime        = $ResultParameter[4]["Value"];
  $ReceiverPartyPublicName             = $ResultParameter[2]["Value"];
  $B2CChargesPaidAccountAvailableFunds = $ResultParameter[6]["Value"];
  $B2CRecipientIsRegisteredCustomer    = $ResultParameter[7]["Value"];
  $ReferenceData                       = $Result["ReferenceData"];
  $ReferenceItem                       = $ReferenceData["ReferenceItem"];
  $QueueTimeoutURL                     = $ReferenceItem[0]["Value"];

  // Update Database record with $TransactionID as the MPESA receipt number where $OriginatorConversationID
});
```

See [the README](README.md) for making and processing payment requests.
