<?php defined('BASEPATH') or exit('No direct script access allowed');

class MY_Config_Web
{
	use CI_Instance;

	public function __construct()
	{
		$this->load->helper(['config_web']);
	}
}

/* End of file MY_Config_Web.php */
