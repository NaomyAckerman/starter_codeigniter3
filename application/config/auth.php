<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Tables.
| -------------------------------------------------------------------------
| Database table names.
*/
$config['tables']['users'] = 'users';
$config['tables']['roles'] = 'roles';
$config['tables']['permissions'] = 'permissions';
$config['tables']['user_roles'] = 'user_roles';
$config['tables']['role_permissions'] = 'role_permissions';
$config['tables']['login_attempts'] = 'login_attempts';

/*
 | -------------------------------------------------------------------------
 | Hash Method (bcrypt or argon2)
 | -------------------------------------------------------------------------
 | Bcrypt is available in PHP 5.3+
 | Argon2 is available in PHP 7.2
 | Argon2id is available in PHP 7.3
 |
 | Bcrypt is the current PHP language default.
 |
 | Bcrypt specific:
 | 		bcrypt_default_cost settings:  This defines how strong the encryption will be.
 | 		However, higher the cost, longer it will take to hash (CPU usage) So adjust
 | 		this based on your server hardware.
 |
 | 		You can (and should!) benchmark your server. This can be done easily with this little script:
 | 		https://gist.github.com/Indigo744/24062e07477e937a279bc97b378c3402
 |
 | 		With bcrypt, an example hash of "password" is:
 | 		$2y$08$200Z6ZZbp3RAEXoaWcMA6uJOFicwNZaqk4oDhqTUiFXFe63MG.Daa
 |
 |
 | Argon2 specific:
 | 		argon2_default_params settings:  This is an array containing the options for the Argon2 algorithm.
 | 		You can define 3 differents keys:
 | 			memory_cost (default 4096 kB)
 |				Maximum memory (in kBytes) that may be used to compute the Argon2 hash
 |				The spec recommends setting the memory cost to a power of 2.
 | 			time_cost (default 2)
 |				Number of iterations (used to tune the running time independently of the memory size).
				This defines how strong the encryption will be.
 | 			threads (default 2)
 |				Number of threads to use for computing the Argon2 hash
 |				The spec recommends setting the number of threads to a power of 2.
 |
 | 		You can (and should!) benchmark your server. This can be done easily with this little script:
 | 		https://gist.github.com/Indigo744/e92356282eb808b94d08d9cc6e37884c
 |
 | 		With argon2, an example hash of "password" is:
 | 		$argon2i$v=19$m=1024,t=2,p=2$VEFSSU4wSzh3cllVdE1JZQ$PDeks/7JoKekQrJa9HlfkXIk8dAeZXOzUxLBwNFbZ44
 |
 |
 | For more information, check the password_hash function help: http://php.net/manual/en/function.password-hash.php
 |
 */
$config['hash_method'] = 'bcrypt';	// bcrypt, argon2, or argon2id
$config['bcrypt_default_cost'] = defined('PASSWORD_BCRYPT_DEFAULT_COST') ? PASSWORD_BCRYPT_DEFAULT_COST : 10;		// Set cost according to your server benchmark - but no lower than 10 (default PHP value)
$config['argon2_default_params'] = [
	'memory_cost' => defined('PASSWORD_ARGON2_DEFAULT_MEMORY_COST') ? PASSWORD_ARGON2_DEFAULT_MEMORY_COST : 1 << 12,
	'time_cost' => defined('PASSWORD_ARGON2_DEFAULT_TIME_COST') ? PASSWORD_ARGON2_DEFAULT_TIME_COST : 2,
	'threads' => defined('PASSWORD_ARGON2_DEFAULT_THREADS') ? PASSWORD_ARGON2_DEFAULT_THREADS : 2
];

/*
 | -------------------------------------------------------------------------
 | Authentication options.
 | -------------------------------------------------------------------------
 | maximum_login_attempts: 	This maximum is not enforced by the library, but is used by
 | 							is_max_login_attempts_exceeded().
 | 							The controller should check this function and act appropriately.
 | 							If this variable set to 0, there is no maximum.
 | min_password_length:		This minimum is not enforced directly by the library.
 | 							The controller should define a validation rule to enforce it.
 | 							See the Auth controller for an example implementation.
 |
 | The library will fail for empty password or password size above 4096 bytes.
 | This is an arbitrary (long) value to protect against DOS attack.
 */
$config['title_email'] = env('APP_NAME'); // Email title
$config['admin_email'] = "admin@example.com"; // Email admin, admin@example.com
$config['identity'] = 'email';             /* You can use any unique column in your table as identity column.
The values in this column, alongside password, will be used for login purposes
IMPORTANT: If you are changing it from the default (email),
update the UNIQUE constraint in your DB */
$config['min_password_length'] = 8;                   // Minimum Required Length of Password (not enforced by lib - see note above)
$config['email_activation'] = FALSE;               // Email Activation for registration
$config['manual_activation'] = FALSE;               // Manual Activation for registration
$config['remember_users'] = TRUE;                // Allow users to be remembered and enable auto-login
$config['user_expire'] = 86500;               // How long to remember the user (seconds). Set to zero for no expiration - see sess_expiration in CodeIgniter Session Config for session expiration
$config['user_extend_on_login'] = FALSE;               // Extend the users cookies every time they auto-login
$config['track_login_attempts'] = TRUE;                // Track the number of failed login attempts for each user or ip.
$config['track_login_ip_address'] = TRUE;                // Track login attempts by IP Address, if FALSE will track based on identity. (Default: TRUE)
$config['maximum_login_attempts'] = 3;                   // The maximum number of failed login attempts.
$config['lockout_time'] = 600;                 /* The number of seconds to lockout an account due to exceeded attempts
You should not use a value below 60 (1 minute) */
$config['forgot_password_expiration'] = 1800;                /* The number of seconds after which a forgot password request will expire. If set to 0, forgot password requests will not expire.
30 minutes to 1 hour are good values (enough for a user to receive the email and reset its password)
You should not set a value too high, as it would be a security issue! */
$config['recheck_timer'] = 0;                   /* The number of seconds after which the session is checked again against database to see if the user still exists and is active.
Leave 0 if you don't want session recheck. if you really think you need to recheck the session against database, we would
recommend a higher value, as this would affect performance */
$config['recheck_session_access_timer'] = 0; /** The number of seconds to check session roles and permissions when access data changes occur  */

/*
 | -------------------------------------------------------------------------
 | Login session hash
 | -------------------------------------------------------------------------
 | session_hash Default: sha1()
 |
 | Please customize
 */
$config['session_hash'] = '6583d6c4f205998ecacc9f51b68a2a2e44ea0006';

/*
 | -------------------------------------------------------------------------
 | Cookie options.
 | -------------------------------------------------------------------------
 | remember_cookie_name Default: remember_code
 */
$config['remember_cookie_name'] = 'remember_code';

/*
 | -------------------------------------------------------------------------
 | Email templates.
 | -------------------------------------------------------------------------
 | Folder where email templates are stored.
 | Default: email/
 */
$config['email_templates'] = 'email/';

/*
 | -------------------------------------------------------------------------
 | Activate Account Email Template
 | -------------------------------------------------------------------------
 | Default: activation.php
 */
$config['email_activation_view'] = 'activation.php';

/*
 | -------------------------------------------------------------------------
 | Forgot Password Email Template
 | -------------------------------------------------------------------------
 | Default: forgot_password.php
 */
$config['email_forgot_password_view'] = 'forgot_password.php';
