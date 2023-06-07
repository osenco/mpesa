<?php

namespace Osen\Mpesa;

class Service
{
    /**
     * @var object $config Configuration options
     */
    public static object $config;
    public static string $token;
    protected static string $baseUrl;

    /**
     * Setup global configuration for classes
     * @param array $configs Formatted configuration options
     *
     * @return void
     */
    public static function init($configs)
    {
        $base     = (isset($_SERVER["HTTPS"]) ? "https" : "http") . "://" . (isset($_SERVER["SERVER_NAME"]) ? $_SERVER["SERVER_NAME"] : '');
        $defaults = array (
            "env"              => "sandbox",
            "type"             => 4,
            "shortcode"        => "174379",
            "store"            => "174379",
            "key"              => "Your Consumer Key",
            "secret"           => "Your Consumer Secret",
            "username"         => "apitest",
            "password"         => "",
            "passkey"          => "bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919",
            "validation_url"   => $base . "api/lipwa/validate",
            "confirmation_url" => $base . "api/lipwa/confirm",
            "callback_url"     => $base . "api/lipwa/reconcile",
            "timeout_url"      => $base . "api/lipwa/timeout",
            "results_url"      => $base . "api/lipwa/results",
        );

        if (!empty($configs) && (!isset($configs["store"]) || empty($configs["store"]))) {
            $defaults["store"] = $configs["shortcode"];
        }

        foreach ($defaults as $key => $value) {
            if (isset($configs[$key])) {
                $defaults[$key] = $configs[$key];
            } else {
                $defaults[$key] = $value;
            }
        }

        self::$config = (object) $defaults;
        self::$baseUrl = (self::$config->env == "live")
            ? "https://api.safaricom.co.ke/mpesa"
            : "https://sandbox.safaricom.co.ke/mpesa";
    }

    /**
     * Perform a GET request to the M-PESA Daraja API
     * @param string $endpoint Daraja API URL Endpoint
     * @param string $credentials Formated Auth credentials
     *
     * @return string
     */
    public static function get($endpoint, $credentials = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, self::$baseUrl . $endpoint);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array ("Authorization: Basic " . $credentials));
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        return curl_exec($curl);
    }

    /**
     * Perform a POST request to the M-PESA Daraja API
     * @param string $endpoint Daraja API URL Endpoint
     * @param array $data Formated array of data to send
     *
     * @return string
     */
    public static function post($endpoint, $data = array ())
    {
        $curl        = curl_init();
        $data_string = json_encode($data);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_URL, self::$baseUrl . $endpoint);
        curl_setopt(
            $curl,
            CURLOPT_HTTPHEADER,
            array (
                "Content-Type:application/json",
                "Authorization:Bearer " . self::$token,
            )
        );

        return curl_exec($curl);
    }

    /**
     * Fetch $token To Authenticate Requests
     */
    public static function authorize($token = null, callable $callback = null)
    {
        if (is_null($token)) {
            $credentials = base64_encode(self::$config->key . ":" . self::$config->secret);
            $response    = self::get("/oauth/v1/generate?grant_type=client_credentials", $credentials);
            $result      = json_decode($response);

            self::$token = isset($result->access_token) ? $result->access_token : "";
        } else {
            self::$token = $token;
        }
    }

    /**
     * Get Status of a Transaction
     *
     * @param string $transaction
     * @param string $command
     * @param string $remarks
     * @param string $occassion
     *
     * @return array Result
     */
    public static function status(
        $transaction,
        $command = "TransactionStatusQuery",
        $remarks = "Transaction Status Query",
        $occasion = "Transaction Status Query",
        $callback = null
    ) {
        $env       = self::$config->env;
        $plaintext = self::$config->password;
        $publicKey = file_get_contents(__DIR__ . "/certs/{$env}/cert.cer");

        openssl_public_encrypt($plaintext, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING);
        $password = base64_encode($encrypted);

        $payload = array (
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
        $response = self::post("/transactionstatus/v1/query", $payload);
        $result   = json_decode($response, true);

        return is_null($callback)
            ? $result
            : $callback($result);
    }

    /**
     * Reverse a Transaction
     *
     * @param string $transaction
     * @param int $amount
     * @param int $receiver
     * @param string $receiver_type
     * @param string $remarks
     * @param string $occassion
     *
     * @return array Result
     */
    public static function reverse(
        $transaction,
        $amount,
        $receiver,
        $receiver_type = 3,
        $remarks = "Transaction Reversal",
        $occasion = "Transaction Reversal",
        $callback = null
    ) {
        $env       = self::$config->env;
        $plaintext = self::$config->password;
        $publicKey = file_get_contents(__DIR__ . "/certs/{$env}/cert.cer");

        openssl_public_encrypt($plaintext, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING);
        $password = base64_encode($encrypted);

        $payload = array (
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

        $response = self::post("/reversal/v1/request", $payload);
        $result   = json_decode($response, true);

        return is_null($callback)
            ? $result
            : $callback($result);
    }

    /**
     * Check Account Balance
     *
     * @param string $command
     * @param string $remarks
     * @param string $occassion
     *
     * @return array Result
     */
    public static function balance(
        $command,
        $remarks = "Balance Query",
        $callback = null
    ) {
        $env       = self::$config->env;
        $plaintext = self::$config->password;
        $publicKey = file_get_contents(__DIR__ . "/certs/{$env}/cert.cer");

        openssl_public_encrypt($plaintext, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING);
        $password = base64_encode($encrypted);

        $payload = array (
            "CommandID"          => $command,
            "Initiator"          => self::$config->username,
            "SecurityCredential" => $password,
            "PartyA"             => self::$config->shortcode,
            "IdentifierType"     => self::$config->type,
            "Remarks"            => $remarks,
            "QueueTimeOutURL"    => self::$config->timeout_url,
            "ResultURL"          => self::$config->results_url,
        );

        $response = self::post("/accountbalance/v1/query", $payload);
        $result   = json_decode($response, true);

        return is_null($callback)
            ? $result
            : $callback($result);
    }

    /**
     * Validate Transaction Data
     *
     * @param callable $callback Defined function or closure to process data and return true/false
     *
     * @return array 
     */
    public static function validate($callback = null)
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (is_null($callback)) {
            return array (
                "ResultCode" => 0,
                "ResultDesc" => "Success",
            );
        } else {
            return call_user_func_array ($callback, array ($data))
                ? array (
                    "ResultCode" => 0,
                    "ResultDesc" => "Success",
                )
                : array (
                    "ResultCode" => 1,
                    "ResultDesc" => "Failed",
                );
        }
    }

    /**
     * Confirm Transaction Data
     *
     * @param callable $callback Defined function or closure to process data and return true/false
     *
     * @return array 
     */
    public static function confirm($callback = null)
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (is_null($callback)) {
            return array (
                "ResultCode" => 0,
                "ResultDesc" => "Success",
            );
        } else {
            return call_user_func_array ($callback, array ($data))
                ? array (
                    "ResultCode" => 0,
                    "ResultDesc" => "Success",
                )
                : array (
                    "ResultCode" => 1,
                    "ResultDesc" => "Failed",
                );
        }
    }

    /**
     * Reconcile Transaction Using Instant Payment Notification from M-PESA
     *
     * @param callable $callback Defined function or closure to process data and return true/false
     *
     * @return array 
     */
    public static function reconcile(callable $callback = null)
    {
        $response = json_decode(file_get_contents("php://input"), true);

        if (is_null($callback)) {
            return array (
                "ResultCode" => 0,
                "ResultDesc" => "Service request successful",
            );
        } else {
            return call_user_func_array ($callback, array ($response))
                ? array (
                    "ResultCode" => 0,
                    "ResultDesc" => "Service request successful",
                )
                : array (
                    "ResultCode" => 1,
                    "ResultDesc" => "Service request failed",
                );
        }
    }

    /**
     * Process Results of an API Request
     *
     * @param callable $callback Defined function or closure to process data and return true/false
     */
    public static function results(callable $callback = null): array
    {
        $response = json_decode(file_get_contents("php://input"), true);

        if (is_null($callback)) {
            return array (
                "ResultCode" => 0,
                "ResultDesc" => "Service request successful",
            );
        } else {
            return call_user_func_array ($callback, array ($response))
                ? array (
                    "ResultCode" => 0,
                    "ResultDesc" => "Service request successful",
                )
                : array (
                    "ResultCode" => 1,
                    "ResultDesc" => "Service request failed",
                );
        }
    }

    /**
     * Process Transaction Timeout
     *
     * @param callable $callback Defined function or closure to process data and return true/false
     */
    public static function timeout(callable $callback = null): array
    {
        $response = json_decode(file_get_contents("php://input"), true);

        if (is_null($callback)) {
            return array (
                "ResultCode" => 0,
                "ResultDesc" => "Service request successful",
            );
        } else {
            return call_user_func_array ($callback, array ($response))
                ? array (
                    "ResultCode" => 0,
                    "ResultDesc" => "Service request successful",
                )
                : array (
                    "ResultCode" => 1,
                    "ResultDesc" => "Service request failed",
                );
        }
    }
}
