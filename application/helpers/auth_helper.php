<?php defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('auth_login')) {
	/**
	 * Logs the user into the system.
	 * 
	 * @param  string $identity
	 * @param  string $password
	 * @param  bool $remember
	 * @return bool
	 */
	function auth_login(string $identity, string $password, bool $remember)
	{
		$CI = &get_instance();
		return $CI->auth->login($identity, $password, $remember);
	}
}

if (!function_exists('auth_logout')) {
	/**
	 * Logs the user out of the system.
	 * 
	 * @return bool
	 */
	function auth_logout()
	{
		$CI = &get_instance();
		return $CI->auth->logout();
	}
}

if (!function_exists('auth_register')) {
	/**
	 * Register (create) a new user.
	 * note:
	 * - $roles can be the id or name of the role.
	 * 
	 * @param  string $identity
	 * @param  string $password
	 * @param  array $additional_data
	 * @param  int|string|array|null $roles
	 * @return array => key ['status', 'data', 'code', 'id']
	 */
	function auth_register(string $identity, string $password, array $additional_data = [], $roles = null)
	{
		$CI = &get_instance();
		return $CI->auth->register($identity, $password, $additional_data);
	}
}

if (!function_exists('auth_forgotten_password')) {
	/**
	 * Resets a users password by emailing the user a reset code.
	 *
	 * @param  string $identity
	 * @return array => key ['status', 'data', 'code']
	 */
	function auth_forgotten_password(string $identity)
	{
		$CI = &get_instance();
		return $CI->auth->forgottenPassword($identity);
	}
}

if (!function_exists('auth_forgotten_password_check')) {
	/**
	 * Check to see if the forgotten password code is valid.
	 *
	 * @param  string $code
	 * @return array => key ['status', 'data']
	 */
	function auth_forgotten_password_check(string $code)
	{
		$CI = &get_instance();
		return $CI->auth->forgottenPasswordCheck($code);
	}
}

if (!function_exists('auth_reset_password')) {
	/**
	 * Reset password
	 *
	 * @param  string $identity
	 * @param  string $new_password
	 * @return bool
	 */
	function auth_reset_password(string $identity, string $new_password)
	{
		$CI = &get_instance();
		return $CI->auth->resetPassword($identity, $new_password);
	}
}

if (!function_exists('auth_activate')) {
	/**
	 * Validates and removes activation code.
	 *
	 * @param  int $user_id
	 * @param  string|null $code
	 * @return bool
	 */
	function auth_activate(int $user_id, string $code = null)
	{
		$CI = &get_instance();
		return $CI->auth->activate($user_id, $code);
	}
}

if (!function_exists('auth_deactivate')) {
	/**
	 * Updates a users row with an activation code.
	 *
	 * @param  int $user_id
	 * @return array => key ['status', 'code']
	 */
	function auth_deactivate(int $user_id)
	{
		$CI = &get_instance();
		return $CI->auth->deactivate($user_id);
	}
}

if (!function_exists('auth_check')) {
	/**
	 * Check to see if a user is logged in.
	 *
	 * @return bool
	 */
	function auth_check()
	{
		$CI = &get_instance();
		return $CI->auth->check();
	}
}

if (!function_exists('auth_current_id')) {
	/**
	 * The user's ID from the session user data or NULL if not found.
	 * 
	 * @return int|null
	 **/
	function auth_current_id()
	{
		$CI = &get_instance();
		return $CI->auth->currentId();
	}
}

if (!function_exists('auth_user')) {
	/**
	 * Get a user.
	 * note:
	 * - if $id is null, it will use the $id of the current user session, however.
	 *
	 * @param  int|null $id
	 * @return object|null
	 */
	function auth_user(int $id = null)
	{
		$CI = &get_instance();
		return $CI->auth->user($id);
	}
}

if (!function_exists('auth_users')) {
	/**
	 * Get the users.
	 *
	 * @return array
	 */
	function auth_users()
	{
		$CI = &get_instance();
		return $CI->auth->users();
	}
}

if (!function_exists('auth_users_with_permissions')) {
	/**
	 * Get all users with certain permissions.
	 *
	 * @param  int|string|array $permission => id or name of permission
	 * @return array
	 */
	function auth_users_with_permissions($permission)
	{
		$CI = &get_instance();
		return $CI->auth->usersWithPermissions($permission);
	}
}

if (!function_exists('auth_users_with_roles')) {
	/**
	 * Get all users with certain roles.
	 *
	 * @param  int|string|array $role => id or name of role
	 * @return array
	 */
	function auth_users_with_roles($role)
	{
		$CI = &get_instance();
		return $CI->auth->usersWithRoles($role);
	}
}

if (!function_exists('auth_update_user')) {
	/**
	 * Update a user.
	 *
	 * @param  int $id
	 * @param  array $data
	 * @return bool
	 */
	function auth_update_user(int $id, array $data)
	{
		$CI = &get_instance();
		return $CI->auth->updateUser($id, $data);
	}
}

if (!function_exists('auth_delete_user')) {
	/**
	 * Delete a user.
	 *
	 * @param  int $id
	 * @return bool
	 */
	function auth_delete_user(int $id)
	{
		$CI = &get_instance();
		return $CI->auth->deleteUser($id);
	}
}

if (!function_exists('auth_has_attempts_exceeded')) {
	/**
	 * Check whether the maximum login attempts have been exceeded.
	 *
	 * @param  	string $identity
	 * @param 	string|null $ip_address IP address
	 *                                Only used if track_login_ip_address is set to TRUE.
	 * @return bool
	 */
	function auth_has_attempts_exceeded(string $identity, string $ip_address = null)
	{
		$CI = &get_instance();
		return $CI->auth->hasAttemptsExceeded($identity, $ip_address);
	}
}

if (!function_exists('auth_get_attempts')) {
	/**
	 * Get the number of login attempts.
	 *
	 * @param  	string $identity
	 * @param	string|null $ip_address IP address
	 *                                Only used if track_login_ip_address is set to TRUE.
	 * @return int
	 */
	function auth_get_attempts(string $identity, string $ip_address = null)
	{
		$CI = &get_instance();
		return $CI->auth->getAttempts($identity, $ip_address);
	}
}

if (!function_exists('auth_get_last_attempt')) {
	/**
	 * Get the last time a login attempt occurred from given identity
	 *
	 * @param  	string $identity
	 * @param 	string|null $ip_address IP address
	 *                                Only used if track_login_ip_address is set to TRUE.
	 *
	 * @return object|null
	 */
	function auth_get_last_attempt(string $identity, string $ip_address = NULL)
	{
		$CI = &get_instance();
		return $CI->auth->getLastAttempt($identity, $ip_address = NULL);
	}
}

if (!function_exists('auth_increase_attempts')) {
	/**
	 * Increase login attempts.
	 *
	 * @param  string $identity
	 * @return bool
	 */
	function auth_increase_attempts(string $identity)
	{
		$CI = &get_instance();
		return $CI->auth->increaseAttempts($identity);
	}
}

if (!function_exists('auth_clear_attempts')) {
	/**
	 * Clear loggin attempts
	 *
	 * @param  	string $identity
	 * @param 	int         $old_attempts_expire_period In seconds, any attempts older than this value will be removed.
	 *                                                It is used for regularly purging the attempts table.
	 *                                                (for security reason, minimum value is lockout_time config value)
	 * @param	string|null $ip_address IP address
	 *                                Only used if track_login_ip_address is set to TRUE.
	 * @return bool
	 */
	function auth_clear_attempts(string $identity, int $old_attempts_expire_period = 86400, string $ip_address = null)
	{
		$CI = &get_instance();
		return $CI->auth->clearAttempts($identity, $old_attempts_expire_period, $ip_address);
	}
}

if (!function_exists('auth_role')) {
	/**
	 * Get a roles.
	 *
	 * @param  int $id
	 * @return object|null
	 */
	function auth_role(int $id)
	{
		$CI = &get_instance();
		return $CI->auth->role($id);
	}
}

if (!function_exists('auth_roles')) {
	/**
	 * Get the roles.
	 *
	 * @return array
	 */
	function auth_roles()
	{
		$CI = &get_instance();
		return $CI->auth->roles();
	}
}

if (!function_exists('auth_create_role')) {
	/**
	 * Create new role.
	 * @method createRole()
	 *
	 * @param  string $name
	 * @param  array $additional_data
	 * @return array => key ['status', 'id']
	 */
	function auth_create_role(string $name, array $additional_data = [])
	{
		$CI = &get_instance();
		return $CI->auth->createRole($name, $additional_data);
	}
}

if (!function_exists('auth_update_role')) {
	/**
	 * Update role.
	 * @method updateRole()
	 *
	 * @param  int $id
	 * @param  array $data
	 * @return bool
	 */
	function auth_update_role(int $id, array $data = [])
	{
		$CI = &get_instance();
		return $CI->auth->updateRole($id, $data);
	}
}

if (!function_exists('auth_delete_role')) {
	/**
	 * Delete role.
	 *
	 * @param  int $id
	 * @return bool
	 */
	function auth_delete_role(int $id)
	{
		$CI = &get_instance();
		return $CI->auth->deleteRole($id);
	}
}

if (!function_exists('auth_assign_role')) {
	/**
	 * Add user to role.
	 * note:
	 * - $key can be the id or name of the role.
	 * @method assignRole()
	 *
	 * @param  int|string|array $key
	 * @param  int $user_id
	 * @return bool
	 */
	function auth_assign_role($key, int $user_id)
	{
		$CI = &get_instance();
		return $CI->auth->assignRole($key, $user_id);
	}
}

if (!function_exists('auth_remove_role')) {
	/**
	 * Remove role from user.
	 * note:
	 * - $key can be the id or name of the role.
	 * @method removeRole()
	 *
	 * @param  int|string|array $key
	 * @param  int $user_id
	 * @return bool
	 */
	function auth_remove_role($key, int $user_id)
	{
		$CI = &get_instance();
		return $CI->auth->removeRole($key, $user_id);
	}
}

if (!function_exists('auth_get_roles')) {
	/**
	 * Get user role.
	 * note:
	 * - if $user_id is null, it will return the roles of the current user session.
	 *
	 * @param  int|null $user_id
	 * @return array
	 */
	function auth_get_roles(int $user_id = null)
	{
		$CI = &get_instance();
		return $CI->auth->getRoles($user_id);
	}
}

if (!function_exists('auth_has_role')) {
	/**
	 * Check if the user has the role.
	 * note:
	 * - If $user_id is null, the value used is the current user session.
	 * - $key can be the id or name of the role.
	 * - $check_all will check all data if $key is an array
	 * @method hasRole()
	 *
	 * @param  int|string|array $key
	 * @param  int|null $user_id
	 * @param  bool $check_all
	 * @return bool
	 */
	function auth_has_role($key, int $user_id = null, bool $check_all = false)
	{
		$CI = &get_instance();
		return $CI->auth->hasRole($key, $user_id, $check_all);
	}
}

if (!function_exists('auth_permission')) {
	/**
	 * Get a permissions.
	 *
	 * @param  int $id
	 * @return object|null
	 */
	function auth_permission(int $id)
	{
		$CI = &get_instance();
		return $CI->auth->permission($id);
	}
}

if (!function_exists('auth_permissions')) {
	/**
	 * Get the permissions.
	 *
	 * @return array
	 */
	function auth_permissions()
	{
		$CI = &get_instance();
		return $CI->auth->permissions();
	}
}

if (!function_exists('auth_create_permission')) {
	/**
	 * Create new permission.
	 *
	 * @param  string $name
	 * @param  array $additional_data
	 * @return array => key ['status', 'id']
	 */
	function auth_create_permission(string $name, array $additional_data = [])
	{
		$CI = &get_instance();
		return $CI->auth->createPermission($name, $additional_data);
	}
}

if (!function_exists('auth_update_permission')) {
	/**
	 * Update permission.
	 *
	 * @param  int $id
	 * @param  array $data
	 * @return bool
	 */
	function auth_update_permission(int $id, array $data = [])
	{
		$CI = &get_instance();
		return $CI->auth->updatePermission($id, $data);
	}
}

if (!function_exists('auth_delete_permission')) {
	/**
	 * Delete permission.
	 *
	 * @param  int $id
	 * @return bool
	 */
	function auth_delete_permission(int $id)
	{
		$CI = &get_instance();
		return $CI->auth->deletePermission($id);
	}
}

if (!function_exists('auth_assign_permission')) {
	/**
	 * Add role to permission.
	 * note:
	 * - $key can be the id or name of the permission.
	 *
	 * @param  int|string|array $key
	 * @param  int $role_id
	 * @return bool
	 */
	function auth_assign_permission($key, int $role_id)
	{
		$CI = &get_instance();
		return $CI->auth->assignPermission($key, $role_id);
	}
}

if (!function_exists('auth_remove_permission')) {
	/**
	 * Remove permission from role.
	 * note:
	 * - $key can be the id or name of the permission.
	 *
	 * @param  int|string|array $key
	 * @param  int $role_id
	 * @return bool
	 */
	function auth_remove_permission($key, int $role_id)
	{
		$CI = &get_instance();
		return $CI->auth->removePermission($key, $role_id);
	}
}

if (!function_exists('auth_get_roles')) {
	/**
	 * Get user permission.
	 * note:
	 * - if $user_id is null, it will return the permissions of the current user session.
	 *
	 * @param  int|null $user_id
	 * @return array
	 */
	function auth_get_permissions(int $user_id = null)
	{
		$CI = &get_instance();
		return $CI->auth->getPermissions($user_id);
	}
}

if (!function_exists('auth_has_permission')) {
	/**
	 * Check if the user has the permission.
	 * note:
	 * - If $user_id is null, the value used is the current user session.
	 * - $key can be the id or name of the permission.
	 * - $check_all will check all data if $key is an array
	 *
	 * @param  int|string|array $key
	 * @param  int|null $user_id
	 * @param  bool $check_all
	 * @return bool
	 */
	function auth_has_permission($key, int $user_id = null, bool $check_all = false)
	{
		$CI = &get_instance();
		return $CI->auth->hasPermission($key, $user_id, $check_all);
	}
}

if (!function_exists('auth_messages')) {
	/**
	 * Get messages.
	 *
	 * @return string
	 */
	function auth_messages()
	{
		$CI = &get_instance();
		return $CI->auth->messages();
	}
}

if (!function_exists('auth_messages_array')) {
	/**
	 * Get messages as an array.
	 *
	 * @return array
	 */
	function auth_messages_array()
	{
		$CI = &get_instance();
		return $CI->auth->messagesArray();
	}
}

if (!function_exists('auth_errors')) {
	/**
	 * Get errors.
	 *
	 * @return string
	 */
	function auth_errors()
	{
		$CI = &get_instance();
		return $CI->auth->errors();
	}
}

if (!function_exists('auth_errors_array')) {
	/**
	 * Get errors as an array.
	 *
	 * @return array
	 */
	function auth_errors_array()
	{
		$CI = &get_instance();
		return $CI->auth->errorsArray();
	}
}

if (!function_exists('auth_hash_password')) {
	/**
	 * Hashes the password to be stored in the database.
	 *
	 * @param  string $password
	 * @return string|null
	 */
	function auth_hash_password(string $password)
	{
		$CI = &get_instance();
		return $CI->auth->hashPassword($password);
	}
}

if (!function_exists('auth_verify_password')) {
	/**
	 * This function takes a password and validates it.
	 *
	 * @param  string $password
	 * @param  string $hash_password_db
	 * @return bool
	 */
	function auth_verify_password(string $password, string $hash_password_db)
	{
		$CI = &get_instance();
		return $CI->auth->verifyPassword($password, $hash_password_db);
	}
}

if (!function_exists('auth_recheck_session_access')) {
	/**
	 * Recheck session roles and permissions
	 *
	 * @return bool
	 */
	function auth_recheck_session_access()
	{
		$CI = &get_instance();
		return $CI->auth->recheckSessionAccess;
	}
}

/* End of file auth_helper.php */



