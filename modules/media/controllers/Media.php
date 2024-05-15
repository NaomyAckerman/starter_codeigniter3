<?php defined('BASEPATH') or exit('No direct script access allowed');

class Media extends Base_Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	public function index($path)
	{
		if (!$path)
			show_404();
		$path = explode('.', $path)[0] ?? '';
		$file = asset("storage/" . base64_decode(urldecode($path)));
		if (!$path || !@getimagesize($file))
			show_404();
		header('Content-Type: ' . get_mime_by_extension($file));
		echo file_get_contents($file);
		exit;
	}
}
