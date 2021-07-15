<?php

namespace Osen\Mpesa;

use Osen\Mpesa\Service;

class C2B extends Service
{
    /**
     * Registers your confirmation and validation URLs to M-Pesa.
     * Whenever M-Pesa receives a transaction on the shortcode, it triggers a validation request against the validation URL and the 3rd party system responds to M-Pesa with a validation response (either a success or an error code).
     * M-Pesa completes or cancels the transaction depending on the validation response it receives from the 3rd party system. A confirmation request of the transaction is then sent by M-Pesa through the confirmation URL back to the 3rd party which then should respond with a success acknowledging the confirmation.
     * @param callable $callback Defined function or closure to process data and return true/false
     * @param string $response_type Response Type
     * 
     * @return bool/array
     */
    public static function register(
        $callback = null,
        $response_type = "Completed"
    ) {
        $payload = array(
            "ShortCode"       => parent::$config->store,
            "ResponseType"    => $response_type,
            "ConfirmationURL" => parent::$config->confirmation_url,
            "ValidationURL"   => parent::$config->validation_url,
        );

        $response = parent::post("/c2b/v1/registerurl", $payload);
        $result   = json_decode($response, true);

        return is_null($callback)
            ? $result
            : $callback($result);
    }


    /**
     * @param string $phone The MSISDN sending the funds.
     * @param integer $amount The amount to be transacted.
     * @param string $reference Used with M-Pesa PayBills.
     * @param string $description A description of the transaction.
     * @param string $remark Remarks
     * @param callable $callback Defined function or closure to process data and return true/false
     *
     * @return array/bool Response
     */
    public static function stk(
        $phone,
        $amount,
        $reference = "ACCOUNT",
        $description = "Transaction Description",
        $remark = "Remark",
        $callback = null
    ) {
        $phone     = (substr($phone, 0, 1) == "+") ? str_replace("+", "", $phone) : $phone;
        $phone     = (substr($phone, 0, 1) == "0") ? preg_replace("/^0/", "254", $phone) : $phone;
        $phone     = (substr($phone, 0, 1) == "7") ? "254{$phone}" : $phone;

        $timestamp = date("YmdHis");
        $password  = base64_encode(parent::$config->shortcode . parent::$config->passkey . $timestamp);

        $payload = array(
            "BusinessShortCode" => parent::$config->store,
            "Password"          => $password,
            "Timestamp"         => $timestamp,
            "TransactionType"   => (parent::$config->type == 4) ? "CustomerPayBillOnline" : "CustomerBuyGoodsOnline",
            "Amount"            => round($amount),
            "PartyA"            => $phone,
            "PartyB"            => parent::$config->shortcode,
            "PhoneNumber"       => $phone,
            "CallBackURL"       => parent::$config->callback_url,
            "AccountReference"  => $reference,
            "TransactionDesc"   => $description,
            "Remark"            => $remark,
        );

        $response = parent::post("/stkpush/v1/processrequest", $payload);
        $result   = json_decode($response, true);

        return is_null($callback)
            ? $result
            : $callback($result);
    }

    /**
     * Simulates a C2B request
     * 
     * @param string $phone Receiving party phone
     * @param integer $amount Amount to transfer
     * @param string $command Command ID
     * @param string $reference
     * @param callable $callback Defined function or closure to process data and return true/false
     *
     * @return array
     */
    public static function simulate(
        $phone = null,
        $amount = 10,
        $reference = "TRX",
        $command = "",
        callable $callback = null
    ) {
        $phone = (substr($phone, 0, 1) == "+") ? str_replace("+", "", $phone) : $phone;
        $phone = (substr($phone, 0, 1) == "0") ? preg_replace("/^0/", "254", $phone) : $phone;
        $phone = (substr($phone, 0, 1) == "7") ? "254{$phone}" : $phone;

        $payload = array(
            "ShortCode"     => parent::$config->shortcode,
            "CommandID"     => $command,
            "Amount"        => round($amount),
            "Msisdn"        => $phone,
            "BillRefNumber" => $reference,
        );

        $response = parent::post("/c2b/v1/simulate", $payload);
        $result   = json_decode($response, true);

        return is_null($callback)
            ? $result
            : $callback($result);
    }
}
