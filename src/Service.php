<?php

namespace Osen\Mpesa;

class Service
{
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
			'username'          => '',
			'passkey'           => 'Your Online Passkey',
			'validation_url'    => '/mpesa/validate',
			'confirmation_url'  => '/mpesa/confirm',
			'callback_url'      => '/mpesa/reconcile',
			'timeout_url'       => '/mpesa/timeout',
		);

		foreach ($configs as $key => $value) {
			$parsed = array_combine($defaults, $configs);
		}
        self::$config 	= (object)$parsed;
    }

    public static function token()
    {
        $endpoint = (self::$config->env == 'live') ? 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials' : 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
		$credentials = base64_encode(self::$config->key.':'.self::$config->secret);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $endpoint);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Basic '.$credentials));
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $curl_response = curl_exec($curl);
        
		return json_decode($curl_response)->access_token;
    }

    public static function status(string $transaction = null)
    {
        $transaction = $args['id'];
    	$CommandID = isset($args['command']) ? $args['command'] : 'TransactionStatusQuery';
    	$Remarks = isset($args['remarks']) ? $args['remarks'] : 'Transaction Status Query';
    	$Occasion = isset($args['occassion']) ? $args['occassion'] : '';
      	$mpesa = new self;
		$token = self::token();
      	$endpoint = (self::$config->env == 'live') ? 'https://api.safaricom.co.ke/mpesa/transactionstatus/v1/query' : 'https://sandbox.safaricom.co.ke/mpesa/transactionstatus/v1/query';
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
	        'CommandID'           => $CommandID,
	        'TransactionID'       => $transaction,
	        'PartyA'              => self::$config->shortcode,
	        'IdentifierType'      => self::$config->type,
	        'ResultURL'           => self::$config->result_url,
	        'QueueTimeOutURL'     => self::$config->timeout_url,
	        'Remarks'             => $Remarks,
	        'Occasion'            => $Occasion
	  	);
        $data_string = json_encode($curl_post_data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $response = curl_exec($curl);
		
		return json_decode($response);
    }

    public static function reverse($transaction)
    {
        $token = self::token();
    	$endpoint = (self::$config->env == 'live') ? 'https://api.safaricom.co.ke/mpesa/reversal/v1/request' : 'https://sandbox.safaricom.co.ke/mpesa/reversal/v1/request';
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
	        'Amount'                  => $Amount,
	        'ReceiverParty'           => $ReceiverParty,
	        'RecieverIdentifierType'  => $RecieverIdentifierType,
	        'ResultURL'               => self::$config->result_url,
	        'QueueTimeOutURL'         => self::$config->timeout_url,
	        'Remarks'                 => $Remarks,
	        'Occasion'                => $Occasion
	  	);
        $data_string = json_encode($curl_post_data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $response = curl_exec($curl);
		
		return json_decode($response);
    }

    public static function balance(int $transaction = null)
    {
        $token = self::token();
      	
        $endpoint = (self::$config->env == 'live') ? 'https://api.safaricom.co.ke/mpesa/accountbalance/v1/query' : 'https://sandbox.safaricom.co.ke/mpesa/accountbalance/v1/query';
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
	        'CommandID'           => $CommandID,
	        'Initiator'           => self::$config->username,
	        'SecurityCredential'  => self::$config->credentials,
	        'PartyA'              => self::$config->shortcode,
	        'IdentifierType'      => self::$config->type,
	        'Remarks'             => $Remarks,
	        'QueueTimeOutURL'     => self::$config->timeout_url,
	        'ResultURL'           => self::$config->result_url
	  );
        $data_string = json_encode($curl_post_data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $response = curl_exec($curl);
		
		return json_decode($response);
    }

    public static function validate($data = null, $callback = null)
	{
		$data = is_null($data) ? json_decode(file_get_contents('php://input'), true) : array();

	    if(is_null($callback)){
		    return array(
		        'ResponseCode'            => 0, 
		        'ResponseDesc'            => 'Success',
		        'ThirdPartyTransID'       => isset($data['transID']) ? $data['transID'] : 0
	      	);
	    } else {
	        if (!call_user_func_array($callback, array($data))) {
	          	return array(
	            	'ResponseCode'        => 1, 
	            	'ResponseDesc'        => 'Failed',
	            	'ThirdPartyTransID'   => isset($data['transID']) ? $data['transID'] : 0
	           	);
	        } else {
	          	return array(
	            	'ResponseCode'        => 0, 
	            	'ResponseDesc'        => 'Success',
	            	'ThirdPartyTransID'   => isset($data['transID']) ? $data['transID'] : 0
	         	);
	        }
	    }
    }

    public static function confirm($data = null, $callback = null)
	{
		$data = is_null($data) ? json_decode(file_get_contents('php://input'), true) : array();

	    if(is_null($callback)){
		    return array(
		        'ResponseCode'          => 0, 
		        'ResponseDesc'          => 'Success',
		        'ThirdPartyTransID'     =>  isset($data['transID']) ? $data['transID'] : 0
		  	);
	    } else {
	      	if (!call_user_func_array($callback, array($data))) {
	        	return array(
	          		'ResponseCode'        => 1, 
	          		'ResponseDesc'        => 'Failed',
	          		'ThirdPartyTransID'   => isset($data['transID']) ? $data['transID'] : 0
	         	);
	      	} else {
	        	return array(
	          		'ResponseCode'        => 0, 
	          		'ResponseDesc'        => 'Success',
	          		'ThirdPartyTransID'   => isset($data['transID']) ? $data['transID'] : 0
	         	);
	      	}
	    }
	}
    	
	/**
	* Function to process response data for reconciliation
	* @param String $callback - Optional callable function to process the response - must return boolean
	* @return bool/array
	*/            
	public static function reconcile($data = null, $callback = null)
	{
		if(is_null($data)){
			$response = json_decode(file_get_contents('php://input'), true);
			$response = isset($response['Body']) ? $response['Body'] : array();
		} else {
			$response = $data;
		}
	    
        return is_null($callback) ? array('resultCode' => 0, 'resultDesc' => 'Reconciliation successful') : call_user_func_array($callback, array($response));
	}

	/**
	* Function to process response data if system times out
	* @param String $callback - Optional callable function to process the response - must return boolean
	* @return bool/array
	*/ 
	public static function timeout($data = null, $callback = null)
	{
		if(is_null($data)){
			$response = json_decode(file_get_contents('php://input'), true);
			$response = isset($response['Body']) ? $response['Body'] : array();
		} else {
			$response = $data;
		}
		if(is_null($callback)){
			return true;
		} else {
			return call_user_func_array($callback, array($response));
		}
	}
}
