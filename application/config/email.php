<?php
defined('BASEPATH') or exit('No direct script access allowed');

$config = array(
	'protocol' => env('MAIL_DRIVER', 'smtp'), // 'mail', 'sendmail', or 'smtp'
	'smtp_host' => env('MAIL_HOST', 'smtp.gmail.com'),
	'smtp_user' => env('MAIL_USERNAME', ''),
	'smtp_pass' => env('MAIL_PASSWORD', ''),
	'smtp_port' => env('MAIL_PORT', 465),
	'smtp_crypto' => env('MAIL_ENCRYPTION', 'ssl'), //can be 'ssl' or 'tls' for example
	'mailtype' => 'html', //plaintext 'text' mails or 'html'
	'smtp_timeout' => '4', //in seconds
	'charset' => 'utf-8',
	'wordwrap' => TRUE,
	'crlf' => "\r\n",
	'newline' => "\r\n"
);

