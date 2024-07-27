<?php
defined('BASEPATH') or exit('No direct script access allowed');

$config['protocol'] = env('MAIL_DRIVER', 'smtp'); // 'mail', 'sendmail', or 'smtp'
$config['smtp_host'] = env('MAIL_HOST', 'smtp.gmail.com'); // ex 'smtp.gmail.com'
$config['smtp_user'] = env('MAIL_USERNAME');
$config['smtp_pass'] = env('MAIL_PASSWORD');
$config['smtp_port'] = env('MAIL_PORT', 465);
$config['smtp_crypto'] = env('MAIL_ENCRYPTION', 'ssl'); //can be 'ssl' or 'tls' for example
$config['mailtype'] = 'html'; //plaintext 'text' mails or 'html'
$config['smtp_timeout'] = '4'; //in seconds
$config['charset'] = 'utf-8';
$config['wordwrap'] = TRUE;
$config['crlf'] = "\r\n";
$config['newline'] = "\r\n";

