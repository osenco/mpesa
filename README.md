# Mpesa PHP SDK
Intuitive, Dynamic Mpesa PHP SDK

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
            <td>STK - SIM Tool Kit Prompt</td>
            <td>Customer Online Checkout</td>
        </tr>
        <tr>
            <td>C2B - Customer To Business</td>
            <td>Reconciling Manual Payments</td>
        </tr>
        <tr>
            <td>B2C - Business To Customer</td>
            <td>Salary Payments, Disbursements, Reversals</td>
        </tr>
        <tr>
            <td>B2B - Business To Business</td>
            <td>Payment For Supplies</td>
        </tr>
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

```bash
composer require osenco/mpesa
```

For Laravel Usesrs, there is a detailed guide [here](LARAVEL.md) as well as a sample [controller](examples/MpesaController.php)

## Usage
### Import Class With Namespace
Import the class namespace into your class or app to make it available for use. Replace STK with your API of choice. We will be using STK here.

```php
use Osen\Mpesa\STK;
```

### Instantiating The Class
The class uses static methods and does not need to be instantiated. This is to persist configuration in memory troughout execution of the script. To pass configuration options to the object, use the `init()` method at the top of your script.

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
        'timeout_url'       => url('mpesa/timeout'),
        'results_url'       => url('mpesa/results'),
    )
);
```

### Making A Payment Request
Wrap your request in a try catch to ensure proper error handling

```php
try {
    return $res = STK::send($phone, $amount, $reference);
} catch (\Throwable $th) {
    return $th;
}
```

### Validating/Confirming Transaction Details
Call either function at your confirmation/validation endpoint

```php
STK::confirm();
STK::validate();
```

These functions take an optional argument for a callback function that processes the response. If none is provided, the function will return a succesful response. The callback function can either be a defined funtion or a closure(anonymous)

```php
function validate_data($data){
    // Process data
    return true;
}
STK::validate('validate_data');
```

```php
STK::confirm(function($data){
    // Process $data
    return true;
});
```

## Reconciling The Payment
The Mpesa transaction requests are asynchronous, and as such the payment details are not instantaneous. To get the transaction data and update the payment, use the `reconcile()` method. A callback function may be supplied to process the data. The callback function can either be a defined funtion or a closure(anonymous). If ommited, the method will return a successful response by default.

```php
STK::reconcile();
```

```php
STK::reconcile(function($data){
    // Process $data
    return true;
});
```

### Timeouts
Sometimes the system times out.

```php
STK::timeout();
```

This function takes the data sent by Safaricom, and returns a response. You can pass an optional argument to process the data and return true.

```php
STK::timeout(function($data){
    // Process results
    return true;
});
```

### Processing Results
There are scenarios when Safaricom needs to send data to your application. This could be when you make a balance query, or transaction status check.

```php
STK::results();
```

This function takes the data sent by Safaricom, and returns a response. You can pass an optional argument to process the data and return true.
```php
STK::results(function($data){
    // Process results
    return true;
});
```

## Helper Functions
You can use the helper functions for more concise code

To configure the class, use the `mpesa_setup_config` function, passing your configuration options as the first argument, and the API you wish to setup(STK, C2B, B2C, B2B) as the second argument. The API is set to STK by default.

```php
/**
 * Define your configuration options and pass them as the argument
*/
$config = array(
    'env'               => 'sandbox',
    'type'              => 4, // For Paybill, or, 2 for Till
    'shortcode'         => '174379',
    'headoffice'          => '174379',
    'key'               => 'Your Consumer Key',
    'secret'            => 'Your Consumer Secret',
    'username'          => '',
    'passkey'           => 'Your Online Passkey',
    'validation_url'    => url('mpesa/validate'),
    'confirmation_url'  => url('mpesa/confirm'),
    'callback_url'      => url('mpesa/reconcile'),
    'results_url'       => url('mpesa/results'),
);
mpesa_setup_config($config, 'STK');
```

Optionally, you could configure with the `mpesa_setup_*` functions

```php
mpesa_setup_stk($config);
mpesa_setup_c2b($config);
mpesa_setup_b2c($config);
mpesa_setup_b2b($config);
```

To make a STK Prompt request, pass the user's phone number, the amount due, and an optional reference(shows up on the user's phone) respectively

```php
/**
 * @param $phone Phone Number (starting with country code e.g 254)
*/
mpesa_stk_push($phone, $amount, $reference);
```

To process c2b transactions, call the function as follows, passing the user's phone number, the amount due, and an optional reference respectively

```php
/**
 * @param $phone Phone Number (starting with country code e.g 254)
*/
mpesa_c2b_request($phone, $amount, $reference);
```

To send funds to a customer

```php
/**
 * @param $phone Phone Number
*/
mpesa_b2c_request();
```

Transfer funds between one business to another

```php
/**
 * Transfer funds between one business to another
*/
mpesa_b2b_request();
```

Validate Or Confirm Transaction Details

```php
/**
 * Call this function at your validation/confirmation endpoint
*/
mpesa_validate();
mpesa_confirm()
```

## Credits & Acknowledgements
Mpesa is a service and registered trademark of [Safaricom PLC](https://safaricom.co.ke).

## Licensing
This software is released under [MIT License](LICENSE).

## Usage & Contribution
This library is free and open source software. You can copy, modify and distribute it as you so wish. If you have any ideas on how to improve it, shoot us an email at [hi@osen.co.ke](mailto:hi@osen.co.ke) or raise an issue.