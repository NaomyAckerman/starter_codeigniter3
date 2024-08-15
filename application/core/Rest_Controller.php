<?php
require_once APPPATH . 'third_party/rest/RestController.php';
use rest\chriskacerguis\RestServer\RestController;

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * * // Custom Library Type ------------------------------------------------------------------------
 * If you want the cast type to be loaded globally, you can register it in the CI_Type trait
 * @property MY_Config_Web		 $config_web
 * @property MY_Custom		     $custom
 */
class Rest_Controller extends RestController
{
	use CI_Type;

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

	public function bearerToken()
	{
		$headers = null;
		if (isset($_SERVER['Authorization'])) {
			$headers = trim($_SERVER["Authorization"]);
		} else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { // Nginx or fast CGI
			$headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
		} elseif (function_exists('apache_request_headers')) {
			$requestHeaders = apache_request_headers();
			// Server-side fix for bug in old versions of PHP
			if (isset($requestHeaders['Authorization'])) {
				$headers = trim($requestHeaders['Authorization']);
			}
		}

		// HEADER: Get the access token from the header
		if ($headers) {
			if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
				return $matches[1];
			}
		}
		return '';
	}
}
