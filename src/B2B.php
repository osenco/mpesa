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
	 * @param string $reference Account Reference mandatory for “BusinessPaybill” CommandID.
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
		
		return json_decode($response, true);
    }
    
}

// {
//   "Result":{
//     "ResultType":0,
//     "ResultCode":0,
//     "ResultDesc":"The service request has been accepted successfully.",
//     "OriginatorConversationID":"8551-61996-3",
//     "ConversationID":"AG_20170727_00006baee344f4ce0796",
//     "TransactionID":"LGR519G2QV",
//     "ResultParameters":{
//       "ResultParameter":[
//         {
//           "Key":"InitiatorAccountCurrentBalance",
//           "Value":"{ Amount={BasicAmount=46713.00, MinimumAmount=4671300, CurrencyCode=KES}}"
//         },
//         {
//           "Key":"DebitAccountCurrentBalance",
//           "Value":"{Amount={BasicAmount=46713.00, MinimumAmount=4671300, CurrencyCode=KES}}"
//         },
//         {
//           "Key":"Amount",
//           "Value":10
//         },
//         {
//           "Key":"DebitPartyAffectedAccountBalance",
//           "Value":"Working Account|KES|46713.00|46713.00|0.00|0.00"
//         },
//         {
//           "Key":"TransCompletedTime",
//           "Value":20170727102524
//         },
//         {
//           "Key":"DebitPartyCharges",
//           "Value":"Business Pay Bill Charge|KES|77.00"
//         },
//         {
//           "Key":"ReceiverPartyPublicName",
//           "Value":"603094 - Safaricom3117"
//         },
//         {
//           "Key":"Currency",
//           "Value":"KES"
//         }
//       ]
//     },
//     "ReferenceData":{
//       "ReferenceItem":[
//         {
//           "Key":"BillReferenceNumber",
//           "Value":"aaa"
//         },
//         {
//           "Key":"QueueTimeoutURL",
//           "Value":"https://internalsandbox.safaricom.co.ke/mpesa/b2bresults/v1/submit"
//         },
//         {
//           "Key":"Occasion"
//         }
//       ]
//     }
//   }
// }