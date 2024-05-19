<?php defined('BASEPATH') or exit('No direct script access allowed');

class Test_Controller extends Base_Controller
{
	public function __construct()
	{
		parent::__construct();
		if (env('APP_ENV') == 'production')
			show_404();
	}
}

/* End of file Test_Controller.php */
