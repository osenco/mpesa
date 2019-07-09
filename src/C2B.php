<?php

namespace Osen\Mpesa;

use Osen\Mpesa\Service;

class C2B extends Service
{
    /**
     * Registers your confirmation and validation URLs to M-Pesa.
     * Whenever M-Pesa receives a transaction on the shortcode, it triggers a validation request against the validation URL and the 3rd party system responds to M-Pesa with a validation response (either a success or an error code). 
     * M-Pesa completes or cancels the transaction depending on the validation response it receives from the 3rd party system. A confirmation request of the transaction is then sent by M-Pesa through the confirmation URL back to the 3rd party which then should respond with a success acknowledging the confirmation.
     */
    public static function register($callback = null)
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
		$response   = curl_exec($curl);

		$content    = json_decode($response, true);
        
		if(is_null($callback)){
            $status     = ($response || isset($content['ResponseDescription'])) 
                ? $content['ResponseDescription'] 
                : 'Sorry could not connect to Daraja. Check your connection/configuration and try again.';
            return array('Registration status' => $status);
        } else {
            return \call_user_func_array($callback, $content);
        }
    }

	/**
	 * Simulates a C2B request
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
        $curl_post_data     = array(
            'ShortCode'     => parent::$config->shortcode,
            'CommandID'     => $command,
            'Amount'        => round($amount),
            'Msisdn'        => $phone,
            'BillRefNumber' => $reference
        );
        $data_string        = json_encode($curl_post_data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $curl_response = curl_exec($curl);
        $response = curl_exec($curl);
        
        return json_decode($response, true);
    }
    
}
