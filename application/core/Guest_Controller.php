<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * * // Custom Library Type ------------------------------------------------------------------------
 * If you want the cast type to be loaded globally, you can register it in the CI_Type trait
 * @property MY_Auth $auth
 */
class Guest_Controller extends Base_Controller
{
	public function __construct()
	{
		parent::__construct();

		if ($this->auth->check() && !in_array($this->getData('meta.method'), $this->force_method_request)) {
			redirect('/', 'refresh');
		}
	}
}

/* End of file Guest_Controller.php */
