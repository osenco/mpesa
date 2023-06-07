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
    public static function send(
        $phone,
        $amount = 10,
        $command = "BusinessPayment",
        $remarks = "",
        $occassion = "",
        callable $callback = null
    ) {
        $env       = parent::$config->env;
        $phone     = '254'.substr($phone, -9);
        $plaintext = parent::$config->password;
        $publicKey = file_get_contents(__DIR__ . "/certs/{$env}/cert.cer");

        openssl_public_encrypt($plaintext, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING);
        $password = base64_encode($encrypted);

        $payload  = array (
            "InitiatorName"      => parent::$config->username,
            "SecurityCredential" => ($env == "live") ? $password : "Safaricom568!#",
            "CommandID"          => $command,
            "Amount"             => round($amount),
            "PartyA"             => parent::$config->shortcode,
            "PartyB"             => $phone,
            "Remarks"            => $remarks,
            "QueueTimeOutURL"    => parent::$config->timeout_url,
            "ResultURL"          => parent::$config->results_url,
            "Occasion"           => $occassion,
        );

        $response = parent::post("/b2c/v1/paymentrequest", $payload);
        $result   = json_decode($response, true);

        return is_null($callback)
            ? $result
            : $callback($result);
    }
}
