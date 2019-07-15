<?php

namespace Osen\Mpesa;

class Service
{
	/**
	 * @var object $config Configuration options
	 */
    public static $config;

    public static function init(array $configs = [])
    {
		$defaults = array(
			'env'               => 'sandbox',
			'type'              => 4,
			'shortcode'         => '174379',
			'headoffice'        => '174379',
			'key'               => 'Your Consumer Key',
			'secret'            => 'Your Consumer Secret',
			'username'          => 'apitest',
			'passkey'           => 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919',
			'validation_url'    => '/mpesa/validate',
			'confirmation_url'  => '/mpesa/confirm',
			'callback_url'      => '/mpesa/reconcile',
			'timeout_url'       => '/mpesa/timeout',
			'results_url'       => '/mpesa/results',
		);

		if(!isset($configs['headoffice']) || empty($configs['headoffice'])){
			$defaults['headoffice'] = $configs['shortcode'];
		}

		$parsed = array_merge($defaults, $configs);
	
        self::$config 	= (object)$parsed;
    }

	/**
	 * @return string Access token
	 */
    public static function token()
    {
        $endpoint = (self::$config->env == 'live') ? 
			'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials' : 
			'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

		$credentials = base64_encode(self::$config->key.':'.self::$config->secret);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $endpoint);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Basic '.$credentials));
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $curl_response = curl_exec($curl);

		$result = json_decode($curl_response);
        
		return isset($result->access_token) ? $result->access_token : '';
    }

	/**
	 * @param int $transaction
	 * @param string $command
	 * @param string $remarks
	 * @param string $occassion\
	 * 
	 * @return array Response
	 */
    public static function status(int $transaction, string $command = 'TransactionStatusQuery', string $remarks = 'Transaction Status Query', string $occassion = '')
    {
		// {
		// 	"Result":{
		// 		"ResultType":0,
		// 		"ResultCode":0,
		// 		"ResultDesc":"The service request has been accepted successfully.",
		// 		"OriginatorConversationID":"10816-694520-2",
		// 		"ConversationID":"AG_20170727_000059c52529a8e080bd",
		// 		"TransactionID":"LGR0000000",
		// 		"ResultParameters":{
		// 		"ResultParameter":[
		// 			{
		// 			"Key":"ReceiptNo",
		// 			"Value":"LGR919G2AV"
		// 			},
		// 			{
		// 			"Key":"Conversation ID",
		// 			"Value":"AG_20170727_00004492b1b6d0078fbe"
		// 			},
		// 			{
		// 			"Key":"FinalisedTime",
		// 			"Value":20170727101415
		// 			},
		// 			{
		// 			"Key":"Amount",
		// 			"Value":10
		// 			},
		// 			{
		// 			"Key":"TransactionStatus",
		// 			"Value":"Completed"
		// 			},
		// 			{
		// 			"Key":"ReasonType",
		// 			"Value":"Salary Payment via API"
		// 			},
		// 			{
		// 			"Key":"TransactionReason"
		// 			},
		// 			{
		// 			"Key":"DebitPartyCharges",
		// 			"Value":"Fee For B2C Payment|KES|33.00"
		// 			},
		// 			{
		// 			"Key":"DebitAccountType",
		// 			"Value":"Utility Account"
		// 			},
		// 			{
		// 			"Key":"InitiatedTime",
		// 			"Value":20170727101415
		// 			},
		// 			{
		// 			"Key":"Originator Conversation ID",
		// 			"Value":"19455-773836-1"
		// 			},
		// 			{
		// 			"Key":"CreditPartyName",
		// 			"Value":"254708374149 - John Doe"
		// 			},
		// 			{
		// 			"Key":"DebitPartyName",
		// 			"Value":"600134 - Safaricom157"
		// 			}
		// 		]
		// 		},
		// 		"ReferenceData":{
		// 		"ReferenceItem":{
		// 			"Key":"Occasion",
		// 			"Value":"aaaa"
		// 		}
		// 		}
		// 	}
		// 	}

		$token = self::token();
      	$endpoint = (self::$config->env == 'live') ? 
		  	'https://api.safaricom.co.ke/mpesa/transactionstatus/v1/query' : 
			'https://sandbox.safaricom.co.ke/mpesa/transactionstatus/v1/query';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $endpoint);
        curl_setopt(
        	$curl, 
        	CURLOPT_HTTPHEADER, 
        	array(
        		'Content-Type:application/json',
        		'Authorization:Bearer '.$token 
        	) 
      	);
        $curl_post_data = array(
	        'Initiator'           => self::$config->username,
	        'SecurityCredential'  => self::$config->credentials,
	        'CommandID'           => $command,
	        'TransactionID'       => $transaction,
	        'PartyA'              => self::$config->shortcode,
	        'IdentifierType'      => self::$config->type,
	        'ResultURL'           => self::$config->results_url,
	        'QueueTimeOutURL'     => self::$config->timeout_url,
	        'Remarks'             => $remarks,
	        'Occasion'            => $occasion
	  	);
        $data_string = json_encode($curl_post_data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $response = curl_exec($curl);
		
		return json_decode($response, true);
    }

	/**
	 * @param int $transaction
	 * @param int $amount
	 * @param string $receiver
	 * @param int $receiver_type
	 * @param string $remarks
	 * @param string $occassion
	 * 
	 * @return array Response
	 */
    public static function reverse(int $transaction, int $amount, string $receiver, int $receiver_type = 3, string $remarks = 'Transaction Reversal', string $occassion = '')
    {
		// {
		// 	"Result":{
		// 		"ResultType":0,
		// 		"ResultCode":0,
		// 		"ResultDesc":"The service request has been accepted successfully.",
		// 		"OriginatorConversationID":"10819-695089-1",
		// 		"ConversationID":"AG_20170727_00004efadacd98a01d15",
		// 		"TransactionID":"LGR019G3J2",
		// 		"ReferenceData":{
		// 		"ReferenceItem":{
		// 			"Key":"QueueTimeoutURL",
		// 			"Value":"https://internalsandbox.safaricom.co.ke/mpesa/reversalresults/v1/submit"
		// 		}
		// 		}
		// 	}
		// 	}

        $token = self::token();
    	$endpoint = (self::$config->env == 'live') ? 
			'https://api.safaricom.co.ke/mpesa/reversal/v1/request' : 
			'https://sandbox.safaricom.co.ke/mpesa/reversal/v1/request';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $endpoint);
        curl_setopt(
        	$curl, 
        	CURLOPT_HTTPHEADER, 
        	array(
        		'Content-Type:application/json',
        		'Authorization:Bearer '.$token 
        	) 
      	);
        $curl_post_data = array(
	        'CommandID'               => 'TransactionReversal',
	        'Initiator'               => self::$config->business,
	        'SecurityCredential'      => self::$config->credentials,
	        'TransactionID'           => $transaction,
	        'Amount'                  => $amount,
	        'ReceiverParty'           => $receiver,
	        'RecieverIdentifierType'  => $reciever_type,
	        'ResultURL'               => self::$config->results_url,
	        'QueueTimeOutURL'         => self::$config->timeout_url,
	        'Remarks'                 => $remarks,
	        'Occasion'                => $occasion
	  	);
        $data_string = json_encode($curl_post_data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $response = curl_exec($curl);
		
		return json_decode($response);
    }

	/**
	 * @param string $command
	 * @param string $remarks
	 * @param string $occassion
	 * 
	 * @return array Response
	 */
    public static function balance(string $command, string $remarks = 'Balance Query', string $occassion = '')
    {
		// {
		// 	"Result":{
		// 		"ResultType":0,
		// 		"ResultCode":0,
		// 		"ResultDesc":"The service request has been accepted successfully.",
		// 		"OriginatorConversationID":"10816-694520-2",
		// 		"ConversationID":"AG_20170727_000059c52529a8e080bd",
		// 		"TransactionID":"LGR0000000",
		// 		"ResultParameters":{
		// 		"ResultParameter":[
		// 			{
		// 			"Key":"ReceiptNo",
		// 			"Value":"LGR919G2AV"
		// 			},
		// 			{
		// 			"Key":"Conversation ID",
		// 			"Value":"AG_20170727_00004492b1b6d0078fbe"
		// 			},
		// 			{
		// 			"Key":"FinalisedTime",
		// 			"Value":20170727101415
		// 			},
		// 			{
		// 			"Key":"Amount",
		// 			"Value":10
		// 			},
		// 			{
		// 			"Key":"TransactionStatus",
		// 			"Value":"Completed"
		// 			},
		// 			{
		// 			"Key":"ReasonType",
		// 			"Value":"Salary Payment via API"
		// 			},
		// 			{
		// 			"Key":"TransactionReason"
		// 			},
		// 			{
		// 			"Key":"DebitPartyCharges",
		// 			"Value":"Fee For B2C Payment|KES|33.00"
		// 			},
		// 			{
		// 			"Key":"DebitAccountType",
		// 			"Value":"Utility Account"
		// 			},
		// 			{
		// 			"Key":"InitiatedTime",
		// 			"Value":20170727101415
		// 			},
		// 			{
		// 			"Key":"Originator Conversation ID",
		// 			"Value":"19455-773836-1"
		// 			},
		// 			{
		// 			"Key":"CreditPartyName",
		// 			"Value":"254708374149 - John Doe"
		// 			},
		// 			{
		// 			"Key":"DebitPartyName",
		// 			"Value":"600134 - Safaricom157"
		// 			}
		// 		]
		// 		},
		// 		"ReferenceData":{
		// 		"ReferenceItem":{
		// 			"Key":"Occasion",
		// 			"Value":"aaaa"
		// 		}
		// 		}
		// 	}
		// }

        $token = self::token();
      	
        $endpoint = (self::$config->env == 'live')
			? 'https://api.safaricom.co.ke/mpesa/accountbalance/v1/query'
			: 'https://sandbox.safaricom.co.ke/mpesa/accountbalance/v1/query';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $endpoint);
        curl_setopt(
        	$curl, 
        	CURLOPT_HTTPHEADER, 
        	array(
        		'Content-Type:application/json',
        		'Authorization:Bearer '.$token 
        	) 
      	);
		
        $curl_post_data = array(
	        'CommandID'           => $command,
	        'Initiator'           => self::$config->username,
	        'SecurityCredential'  => self::$config->credentials,
	        'PartyA'              => self::$config->shortcode,
	        'IdentifierType'      => self::$config->type,
	        'Remarks'             => $remarks,
	        'QueueTimeOutURL'     => self::$config->timeout_url,
	        'ResultURL'           => self::$config->results_url
	  	);
        $data_string = json_encode($curl_post_data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $response = curl_exec($curl);
		
		return json_decode($response);
    }
	
	/**
	 * @param callable $callback Defined function or closure to process data and return true/false
	 * 
	 * @return array
	 */
    public static function validate($callback = null)
	{
		// {
		// 	"TransactionType":"",
		// 	"TransID":"LGR219G3EY",
		// 	"TransTime":"20170727104247",
		// 	"TransAmount":"10.00",
		// 	"BusinessShortCode":"600134",
		// 	"BillRefNumber":"xyz",
		// 	"InvoiceNumber":"",
		// 	"OrgAccountBalance":"",
		// 	"ThirdPartyTransID":"",
		// 	"MSISDN":"254708374149",
		// 	"FirstName":"John",
		// 	"MiddleName":"Doe",
		// 	"LastName":""
		// }

		$data = json_decode(file_get_contents('php://input'), true);

	    if(is_null($callback)){
		    return array('ResponseCode' => 0, 'ResponseDesc' => 'Success');
	    } else {
	        return call_user_func_array($callback, array($data)) 
				? array('ResponseCode' => 0, 'ResponseDesc' => 'Success') 
				: array('ResponseCode' => 1, 'ResponseDesc' => 'Failed');
	    }
    }
	
	/**
	 * @param callable $callback Defined function or closure to process data and return true/false
	 * 
	 * @return array
	 */
    public static function confirm($callback = null)
	{
		// {
		// 	"TransactionType":"",
		// 	"TransID":"LGR219G3EY",
		// 	"TransTime":"20170727104247",
		// 	"TransAmount":"10.00",
		// 	"BusinessShortCode":"600134",
		// 	"BillRefNumber":"xyz",
		// 	"InvoiceNumber":"",
		// 	"OrgAccountBalance":"49197.00",
		// 	"ThirdPartyTransID":"1234567890",
		// 	"MSISDN":"254708374149",
		// 	"FirstName":"John",
		// 	"MiddleName":"",
		// 	"LastName":""
		// }

		$data = json_decode(file_get_contents('php://input'), true);

	    if(is_null($callback)){
		    return array('ResponseCode' => 0, 'ResponseDesc' => 'Success');
	    } else {
	        return call_user_func_array($callback, array($data)) 
				? array('ResponseCode' => 0, 'ResponseDesc' => 'Success') 
				: array('ResponseCode' => 1, 'ResponseDesc' => 'Failed');
	    }
	}
	
	/**
	 * @param callable $callback Defined function or closure to process data and return true/false
	 * 
	 * @return array
	 */    
	public static function reconcile(callable $callback = null)
	{
		$response = json_decode(file_get_contents('php://input'), true);
	    
        if(is_null($callback)){
			return array('resultCode' => 0, 'resultDesc' => 'Service request successful');
		 } else {
			return call_user_func_array($callback, array($response))
				? array('resultCode' => 0, 'resultDesc' => 'Service request successful') 
				: array('resultCode' => 1, 'resultDesc' => 'Service request failed');
		 }
	}
	
	/**
	 * @param callable $callback Defined function or closure to process data and return true/false
	 * 
	 * @return array
	 */
	public static function results(callable $callback = null)
	{
		$response = json_decode(file_get_contents('php://input'), true);
	    
        if(is_null($callback)){
			return array('resultCode' => 0, 'resultDesc' => 'Service request successful');
		 } else {
			return call_user_func_array($callback, array($response))
				? array('resultCode' => 0, 'resultDesc' => 'Service request successful')
				: array('resultCode' => 1, 'resultDesc' => 'Service request failed');
		 }
	}
	
	/**
	 * @param callable $callback Defined function or closure to process data and return true/false
	 * 
	 * @return array
	 */
	public static function timeout(callable $callback = null)
	{
		$response = json_decode(file_get_contents('php://input'), true);
	    
        if(is_null($callback)){
			return array('resultCode' => 0, 'resultDesc' => 'Service request successful');
		 } else {
			return call_user_func_array($callback, array($response))
				? array('resultCode' => 0, 'resultDesc' => 'Service request successful')
				: array('resultCode' => 1, 'resultDesc' => 'Service request failed');
		 }
	}

}
