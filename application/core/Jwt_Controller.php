<?php defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'third_party/php-jwt/JWTExceptionWithPayloadInterface.php';
require_once APPPATH . 'third_party/php-jwt/CachedKeySet.php';
require_once APPPATH . 'third_party/php-jwt/BeforeValidException.php';
require_once APPPATH . 'third_party/php-jwt/ExpiredException.php';
require_once APPPATH . 'third_party/php-jwt/SignatureInvalidException.php';
require_once APPPATH . 'third_party/php-jwt/Key.php';
require_once APPPATH . 'third_party/php-jwt/JWT.php';
require_once APPPATH . 'third_party/php-jwt/JWK.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * * // Custom Library Type ------------------------------------------------------------------------
 * If you want the cast type to be loaded globally, you can register it in the CI_Type trait
 */
class Jwt_Controller extends Rest_Controller
{
	protected $token_key;
	protected $token_algorithm;
	protected $token_header;
	protected $token_expire_time;
	protected $token_start_time;
	public function __construct()
	{
		parent::__construct();

		$this->load->config('jwt');

		$this->token_key = config_item('jwt_key');
		$this->token_algorithm = config_item('jwt_algorithm');
		$this->token_header = config_item('token_header');
		$this->token_expire_time = config_item('token_expire_time');
		$this->token_start_time = config_item('token_start_time');
	}

	/**
	 * generateToken
	 *
	 * @param  array $payload
	 * @param  array<string, string> $headers
	 * @return mixed
	 */
	public function generateToken($payload = [], $headers = null)
	{
		// iat: the timestamp of token issuing.
		// key: a unique string, which could be used to validate a token, 
		// 		but goes against not having a centralized issuer authority.
		// iss: a string containing the name or identifier of the issuer. 
		// 		Can be a domain name and can be used to discard tokens from other applications.
		// nbf: a timestamp of when the token should start being considered valid. 
		// 		Should be equal to or greater than iat.
		// exp: a timestamp of when the token should cease to be valid. 
		// 		Should be greater than iat and nbf.
		$current_time = time();
		$expire_time = strtotime("+$this->token_expire_time seconds");
		$start_time = $this->token_start_time ? strtotime("+$this->token_start_time seconds") : $current_time;
		$payload_meta = [
			'iat' => $current_time,				// Issued at: time when the token was generated
			'iss' => $_SERVER['HTTP_HOST'],     // Issuer
			'nbf' => $start_time,				// Not before
			'exp' => $expire_time,              // Expire
		];
		$payload = array_merge($payload, $payload_meta);
		try {
			return JWT::encode($payload, $this->token_key, $this->token_algorithm, null, $headers);
		} catch (\Throwable $e) {
			return $e->getMessage();
		}
	}

	/**
	 * validateToken
	 *
	 * @return array<string,mixed>
	 */
	public function validateToken()
	{
		$token = $this->tokenIsExist();
		$result = ['status' => false, 'message' => '', 'payload' => []];
		try {
			$payload = (array) JWT::decode($token, new Key($this->token_key, $this->token_algorithm));
			unset($payload['iat']);
			unset($payload['iss']);
			unset($payload['nbf']);
			unset($payload['exp']);
			$result = ['status' => true, 'message' => 'Successfully', 'payload' => $payload];
		} catch (\Throwable $e) {
			$result['message'] = $e->getMessage();
		}
		return $result;
	}

	/**
	 * tokenIsExist
	 *
	 * @return string
	 */
	public function tokenIsExist()
	{
		$headers = $this->input->request_headers();
		if (!empty($headers) and is_array($headers)) {
			foreach ($headers as $header_name => $header_value) {
				if (strtolower(trim($header_name)) == strtolower(trim($this->token_header)))
					return str_replace("Bearer ", "", $header_value);
			}
		}
		return '';
	}
}

/* End of file Jwt_Controller.php */
