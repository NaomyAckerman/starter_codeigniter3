<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Memcached settings
| -------------------------------------------------------------------------
| Your Memcached servers can be specified below.
|
|	See: https://codeigniter.com/userguide3/libraries/caching.html#memcached
|
*/
$config = array(
	'default' => array(
		'hostname' => env('MEMCACHED_HOST', '127.0.0.1'),
		'port' => env('MEMCACHED_PORT', '11211'),
		'weight' => env('MEMCACHED_WEIGHT', '1'),
	),
);
