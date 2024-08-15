<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * * // Custom Library Type ------------------------------------------------------------------------
 * If you want the cast type to be loaded globally, you can register it in the CI_Type trait
 * @property MY_Config_Web		 $config_web
 * @property MY_Custom		     $custom
 */
class Cli_Controller extends MX_Controller
{
	use CI_Type;

	public function __construct()
	{
		parent::__construct();
		is_cli() && $this->router->fetch_method() == 'run' ?: show_404();
	}
}

/* End of file Cli_Controller.php */
