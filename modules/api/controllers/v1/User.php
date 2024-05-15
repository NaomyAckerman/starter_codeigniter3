<?php defined('BASEPATH') or exit('No direct script access allowed');

class User extends Rest_Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	public function index_get()
	{
		$users = [
			['id' => 0, 'name' => 'John', 'email' => 'john@example.com'],
			['id' => 1, 'name' => 'Jim', 'email' => 'jim@example.com'],
		];
		$this->response($users, self::HTTP_OK);
	}
}
