<?php defined('BASEPATH') or exit('No direct script access allowed');

$config['host'] = env('REDIS_HOST', '127.0.0.1');
$config['password'] = env('REDIS_PASSWORD');
$config['port'] = env('REDIS_PORT', 6379);
$config['timeout'] = env('REDIS_TIMEOUT', 0);
$config['database'] = env('REDIS_DATABASE', 0);

/* End of file redis.php */
