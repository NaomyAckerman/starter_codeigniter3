<?php defined('BASEPATH') or exit('No direct script access allowed');

class MY_Config_Web
{
	use CI_Instance;
	use CI_Type;

	/**
	 * table
	 *
	 * @var string
	 */
	protected $table = 'config_web';
	/**
	 * cache_key
	 *
	 * @var string
	 */
	protected $cache_key = 'config_web';
	/**
	 * item
	 *
	 * @var array
	 */
	protected $items = [];

	public function __construct()
	{
		$this->load->helper(['config_web']);
		$this->load->driver('cache', ['adapter' => 'file']);

		// ! do not run any methods before items are loaded
		// * The code below is the code for loading items
		$this->items = $this->cache->file->get($this->cache_key);
		if (!$this->items) {
			$this->revalidate();
		}
		// * loading items is complete
	}

	/**
	 * getItems
	 *
	 * @return array
	 */
	public function getItems()
	{
		return $this->items;
	}

	/**
	 * getItem
	 *
	 * @param  string $key
	 * @param  mixed $default
	 * @return mixed
	 */
	public function getItem(string $key, $default = null)
	{
		return $this->items[$key]['value'] ?? $default;
	}

	/**
	 * setItem
	 *
	 * @param  string $key
	 * @param  mixed $value
	 * @param  bool $status
	 * @return bool
	 */
	public function setItem(string $key, $value, bool $status = true)
	{
		$status = (int) $status;
		$insert_value = is_object($value) || is_array($value) ? json_encode($value) : $value;
		if (array_key_exists($key, $this->items)) {
			$result = $this->db->update($this->table, ['value' => $insert_value, 'is_active' => $status], ['key' => $key]);
		} else {
			$result = $this->db->insert($this->table, ['key' => $key, 'value' => $insert_value, 'is_active' => $status]);
		}
		if ($result) {
			$this->items[$key] = [
				'is_active' => $status,
				'value' => $this->_parseData($insert_value),
			];
			$this->cache->file->save($this->cache_key, $this->items, null);
		}
		return $result;
	}

	/**
	 * removeItem
	 *
	 * @param  string $key
	 * @return bool
	 */
	public function removeItem(string $key)
	{
		$result = $this->db->delete($this->table, ['key' => $key]);
		if ($result) {
			unset($this->items[$key]);
			$this->cache->file->save($this->cache_key, $this->items, null);
		}
		return $result;
	}

	/**
	 * clean
	 *
	 * @return bool
	 */
	public function clean()
	{
		$result = $this->db->truncate($this->table);
		if ($result) {
			$this->items = [];
			$this->cache->file->delete($this->cache_key);
		}
		return $result;
	}

	/**
	 * revalidate
	 *
	 * @return void
	 */
	public function revalidate()
	{
		$items = [];
		$result = $this->_parseData($this->db->select('key, value, is_active')->get($this->table)->result());
		foreach ($result as $item) {
			$items[$item->key] = ['is_active' => $item->is_active, 'value' => $item->value];
		}
		$this->items = $items;
		$this->cache->file->save($this->cache_key, $items, null);
	}

	protected function _parseData($data = null)
	{
		if (!is_array($data) && !is_object($data)) {
			$new_data = json_decode($data);
			if (!$new_data) {
				return $data;
			}
			$data = is_array($new_data) || is_object($new_data) ? $new_data : $this->_parseData($new_data);
		}
		foreach ($data as $key => $value) {
			if (is_array($value) || is_object($value)) {
				$new_value = $this->_parseData($value);
			} else {
				$new_value = json_decode($value);
				if ($new_value === null) {
					$new_value = $value;
				} elseif (is_array($new_value) || is_object($new_value)) {
					$new_value = $this->_parseData($value);
				}
			}
			if (is_array($data)) {
				$data[$key] = $new_value;
			} elseif (is_object($data)) {
				$data->$key = $new_value;
			}
		}
		return $data;
	}
}

/* End of file MY_Config_Web.php */
