<?php

if (!function_exists('setup_mpesa')) {
	function setup_mpesa(array $config = [], $api = 'C2B')
	{
    	$API = '\\Osen\\Mpesa\\'.strtoupper($api);
		return $API::init($config);
	}
}

if (!function_exists('stk_push')) {
	function stk_push()
	{
		return \Osen\Mpesa\STK();
	}
}

if (!function_exists('c2b_request')) {
	function c2b_request()
	{
		return \Osen\Mpesa\C2B();
	}
}

if (!function_exists('b2c_request')) {
	function b2c_request()
	{
		return \Osen\Mpesa\B2C();
	}
}

if (!function_exists('b2b_request')) {
	function b2b_request()
	{
		return \Osen\Mpesa\B2B();
	}
}
