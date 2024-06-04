<?php defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('config_web_items')) {
	/**
	 * config_web_items
	 *
	 * @return array
	 */
	function config_web_items()
	{
		$CI = &get_instance();
		return $CI->config_web->getItems();
	}
}

if (!function_exists('config_web_item')) {
	/**
	 * config_web_item
	 *
	 * @param  string $key
	 * @param  mixed $default
	 * @return mixed
	 */
	function config_web_item(string $key, $default = null)
	{
		$CI = &get_instance();
		return $CI->config_web->getItem($key, $default);
	}
}

if (!function_exists('config_web_set_item')) {
	/**
	 * config_web_set_item
	 *
	 * @param  string $key
	 * @param  mixed $value
	 * @param  bool $status
	 * @return bool
	 */
	function config_web_set_item(string $key, $value, bool $status = true)
	{
		$CI = &get_instance();
		return $CI->config_web->setItem($key, $value, $status);
	}
}

if (!function_exists('config_web_remove_item')) {
	/**
	 * config_web_remove_item
	 *
	 * @param  string $key
	 * @return bool
	 */
	function config_web_remove_item(string $key)
	{
		$CI = &get_instance();
		return $CI->config_web->removeItem($key);
	}
}

if (!function_exists('config_web_clean')) {
	/**
	 * config_web_clean
	 *
	 * @return bool
	 */
	function config_web_clean()
	{
		$CI = &get_instance();
		return $CI->config_web->clean();
	}
}

if (!function_exists('config_web_revalidate')) {
	/**
	 * config_web_revalidate
	 *
	 * @return void
	 */
	function config_web_revalidate()
	{
		$CI = &get_instance();
		return $CI->config_web->revalidate();
	}
}

/* End of file config_web_helper.php */
