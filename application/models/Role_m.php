<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Role_m extends Base_Model
{
	protected $table = 'roles';
	protected $key = 'id';

	public function __construct()
	{
		parent::__construct();
	}
}

/* End of file Role_m.php */
