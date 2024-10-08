<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @package    Auth
 * @author     NaomyAckerman
 * @link       https://github.com/NaomyAckerman
 * 
 */

class MY_Auth
{
	use CI_Instance;
	use CI_Type;

	/**
	 * tables
	 *
	 * @var array
	 */
	protected $tables = [];
	/**
	 * identity
	 *
	 * @var string
	 */
	protected $identity;
	/**
	 * hash_method
	 *
	 * @var string
	 */
	protected $hash_method;
	/**
	 * messages
	 *
	 * @var array
	 */
	protected $messages = [];
	protected $errors = [];
	const MAX_COOKIE_LIFETIME = 63072000; // 2 years = 60*60*24*365*2 = 63072000 seconds;
	const MAX_PASSWORD_SIZE_BYTES = 4096;

	public function __construct()
	{
		$this->config->load('auth', true);
		$this->lang->load('auth');
		$this->load->library(['email', 'session']);
		$this->load->helper(['cookie', 'language', 'url', 'auth']);

		$this->_checkCompatibility();

		// initialize identity
		$this->identity = $this->config->item('identity', 'auth');
		// initialize hash method options (Bcrypt)
		$this->hash_method = $this->config->item('hash_method', 'auth');
		// initialize db tables data
		$this->tables = $this->config->item('tables', 'auth');
	}

	// * Start Auth ------------------------------------------------------------------------

	/**
	 * Logs the user into the system.
	 * @method login()
	 * 
	 * @param  string $identity Identity column.
	 * @param  string $password
	 * @param  bool $remember
	 * @return bool
	 */
	public function login(string $identity, string $password, bool $remember = false)
	{
		if ($identity && $password) {
			$query = $this->db
				->get_where($this->tables['users'], [$this->identity => $identity]);
			if ($query->num_rows() === 1) {
				if ($this->hasAttemptsExceeded($identity)) {
					$this->_setError('login_timeout');
					return false;
				}
				$user = $query->row();
				if ($this->verifyPassword($password, $user->password)) {
					if ($user->is_active == 0) {
						$this->_setError('login_unsuccessful_not_active');
						return false;
					}
					$this->_setSession($user);
					$this->_updateLastLogin($user->id);
					$this->_clearForgottenPasswordCode($identity);
					$this->clearAttempts($identity);
					if ($this->config->item('remember_users', 'auth')) {
						if ($remember) {
							$this->_setRememberUser($identity);
						} else {
							$this->_clearRememberCode($identity);
						}
					}
					// Rehash if needed
					$this->_reverifyPasswordIfNeeded($user->password, $identity, $password);
					// Regenerate the session (for security purpose: to avoid session fixation)
					$this->session->sess_regenerate(false);
					$this->_setMessage('login_successful');
					return true;
				}
			}
		}
		$this->increaseAttempts($identity);
		$this->_setError('login_unsuccessful');
		return false;
	}

	/**
	 * Logs the user out of the system.
	 * @method logout()
	 * 
	 * @return bool
	 */
	public function logout()
	{
		$this->session->unset_userdata(['identity', 'user_id', 'auth_session_hash', 'roles', 'permissions']);
		// delete the remember me cookies if they exist
		delete_cookie($this->config->item('remember_cookie_name', 'auth'));
		// Clear all codes
		$this->_clearForgottenPasswordCode($this->identity);
		$this->_clearRememberCode($this->identity);
		// Destroy the session
		$this->session->sess_destroy();
		$this->_setMessage('logout_successful');
		return true;
	}

	/**
	 * Register (create) a new user.
	 * @method register() 
	 * 
	 * @param  string $identity Identity column.
	 * @param  string $password
	 * @param  array $data
	 * @param  int|string|array|null $role ID or Name of the role.
	 * @return array => key ['status', 'data', 'code', 'id'].
	 */
	public function register(string $identity, string $password, array $data = [], $role = null)
	{
		$result = ['status' => false, 'data' => null, 'code' => null, 'id' => null];
		$email_activation = $this->config->item('email_activation', 'auth');
		$manual_activation = $this->config->item('manual_activation', 'auth');
		$email = $this->identity == 'email' ? $identity : ($data['email'] ?? null);
		if ($this->_identityCheck($identity)) {
			$this->_setError('account_creation_duplicate_identity');
			return $result;
		}
		$password = $this->hashPassword($password);
		if (!$password) {
			$this->_setError('account_creation_unsuccessful');
			return $result;
		}
		$data = $this->_filterData($this->tables['users'], array_merge($data, [
			$this->identity => $identity,
			'password' => $password,
			'is_active' => ($manual_activation ? 0 : 1),
		]));
		$result_insert = $this->db->insert($this->tables['users'], $data);
		$user_id = $this->db->insert_id();
		if (!$user_id && !$result_insert) {
			$this->_setError('account_creation_unsuccessful');
			return $result;
		}
		if ($role) {
			$this->assignRole($role, $user_id);
		}
		if (!$email_activation) {
			$result['status'] = true;
			$result['data'] = $data;
			$result['id'] = $user_id;
			$this->_setMessage('account_creation_successful');
			return $result;
		} else {
			// deactivate so the user must follow the activation flow
			['status' => $deactivate_status, 'code' => $activation_code] = $this->deactivate($user_id);
			// the deactivate method call adds a message, here we need to clear that
			$this->_clearMessages();
			if (!$deactivate_status) {
				// the deactivate set error
				return $result;
			}
			$result['status'] = true;
			$result['data'] = $data;
			$result['id'] = $user_id;
			$result['code'] = $activation_code;

			if ($email) {
				$email_activation_path = $this->config->item('email_templates', 'auth') . $this->config->item('email_activation_view', 'auth');
				$email_data = [
					'id' => $user_id,
					'identity' => $identity,
					'email' => $email,
					'activation_code' => $activation_code,
				];
				$message = $this->load->view($email_activation_path, $email_data, true);
				$this->email->clear();
				$this->email->from($this->config->item('admin_email', 'auth'), $this->config->item('title_email', 'auth'));
				$this->email->to($email);
				$this->email->subject($this->config->item('title_email', 'auth') . ' - ' . $this->lang->line('email_activation_subject'));
				$this->email->message($message);
				if ($this->email->send() === true) {
					$this->_setMessage('activation_email_successful');
				} else {
					$this->_setMessage('activation_email_unsuccessful');
				}
			} else {
				$this->_setMessage('account_creation_successful');
			}
			return $result;
		}
	}

	/**
	 * Resets a users password by emailing the user a reset code.
	 * @method forgottenPassword()
	 *
	 * @param  string $identity Identity column.
	 * @return array => key ['status', 'data', 'code'].
	 */
	public function forgottenPassword(string $identity)
	{
		$result = ['status' => false, 'data' => null, 'code' => null];
		// Retrieve user information
		$user = $this->db->get_where($this->tables['users'], [
			$this->identity => $identity,
			'is_active' => 1
		])->row();
		if ($user) {
			$forgotten_password_code = null;
			// Generate random token: smaller size because it will be in the URL
			$token = $this->_generateSelectorValidatorCouple(20, 80);
			if ($token->status) {
				$update = [
					'forgotten_password_selector' => $token->selector,
					'forgotten_password_code' => $token->validator_hashed,
					'forgotten_password_time' => date('Y-m-d H:i:s')
				];
				$result_update = $this->db->update($this->tables['users'], $update, [$this->identity => $identity]);
				if ($result_update) {
					$forgotten_password_code = $token->user_code;
				}
				if ($forgotten_password_code) {
					$result['status'] = true;
					$result['code'] = $forgotten_password_code;
					$result['data'] = $user;
					$email = $this->identity == 'email' ? $identity : ($user->email ?? null);
					if ($email) {
						$email_forgot_password_path = $this->config->item('email_templates', 'auth') . $this->config->item('email_forgot_password_view', 'auth');
						$data_email = [
							'id' => $user->id,
							'identity' => $identity,
							'email' => $email,
							'forgotten_password_code' => $forgotten_password_code
						];
						$message = $this->load->view($email_forgot_password_path, $data_email, TRUE);
						$this->email->clear();
						$this->email->from($this->config->item('admin_email', 'auth'), $this->config->item('title_email', 'auth'));
						$this->email->to($email);
						$this->email->subject($this->config->item('title_email', 'auth') . ' - ' . $this->lang->line('email_forgotten_password_subject'));
						$this->email->message($message);
						if ($this->email->send()) {
							$this->_setMessage('forgot_password_email_successful');
						} else {
							$this->_setMessage('forgot_password_email_unsuccessful');
						}
					} else {
						$this->_setMessage('forgot_password_successful');
					}
					return $result;
				}
			}
		}
		$this->_setError('forgot_password_unsuccessful');
		return $result;
	}

	/**
	 * Check to see if the forgotten password code is valid.
	 * @method forgottenPasswordCheck
	 *
	 * @param  string $code Forgotten password code.
	 * @return array => key ['status', 'data'].
	 */
	public function forgottenPasswordCheck(string $code)
	{
		$result = ['status' => false, 'data' => null];
		$user = null;
		// Retrieve the token object from the code
		$token = $this->_retrieveSelectorValidatorCouple($code);
		if ($token->status) {
			// Retrieve the user according to this selector
			$res = $this->db->get_where($this->tables['users'], [
				'forgotten_password_selector' => $token->selector
			])->row();
			if ($res) {
				// Check the hash against the validator
				if ($this->verifyPassword($token->validator, $res->forgotten_password_code)) {
					$user = $res;
				}
			}
		}
		if (!$user) {
			$this->_setError('forgot_password_invalid');
			return $result;
		} else {
			if ($this->config->item('forgot_password_expiration', 'auth') > 0) {
				//Make sure it isn't expired
				$expiration = $this->config->item('forgot_password_expiration', 'auth');
				if (time() - strtotime($user->forgotten_password_time) > $expiration) {
					//it has expired
					$identity = $user->{$this->identity};
					$this->_clearForgottenPasswordCode($identity);
					$this->_setError('forgot_password_expired');
					return $result;
				}
			}
			$result['status'] = true;
			$result['data'] = $user;
			return $result;
		}
	}

	/**
	 * Reset password.
	 * @method resetPassword()
	 *
	 * @param  string $identity Identity column.
	 * @param  string $password New password.
	 * @return bool
	 */
	public function resetPassword(string $identity, string $password)
	{
		$hash = $this->hashPassword($password);
		if (!$this->_identityCheck($identity) || !$hash) {
			$this->_setError('password_change_unsuccessful');
			return FALSE;
		}
		$data = [
			'password' => $hash,
			'remember_code' => NULL,
			'forgotten_password_code' => NULL,
			'forgotten_password_time' => NULL
		];
		$result_update = $this->db->update($this->tables['users'], $data, [$this->identity => $identity]);
		if ($result_update) {
			$this->_setMessage('password_change_successful');
		} else {
			$this->_setError('password_change_unsuccessful');
		}
		return $result_update;
	}

	/**
	 * Validates and removes activation code.
	 * @method activate()
	 *
	 * @param  int $id User ID.
	 * @param  string|null $code Activation code.
	 * @return bool
	 */
	public function activate(int $id, string $code = null)
	{
		$user = null;
		if ($code) {
			// Retrieve the token object from the code
			$token = $this->_retrieveSelectorValidatorCouple($code);
			if ($token->status) {
				// Retrieve the user according to this selector
				$res_user = $this->db->get_where($this->tables['users'], ['activation_selector' => $token->selector])->row();
				if ($res_user) {
					// Check the hash against the validator
					if ($this->verifyPassword($token->validator, $res_user->activation_code)) {
						$user = $res_user;
					}
				}
			}
		}
		// Activate if no code is given
		// Or if a user was found with this code, and that it matches the id
		if (!$code || ($user && (($user->id ?? null) == $id))) {
			$data = [
				'activation_selector' => NULL,
				'activation_code' => NULL,
				'is_active' => 1
			];
			$result = $this->db->update($this->tables['users'], $data, ['id' => $id]);
			if ($result) {
				$this->_setMessage('activate_successful');
				return true;
			}
		}
		$this->_setError('activate_unsuccessful');
		return false;
	}

	/**
	 * Updates a users row with an activation code.
	 * @method deactivate()
	 *
	 * @param  int $id User ID.
	 * @return array => key ['status', 'code'].
	 */
	public function deactivate(int $id)
	{
		$result = ['status' => false, 'code' => null];

		if ($this->check() && ($this->user()->id ?? null) == $id) {
			$this->_setError('deactivate_current_user_unsuccessful');
			return $result;
		}

		$token = $this->_generateSelectorValidatorCouple(20, 40);
		if ($token->status) {
			$data = [
				'activation_selector' => $token->selector,
				'activation_code' => $token->validator_hashed,
				'is_active' => 0
			];
			$result_update = $this->db->update($this->tables['users'], $data, ['id' => $id]);
			if ($result_update) {
				$result['status'] = true;
				$result['code'] = $token->user_code;
				$this->_setMessage('deactivate_successful');
				return $result;
			}
		}
		$this->_setError('deactivate_unsuccessful');
		return $result;
	}

	/**
	 * Check to see if a user is logged in.
	 * @method check()
	 *
	 * @return bool
	 */
	public function check()
	{
		$recheck = $this->_recheckSession();
		// auto-login the user if they are remembered
		if (!$recheck && ($this->config->item('remember_users', 'auth')) && get_cookie($this->config->item('remember_cookie_name', 'auth'))) {
			$recheck = $this->_loginRememberedUser();
		}
		return $recheck;
	}

	/**
	 * The user's ID from the session user data or NULL if not found.
	 * @method currentId()
	 * 
	 * @return int|null
	 **/
	public function currentId()
	{
		return $this->session->userdata('user_id');
	}

	/**
	 * Get the user with the current session.
	 * @method currentUser()
	 *
	 * @return object|null
	 */
	public function currentUser()
	{
		$id = $this->session->userdata('user_id');
		return $this->db->get_where($this->tables['users'], [
			"{$this->tables['users']}.id" => $id
		])->row();
	}

	/**
	 * Get a user.
	 * @method user()
	 *
	 * @param  int|null $id User ID, if User ID is null, it will use the User ID of the current user session, however.
	 * @return object|null
	 */
	public function user(int $id = null)
	{
		// if no id was passed use the current users id
		$id = $id ?: $this->session->userdata('user_id');
		return $this->db->get_where($this->tables['users'], [
			"{$this->tables['users']}.id" => $id
		])->row();
	}

	/**
	 * Get the users.
	 * @method users()
	 *
	 * @return array
	 */
	public function users()
	{
		return $this->db->get($this->tables['users'])->result();
	}

	/**
	 * Get all users with certain permissions.
	 * @method usersWithPermissions()
	 *
	 * @param  int|string|array $permission ID or Name of the permission, format of name (resource.action).
	 * @return array
	 */
	public function usersWithPermissions($permission)
	{
		if (!is_array($permission)) {
			$permission = [$permission];
		}
		return $this->db
			->select('u.*')
			->join("{$this->tables['user_roles']} as ur", 'u.id = ur.user_id')
			->join("{$this->tables['roles']} as r", 'ur.role_id = r.id')
			->join("{$this->tables['role_permissions']} as rp", 'r.id = rp.role_id')
			->join("{$this->tables['permissions']} as p", 'rp.permission_id = p.id')
			->where_in('p.id', $permission)
			->or_where_in('p.name', $permission)
			->group_by('u.id')
			->get("{$this->tables['users']} as u")->result();
	}

	/**
	 * Get all users with certain roles.
	 * @method usersWithRoles()
	 *
	 * @param  int|string|array $role => ID or Name of the role.
	 * @return array
	 */
	public function usersWithRoles($role)
	{
		if (!is_array($role)) {
			$role = [$role];
		}
		return $this->db
			->select('u.*')
			->join("{$this->tables['user_roles']} as ur", 'u.id = ur.user_id')
			->join("{$this->tables['roles']} as r", 'ur.role_id = r.id')
			->where_in('r.id', $role)
			->or_where_in('r.name', $role)
			->group_by('u.id')
			->get("{$this->tables['users']} as u")->result();
	}

	/**
	 * Create new user.
	 * @method createUser()
	 *
	 * @param  array $data
	 * @return array => key ['status', 'id']
	 */
	public function createUser(array $data = [])
	{
		$result = ['status' => false, 'id' => null];
		$data = $this->_filterData($this->tables['users'], $data);
		$result_insert = $this->db->insert($this->tables['users'], $data);
		if ($result_insert) {
			$result['id'] = $this->db->insert_id();
			$result['status'] = true;
		}
		return $result;
	}

	/**
	 * Update a user.
	 * @method updateUser()
	 *
	 * @param  int $id User ID.
	 * @param  array $data
	 * @return bool
	 */
	public function updateUser(int $id, array $data)
	{
		$result = $this->db->update($this->tables['users'], $this->_filterData($this->tables['users'], $data), ['id' => $id]);
		return $result;
	}

	/**
	 * Delete a user.
	 * @method deleteUser()
	 *
	 * @param  int $id User ID.
	 * @return bool
	 */
	public function deleteUser(int $id)
	{
		return $this->db->delete($this->tables['users'], ['id' => $id]) ? true : false;
	}

	/**
	 * User builder.
	 * @method userBuilder
	 *
	 * @param  string|null $as Alias table of user.
	 * @return CI_DB_query_builder
	 */
	public function userBuilder(string $as = null): CI_DB_query_builder
	{
		$table = $as ? ($this->tables['users'] . " as " . $as) : $this->tables['users'];
		$this->db->from($table);
		return $this->db;
	}

	/**
	 * Check whether the maximum login attempts have been exceeded.
	 * @method hasAttemptsExceeded()
	 *
	 * @param  string $identity Identity column.
	 * @param 	string|null $ip_address Only used if track_login_ip_address is set to TRUE.
	 * @return bool
	 */
	public function hasAttemptsExceeded(string $identity, string $ip_address = null)
	{
		if ($this->config->item('track_login_attempts', 'auth')) {
			$max_attempts = $this->config->item('maximum_login_attempts', 'auth');
			if ($max_attempts > 0) {
				$attempts = $this->getAttempts($identity, $ip_address);
				return $attempts >= $max_attempts;
			}
		}
		return false;
	}

	/**
	 * Get the number of login attempts.
	 * @method getAttempts()
	 *
	 * @param  	string $identity Identity column.
	 * @param	string|null $ip_address Only used if track_login_ip_address is set to TRUE.
	 * @return int
	 */
	public function getAttempts(string $identity, string $ip_address = null)
	{
		if ($this->config->item('track_login_attempts', 'auth')) {
			$this->db->where('login', $identity);
			if ($this->config->item('track_login_ip_address', 'auth') && $ip_address) {
				$this->db->where('ip_address', $ip_address);
			}
			return $this->db
				->where(['time >' => date('Y-m-d H:i:s', time() - $this->config->item('lockout_time', 'auth'))], FALSE)
				->get($this->tables['login_attempts'])->num_rows();
		}
		return 0;
	}

	/**
	 * Get the last time a login attempt occurred from given identity
	 * @method getLastAttempt()
	 *
	 * @param  	string $identity Identity column.
	 * @param 	string|null $ip_address Only used if track_login_ip_address is set to TRUE.
	 * @return object|null
	 */
	public function getLastAttempt(string $identity, string $ip_address = NULL)
	{
		if ($this->config->item('track_login_attempts', 'auth')) {
			$this->db->where('login', $identity);
			if ($this->config->item('track_login_ip_address', 'auth') && $ip_address) {
				$this->db->where('ip_address', $ip_address);
			}
			return $this->db
				->order_by('id', 'desc')
				->limit(1)
				->get($this->tables['login_attempts'])
				->row();
		}
		return null;
	}

	/**
	 * Increase login attempts.
	 * @method increaseAttempts()
	 *
	 * @param  string $identity Identity column.
	 * @return bool
	 */
	public function increaseAttempts(string $identity)
	{
		if ($this->config->item('track_login_attempts', 'auth')) {
			$data = ['ip_address' => null, 'login' => $identity, 'time' => date('Y-m-d H:i:s')];
			if ($this->config->item('track_login_ip_address', 'auth')) {
				$data['ip_address'] = $this->input->ip_address();
			}
			return $this->db->insert($this->tables['login_attempts'], $data);
		}
		return false;
	}

	/**
	 * Clear loggin attempts
	 * @method clearAttempts()
	 *
	 * @param  	string 	$identity Identity column.
	 * @param 	int 	$old_attempts_expire_period In seconds, any attempts older than this value will be removed.
	 *                                                It is used for regularly purging the attempts table.
	 *                                                (for security reason, minimum value is lockout_time config value)
	 * @param	string|null $ip_address Only used if track_login_ip_address is set to TRUE.
	 * @return bool
	 */
	public function clearAttempts(string $identity, int $old_attempts_expire_period = 86400, string $ip_address = NULL)
	{
		if ($this->config->item('track_login_attempts', 'auth')) {
			// Make sure $old_attempts_expire_period is at least equals to lockout_time
			$old_attempts_expire_period = max($old_attempts_expire_period, $this->config->item('lockout_time', 'auth'));

			$this->db->where('login', $identity);
			if ($this->config->item('track_login_ip_address', 'auth') && $ip_address) {
				$this->db->where('ip_address', $ip_address);
			}
			// Purge obsolete login attempts
			$this->db->or_where(['time <' => date('Y-m-d H:i:s', time() - $old_attempts_expire_period)], FALSE);

			return $this->db->delete($this->tables['login_attempts']);
		}
		return FALSE;
	}

	// * Start Role ------------------------------------------------------------------------

	/**
	 * Role builder.
	 * @method roleBuilder
	 *
	 * @param  string|null $as Alias table of role.
	 * @return CI_DB_query_builder
	 */
	public function roleBuilder(string $as = null): CI_DB_query_builder
	{
		$table = $as ? ($this->tables['roles'] . " as " . $as) : $this->tables['roles'];
		$this->db->from($table);
		return $this->db;
	}

	/**
	 * Get role by id.
	 * @method role()
	 *
	 * @param  int $id Role ID.
	 * @return object|null
	 */
	public function role(int $id)
	{
		return $this->db->get_where($this->tables['roles'], ['id' => $id])->row();
	}

	/**
	 * Get all roles.
	 * @method roles()
	 * 
	 * @return array
	 */
	public function roles()
	{
		return $this->db->get($this->tables['roles'])->result();
	}

	/**
	 * Get all roles with a specific user.
	 * @method rolesWithUser()
	 *
	 * @param  int $id User ID.
	 * @return array
	 */
	public function rolesWithUser(int $id)
	{
		return $this->db
			->select('r.*')
			->join("{$this->tables['user_roles']} as ur", 'r.id = ur.role_id')
			->where(['ur.user_id' => $id])
			->group_by('ur.role_id')
			->get("{$this->tables['roles']} as r")->result();
	}

	/**
	 * Get all roles with specific permissions.
	 * @method rolesWithPermission()
	 *
	 * @param  int|string|array $permission ID or Name of the permission, format of name (resource.action).
	 * @return array
	 */
	public function rolesWithPermission($permission)
	{
		if (!is_array($permission)) {
			$permission = [$permission];
		}
		return $this->db
			->select("r.*")
			->join("{$this->tables['role_permissions']} as rp", 'r.id = rp.role_id')
			->join("{$this->tables['permissions']} as p", 'rp.permission_id = p.id')
			->where_in('p.id', $permission)
			->or_where_in('p.name', $permission)
			->group_by('r.id')
			->get("{$this->tables['roles']} as r")->result();
	}

	/**
	 * Create new role.
	 * @method createRole()
	 *
	 * @param  string $name
	 * @param  array $data
	 * @return array => key ['status', 'id'].
	 */
	public function createRole(string $name, array $data = [])
	{
		$result = ['status' => false, 'id' => null];
		$data = $this->_filterData($this->tables['roles'], array_merge($data, [
			'name' => $name
		]));
		$result_insert = $this->db->insert($this->tables['roles'], $data);
		if ($result_insert) {
			$result['id'] = $this->db->insert_id();
			$result['status'] = true;
		}
		return $result;
	}

	/**
	 * Update role.
	 * @method updateRole()
	 *
	 * @param  int $id Role ID.
	 * @param  array $data
	 * @return bool
	 */
	public function updateRole(int $id, array $data = [])
	{
		$data = $this->_filterData($this->tables['roles'], $data);
		return $this->db->update($this->tables['roles'], $data, ['id' => $id]);
	}

	/**
	 * Delete role.
	 * @method deleteRole()
	 *
	 * @param  int $id Role ID.
	 * @return bool
	 */
	public function deleteRole(int $id)
	{
		return $this->db->delete($this->tables['roles'], ['id' => $id]) ? true : false;
	}

	/**
	 * Assign users to roles.
	 * @method assignRole()
	 *
	 * @param  int|string|array $role ID or Name of the role.
	 * @param  int $id User ID.
	 * @return bool
	 */
	public function assignRole($role, int $id)
	{
		if (!is_array($role)) {
			$role = [$role];
		}
		$user = $this->db->get_where($this->tables['users'], ['id' => $id])->row();
		$roles = $this->db
			->select('id')
			->where_in('id', $role)
			->or_where_in('name', $role)
			->get($this->tables['roles'])->result();
		if (!$user || !$roles) {
			return false;
		}
		// Then insert each into the database
		foreach ($roles as $role) {
			$check_user_role = $this->db->get_where($this->tables['user_roles'], [
				'user_id' => $id,
				'role_id' => $role->id,
			])->row();
			if (!$check_user_role) {
				$this->db->insert(
					$this->tables['user_roles'],
					[
						'user_id' => $id,
						'role_id' => $role->id,
					]
				);
			}
		}
		return true;
	}

	/**
	 * Revoke the role from the user.
	 * @method revokeRole()
	 *
	 * @param  int|string|array $role ID or Name of the role.
	 * @param  int $id User ID.
	 * @return bool
	 */
	public function revokeRole($role, int $id)
	{
		if (!is_array($role)) {
			$role = [$role];
		}
		$user = $this->db->get_where($this->tables['users'], ['id' => $id])->row();
		$roles = $this->db
			->select('id')
			->where_in('id', $role)
			->or_where_in('name', $role)
			->get($this->tables['roles'])->result();
		if (!$user || !$roles) {
			return false;
		}
		foreach ($roles as $role) {
			$this->db->delete(
				$this->tables['user_roles'],
				['user_id' => $id, 'role_id' => $role->id]
			);
		}
		return true;
	}

	/**
	 * get all roles from current user session
	 * @method currentRoles()
	 *
	 * @param bool $first If set to true it will return the first role.
	 * @return array|string
	 */
	public function currentRoles(bool $first = false)
	{
		$roles = $this->session->userdata('roles') ?? [];
		return $first ? ($roles[0] ?? '') : $roles;
	}

	/**
	 * Check if the user has the role.
	 * @method hasRole()
	 *
	 * @param  int|string|array $role ID or Name of the role.
	 * @param  int|null $id User ID, if User ID is null, it will use the User ID of the current user session, however.
	 * @param  bool $check_all If set to true, it will check the entire array value in the $role variable.
	 * @return bool
	 */
	public function hasRole($role, int $id = null, $check_all = false)
	{
		$user_roles = $id ? $this->rolesWithUser($id) : $this->currentRoles();
		$id = $id ?? $this->session->userdata('user_id');
		if (!$user_roles || !$id) {
			return false;
		}
		if (!is_array($role)) {
			$role = [$role];
		}
		$roles_array = [];
		foreach ($user_roles as $user_role) {
			$roles_array[$user_role->id] = strtolower($user_role->name);
		}
		foreach ($role as $value) {
			$roles = (is_numeric($value)) ? array_keys($roles_array) : $roles_array;
			if (in_array(strtolower($value), $roles) xor $check_all) {
				return !$check_all;
			}
		}
		return $check_all;
	}

	// * Start Permission ------------------------------------------------------------------------

	/**
	 * Permission builder.
	 * @method permissionBuilder
	 *
	 * @param  string|null $as Alias table of permission.
	 * @return CI_DB_query_builder
	 */
	public function permissionBuilder(string $as = null): CI_DB_query_builder
	{
		$table = $as ? ($this->tables['permissions'] . " as " . $as) : $this->tables['permissions'];
		$this->db->from($table);
		return $this->db;
	}

	/**
	 * Get permission by id.
	 * @method permission()
	 *
	 * @param  int $id Permission ID.
	 * @return object|null
	 */
	public function permission(int $id)
	{
		return $this->db->get_where($this->tables['permissions'], ['id' => $id])->row();
	}

	/**
	 * Get all permissions.
	 * @method permissions()
	 * 
	 * @return array
	 */
	public function permissions()
	{
		return $this->db->get($this->tables['permissions'])->result();
	}

	/**
	 * Get all permissions with a specific user.
	 * @method permissionsWithUser()
	 *
	 * @param  int $id User ID.
	 * @param  bool $chunk_resource If set to true it will return data based on the resource.
	 * @return array
	 */
	public function permissionsWithUser(int $id, bool $chunk_resource = false)
	{
		$permissions = $this->db
			->select('p.*')
			->join("{$this->tables['role_permissions']} as rp", 'p.id = rp.permission_id')
			->join("{$this->tables['roles']} as r", 'rp.role_id = r.id')
			->join("{$this->tables['user_roles']} as ur", 'r.id = ur.role_id')
			->where('ur.user_id', $id)
			->group_by('p.id')
			->get("{$this->tables['permissions']} as p")->result();
		return $chunk_resource ? $this->_chunkPermissionByResource($permissions) : $permissions;
	}

	/**
	 * Get all permissions with a specific role.
	 * @method permissionsWithRole()
	 *
	 * @param  int|string|array $role ID or Name of the role.
	 * @param  bool $chunk_resource If set to true it will return data based on the resource.
	 * @return array
	 */
	public function permissionsWithRole($role, bool $chunk_resource = false)
	{
		if (!is_array($role)) {
			$role = [$role];
		}
		$permissions = $this->db
			->select('p.*')
			->join("{$this->tables['role_permissions']} as rp", 'p.id = rp.permission_id')
			->join("{$this->tables['roles']} as r", 'rp.role_id = r.id')
			->where_in('r.id', $role)
			->or_where_in('r.name', $role)
			->group_by('p.id')
			->get("{$this->tables['permissions']} as p")->result();
		return $chunk_resource ? $this->_chunkPermissionByResource($permissions) : $permissions;
	}

	/**
	 * Create new permission.
	 * @method createPermission()
	 *
	 * @param  string $name Format of name (resource.action).
	 * @param  array $data
	 * @return array => key ['status', 'id'].
	 */
	public function createPermission(string $name, array $data = [])
	{
		$result = ['status' => false, 'id' => null];
		$data = $this->_filterData($this->tables['permissions'], array_merge($data, [
			'name' => $name
		]));
		$result_insert = $this->db->insert($this->tables['permissions'], $data);
		if ($result_insert) {
			$result['id'] = $this->db->insert_id();
			$result['status'] = true;
		}
		return $result;
	}

	/**
	 * Update permission.
	 * @method updatePermission()
	 *
	 * @param  int $id Permission ID.
	 * @param  array $data
	 * @return bool
	 */
	public function updatePermission(int $id, array $data = [])
	{
		$data = $this->_filterData($this->tables['permissions'], $data);
		return $this->db->update($this->tables['permissions'], $data, ['id' => $id]);
	}

	/**
	 * Delete permission.
	 * @method deletePermission()
	 *
	 * @param  int $id Permission ID.
	 * @return bool
	 */
	public function deletePermission(int $id)
	{
		return $this->db->delete($this->tables['permissions'], ['id' => $id]) ? true : false;
	}

	/**
	 * Assign roles to permissions.
	 * @method assignPermission()
	 *
	 * @param  int|string|array $permission ID or Name of the permission, format of name (resource.action).
	 * @param  int $id Role ID.
	 * @return bool
	 */
	public function assignPermission($permission, int $id)
	{
		if (!is_array($permission)) {
			$permission = [$permission];
		}
		$role = $this->db->get_where($this->tables['roles'], ['id' => $id])->row();
		$permissions = $this->db
			->select('id')
			->where_in('id', $permission)
			->or_where_in('name', $permission)
			->get($this->tables['permissions'])->result();
		if (!$role || !$permissions) {
			return false;
		}
		// Then insert each into the database
		foreach ($permissions as $permission) {
			$check_role_permission = $this->db->get_where($this->tables['role_permissions'], [
				'role_id' => $id,
				'permission_id' => $permission->id,
			])->row();
			if (!$check_role_permission) {
				$this->db->insert(
					$this->tables['role_permissions'],
					[
						'role_id' => $id,
						'permission_id' => $permission->id,
					]
				);
			}
		}
		return true;
	}

	/**
	 * Revoke the permission from the role.
	 * @method revokePermission()
	 *
	 * @param  int|string|array $permission ID or Name of the permission, format of name (resource.action).
	 * @param  int $id Role ID.
	 * @return bool
	 */
	public function revokePermission($permission, int $id)
	{
		if (!is_array($permission)) {
			$permission = [$permission];
		}
		$role = $this->db->get_where($this->tables['roles'], ['id' => $id])->row();
		$permissions = $this->db
			->select('id')
			->where_in('id', $permission)
			->or_where_in('name', $permission)
			->get($this->tables['permissions'])->result();
		if (!$role || !$permissions) {
			return false;
		}
		foreach ($permissions as $permission) {
			$this->db->delete(
				$this->tables['role_permissions'],
				['role_id' => $id, 'permission_id' => $permission->id]
			);
		}
		return true;
	}

	/**
	 * get all permissions from current user session
	 * @method currentPermissions()
	 *
	 * @return array
	 */
	public function currentPermissions()
	{
		return $this->session->userdata('permissions') ?? [];
	}

	/**
	 * Check if the user has the permission.
	 * @method hasPermission()
	 *
	 * @param  int|string|array $permission ID or Name of the permission, format of name (resource.action).
	 * @param  int|null $id User ID, if User ID is null, it will use the User ID of the current user session, however.
	 * @param  bool $check_all If set to true, it will check the entire array value in the $permission variable
	 * @return bool
	 */
	public function hasPermission($permission, int $id = null, $check_all = false)
	{
		$user_permissions = $id ? $this->permissionsWithUser($id) : $this->currentPermissions();
		$id = $id ?? $this->session->userdata('user_id');
		if (!$user_permissions || !$id) {
			return false;
		}
		if (!is_array($permission)) {
			$permission = [$permission];
		}
		$permissions_array = [];
		foreach ($user_permissions as $user_permission) {
			$permissions_array[$user_permission->id] = strtolower($user_permission->name);
		}
		foreach ($permission as $value) {
			$permissions = (is_numeric($value)) ? array_keys($permissions_array) : $permissions_array;
			if (in_array(strtolower($value), $permissions) xor $check_all) {
				return !$check_all;
			}
		}
		return $check_all;
	}

	// * Start Util ------------------------------------------------------------------------

	/**
	 * Get single message.
	 * @method message()
	 *
	 * @return string
	 */
	public function message()
	{
		$message = $this->messages[0] ?? "";
		$_output = $this->lang->line($message) ?: $message;
		return $_output;
	}

	/**
	 * Get messages as an array.
	 * @method messagesToArray()
	 *
	 * @return array
	 */
	public function messagesToArray()
	{
		$_output = [];
		foreach ($this->messages as $message) {
			$messageLang = $this->lang->line($message) ?: $message;
			$_output[$message] = $messageLang;
		}
		return $_output;
	}

	/**
	 * Get single error.
	 * @method error()
	 *
	 * @return string
	 */
	public function error()
	{
		$error = $this->errors[0] ?? "";
		$_output = $this->lang->line($error) ?: $error;
		return $_output;
	}

	/**
	 * Get errors as an array.
	 * @method errorsToArray()
	 *
	 * @return array
	 */
	public function errorsToArray()
	{
		$_output = [];
		foreach ($this->errors as $error) {
			$errorLang = $this->lang->line($error) ?: $error;
			$_output[$error] = $errorLang;
		}
		return $_output;
	}

	/**
	 * Hashes the password to be stored in the database.
	 * @method hashPassword()
	 *
	 * @param  string $password
	 * @return string|null
	 */
	public function hashPassword(string $password)
	{
		// Check for empty password, or password containing null char, or password above limit
		// Null char may pose issue: http://php.net/manual/en/function.password-hash.php#118603
		// Long password may pose DOS issue (note: strlen gives size in bytes and not in multibyte symbol)
		if (
			empty($password) || strpos($password, "\0") !== FALSE ||
			strlen($password) > self::MAX_PASSWORD_SIZE_BYTES
		) {
			return null;
		}

		$algo = $this->_getHashAlgo();
		$params = $this->_getHashParameters();

		if ($algo && $params) {
			return password_hash($password, $algo, $params) ?: null;
		}
		return null;
	}

	/**
	 * This function takes a password and validates it.
	 * @method verifyPassword()
	 *
	 * @param  string $password
	 * @param  string $hash_password
	 * @return bool
	 */
	public function verifyPassword(string $password, string $hash_password)
	{
		// Check for empty id or password, or password containing null char, or password above limit
		// Null char may pose issue: http://php.net/manual/en/function.password-hash.php#118603
		// Long password may pose DOS issue (note: strlen gives size in bytes and not in multibyte symbol)
		if (
			empty($password) || empty($hash_password) || strpos($password, "\0") !== FALSE
			|| strlen($password) > self::MAX_PASSWORD_SIZE_BYTES
		) {
			return FALSE;
		}

		return password_verify($password, $hash_password);
	}

	/**
	 * Recheck session roles and permissions
	 * @method recheckSessionAccess()
	 *
	 * @return bool
	 */
	public function recheckSessionAccess()
	{
		$user_id = $this->session->userdata('user_id');
		$identity = $this->session->userdata('identity');
		$session_hash = $this->session->userdata('auth_session_hash');
		if ($user_id && $identity && $session_hash && $session_hash === $this->config->item('session_hash', 'auth')) {
			$this->session->set_userdata([
				'roles' => $this->rolesWithUser($user_id),
				'permissions' => $this->permissionsWithUser($user_id),
			]);
			return true;
		}
		return false;
	}

	// * Start Private ------------------------------------------------------------------------

	/**
	 * Check the compatibility with the server
	 * @method _checkCompatibility()
	 *
	 * @return void
	 */
	protected function _checkCompatibility()
	{
		// PHP password_* function sanity check
		if (!function_exists('password_hash') || !function_exists('password_verify')) {
			show_error("PHP function password_hash or password_verify not found. " .
				"Are you using CI 2 and PHP < 5.5? " .
				"Please upgrade to CI 3, or PHP >= 5.5 " .
				"or use password_compat (https://github.com/ircmaxell/password_compat).");
		}

		// Sanity check for CI2
		if (substr(CI_VERSION, 0, 1) === '2') {
			show_error("Auth library : requires CodeIgniter 3.");
		}

		// Compatibility check for CSPRNG
		// See functions used in Auth::_randomToken()
		if (!function_exists('random_bytes') && !function_exists('mcrypt_create_iv') && !function_exists('openssl_random_pseudo_bytes')) {
			show_error("No CSPRNG functions to generate random enough token. " .
				"Please update to PHP 7 or use random_compat (https://github.com/paragonie/random_compat).");
		}
	}

	/**
	 * Set a message
	 * @method _setMessage()
	 *
	 * @param  string $message
	 * @return string
	 */
	protected function _setMessage(string $message)
	{
		$this->messages[] = $message;
		return $message;
	}

	/**
	 * Clear messages
	 * @method _clearMessages()
	 *
	 * @return bool
	 */
	protected function _clearMessages()
	{
		$this->messages = [];
		return TRUE;
	}

	/**
	 * Set an error message
	 * @method _setError()
	 *
	 * @param string $error
	 * @return string
	 */
	protected function _setError(string $error)
	{
		$this->errors[] = $error;
		return $error;
	}

	/**
	 * Clear Errors
	 * @method _clearErrors()
	 *
	 * @return bool
	 */
	protected function _clearErrors()
	{
		$this->errors = [];
		return TRUE;
	}

	/**
	 * Filter data if any in database table fields
	 * @method _filterData()
	 *
	 * @param  string $table
	 * @param  array $data
	 * @return array
	 */
	protected function _filterData(string $table, array $data = [])
	{
		$filtered_data = [];
		$columns = $this->db->list_fields($table);

		if (is_array($data)) {
			foreach ($columns as $column) {
				if (array_key_exists($column, $data))
					$filtered_data[$column] = $data[$column];
			}
		}

		return $filtered_data;
	}

	/**
	 * Retrieve hash algorithm according to options
	 * @method _getHashAlgo()
	 *
	 * @return string|null
	 */
	protected function _getHashAlgo()
	{
		switch ($this->hash_method) {
			case 'bcrypt':
				$algo = PASSWORD_BCRYPT;
				break;
			case 'argon2':
				$algo = PASSWORD_ARGON2I;
				break;
			case 'argon2id':
				$algo = PASSWORD_ARGON2ID;
				break;
			default:
				$algo = null;
		}
		return $algo;
	}

	/**
	 * Retrieve hash parameter according to options
	 * @method _getHashParameters()
	 *
	 * @return array
	 */
	protected function _getHashParameters()
	{
		switch ($this->hash_method) {
			case 'bcrypt':
				$params = [
					'cost' => $this->config->item('bcrypt_default_cost', 'auth')
				];
				break;
			case 'argon2':
			case 'argon2id':
				$params = $this->config->item('argon2_default_params', 'auth');
				break;
			default:
				$params = [];
		}
		return $params;
	}

	/**
	 * Generate a random token
	 * @method _randomToken()
	 *
	 * @param  int $result_length
	 * @return string|null
	 */
	protected function _randomToken(int $result_length = 32)
	{
		if (!isset($result_length) || intval($result_length) <= 8) {
			$result_length = 32;
		}

		// Try random_bytes: PHP 7
		if (function_exists('random_bytes')) {
			return bin2hex(random_bytes($result_length / 2));
		}

		// Try mcrypt
		if (function_exists('mcrypt_create_iv')) {
			return bin2hex(mcrypt_create_iv($result_length / 2, MCRYPT_DEV_URANDOM));
		}

		// Try openssl
		if (function_exists('openssl_random_pseudo_bytes')) {
			return bin2hex(openssl_random_pseudo_bytes($result_length / 2));
		}

		// No luck!
		return null;
	}

	/**
	 * Generate a random selector/validator couple
	 * @method _generateSelectorValidatorCouple()
	 *
	 * @param  int $selector_size
	 * @param  int $validator_size
	 * @return object key => ['status', 'selector', 'validator_hashed', 'user_code']
	 */
	protected function _generateSelectorValidatorCouple(int $selector_size = 40, int $validator_size = 128)
	{
		// The selector is a simple token to retrieve the user
		$selector = $this->_randomToken($selector_size);

		// The validator will strictly validate the user and should be more complex
		$validator = $this->_randomToken($validator_size);

		// The validator is hashed for storing in DB (avoid session stealing in case of DB leaked)
		$validator_hashed = $this->hashPassword($validator);

		// The code to be used user-side
		$user_code = "$selector.$validator";

		return (object) [
			'status' => $selector && $validator && $validator_hashed,
			'selector' => $selector,
			'validator_hashed' => $validator_hashed,
			'user_code' => $user_code,
		];
	}

	/**
	 * Retrieve remember cookie info
	 * @method _retrieveSelectorValidatorCouple()
	 *
	 * @param  string $user_code
	 * @return object key => ['status', 'selector', 'validator']
	 */
	protected function _retrieveSelectorValidatorCouple(string $user_code)
	{
		$result = (object) [
			'status' => false,
			'selector' => null,
			'validator' => null
		];
		// Check code
		if ($user_code) {
			$tokens = explode('.', $user_code);
			// Check tokens
			if (count($tokens) === 2) {
				$result->status = true;
				$result->selector = $tokens[0];
				$result->validator = $tokens[1];
			}
		}
		return $result;
	}

	/**
	 * Check if password needs to be rehashed
	 * @method _reverifyPasswordIfNeeded()
	 *
	 * @param  string $hash
	 * @param  string $identity Identity column.
	 * @param  string $password
	 * @return bool
	 */
	protected function _reverifyPasswordIfNeeded(string $hash, string $identity, string $password)
	{
		$algo = $this->_getHashAlgo();
		$params = $this->_getHashParameters();

		if ($algo && $params) {
			if (password_needs_rehash($hash, $algo, $params)) {
				$hash = $this->hashPassword($password);
				if ($hash) {
					// When setting a new password, invalidate any other token
					$data = [
						'password' => $hash,
						'remember_code' => NULL,
						'forgotten_password_code' => NULL,
						'forgotten_password_time' => NULL
					];
					return $this->db->update($this->tables['users'], $data, [$this->identity => $identity]);
				}
			}
		}
		return false;
	}

	/**
	 * Identity check
	 * @method _identityCheck()
	 *
	 * @param  string $identity Identity column.
	 * @return bool
	 */
	protected function _identityCheck(string $identity)
	{
		return $this->db->get_where($this->tables['users'], [
			$this->identity => $identity
		])->num_rows() > 0;
	}

	/**
	 * Set user session
	 * @method _setSession()
	 *
	 * @param object $user User data
	 * @return void
	 */
	protected function _setSession(object $user)
	{
		$session_data = [
			'identity' => $user->{$this->identity} ?? '',
			'user_id' => $user->id ?? 0, //everyone likes to overwrite id so we'll use user_id
			'email' => $user->email ?? '',
			'last_login' => $user->last_login ?? '',
			'last_check' => date('Y-m-d H:i:s'),
			'auth_session_hash' => $this->config->item('session_hash', 'auth'),
			'roles' => $this->rolesWithUser($user->id ?? 0),
			'permissions' => $this->permissionsWithUser($user->id ?? 0)
		];
		$this->session->set_userdata($session_data);
	}

	/**
	 * Update last login
	 * @method _updateLastLogin()
	 *
	 * @param int $user_id
	 * @return bool
	 */
	protected function _updateLastLogin(int $user_id)
	{
		return $this->db->update($this->tables['users'], [
			'last_login' => date('Y-m-d H:i:s')
		], ['id' => $user_id]);
	}

	/**
	 * Clear the forgotten password code for a user
	 * @method _clearForgottenPasswordCode()
	 *
	 * @param  string $identity Identity column.
	 * @return bool
	 */
	protected function _clearForgottenPasswordCode(string $identity)
	{
		if ($identity) {
			$data = [
				'forgotten_password_selector' => NULL,
				'forgotten_password_code' => NULL,
				'forgotten_password_time' => NULL
			];
			return $this->db->update($this->tables['users'], $data, [$this->identity => $identity]);
		}
		return false;
	}

	/**
	 * Clear the remember code for a user
	 * @method _clearRememberCode()
	 *
	 * @param  string $identity Identity column.
	 * @return bool
	 */
	protected function _clearRememberCode(string $identity)
	{
		if ($identity) {
			$data = [
				'remember_selector' => NULL,
				'remember_code' => NULL
			];
			$result = $this->db->update($this->tables['users'], $data, [$this->identity => $identity]);
			if ($result) {
				delete_cookie($this->config->item('remember_cookie_name', 'auth'));
				return true;
			}
		}
		return false;
	}

	/**
	 * Verifies if the session should be rechecked according to the configuration item recheck_timer. If it does, then
	 * it will check if the user is still active
	 * @method _recheckSession()
	 * 
	 * @return bool
	 */
	protected function _recheckSession()
	{
		$identity = $this->session->userdata('identity');
		if (!$identity) {
			return false;
		}
		$recheck = $this->config->item('recheck_timer', 'auth') ?: 0;
		if ($recheck > 0) {
			$last_login = $this->session->userdata('last_check');
			if (strtotime($last_login) + $recheck < time()) {
				$query = $this->db->select('id')
					->where([
						$this->identity => $identity,
						'is_active' => '1'
					])
					->limit(1)
					->order_by('id', 'desc')
					->get($this->tables['users']);
				if ($query->num_rows() === 1) {
					$this->session->set_userdata('last_check', date('Y-m-d H:i:s'));
				} else {
					$this->session->unset_userdata(['identity', 'user_id', 'auth_session_hash', 'roles', 'permissions']);
					// delete the remember me cookies if they exist
					delete_cookie($this->config->item('remember_cookie_name', 'auth'));
					// Clear all codes
					$this->_clearForgottenPasswordCode($this->identity);
					$this->_clearRememberCode($this->identity);
					// Destroy the session
					$this->session->sess_destroy();
					return false;
				}
			}
		}
		$current_session_hash = $this->session->userdata('auth_session_hash');
		$session_hash = $this->config->item('session_hash', 'auth');
		return (bool) $current_session_hash && ($current_session_hash === $session_hash);
	}

	/**
	 * Set a user to be remembered
	 * @method _setRememberUser()
	 *
	 * @param  string $identity Identity column.
	 * @return bool
	 */
	protected function _setRememberUser(string $identity)
	{
		if ($identity) {
			// Generate random tokens
			$token = $this->_generateSelectorValidatorCouple();
			if ($token->status) {
				$result = $this->db->update(
					$this->tables['users'],
					[
						'remember_selector' => $token->selector,
						'remember_code' => $token->validator_hashed
					],
					[$this->identity => $identity]
				);
				if ($result) {
					$expire = $this->config->item('user_expire', 'auth') ?? 0;
					// if the user_expire is set to zero we'll set the expiration two years from now.
					if ($expire === 0) {
						$expire = self::MAX_COOKIE_LIFETIME;
					}
					set_cookie([
						'name' => $this->config->item('remember_cookie_name', 'auth'),
						'value' => $token->user_code,
						'expire' => $expire,
						'httponly' => true,
					]);
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Login automatically a user with the "Remember me" feature
	 * Implemented as described in
	 * https://paragonie.com/blog/2015/04/secure-authentication-php-with-long-term-persistence
	 * @method _loginRememberedUser()
	 *
	 * @return bool
	 */
	public function _loginRememberedUser()
	{
		// Retrieve token from cookie
		$remember_cookie = get_cookie($this->config->item('remember_cookie_name', 'auth'));
		$token = $this->_retrieveSelectorValidatorCouple($remember_cookie);
		if (!$token->status) {
			return FALSE;
		}
		// get the user with the selector
		$query = $this->db
			->where('remember_selector', $token->selector)
			->where('is_active', 1)
			->limit(1)
			->get($this->tables['users']);
		// Check that we got the user
		if ($query->num_rows() === 1) {
			// Retrieve the information
			$user = $query->row();
			// Check the code against the validator
			$identity = $user->{$this->identity};
			if ($this->verifyPassword($token->validator, $user->remember_code)) {
				$this->_updateLastLogin($user->id);
				$this->_setSession($user);
				$this->_clearForgottenPasswordCode($identity);
				// extend the users cookies if the option is enabled
				if ($this->config->item('user_extend_on_login', 'auth')) {
					$this->_setRememberUser($identity);
				}
				// Regenerate the session (for security purpose: to avoid session fixation)
				$this->session->sess_regenerate(false);
				return true;
			}
		}
		delete_cookie($this->config->item('remember_cookie_name', 'auth'));
		return false;
	}

	/**
	 * Chunk permission by resource
	 * @method _chunkPermissionByResource
	 *
	 * @param  array $permissions
	 * @return array
	 */
	private function _chunkPermissionByResource(array $permissions)
	{
		$result = [];
		foreach ($permissions as $permission) {
			[$resource, $action] = explode('.', $permission->name ?? '');
			if ($resource) {
				$permission->action = $action ?? '';
				$result[$resource][] = $permission;
			}
		}
		return $result;
	}
}

/* End of file MY_Auth.php */
