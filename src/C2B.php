<?php

use Osen\Mpesa\Service;

namespace Osen\Mpesa;

class C2B extends Service
{

    public static function register()
    {
        $token = self::token();

		$endpoint = (self::$config->env == 'live') ? 'https://api.safaricom.co.ke/mpesa/c2b/v1/registerurl' : 'https://sandbox.safaricom.co.ke/mpesa/c2b/v1/registerurl';
		$curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $endpoint);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization:Bearer '.$token));
			
		$curl_post_data = array(
            'ShortCode' 		=> self::$config->shortcode,
			'ResponseType' 		=> 'Cancelled',
			'ConfirmationURL' 	=> self::$config->confirmation_url,
			'ValidationURL' 	=> self::$config->validation_url
      );
		$data_string = json_encode($curl_post_data);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($curl, CURLOPT_HEADER, false);
		$response = curl_exec($curl);
		$content = json_decode($response)->ResponseDescription;
		if ($response || isset($content->ResponseDescription)) {
			$status = $content->ResponseDescription;
		} else {
			$status = 'Sorry could not connect to Daraja. Check your configuration and try again.';
		}
		return array('Registration status' => $status);
    }

    public static function send(string $phone = null, int $amount = 10, string $reference = 'TRX')
    {
        $token = parent::token();

        // Remove the plus sign before the customer's phone number if present
        if (substr($phone, 0,1) == '+') $phone = str_replace('+', '', $phone);
        if (substr($phone, 0,1) == '0') $phone = preg_replace('/^0/', '254', $phone);
        
        $endpoint = (parent::$config->env == 'live') ? 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest' : 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
        $timestamp = date('YmdHis');
        $password = base64_encode(parent::$config->shortcode.parent::$config->passkey.$timestamp);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $endpoint);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type:application/json', 'Authorization:Bearer '.$token]);
        $curl_post_data = array(
            'BusinessShortCode' => parent::$config->headoffice,
            'Password'          => $password,
            'Timestamp'         => $timestamp,
            'TransactionType'   => 'CustomerPayBillOnline',
            'Amount'            => round($amount),
            'PartyA'            => $phone,
            'PartyB'            => parent::$config->shortcode,
            'PhoneNumber'       => $phone,
            'CallBackURL'       => parent::$config->callback_url,
            'AccountReference'  => $reference,
            'TransactionDesc'   => 'WooCommerce Payment',
            'Remark'            => 'WooCommerce Payment'
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
