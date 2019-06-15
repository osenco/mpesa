<?php

use Osen\Mpesa\Service;

namespace Osen\Mpesa;

class B2B extends Service
{
	/**
	 * Transfer funds between two paybills
	 * @param string $receiver Receiving party paybill
	 * @param string $receiver_type Receiver party type
	 * @param int $amount Amount to transfer
	 * @param string $command Command ID
	 * @param string $reference 
	 * @param string $remarks
	 * 
	 * @return array
	 */
    public static function send(string $receiver, string $receiver_type, int $amount = 10, $command = '',  string $reference = 'TRX', string $remarks = '')
    {
        $token 		= parent::token();

		$env        = parent::$config->env;

        $endpoint 	= ($env == 'live')
			? 'https://api.safaricom.co.ke/mpesa/b2b/v1/paymentrequest'
			: 'https://sandbox.safaricom.co.ke/mpesa/b2b/v1/paymentrequest';

        $plaintext  = parent::$config->password;
        $publicKey  = file_get_contents('certs/'.$env.'/cert.cr');

        openssl_public_encrypt($plaintext, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING);
        $password 	= base64_encode($encrypted);

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
	        'SecurityCredential'      => $password,
	        'CommandID'               => $command,
	        'SenderIdentifierType'    => parent::$config->type,
	        'RecieverIdentifierType'  => $receiver_type,
	        'Amount'                  => $amount,
	        'PartyA'                  => parent::$config->shortcode,
	        'PartyB'                  => $receiver,
	        'AccountReference'        => $reference,
	        'Remarks'                 => $remarks,
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
