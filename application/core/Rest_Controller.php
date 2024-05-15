<?php
require_once APPPATH . 'third_party/rest/RestController.php';
use rest\chriskacerguis\RestServer\RestController;

defined('BASEPATH') or exit('No direct script access allowed');

class Rest_Controller extends RestController
{
	public function __construct()
	{
		parent::__construct();
		$this->form_validation->set_data($this->{$this->request->method}());
	}

	/**
	 * @override
	 */
	protected function _parse_delete()
	{
		if ($this->request->format) {
			$this->request->body = $this->input->raw_input_stream;
			if ($this->request->format === 'json') {
				$this->_delete_args = json_decode($this->input->raw_input_stream);
			}
		} elseif ($this->input->method() === 'delete') {
			// If no file type is provided, then there are probably just arguments
			$this->_delete_args = $this->input->input_stream();
		}
	}
}
