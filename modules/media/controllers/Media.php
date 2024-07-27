<?php defined('BASEPATH') or exit('No direct script access allowed');

class Media extends Base_Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	public function index($secure_path = null)
	{
		if (!$secure_path)
			show_404();
		$path_info = pathinfo($secure_path);
		$ext = $path_info['extension'] ?? '';
		$ext = strtolower($ext ? ".$ext" : '');
		$secure_path = $path_info['filename'];
		$path = base64_decode(urldecode($secure_path)) . $ext;
		$file = parse_dir_separator(ASSETPATH . 'storage' . DIRECTORY_SEPARATOR . $path);
		if (!file_exists($file))
			show_404();
		// $mime_type = mime_content_type($file);
		// if ($mime_type == 'directory')
		// 	show_404();
		$mime_type = get_mime_by_extension($file);
		if (!$mime_type)
			show_404();
		header('Content-Type: ' . $mime_type);
		echo file_get_contents($file);
		exit;
	}
}
