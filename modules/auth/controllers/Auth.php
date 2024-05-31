<?php defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends Guest_Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	public function logout()
	{
		$this->auth->logout();
		redirect('/', 'refresh');
	}

	public function login()
	{
		echo '<h1>Login</h1>';
	}

	public function register(): void
	{
		echo '<h1>Register</h1>';
	}

	public function forgot(): void
	{
		echo '<h1>Forgot</h1>';
	}

	public function reset(): void
	{
		echo '<h1>Reset</h1>';
	}
}
