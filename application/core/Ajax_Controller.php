<?php defined('BASEPATH') or exit('No direct script access allowed');

class Ajax_Controller extends Base_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->input->is_ajax_request() ?: show_404();
	}
}

/* End of file Ajax_Controller.php */
