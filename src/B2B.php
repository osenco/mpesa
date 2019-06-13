<?php

use Osen\Mpesa\Service;

namespace Osen\Mpesa;

class B2B extends Service
{

    public static function send(string $phone = null, int $amount = 10, string $reference = 'TRX')
    {
        $token = parent::token();

        $endpoint = (parent::$config->env == 'live') ? 'https://api.safaricom.co.ke/mpesa/b2b/v1/paymentrequest' : 'https://sandbox.safaricom.co.ke/mpesa/b2b/v1/paymentrequest';
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
	        'Initiator'               => parent::$config->username,
	        'SecurityCredential'      => parent::$config->credentials,
	        'CommandID'               => $commandID,
	        'SenderIdentifierType'    => parent::$config->type,
	        'RecieverIdentifierType'  => $RecieverIdentifierType,
	        'Amount'                  => $Amount,
	        'PartyA'                  => parent::$config->shortcode,
	        'PartyB'                  => $PartyB,
	        'AccountReference'        => $AccountReference,
	        'Remarks'                 => $Remarks,
	        'QueueTimeOutURL'         => parent::$config->timeout_url,
	        'ResultURL'               => parent::$config->result_url
	   );
        $data_string = json_encode($curl_post_data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $response = curl_exec($curl);
		
		return json_decode($response);
    }
    
}
