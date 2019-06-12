<?php

if (!function_exists('setup_mpesa')) {
	function setup_mpesa(Array $config = [], $api = 'c2b')
	{
    $API = '\Osen\Mpesa\'.strtoupper($api);
		return $API::init($config);
	}
}
