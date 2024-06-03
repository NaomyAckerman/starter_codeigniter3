<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Base_Model extends CI_Model
{
	public $table;
	public $alias;
	public $key = 'id';
	public $sort = 'DESC';
	public $order_by;
	protected $created_at_field = 'created_at';
	protected $updated_at_field = 'updated_at';
	protected $deleted_at_field = 'deleted_at';
	protected $soft_deletes = false;
	protected $timestamps_format = 'Y-m-d H:i:s';

	public function __construct()
	{
		parent::__construct();
		$this->order_by = ($this->alias ?: $this->table) . "." . ($this->order_by ?? $this->key);
	}

	public function switchDatabase(string $database_name)
	{
		$this->db->db_select($database_name);
	}

	public function database()
	{
		return $this->db->database;
	}

	public function lastQuery()
	{
		return $this->db->last_query();
	}

	public function truncate()
	{
		return $this->db->truncate($this->table);
	}

	public function create(array $data = [])
	{
		$result = null;
		if ($data) {
			if ($this->created_at_field)
				$data[$this->created_at_field] = date($this->timestamps_format);
			if ($this->updated_at_field)
				$data[$this->updated_at_field] = date($this->timestamps_format);
			$this->db->insert($this->table, $data);
			$id = $this->db->insert_id();
			$result = $id;
		}
		return $result;
	}

	public function createBatch(array $data = [])
	{
		$data = array_map(function ($row) {
			if ($this->created_at_field)
				$row[$this->created_at_field] = date($this->timestamps_format);
			if ($this->updated_at_field)
				$row[$this->updated_at_field] = date($this->timestamps_format);
			return $row;
		}, $data);
		return $this->db->insert_batch($this->table, $data);
	}

	public function update(array $data = [], array $where = [])
	{
		$result = false;
		if ($data) {
			if ($this->updated_at_field)
				$data[$this->updated_at_field] = date($this->timestamps_format);
			$result = $this->db->update($this->table, $data, $where);
		}
		return $result;
	}

	public function delete(array $where = [])
	{
		$result = false;
		if ($this->deleted_at_field && $this->soft_deletes) {
			$result = $this->update([
				"$this->table.$this->deleted_at_field" => date($this->timestamps_format)
			], $where);
		} else
			$result = $this->forceDelete($where);
		return $result;
	}

	public function forceDelete(array $where = [])
	{
		return $this->db->delete($this->table, $where);
	}

	public function restore(array $where = [])
	{
		$result = true;
		if ($this->deleted_at_field && $this->soft_deletes) {
			$result = $this->db
				->where($where)
				->update($this->table, [
					"$this->table.$this->deleted_at_field" => null
				]);
		}
		return $result;
	}

	public function get(array $where = [], Closure $calback = null)
	{
		$tableAs = $this->alias ?: $this->table;
		$table = $this->alias ? "$this->table as $this->alias" : $this->table;

		if ($calback)
			$calback($this->db, $tableAs);

		// validate soft delete
		if ($this->deleted_at_field && $this->soft_deletes) {
			$where["$tableAs.$this->deleted_at_field"] = null;
		}

		$result = $this->db
			->get_where($table, $where)
			->row();
		return $this->_parseData($result);
	}

	public function getAll(array $where = [], Closure $calback = null)
	{
		$tableAs = $this->alias ?: $this->table;
		$table = $this->alias ? "$this->table as $this->alias" : $this->table;

		$this->db->order_by($this->order_by, $this->sort);

		if ($calback)
			$calback($this->db, $tableAs);

		// validate soft delete
		if ($this->deleted_at_field && $this->soft_deletes) {
			$where["$tableAs.$this->deleted_at_field"] = null;
		}

		$result = $this->db
			->get_where($table, $where)
			->result();

		return $this->_parseData($result);
	}

	public function getTrash(array $where = [], Closure $calback = null)
	{
		$tableAs = $this->alias ?: $this->table;
		$this->soft_deletes = false;
		$where["$tableAs.$this->deleted_at_field"] = '!= null';
		return $this->get($where, $calback);
	}

	public function getAllTrash(array $where = [], Closure $calback = null)
	{
		$tableAs = $this->alias ?: $this->table;
		$this->soft_deletes = false;
		$where["$tableAs.$this->deleted_at_field"] = '!= null';
		return $this->getAll($where, $calback);
	}

	public function getWithTrash(array $where = [], Closure $calback = null)
	{
		$this->soft_deletes = false;
		return $this->get($where, $calback);
	}

	public function getAllWithTrash(array $where = [], Closure $calback = null)
	{
		$this->soft_deletes = false;
		return $this->getAll($where, $calback);
	}

	// private function _parseData($data = null)
	// {
	// 	if (is_object($data) || is_array($data)) {
	// 		foreach ($data as $key => $value) {
	// 			$new_value = json_decode($value);
	// 			if (!$new_value) {
	// 				$new_value = $value;
	// 				if (is_double($value)) {
	// 					$new_value = filter_var($value, FILTER_VALIDATE_FLOAT);
	// 				}
	// 				if (is_integer($value) || in_array(strtolower($value), ['0'])) {
	// 					$new_value = filter_var($value, FILTER_VALIDATE_INT);
	// 				}
	// 				if (in_array(strtolower($value), ['true', 'false'])) {
	// 					$new_value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
	// 				}
	// 			}
	// 			if (is_array($data))
	// 				$data[$key] = $new_value;
	// 			elseif (is_object($data))
	// 				$data->{$key} = $new_value;
	// 		}
	// 	}
	// 	return $data;
	// }

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

/* End of file Base_Model.php */
