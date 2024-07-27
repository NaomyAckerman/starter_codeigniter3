<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * * // Custom Library Type ------------------------------------------------------------------------
 * If you want the cast type to be loaded globally, you can register it in the CI_Type trait
 */
class Ajax_Controller extends Base_Controller
{
	public function __construct()
	{
		parent::__construct();
		if (!$this->input->is_ajax_request()) {
			$this->responseJson([
				'message' => 'Request cannot be accepted'
			], self::HTTP_NOT_ACCEPTABLE);
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

/* End of file Ajax_Controller.php */
