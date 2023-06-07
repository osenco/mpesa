<?php

namespace Osen\Mpesa;

use Osen\Mpesa\Service;

class Pull extends Service
{
    /**
     * Registers your confirmation and validation URLs to M-Pesa.
     * Whenever M-Pesa receives a transaction on the shortcode, it triggers a validation request against the validation URL and the 3rd party system responds to M-Pesa with a validation response (either a success or an error code).
     * M-Pesa completes or cancels the transaction depending on the validation response it receives from the 3rd party system. A confirmation request of the transaction is then sent by M-Pesa through the confirmation URL back to the 3rd party which then should respond with a success acknowledging the confirmation.
     * @param string $phone Nominated phone
     *  @param callable $callback Defined function or closure to process data and return true/false
     * 
     * @return bool/array 
     */
    public static function register($phone, $callback = null) {
        $payload = array(
            "ShortCode"       => parent::$config->store,
            "RequestType"     => 'Pull',
            "NominatedNumber" => $phone,
            "ConfirmationURL" => parent::$config->confirmation_url,
        );

        $response = parent::post("/pulltransactions/v1/register", $payload);
        $result   = json_decode($response, true);

        // {
        //   "ResponseRefID": "18633-7271215-1",
        //   "Response Status": "1001",
        //   "ShortCode": "600000",
        //   "Response Description": "ShortCode already Registered"
        // }

        return is_null($callback)
            ? $result
            : $callback($result);
    }


    /**
     * NB: This API pulls transactions for a period not exceeding 48hrs.
     * 
     * @param string $start The start period of the missing transactions in the format of 2019-07-31 20:35:21 / 2019-07-31 19:00
     * @param string $end The end of the period for the missing transactions in the format of 2019-07-31 20:35:21 / 2019-07-31 22:35
     * @param string $offset Starts from 0. The service uses offset as opposed to page numbers. The OFF SET value allows you to specify which row to start from retrieving data.
     * 
     * @return array /bool Response
     */
    public static function query(
        $start,
        $end,
        $offset = 0,
        $callback = null
    ) {
        $payload = array(
            "ShortCode"   => parent::$config->shortcode,
            "StartDate"   => $start,
            "EndDate"     => $end,
            "OffSetValue" => $offset,
        );

        $response = parent::post("/pulltransactions/v1/query", $payload);
        $result   = json_decode($response, true);

        // {
        //   "ResponseRefID": "26178-42530161-2",
        //   "ResponseCode": "1000",
        //   "ResponseMessage": "Success",
        //   "Response": [
        //     [
        //       {
        //         "transactionId": "yzlyrEsRG1",
        //         "trxDate": "2020-08-05T10:13:00Z",
        //         "msisdn": 72200000,
        //         "sender": "UAT2",
        //         "transactiontype": "c2b-pay-bill-debit",
        //         "billreference": "37207636392",
        //         "amount": "168.00",
        //         "organizationname": "Daraja Pull API Test"
        //       }
        //     ]
        //   ]
        // }

        return is_null($callback)
            ? $result
            : $callback($result);
    }

    /**
     * Simulates a C2B request
     * 
     * @param string $phone Receiving party phone
     * @param int $amount Amount to transfer
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