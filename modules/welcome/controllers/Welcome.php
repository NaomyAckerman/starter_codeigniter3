<?php defined('BASEPATH') or exit('No direct script access allowed');

class Welcome extends Guest_Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		$this->display('welcome_v');
	}
}
