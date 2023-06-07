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
    ) {
        $env       = parent::$config->env;
        $plaintext = parent::$config->password;
        $publicKey = file_get_contents(__DIR__ . "/certs/{$env}/cert.cer");
       
        openssl_public_encrypt($plaintext, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING);
        $password = base64_encode($encrypted);

        $payload = array (
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
        
        $response = parent::post("/b2b/v1/paymentrequest", $payload);
        $result   = json_decode($response, true);

        return is_null($callback)
            ? $result
            : $callback($result);
    }
}
