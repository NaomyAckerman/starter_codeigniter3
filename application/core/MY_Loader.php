<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * CodeIgniter-HMVC
 *
 * @package    CodeIgniter-HMVC
 * @author     N3Cr0N (N3Cr0N@list.ru)
 * @copyright  2019 N3Cr0N
 * @license    https://opensource.org/licenses/MIT  MIT License
 * @link       <URI> (description)
 * @version    GIT: $Id$
 * @since      Version 0.0.1
 * @filesource
 *
 */

// load the MX_Loader class
require APPPATH . "third_party/MX/Loader.php";

class MY_Loader extends MX_Loader
{
	/**
	 * List of loaded traits
	 *
	 * @var	array
	 */
	protected $_ci_traits = array();

	//
	public $CI;

	/**
	 * An array of variables to be passed through to the
	 * view, layout,....
	 */
	protected $data = array();

	/**
	 * [__construct description]
	 *
	 * @method __construct
	 */
	public function __construct()
	{
		// To inherit directly the attributes of the parent class.
		parent::__construct();

		//
		$CI = &get_instance();
	}

	public function initialize($controller = null)
	{
		// * Custom autoload
		if (file_exists(APPPATH . 'config/autoload.php')) {
			include (APPPATH . 'config/autoload.php');
		}
		if (file_exists(APPPATH . 'config/' . ENVIRONMENT . '/autoload.php')) {
			include (APPPATH . 'config/' . ENVIRONMENT . '/autoload.php');
		}
		if (!isset($autoload)) {
			return;
		}
		// Autoload traits
		if (isset($autoload['traits']) && is_array($autoload['traits'])) {
			foreach ($autoload['traits'] as $trait) {
				$this->trait($trait);
			}
		}

		parent::initialize($controller);
	}

	public function trait($traits)
	{
		$traits = is_array($traits) ? $traits : [$traits];
		foreach ($traits as $trait) {
			if (!isset($this->_ci_traits[$trait])) {
				$trait_file = APPPATH . 'traits/' . $trait . '.php';
				if (file_exists($trait_file)) {
					include_once ($trait_file);
					$this->_ci_traits[$trait] = true;
				} else {
					// If we got this far we were unable to find the requested trait.
					log_message('error', 'Unable to load the requested trait: ' . $trait);
					show_error('Unable to load the requested trait: ' . $trait);
				}
			}
		}
	}
}
