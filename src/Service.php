<?php

namespace Osen\Mpesa;

class Service
{
    function __construct()
    {
        # code...
    }

    public static function init(array $configs = [])
    {
        foreach ($configs as $key => $value) {
            self::$$key = $value;
        }
    }

    public static function token()
    {
        # code...
    }

    public static function status(string $transaction = null)
    {
        # code...
    }

    public static function balance(string $transaction = null)
    {
        # code...
    }

    public static function reconcile(array $data = [])
    {
        # code...
    }
}
