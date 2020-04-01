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
    public static function register($callback = null, $response_type = "Completed")
    {
        $endpoint = (parent::$config->env == "live")
        ? "https://api.safaricom.co.ke/mpesa/c2b/v1/registerurl"
        : "https://sandbox.safaricom.co.ke/mpesa/c2b/v1/registerurl";

        $curl_post_data = array(
            "ShortCode"       => parent::$config->shortcode,
            "ResponseType"    => $response_type,
            "ConfirmationURL" => parent::$config->confirmation_url,
            "ValidationURL"   => parent::$config->validation_url,
        );

        $response = parent::remote_post($endpoint, $curl_post_data);
        $result   = json_decode($response, true);

        return is_null($callback)
        ? $result
        : \call_user_func_array($callback, array($result));
    }

    /**
     * Simulates a C2B request
     * 
     * @param $phone Receiving party phone
     * @param $amount Amount to transfer
     * @param $command Command ID
     * @param $reference
     *
     * @return array
     */
    public static function send($phone = null, $amount = 10, $reference = "TRX", $command = "", $callback = null)
    {
        $phone = (substr($phone, 0, 1) == "+") ? str_replace("+", "", $phone) : $phone;
        $phone = (substr($phone, 0, 1) == "0") ? preg_replace("/^0/", "254", $phone) : $phone;
        $phone = (substr($phone, 0, 1) == "7") ? "254{$phone}" : $phone;

        $endpoint = (parent::$config->env == "live")
        ? "https://api.safaricom.co.ke/mpesa/c2b/v1/simulate"
        : "https://sandbox.safaricom.co.ke/mpesa/c2b/v1/simulate";

        $curl_post_data = array(
            "ShortCode"     => parent::$config->shortcode,
            "CommandID"     => $command,
            "Amount"        => round($amount),
            "Msisdn"        => $phone,
            "BillRefNumber" => $reference,
        );

        $response = parent::remote_post($endpoint, $curl_post_data);
        $result   = json_decode($response, true);

        return is_null($callback)
        ? $result
        : \call_user_func_array($callback, array($result));
    }

}