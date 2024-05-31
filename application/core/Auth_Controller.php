<?php defined('BASEPATH') or exit('No direct script access allowed');

class Auth_Controller extends Base_Controller
{
	public function __construct()
	{
		parent::__construct();

		if (!$this->auth->check()) {
			redirect('login', 'refresh');
		}

		$this->auth->recheckSessionAccess();

		$this->setData('auth', [
			'user_id' => $this->auth->currentId(),
			'roles' => $this->auth->getRoles(),
			'permissions' => $this->auth->getPermissions(),
		]);
	}
}

/* End of file Auth_Controller.php */
