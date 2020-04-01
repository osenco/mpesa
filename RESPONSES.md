# MPESA API Responses
This file includes instructions and sample code for processing callback data sent from Safaricom. It includes sample responses from Safaricom and how to process them accordingly.

## STK Response Data
### Sample Response
```json
{
  "Body":{
    "stkCallback":{
    "MerchantRequestID":"19465-780693-1",
    "CheckoutRequestID":"ws_CO_27072017154747416",
    "ResultCode":0,
    "ResultDesc":"The service request is processed successfully.",
    "CallbackMetadata":{
        "Item":[
          {
            "Name":"Amount",
            "Value":1
          },
          {
            "Name":"MpesaReceiptNumber",
            "Value":"LGR7OWQX0R"
          },
          {
            "Name":"Balance"
          },
          {
            "Name":"TransactionDate",
            "Value":20170727154800
          },
          {
            "Name":"PhoneNumber",
            "Value":254721566839
          }
        ]
      }
    }
  }
}
```

### Sample Callback Function
```php
function mpesa_stk_callback($response)
{
  $data                   = $response["Body"]["stkCallback"];

  $MerchantRequestID      = $data["MerchantRequestID"];
  $CheckoutRequestID      = $data["CheckoutRequestID"],
  $ResultCode             = $data["ResultCode"],
  $ResultDesc             = $data["ResultDesc"],
  $CallbackMetadata       = $data["CallbackMetadata"];

  $Amount                 = $CallbackMetadata["Item"][0]["Value"];
  $MpesaReceiptNumber     = $CallbackMetadata["Item"][1]["Value"];
  $Balance                = $CallbackMetadata["Item"][2]["Value"];
  $TransactionDate        = $CallbackMetadata["Item"][3]["Value"];
  $PhoneNumber            = $CallbackMetadata["Item"][4]["Value"];

  // Do something with the variables above then return true or false

  return true
}
```

## C2B Response Data
### Sample Response
```json
{
  "Body":
  {
    "stkCallback":
    {
      "MerchantRequestID":"19465-780693-1",
      "CheckoutRequestID":"ws_CO_27072017154747416",
      "ResultCode":0,
      "ResultDesc":"The service request is processed successfully.",
      "CallbackMetadata":
      {
          "Item":
        [
          {
            "Name":"Amount",
            "Value":1
          },
          {
            "Name":"MpesaReceiptNumber",
            "Value":"LGR7OWQX0R"
          },
          {
            "Name":"Balance"
          },
          {
            "Name":"TransactionDate",
            "Value":20170727154800
          },
          {
            "Name":"PhoneNumber",
            "Value":254721566839
          }
        ]
      }
    }
  }
}
```

### Sample Callback Function
```php
function mpesa_c2b_callback($response)
{
  $data                   = $response["Body"]["stkCallback"];

  $MerchantRequestID      = $data["MerchantRequestID"];
  $CheckoutRequestID      = $data["CheckoutRequestID"],
  $ResultCode             = $data["ResultCode"],
  $ResultDesc             = $data["ResultDesc"],
  $CallbackMetadata       = $data["CallbackMetadata"];

  $Amount                 = $CallbackMetadata["Item"][0]["Value"];
  $MpesaReceiptNumber     = $CallbackMetadata["Item"][0]["Value"];
  $Balance                = $CallbackMetadata["Item"][0]["Value"];
  $TransactionDate        = $CallbackMetadata["Item"][0]["Value"];
  $PhoneNumber            = $CallbackMetadata["Item"][0]["Value"];

  // Do something with the variables above then return true or false

  return true
}
```

## B2C Response Data
### Sample Response
```json
{
  "Result":
  {
    "ResultType":0,
    "ResultCode":0,
    "ResultDesc":"The service request has been accepted successfully.",
    "OriginatorConversationID":"19455-424535-1",
    "ConversationID":"AG_20170717_00006be9c8b5cc46abb6",
    "TransactionID":"LGH3197RIB",
    "ResultParameters":
    {
      "ResultParameter":
      [
        {
          "Key":"TransactionReceipt",
          "Value":"LGH3197RIB"
        },
        {
          "Key":"TransactionAmount",
          "Value":8000
        },
        {
          "Key":"B2CWorkingAccountAvailableFunds",
          "Value":150000
        },
        {
          "Key":"B2CUtilityAccountAvailableFunds",
          "Value":133568
        },
        {
          "Key":"TransactionCompletedDateTime",
          "Value":"17.07.2017 10:54:57"
        },
        {
          "Key":"ReceiverPartyPublicName",
          "Value":"254708374149 - John Doe"
        },
        {
          "Key":"B2CChargesPaidAccountAvailableFunds",
          "Value":0
        },
        {
          "Key":"B2CRecipientIsRegisteredCustomer",
          "Value":"Y"
        }
      ]
    },
    "ReferenceData":{
      "ReferenceItem":{
        "Key":"QueueTimeoutURL",
        "Value":"https://internalsandbox.safaricom.co.ke/mpesa/b2cresults/v1/submit"
      }
    }
  }
}
```

### Sample Callback Function
```php
function mpesa_b2c_callback($response)
{
  $data                                   = $response["Result"];

  $ResultType                             = $data["ResultType"];
  $ResultCode                             = $data["ResultCode"];
  $ResultDesc                             = $data["ResultDesc"];
  $OriginatorConversationID               = $data["OriginatorConversationID"];
  $ConversationID                         = $data["ConversationID"];
  $TransactionID                          = $data["TransactionID"];
  $ResultParameters                       = $data["ResultParameters"]["ResultParameter"];
  $QueueTimeoutURL                        = $data["ReferenceData"]["ReferenceItem"][0]["Value"];
  
  $TransactionReceipt                     = $ResultParameters[0]["Value"];
  $TransactionAmount                      = $ResultParameters[1]["Value"];
  $B2CWorkingAccountAvailableFunds        = $ResultParameters[2]["Value"];
  $B2CUtilityAccountAvailableFunds        = $ResultParameters[3]["Value"];
  $TransactionCompletedDateTime           = $ResultParameters[4]["Value"];
  $ReceiverPartyPublicName                = $ResultParameters[5]["Value"];
  $B2CChargesPaidAccountAvailableFunds    = $ResultParameters[6]["Value"];
  $B2CRecipientIsRegisteredCustomer       = $ResultParameters[7]["Value"];
  
  // Do something with the variables above then return true or false

  return true
}
```
