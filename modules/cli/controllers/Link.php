<?php defined('BASEPATH') or exit('No direct script access allowed');

class Link extends Cli_Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	public function test()
	{
		echo "test";
	}

	// ? run : composer storage:link || php index.php link index
	public function index()
	{
		$target = config_item('upload_path');
		$link = ASSETPATH . 'storage';
		if (!windows_os()) {
			return symlink($target, $link);
		}
		$mode = is_dir($target) ? 'J' : 'H';
		exec("mklink /{$mode} " . escapeshellarg($link) . ' ' . escapeshellarg($target));
	}
}
