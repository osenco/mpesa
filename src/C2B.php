<?php

use Osen\Mpesa\Service;

namespace Osen\Mpesa;

class C2B extends Service
{

    public static function register()
    {
        $token      = parent::token();

		$endpoint   = (parent::$config->env == 'live') ? 
            'https://api.safaricom.co.ke/mpesa/c2b/v1/registerurl' : 
            'https://sandbox.safaricom.co.ke/mpesa/c2b/v1/registerurl';

		$curl       = curl_init();
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
            'ShortCode' 		=> parent::$config->shortcode,
			'ResponseType' 		=> 'Cancelled',
			'ConfirmationURL' 	=> parent::$config->confirmation_url,
			'ValidationURL' 	=> parent::$config->validation_url
        );
		$data_string = json_encode($curl_post_data);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($curl, CURLOPT_HEADER, false);
		$response = curl_exec($curl);

		$content = json_decode($response)->ResponseDescription;
        $status = ($response || isset($content->ResponseDescription)) 
            ? $content->ResponseDescription 
            : 'Sorry could not connect to Daraja. Check your configuration and try again.';
		
        return array('Registration status' => $status);
    }

	/**
	 * Transfer funds between two paybills
	 * @param string $phone Receiving party phone
	 * @param int $amount Amount to transfer
	 * @param string $command Command ID
	 * @param string $reference 
	 * 
	 * @return array
	 */
    public static function send(string $phone = null, int $amount = 10, string $reference = 'TRX', string $command = '')
    {
        $token = parent::token();

        $phone = (substr($phone, 0,1) == '+') ? str_replace('+', '', $phone) : $phone;
		$phone = (substr($phone, 0,1) == '0') ? preg_replace('/^0/', '254', $phone) : $phone;
        
        $endpoint = (parent::$config->env == 'live') 
            ? 'https://api.safaricom.co.ke/mpesa/c2b/v1/simulate' 
            : 'https://sandbox.safaricom.co.ke/mpesa/c2b/v1/simulate';
        
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
            'ShortCode' => parent::$config->shortcode,
            'CommandID' => $command,
            'Amount' => round($amount),
            'Msisdn' => $phone,
            'BillRefNumber' => $reference
        );
        $data_string = json_encode($curl_post_data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $curl_response = curl_exec($curl);
        $response = curl_exec($curl);
        
        return json_decode($response);
    }
    
}
