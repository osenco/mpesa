# MPESA API Responses

This file includes instructions and sample code for processing callback data sent from Safaricom. It includes sample responses from Safaricom and how to process them accordingly.

## C2B Response Data

### Sample Response

```json
{
	"Body": {
		"stkCallback": {
			"MerchantRequestID": "19465-780693-1",
			"CheckoutRequestID": "ws_CO_27072017154747416",
			"ResultCode": 0,
			"ResultDesc": "The service request is processed successfully.",
			"CallbackMetadata": {
				"Item": [
					{
						"Name": "Amount",
						"Value": 1
					},
					{
						"Name": "MpesaReceiptNumber",
						"Value": "LGR7OWQX0R"
					},
					{
						"Name": "Balance"
					},
					{
						"Name": "TransactionDate",
						"Value": 20170727154800
					},
					{
						"Name": "PhoneNumber",
						"Value": 254721566839
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
	"Body": {
		"stkCallback": {
			"MerchantRequestID": "19465-780693-1",
			"CheckoutRequestID": "ws_CO_27072017154747416",
			"ResultCode": 0,
			"ResultDesc": "The service request is processed successfully.",
			"CallbackMetadata": {
				"Item": [
					{
						"Name": "Amount",
						"Value": 1
					},
					{
						"Name": "MpesaReceiptNumber",
						"Value": "LGR7OWQX0R"
					},
					{
						"Name": "Balance"
					},
					{
						"Name": "TransactionDate",
						"Value": 20170727154800
					},
					{
						"Name": "PhoneNumber",
						"Value": 254721566839
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
	"Result": {
		"ResultType": 0,
		"ResultCode": 0,
		"ResultDesc": "The service request is processed successfully.",
		"OriginatorConversationID": "6453-171377-1",
		"ConversationID": "AG_20210107_000042b7667c678fcc42",
		"TransactionID": "PA76E82NVI",
		"ResultParameters": {
			"ResultParameter": [
				{
					"Key": "TransactionAmount",
					"Value": 10
				},
				{
					"Key": "TransactionReceipt",
					"Value": "PA76E82NVI"
				},
				{
					"Key": "ReceiverPartyPublicName",
					"Value": "254705459494 - MICHAEL MAUNDE"
				},
				{
					"Key": "TransactionCompletedDateTime",
					"Value": "07.01.2021 14:51:31"
				},
				{
					"Key": "B2CUtilityAccountAvailableFunds",
					"Value": 4974.73
				},
				{
					"Key": "B2CWorkingAccountAvailableFunds",
					"Value": 0.0
				},
				{
					"Key": "B2CRecipientIsRegisteredCustomer",
					"Value": "Y"
				},
				{
					"Key": "B2CChargesPaidAccountAvailableFunds",
					"Value": 0.0
				}
			]
		},
		"ReferenceData": {
			"ReferenceItem": {
				"Key": "QueueTimeoutURL",
				"Value": "http://internalapi.safaricom.co.ke/mpesa/b2cresults/v1/submit"
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
