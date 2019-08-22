<?php

namespace Osen\Mpesa;

use Osen\Mpesa\Service;

class STK extends Service
{
    /**
     * @param $phone The MSISDN sending the funds.
     * @param $amount The amount to be transacted.
     * @param $reference Used with M-Pesa PayBills.
     * @param $description A description of the transaction.
     * @param $remark Remarks
     *
     * @return array Response
     */
    public static function send($phone, $amount, $reference = 'ACCOUNT', $description = 'Transaction Description', $remark = 'Remark')
    {
        $token = parent::token();

        $phone     = (substr($phone, 0, 1) == '+') ? str_replace('+', '', $phone) : $phone;
        $phone     = (substr($phone, 0, 1) == '0') ? preg_replace('/^0/', '254', $phone) : $phone;
        $timestamp = date('YmdHis');
        $password  = base64_encode(parent::$config->shortcode . parent::$config->passkey . $timestamp);

        $endpoint = (parent::$config->env == 'live')
        ? 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest'
        : 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $endpoint);
        curl_setopt(
            $curl,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type:application/json',
                'Authorization:Bearer ' . $token,
            )
        );
        $curl_post_data = array(
            'BusinessShortCode' => parent::$config->headoffice,
            'Password'          => $password,
            'Timestamp'         => $timestamp,
            'TransactionType'   => (parent::$config->type == 4) ? 'CustomerPayBillOnline' : 'CustomerBuyGoodsOnline',
            'Amount'            => round($amount),
            'PartyA'            => $phone,
            'PartyB'            => parent::$config->shortcode,
            'PhoneNumber'       => $phone,
            'CallBackURL'       => parent::$config->callback_url,
            'AccountReference'  => $reference,
            'TransactionDesc'   => $description,
            'Remark'            => $remark,
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
