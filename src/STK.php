<?php

use Osen\Mpesa\Service;

namespace Osen\Mpesa;

class STK extends Service
{

    public static function __invoke(string $phone = null, int $amount = 10, string $reference = 'TRX')
    {
        $token = parent::token();
        
		// Remove the plus sign before the customer's phone number if present
		if ( substr( $phone, 0,1 ) == "+" ) $phone = str_replace( "+", "", $phone );
		if ( substr( $phone, 0,1 ) == "0" ) $phone = preg_replace('/^0/', '254', $phone);
        
		$endpoint = ( parent::$config->env == 'live' ) ? 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest' : 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
		$timestamp = date( 'YmdHis' );
        $password = base64_encode( parent::$config->shortcode.parent::$config->passkey.$timestamp );
        $curl = curl_init();
        curl_setopt( $curl, CURLOPT_URL, $endpoint );
        curl_setopt( $curl, CURLOPT_HTTPHEADER, ['Content-Type:application/json', 'Authorization:Bearer '.$token ] );
        $curl_post_data = array( 
            'BusinessShortCode' => parent::$config->honumber,
            'Password' 			=> $password,
            'Timestamp' 		=> $timestamp,
            'TransactionType' 	=> 'CustomerPayBillOnline',
            'Amount' 			=> round( $amount ),
            'PartyA' 			=> $phone,
            'PartyB' 			=> parent::$config->shortcode,
            'PhoneNumber' 		=> $phone,
            'CallBackURL' 		=> parent::$config->callback_url,
            'AccountReference' 	=> $reference,
            'TransactionDesc' 	=> 'WooCommerce Payment',
            'Remark'			=> 'WooCommerce Payment'
        );
        $data_string = json_encode( $curl_post_data );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $curl, CURLOPT_POST, true );
        curl_setopt( $curl, CURLOPT_POSTFIELDS, $data_string );
        curl_setopt( $curl, CURLOPT_HEADER, false );
        $response = curl_exec( $curl );
		
		return json_decode( $response );
    }
    
}
