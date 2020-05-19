<?php

namespace Osen\Mpesa;

use Osen\Mpesa\Service;

class B2B extends Service
{
    /**
     * Transfer funds between two paybills
     * @param $receiver Receiving party paybill
     * @param $receiver_type Receiver party type
     * @param $amount Amount to transfer
     * @param $command Command ID
     * @param $reference Account Reference mandatory for “BusinessPaybill” CommandID.
     * @param $remarks
     *
     * @return array
     */
    public static function send(
        $receiver, 
        $receiver_type, 
        $amount = 10, 
        $command = "", 
        $reference = "TRX", 
        $remarks = "", 
        $callback = null
        )
    {
        $token = parent::token();

        $env = parent::$config->env;

        $endpoint = ($env == "live")
        ? "https://api.safaricom.co.ke/mpesa/b2b/v1/paymentrequest"
        : "https://sandbox.safaricom.co.ke/mpesa/b2b/v1/paymentrequest";

        $plaintext = parent::$config->password;
        $publicKey = file_get_contents(__DIR__ . "/certs/{$env}/cert.cer");

        openssl_public_encrypt($plaintext, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING);
        $password = base64_encode($encrypted);

        $curl_post_data = array(
            "Initiator"              => parent::$config->username,
            "SecurityCredential"     => $password,
            "CommandID"              => $command,
            "SenderIdentifierType"   => parent::$config->type,
            "RecieverIdentifierType" => $receiver_type,
            "Amount"                 => $amount,
            "PartyA"                 => parent::$config->shortcode,
            "PartyB"                 => $receiver,
            "AccountReference"       => $reference,
            "Remarks"                => $remarks,
            "QueueTimeOutURL"        => parent::$config->timeout_url,
            "ResultURL"              => parent::$config->result_url,
        );
        $response = parent::remote_post($endpoint, $token, $curl_post_data);
        $result   = json_decode($response, true);

        return is_null($callback)
        ? $result
        : \call_user_func_array($callback, array($result));
    }
}