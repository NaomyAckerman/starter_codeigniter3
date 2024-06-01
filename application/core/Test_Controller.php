<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * * // Custom Library Type ------------------------------------------------------------------------
 * If you want the cast type to be loaded globally, you can register it in the CI_Type trait
 */
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
