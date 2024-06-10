<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User_m extends Base_Model
{
	protected $table = 'users';
	protected $key = 'id';

	public function __construct()
	{
		parent::__construct();
	}
}

/* End of file User_m.php */
