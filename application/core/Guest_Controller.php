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

		$force_method = ['logout'];

		if ($this->auth->check() && !in_array($this->getData('meta.method'), $force_method)) {
			redirect('/', 'refresh');
		}
	}
}

/* End of file Guest_Controller.php */
