<?php

namespace Osen\Mpesa;

class Service
{
    /**
     * @var object $config Configuration options
     */
    public static $config;

    public static function init($configs)
    {
        $base     = (isset($_SERVER["HTTPS"]) ? "https" : "http") . "://" . (isset($_SERVER["SERVER_NAME"]) ? $_SERVER["SERVER_NAME"] : '');
        $defaults = array(
            "env"              => "sandbox",
            "type"             => 4,
            "shortcode"        => "174379",
            "headoffice"       => "174379",
            "key"              => "Your Consumer Key",
            "secret"           => "Your Consumer Secret",
            "username"         => "apitest",
            "password"         => "",
            "passkey"          => "bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919",
            "validation_url"   => $base . "/lipwa/validate",
            "confirmation_url" => $base . "/lipwa/confirm",
            "callback_url"     => $base . "/lipwa/reconcile",
            "timeout_url"      => $base . "/lipwa/timeout",
            "results_url"      => $base . "/lipwa/results",
        );

        if (!empty($configs) && (!isset($configs["headoffice"]) || empty($configs["headoffice"]))) {
            $defaults["headoffice"] = $configs["shortcode"];
        }

        foreach ($defaults as $key => $value) {
            if (isset($configs[$key])) {
                $defaults[$key] = $configs[$key];
            }
        }

        self::$config = (object) $defaults;
    }

    public static function remote_get($endpoint, $credentials = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $endpoint);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Basic " . $credentials));
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        return curl_exec($curl);
    }

    public static function remote_post($endpoint, $data = array())
    {
        $token       = self::token();
        $curl        = curl_init();
        $data_string = json_encode($data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_URL, $endpoint);
        curl_setopt(
            $curl,
            CURLOPT_HTTPHEADER,
            array(
                "Content-Type:application/json",
                "Authorization:Bearer " . $token,
            )
        );

        return curl_exec($curl);
    }

    /**
     * @return string Access token
     */
    public static function token()
    {
        $endpoint = (self::$config->env == "live")
            ? "https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials"
            : "https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials";

        $credentials = base64_encode(self::$config->key . ":" . self::$config->secret);
        $response    = self::remote_get($endpoint, $credentials);
        $result      = json_decode($response);

        return isset($result->access_token) ? $result->access_token : "";
    }

    /**
     * @param $transaction
     * @param $command
     * @param $remarks
     * @param $occassion\
     *
     * @return array Result
     */
    public static function status($transaction, $command = "TransactionStatusQuery", $remarks = "Transaction Status Query", $occasion = "Transaction Status Query", $callback = null)
    {
        $env       = self::$config->env;
        $plaintext = self::$config->password;
        $publicKey = file_get_contents(__DIR__ . "/certs/{$env}/cert.cer");

        openssl_public_encrypt($plaintext, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING);
        $password = base64_encode($encrypted);

        $endpoint = ($env == "live")
            ? "https://api.safaricom.co.ke/lipwa/transactionstatus/v1/query"
            : "https://sandbox.safaricom.co.ke/lipwa/transactionstatus/v1/query";

        $curl_post_data = array(
            "Initiator"          => self::$config->username,
            "SecurityCredential" => $password,
            "CommandID"          => $command,
            "TransactionID"      => $transaction,
            "PartyA"             => self::$config->shortcode,
            "IdentifierType"     => self::$config->type,
            "ResultURL"          => self::$config->results_url,
            "QueueTimeOutURL"    => self::$config->timeout_url,
            "Remarks"            => $remarks,
            "Occasion"           => $occasion,
        );
        $response = self::remote_post($endpoint, $curl_post_data);
        $result   = json_decode($response, true);

        return is_null($callback)
            ? $result
            : \call_user_func_array($callback, array($result));
    }

    /**
     * @param $transaction
     * @param $amount
     * @param $receiver
     * @param $receiver_type
     * @param $remarks
     * @param $occassion
     *
     * @return array Result
     */
    public static function reverse($transaction, $amount, $receiver, $receiver_type = 3, $remarks = "Transaction Reversal", $occasion = "Transaction Reversal", $callback = null)
    {
        $env       = self::$config->env;
        $plaintext = self::$config->password;
        $publicKey = file_get_contents(__DIR__ . "/certs/{$env}/cert.cer");

        openssl_public_encrypt($plaintext, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING);
        $password = base64_encode($encrypted);

        $endpoint = ($env == "live")
            ? "https://api.safaricom.co.ke/lipwa/reversal/v1/request"
            : "https://sandbox.safaricom.co.ke/lipwa/reversal/v1/request";

        $curl_post_data = array(
            "CommandID"              => "TransactionReversal",
            "Initiator"              => self::$config->business,
            "SecurityCredential"     => $password,
            "TransactionID"          => $transaction,
            "Amount"                 => $amount,
            "ReceiverParty"          => $receiver,
            "RecieverIdentifierType" => $receiver_type,
            "ResultURL"              => self::$config->results_url,
            "QueueTimeOutURL"        => self::$config->timeout_url,
            "Remarks"                => $remarks,
            "Occasion"               => $occasion,
        );

        $response = self::remote_post($endpoint, $curl_post_data);
        $result   = json_decode($response, true);

        return is_null($callback)
            ? $result
            : \call_user_func_array($callback, array($result));
    }

    /**
     * @param $command
     * @param $remarks
     * @param $occassion
     *
     * @return array Result
     */
    public static function balance($command, $remarks = "Balance Query", $callback = null)
    {
        $env       = self::$config->env;
        $plaintext = self::$config->password;
        $publicKey = file_get_contents(__DIR__ . "/certs/{$env}/cert.cer");

        openssl_public_encrypt($plaintext, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING);
        $password = base64_encode($encrypted);

        $endpoint = ($env == "live")
            ? "https://api.safaricom.co.ke/lipwa/accountbalance/v1/query"
            : "https://sandbox.safaricom.co.ke/lipwa/accountbalance/v1/query";

        $curl_post_data = array(
            "CommandID"          => $command,
            "Initiator"          => self::$config->username,
            "SecurityCredential" => $password,
            "PartyA"             => self::$config->shortcode,
            "IdentifierType"     => self::$config->type,
            "Remarks"            => $remarks,
            "QueueTimeOutURL"    => self::$config->timeout_url,
            "ResultURL"          => self::$config->results_url,
        );

        $response = self::remote_post($endpoint, $curl_post_data);
        $result   = json_decode($response, true);

        return is_null($callback)
            ? $result
            : \call_user_func_array($callback, array($result));
    }

    /**
     * @param callable $callback Defined function or closure to process data and return true/false
     *
     * @return array
     */
    public static function validate($callback = null)
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (is_null($callback)) {
            return array("ResultCode" => 0, "ResultDesc" => "Success");
        } else {
            return call_user_func_array($callback, array($data))
                ? array("ResultCode" => 0, "ResultDesc" => "Success")
                : array("ResultCode" => 1, "ResultDesc" => "Failed");
        }
    }

    /**
     * @param callable $callback Defined function or closure to process data and return true/false
     *
     * @return array
     */
    public static function confirm($callback = null)
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (is_null($callback)) {
            return array("ResultCode" => 0, "ResultDesc" => "Success");
        } else {
            return call_user_func_array($callback, array($data))
                ? array("ResultCode" => 0, "ResultDesc" => "Success")
                : array("ResultCode" => 1, "ResultDesc" => "Failed");
        }
    }

    /**
     * @param callable $callback Defined function or closure to process data and return true/false
     *
     * @return array
     */
    public static function reconcile(callable $callback = null)
    {
        $response = json_decode(file_get_contents("php://input"), true);

        if (is_null($callback)) {
            return array("ResultCode" => 0, "ResultDesc" => "Service request successful");
        } else {
            return call_user_func_array($callback, array($response))
                ? array("ResultCode" => 0, "ResultDesc" => "Service request successful")
                : array("ResultCode" => 1, "ResultDesc" => "Service request failed");
        }
    }

    /**
     * @param callable $callback Defined function or closure to process data and return true/false
     *
     * @return array
     */
    public static function results(callable $callback = null)
    {
        $response = json_decode(file_get_contents("php://input"), true);

        if (is_null($callback)) {
            return array("ResultCode" => 0, "ResultDesc" => "Service request successful");
        } else {
            return call_user_func_array($callback, array($response))
                ? array("ResultCode" => 0, "ResultDesc" => "Service request successful")
                : array("ResultCode" => 1, "ResultDesc" => "Service request failed");
        }
    }

    /**
     * @param callable $callback Defined function or closure to process data and return true/false
     *
     * @return array
     */
    public static function timeout(callable $callback = null)
    {
        $response = json_decode(file_get_contents("php://input"), true);

        if (is_null($callback)) {
            return array("ResultCode" => 0, "ResultDesc" => "Service request successful");
        } else {
            return call_user_func_array($callback, array($response))
                ? array("ResultCode" => 0, "ResultDesc" => "Service request successful")
                : array("ResultCode" => 1, "ResultDesc" => "Service request failed");
        }
    }
}
