<?php defined('BASEPATH') or exit('No direct script access allowed');

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
