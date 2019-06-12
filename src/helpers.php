<?php

if (!function_exists('setup_mpesa')) {
	function setup_mpesa(array $config = [], $api = 'C2B')
	{
    	$API = '\\Osen\\Mpesa\\'.strtoupper($api);
		return $API::init($config);
	}
}

if (!function_exists('stk_push')) {
	function stk_push($phone, $amount, $reference)
	{
		return \Osen\Mpesa\STK($phone, $amount, $reference);
	}
}

if (!function_exists('c2b_request')) {
	function c2b_request($phone, $amount, $reference)
	{
		return \Osen\Mpesa\C2B($phone, $amount, $reference);
	}
}

if (!function_exists('b2c_request')) {
	function b2c_request($phone, $amount, $reference)
	{
		return \Osen\Mpesa\B2C($phone, $amount, $reference);
	}
}

if (!function_exists('b2b_request')) {
	function b2b_request()
	{
		return \Osen\Mpesa\B2B();
	}
}
