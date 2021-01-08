# M-PESA PHP SDK

Intuitive and Comprehensive M-PESA SDK for PHP Applications

## Supported APIs

<table>
    <thead>
        <tr>
            <th>API Type</th>
            <th>Application Scenario</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>C2B - Customer To Business</td>
            <td>C2B - SIM Tool Kit Prompt & Reconciling Manual Payments</td>
        </tr>
        <tr>
            <td>B2C - Business To Customer</td>
            <td>Salary Payments, Disbursals, Reversals</td>
        </tr>
        <!-- <tr>
            <td>B2B - Business To Business</td>
            <td>Payment For Supplies</td>
        </tr> -->
        <tr>
            <td>Account Balance Check</td>
            <td>Accounting Purposes</td>
        </tr>
        <tr>
            <td>Transaction Status Check</td>
            <td>Failed Transactions</td>
        </tr>
        <tr>
            <td>Transaction Reversal</td>
            <td>Wrongful Payment</td>
        </tr>
    </tbody>
</table>

## Installation

Install via composer by typing in your terminal

```cmd
composer require osenco/mpesa
```

If you do not use composer you can just download this library from the releases, unzip it in your project and include the [autoload.php](autoload.php) file in your project.

```php
require_once("path/to/autoload.php");
```

For Laravel Users, there is a detailed guide [here](LARAVEL.md) as well as a sample [controller](examples/MpesaController.php)

## Usage

### Import Class With Namespace

Import the class namespace into your class or app to make it available for use. Replace C2B with your API of choice. We will be using C2B here. 

The C2B API enables Paybill and Buy Goods merchants to integrate to M-Pesa and receive real time payments notifications. A user pays through the traditional payment process (i.e goes to M-Pesa menu on their phone and makes the payment to your shortcode). The transaction details are then sent to your app.

This could come in handy and work as a backup to STK push, should the prompt fail, either because the user has not enabled their SIM, or it timed out before they saw it. In this case you should display appropriate instructions for the user to make this payment, making sure to supply your shortcode, and account number (for Paybills).

See how to set up [B2C here](B2C.md) and [B2B here](B2B.md).

```php
use Osen\Mpesa\C2B;
```

### Instantiating The Classes

The classes use static methods and does not need to be instantiated. This is to persist configuration in memory troughout execution of the script. To pass configuration options to the object, use the `init()` method at the top of your script. The `headoffice` key is only required for Till Numbers. Paybill users can ignore it.

```php
C2B::init(
    array(
        "env"               => "sandbox",
        "type"              => 4, // For Paybill, or, 2 for Till, 1	for MSISDN
        "shortcode"         => "174379",
        "headoffice"        => "174379", // Ignore if using Paybill
        "key"               => "Your Consumer Key",
        "secret"            => "Your Consumer Secret",
        "username"          => "", // Required for B2B and B2C APIs only
        "password"          => "", // Required for B2B and B2C APIs only
        "passkey"           => "Your Online Passkey",
        "validation_url"    => "api/lipwa/validate",
        "confirmation_url"  => "api/lipwa/confirm",
        "callback_url"      => "api/lipwa/reconcile",
        "results_url"       => "api/lipwa/results")
    )
);
```

<b>TIP: You can just pass your URL endpoints for testing on sandbox, the system will use the test credentials provided from [Daraja](https://developer.safaricom.co.ke/test_credentials).</b> e.g

```php
C2B::init(
    array(
        "validation_url"    => "api/lipwa/validate",
        "confirmation_url"  => "api/lipwa/confirm",
        "callback_url"      => "api/lipwa/reconcile",
        "results_url"       => "api/lipwa/results")
    )
);
```

### Making A Payment Request

Wrap your request in a try catch to ensure proper error handling

```php
try {
    return $res = C2B::stk($phone, $amount, $reference);

    // Do something with $res, like save to DB with the $res["MerchantRequestID"] as key.
} catch (\Throwable $th) {
    return $th;
}
```

OR

```php
try {
    return $res = STK::send($phone, $amount, $reference);

    // Do something with $res, like save to DB with the $res["MerchantRequestID"] as key.
} catch (\Throwable $th) {
    return $th;
}
```

### Reconciling The Payment

The M-PESA transaction requests are asynchronous, and as such the payment details are not instantaneous. To get the transaction data and update the payment, use the `reconcile()` method. A callback function may be supplied to process the data. The callback function can either be a defined funtion or a closure(anonymous). If ommited, the method will return a successful response by default.

```php
C2B::reconcile();
```

```php
C2B::reconcile(function ($response){
    $response                   = $response["Body"];
    $resultCode 			    = $response["stkCallback"]["ResultCode"];
    $resultDesc 			    = $response["stkCallback"]["ResultDesc"];
    $merchantRequestID 			= $response["stkCallback"]["MerchantRequestID"];

    if(isset($response["stkCallback"]["CallbackMetadata"])){
        $CallbackMetadata       = $response["stkCallback"]["CallbackMetadata"]["Item"];

        $amount                 = $CallbackMetadata[0]["Value"];
        $mpesaReceiptNumber     = $CallbackMetadata[1]["Value"];
        $balance                = $CallbackMetadata[2]["Value"];
        $transactionDate        = $CallbackMetadata[3]["Value"];
        $phone                  = $CallbackMetadata[4]["Value"];

        $payment->status        = "Paid";
        $payment->amount        = $amount;
        $payment->receipt       = $mpesaReceiptNumber;
    }

    return true;
});
```

### The Validation/Confirmation URLs
Whenever M-Pesa receives a transaction on your shortcode, a validation request is sent to the validation URL registered above. M-Pesa completes or cancels the transaction depending on the validation response it receives.

These URLs must be HTTPS in production. Validation is an optional feature that needs to be activated on M-Pesa, the owner of the shortcode needs to make this request for activation. This can be done by sending an email to [apisupport@safaricom.co.ke](mailto:apisupport@safaricom.co.ke), or through a chat on the [developer portal](https://developer.safaricom.co.ke).

####  Register Validation/Confirmation URLs
Simply call the `register` method of the `C2B` class, optionally passing a callback function to process the response from M-PESA. If no callback URL is supplied, the method will return an array of the response from M-PESA. 

You can pass an optional second parameter for the response type. This defaults to `Completed`

```php
C2B::register();
```

or

```php
C2B::register(function ($response){
  // Do something with $response, like echo $response['ResponseDescription']
});
```

### Validate the Payment Data
When a user pays via M-Pesa, and validation is enabled for your shortcode, M-Pesa sends a request to your validation endpoint. The transaction data is sent with this request, and you can use the `validate` method of the `C2B` class to check the validity of this information, then return true. The method allows you to pass a callback function to process the data received.
```php
C2B::validate();
``` 

or

```php
C2B::validate(function ($response){
  // Process $response
  $TransactionType    = $response['TransactionType'];
  $TransID            = $response['TransID'];
  $TransTime          = $response['TransTime'];
  $TransAmount        = $response['TransAmount'];
  $BusinessShortCode  = $response['BusinessShortCode'];
  $BillRefNumber      = $response['BillRefNumber'];
  $InvoiceNumber      = $response['InvoiceNumber'];
  $OrgAccountBalance  = $response['OrgAccountBalance'];
  $ThirdPartyTransID  = $response['ThirdPartyTransID'];
  $MSISDN             = $response['MSISDN'];
  $FirstName          = $response['FirstName'];
  $MiddleName         = $response['MiddleName'];
  $LastName           = $response['LastName'];

  return true;
});
```

### Process the Payment (Confirmation)
If you return a success response at the validation endpoint. a confirmation request of the transaction is sent by M-Pesa to the confirmation URL. The transaction data is sent with this request, and you can use the `confirm` method of the `C2B` class to save this information, then return true. The method allows you to pass a callback function to process the data received.
```php
C2B::confirm();
```

or

```php
C2B::confirm(function ($response){
  // Process $response
  $TransactionType    = $response['TransactionType'];
  $TransID            = $response['TransID'];
  $TransTime          = $response['TransTime'];
  $TransAmount        = $response['TransAmount'];
  $BusinessShortCode  = $response['BusinessShortCode'];
  $BillRefNumber      = $response['BillRefNumber'];
  $InvoiceNumber      = $response['InvoiceNumber'];
  $OrgAccountBalance  = $response['OrgAccountBalance'];
  $ThirdPartyTransID  = $response['ThirdPartyTransID'];
  $MSISDN             = $response['MSISDN'];
  $FirstName          = $response['FirstName'];
  $MiddleName         = $response['MiddleName'];
  $LastName           = $response['LastName'];

  return true;
});
```

### Processing Timeouts

When a valid M-Pesa API request is received by the API Gateway, it is sent to M-Pesa where it is added to a queue. M-Pesa then processes the requests in the queue and sends a response to the API Gateway which then forwards the response to the URL registered in the CallBackURL or ResultURL request parameter. Whenever M-Pesa receives more requests than the queue can handle, M-Pesa responds by rejecting any more requests and the API Gateway sends a queue timeout response to the URL registered in the QueueTimeOutURL request parameter. Use the `timeout()` method to process this response.

```php
C2B::timeout();
```

This function takes the data sent by Safaricom, and returns a response. You can pass an optional argument to process the data and return true.

```php
C2B::timeout(function ($response){
    // Do something with $response
    return true;
});
```

### Check Transaction Status

You can check for the status of a transaction by calling the `status" method at your endpoint.

```php
C2B::status($transaction, $command = "TransactionStatusQuery", $remarks = "Transaction Status Query", $occassion = "Transaction Status Query");
```

You can pass an optional fifth argument that is a callback for processing the response from the request and returning true.

```php
C2B::status($transaction, $command, $remarks, $occassion, function ($response){
    // Do something with $response
    return true;
});
```

### Reverse Transaction

To reverse a transaction, call the `reverse` method at your endpoint.

```php
C2B::reverse($transaction, $amount, $receiver, $receiver_type = 3, $remarks = "Transaction Reversal", $occassion = "Transaction Reversal");
```

You can pass an optional seventh argument that is a callback for processing the response from the request and returning true.

```php
C2B::reverse($transaction, $amount, $receiver, $receiver_type = 3, $remarks = "Transaction Reversal", $occassion = "", function ($response){
    // Do something with $response
    return true;
});
```

### Check Account Balance

To reverse a transaction, call the `reverse` method at your endpoint.

```php
C2B::balance($command, $remarks = "Balance Query", $occassion = "");
```

You can pass an optional callback for processing the response from the request and returning true.

```php
C2B::balance($command, $remarks = "Balance Query", function ($response){
    // Do something with $response
    return true;
});
```

### Processing Results

To process results from a transaction statuscheck, or a reversal, or an account balance check, call the `result` method at your endpoint.

```php
C2B::result();
```

You can pass an optional callback for processing the response from the request and returning true.

```php
C2B::result(function ($response){
    // Process account balance check results
    $result                     = $response["Result"];
    $ResultType                 = $result["ResultType"];
	$ResultCode                 = $result["ResultCode"];
	$ResultDesc                 = $result["ResultDesc"];
	$OriginatorConversationID   = $result["OriginatorConversationID"];
	$ConversationID             = $result["ConversationID"];
	$TransactionID              = $result["TransactionID"];
	$ResultParameters           = $result["ResultParameters"];

    $ResultParameter            = $ResultParameters["ResultParameter"];
	$ReceiptNo                  = $ResultParameter[0]["Value"];
	$Conversation               = $ResultParameter[1]["Value"];
	$FinalisedTime              = $ResultParameter[2]["Value"];
	$Amount                     = $ResultParameter[3]["Value"];
	$TransactionStatus          = $ResultParameter[4]["Value"];
	$ReasonType                 = $ResultParameter[5]["Value"];
    $TransactionReason          = $ResultParameter[6]["Value"];
    $DebitPartyCharges          = $ResultParameter[7]["Value"];
    $DebitAccountType           = $ResultParameter[8]["Value"];
    $InitiatedTime              = $ResultParameter[9]["Value"];
    $OriginatorConversationID   = $ResultParameter[10]["Value"];
    $CreditPartyName            = $ResultParameter[11]["Value"];
    $DebitPartyName             = $ResultParameter[12]["Value"];

    $ReferenceData              = $result["ReferenceData"];
    $ReferenceItem              = $ReferenceData["ReferenceItem"];
	$Occasion                   = $ReferenceItem["Value"];


    // Process transaction reversal results
    $Result                     = $response["Result"];
	$ResultType                 = $Result["ResultType"];
	$ResultCode                 = $Result["ResultCode"];
	$ResultDesc                 = $Result["ResultDesc"];
	$OriginatorConversationID   = $Result["OriginatorConversationID"];
	$ConversationID             = $Result["ConversationID"];
	$TransactionID              = $Result["TransactionID"];
	$ReferenceData              = $Result["ReferenceData"];
	$ReferenceItem              = $Result["ReferenceItem"];
	$QueueTimeoutURL            = $ReferenceItem["Value"];

    // Process transaction status check results
    $Result                     = $response["Result"];
	$ResultType                 = $Result["ResultType"];
	$ResultCode                 = $Result["ResultCode"];
	$ResultDesc                 = $Result["ResultDesc"];
	$OriginatorConversationID   = $Result["OriginatorConversationID"];
	$ConversationID             = $Result["ConversationID"];
	$TransactionID              = $Result["TransactionID"];
	$ResultParameters           = $Result["ResultParameters"];
	$ResultParameter            = $ResultParameters["ResultParameter"];
	$ReceiptNo                  = $ResultParameter[0]["Value"];
	$ConversationID             = $ResultParameter[1]["Value"];
	$FinalisedTime              = $ResultParameter[2]["Value"];
	$Amount                     = $ResultParameter[3]["Value"];
	$TransactionStatus          = $ResultParameter[4]["Value"];
	$ReasonType                 = $ResultParameter[5]["Value"];
	$TransactionReason          = $ResultParameter[6]["Value"];
	$DebitPartyCharges          = $ResultParameter[7]["Value"];
	$DebitAccountType           = $ResultParameter[8]["Value"];
	$InitiatedTime              = $ResultParameter[9]["Value"];
	$OriginatorConversationID   = $ResultParameter[10]["Value"];
	$CreditPartyName            = $ResultParameter[11]["Value"];
	$DebitPartyName             = $ResultParameter[12]["Value"];

    $ReferenceData              = $result["ReferenceData"];
    $ReferenceItem              = $ReferenceData["ReferenceItem"];
	$Occasion                   = $ReferenceItem["Value"];


    //TIP: You can differentiate between responses by checking value of $ResultType
    return true;
});
```

## Available Command IDs

<table>
    <thead>
    <tr>
        <th>Command ID</th>
        <th>Description</th>
    </tr>
    </thead>
    <tbody>
        <tr>
            <td>TransactionReversal</td>
            <td>Reversal for an erroneous C2B transaction.</td>
        </tr>
        <tr>
            <td>SalaryPayment</td>
            <td>Used to send money from an employer to employees e.g. salaries</td>
        </tr>
        <tr>
            <td>BusinessPayment</td>
            <td>Used to send money from business to client e.g. refunds</td>
        </tr>
        <tr>
            <td>PromotionPayment</td>
            <td>Used to send money when promotions take place e.g. raffle winners</td>
        </tr>
        <tr>
            <td>AccountBalance</td>
            <td>Used to check the balance in a paybill/buy goods account (includes utility, MMF, Merchant, Charges paid account).</td>
        </tr>
        <tr>
            <td>CustomerPayBillOnline</td>
            <td>Used to simulate a transaction taking place in the case of C2B Simulate Transaction or to initiate a transaction on behalf of the client (C2B Push).</td>
        </tr>
        <tr>
            <td>TransactionStatusQuery</td>
            <td>Used to query the details of a transaction.</td>
        </tr>
        <tr>
            <td>CheckIdentity</td>
            <td>Similar to C2B push, uses M-Pesa PIN as a service.</td>
        </tr>
        <tr>
            <td>BusinessPayBill</td>
            <td>Sending funds from one paybill to another paybill</td>
        </tr>
        <tr>
            <td>BusinessBuyGoods</td>
            <td>sending funds from buy goods to another buy goods.</td>
        </tr>
        <tr>
            <td>DisburseFundsToBusiness</td>
            <td>Transfer of funds from utility to MMF account.</td>
        </tr>
        <tr>
            <td>BusinessToBusinessTransfer</td>
            <td>Transferring funds from one paybills MMF to another paybills MMF account.</td>
        </tr>
        <tr>
            <td>BusinessTransferFromMMFToUtility</td>
            <td>Transferring funds from paybills MMF to another paybills utility account.</td>
        </tr>
    </tbody>
</table>

## Helper Functions

You can use the helper functions for more concise code

To configure the class, use the `mpesa_setup_config` function , passing your configuration options as the first argument, and the API you wish to setup(C2B, C2B, B2C, B2B) as the second argument. The API is set to C2B by default.

```php
$config = array(
    "env"               => "sandbox",
    "type"              => 4, // For Paybill, or, 2 for Till, 1	for MSISDN
    "shortcode"         => "174379",
    "headoffice"        => "174379",
    "key"               => "Your Consumer Key",
    "secret"            => "Your Consumer Secret",
    "username"          => "",
    "passkey"           => "Your Online Passkey",
    "validation_url"    => "api/lipwa/validate",
    "confirmation_url"  => "api/lipwa/confirm",
    "callback_url"      => "api/lipwa/reconcile",
    "results_url"       => "api/lipwa/results",
);
mpesa_setup_config($config, "C2B");
```

Optionally, you could configure with the `mpesa_setup_*` function s

```php
mpesa_setup_stk($config);
mpesa_setup_c2b($config);
mpesa_setup_b2c($config);
mpesa_setup_b2b($config);
```

To make a C2B Prompt request, pass the user"s phone number, the amount due, and an optional reference(shows up on the user"s phone) respectively

```php
mpesa_stk_push($phone, $amount, $reference);
```

To simulate a c2b transaction, call the function as follows, passing the user"s phone number, the amount due, and an optional reference respectively

```php
mpesa_c2b_request($phone, $amount, $reference);
```

To send funds to a client

```php
mpesa_b2c_request();
```

Transfer funds between one business to another

```php
mpesa_b2b_request();
```

Validate Or Confirm Transaction Details. Call this function at your validation/confirmation endpoint.

```php
mpesa_validate();
mpesa_confirm()
```

## Credits & Acknowledgements

M-PESA is a service and registered trademark of [Safaricom PLC](https://safaricom.co.ke).

## Licensing

This software is released under [MIT License](LICENSE).

## Usage & Contribution

This library is free and open source software. You can copy, modify and distribute it as you so wish. If you have any ideas on how to improve it, shoot us an email at [hi@osen.co.ke](mailto:hi@osen.co.ke) or raise an issue here.
