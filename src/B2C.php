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
    public static function send($phone, $amount = 10, $command = "BusinessPayment", $remarks = "", $occassion = "", $callback = null)
    {
        $env       = parent::$config->env;

        $phone    = (substr($phone, 0, 1) == "+") ? str_replace("+", "", $phone) : $phone;
        $phone    = (substr($phone, 0, 1) == "0") ? preg_replace("/^0/", "254", $phone) : $phone;
        $phone = (substr($phone, 0, 1) == "7") ? "254{$phone}" : $phone;
        $phone = ($env == "live") ? $phone : "254708374149";

        $endpoint = ($env == "live")
            ? "https://api.safaricom.co.ke/mpesa/b2c/v1/paymentrequest"
            : "https://sandbox.safaricom.co.ke/mpesa/b2c/v1/paymentrequest";

        $timestamp = date("YmdHis");
        $plaintext = parent::$config->password;
        $publicKey = file_get_contents(__DIR__ . "/certs/{$env}/cert.cer");

        openssl_public_encrypt($plaintext, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING);
        $password = base64_encode($encrypted);
        $password = ($env == "live") ? $password : "Safaricom568!#";

        $curl_post_data = array(
            "InitiatorName"      => parent::$config->username,
            "SecurityCredential" => $password,
            "CommandID"          => $command,
            "Amount"             => round($amount),
            "PartyA"             => parent::$config->shortcode,
            "PartyB"             => $phone,
            "Remarks"            => $remarks,
            "QueueTimeOutURL"    => parent::$config->timeout_url,
            "ResultURL"          => parent::$config->results_url,
            "Occasion"           => $occassion,
        );

        $response = parent::remote_post($endpoint, $curl_post_data);
        $result   = json_decode($response, true);

        return is_null($callback)
            ? $result
            : \call_user_func_array($callback, array($result));
    }
}
