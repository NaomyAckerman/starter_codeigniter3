<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Permission_m extends Base_Model
{
	protected $table = 'permissions';
	protected $key = 'id';

	public function __construct()
	{
		parent::__construct();
	}
}

/* End of file Permission_m.php */
