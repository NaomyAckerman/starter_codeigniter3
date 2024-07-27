<?php defined('BASEPATH') or exit('No direct script access allowed');

class Notfound extends Base_Controller
{
	public function __construct()
	{
		parent::__construct();
		// ! set theme in here if route not found
	}

	public function index()
	{
		show_404();
	}
}

/* End of file Notfound.php */
