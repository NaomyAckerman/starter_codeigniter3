<?php defined('BASEPATH') or exit('No direct script access allowed');

class Cli_Controller extends MX_Controller
{
	public function __construct()
	{
		parent::__construct();
		is_cli() && $this->router->fetch_method() == 'index' ?: show_404();
	}
}

/* End of file Cli_Controller.php */
