<?php defined('BASEPATH') or exit('No direct script access allowed');

class Media extends Test_Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	public function path($secure_path = null)
	{
		if (!$secure_path)
			show_404();
		$path_info = pathinfo($secure_path);
		$ext = $path_info['extension'] ?? '';
		$ext = strtolower($ext ? ".$ext" : '');
		$secure_path = $path_info['filename'];
		$path = base64_decode(urldecode($secure_path)) . $ext;
		$file = str_replace('/', '\\', ASSETPATH . 'storage' . DIRECTORY_SEPARATOR . $path);
		if (!file_exists($file))
			show_404();
		$this->responseJson([
			"Support curl" => (extension_loaded('curl') ? 'Active' : 'Inactive'),
			"Support allow_url_fopen" => (ini_get('allow_url_fopen') ? 'Active' : 'Inactive'),
			"Path Info" => pathinfo($file),
			"Secure Path" => $secure_path . $ext,
			"Path" => $path,
			"Full Path" => $file,
			"Link" => storage($path, true)
		]);
	}
}
