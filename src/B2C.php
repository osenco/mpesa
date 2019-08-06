<?php

namespace Osen\Mpesa;

use Osen\Mpesa\Service;

class B2C extends Service
{
	/**
	 * Transfer funds between two paybills
	 * @param $receiver Receiving party phone
	 * @param $amount Amount to transfer
	 * @param $command Command ID
	 * @param $occassion
	 * @param $remarks
	 * 
	 * @return array
	 */
    public static function send($receiver, $amount = 10, $command = 'TRX', $remarks = '', $occassion = '')
    {
        $token  = parent::token();

        $phone  = (substr($phone, 0,1) == '+') ? str_replace('+', '', $phone) : $phone;
		$phone  = (substr($phone, 0,1) == '0') ? preg_replace('/^0/', '254', $phone) : $phone;

        $endpoint   = (parent::$config->env == 'live')
            ? 'https://api.safaricom.co.ke/mpesa/b2c/v1/paymentrequest'
            : 'https://sandbox.safaricom.co.ke/mpesa/b2c/v1/paymentrequest';

        $curl   = curl_init();
        curl_setopt($curl, CURLOPT_URL, $endpoint);
        curl_setopt(
            $curl, 
            CURLOPT_HTTPHEADER, 
            array(
                'Content-Type:application/json', 
                'Authorization:Bearer '.$token
            )
        );

        $timestamp  = date('YmdHis');
        $env        = parent::$config->env;
        $plaintext  = parent::$config->password;
        $publicKey  = file_get_contents('certs/'.$env.'/cert.cr');

        openssl_public_encrypt($plaintext, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING);
        $password   = base64_encode($encrypted);

        $curl_post_data = array(
            'InitiatorName'       => parent::$config->username,
            'SecurityCredential'  => $password,
            'CommandID'           => $command,
            'Amount'              => round($amount),
            'PartyA'              => parent::$config->shortcode,
            'PartyB'              => $phone,
            'Remarks'             => $remarks,
            'QueueTimeOutURL'     => parent::$config->timeout_url,
            'ResultURL'           => parent::$config->reconcile_url,
            'Occasion'            => $occassion
        );

        $data_string = json_encode($curl_post_data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $response = curl_exec($curl);
		
		return json_decode($response, true);
    }
    
}
