<?php defined('BASEPATH') or exit('No direct script access allowed');

use Faker\{Factory, Generator};

/**
 * * // Custom Library Type ------------------------------------------------------------------------
 * @property Generator $faker
 * If you want the cast type to be loaded globally, you can register it in the CI_Type trait
 */
class Test_Controller extends Base_Controller
{
	/**
	 * faker
	 *
	 * @var Generator|null
	 */
	protected $faker;

	public function __construct()
	{
		parent::__construct();
		if (env('APP_ENV') == 'production')
			show_404();

		if (!$this->faker) {
			$this->setFakerLocalization();
		}
	}

	public function setFakerLocalization($locale = 'id_ID')
	{
		$this->faker = Factory::create($locale);
	}
}

/* End of file Test_Controller.php */
