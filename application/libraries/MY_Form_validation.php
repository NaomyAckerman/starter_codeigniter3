<?php defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Rules supported
 * ---------------------------------------------------------------------------------------------
 * ! Files
 * * max_size[size,KB]           				Returns FALSE if the file is bigger than the given size. Size can be in format of KB (kilo Byte)
 * * min_size[size,KB]    						Returns FALSE if the file is smaller than the given size. Size can be in format of KB (kilo Byte)
 * * mimes[type]							Tests the file extension for valid file types. You can put a group too (image, application, word_document, code, zip).
 * * except_mimes[type] 					Tests the file extension for no-valid file types
 * * max_dim[x,y]						 	Returns FALSE if the image is smaller than given dimension.
 * * min_dim[x,y]					 		Returns FALSE if the image is bigger than given dimension.
 * * exact_dim[x,y]						 	Returns FALSE if the image is not the given dimension.
 */

class MY_Form_validation extends CI_Form_validation
{
	protected $CI;

	public function __construct()
	{
		parent::__construct();
		$this->CI = &get_instance();

		if ($this->CI->input->method(true) !== 'GET')
			$this->set_data($this->CI->input->post());
	}

	public function _execute($row, $rules, $postdata = NULL, $cycles = 0)
	{
		$field = explode('[', $row['field'])[0] ?? null;
		if (isset($_FILES[$field])) { // file validate
			// it is a file so process as a file
			$postdata = $_FILES[$field];
			if (is_array($postdata['name'])) {
				$postdata = $this->_normalizeFiles($row['keys']);
			}

			// overrides required to file
			if (in_array('required', $rules)) {
				$rules[array_search('required', $rules)] = 'file';
			}

			//before doing anything check for errors
			if ($postdata['error'] !== UPLOAD_ERR_OK) {
				$message = isset($row['errors']['file']) ?
					$this->_build_error_msg($row['errors']['file'], $this->_translate_fieldname($row['label']), '') :
					$this->_fileUploadErrorMessage($row['label'], $postdata['error']);
				// if file required set error
				if (in_array('file', $rules)) {
					$this->_error_array[$row['field']] = $message;
					$this->_field_data[$row['field']]['error'] = $message;
					return FALSE;
				}
			}

			$_in_array = FALSE;

			// If the field is blank, but NOT required, no further tests are necessary
			$callback = FALSE;
			if (!in_array('file', $rules) and $postdata['size'] == 0) {
				// Before we bail out, does the rule contain a callback?
				if (preg_match("/(callback_\w+)/", implode(' ', $rules), $match)) {
					$callback = TRUE;
					$rules = (array('1' => $match[1]));
				} else {
					return;
				}
			}

			$message = '';
			foreach ($rules as $rule) {
				/// COPIED FROM the original class

				// Is the rule a callback?			
				$callback = $callable = FALSE;
				if (is_string($rule)) {
					if (strpos($rule, 'callback_') === 0) {
						$rule = substr($rule, 9);
						$callback = TRUE;
					}
				} elseif (is_callable($rule)) {
					$callable = TRUE;
				}

				// Strip the parameter (if exists) from the rule
				// Rules can contain a parameter: max_length[5]
				$param = FALSE;
				if (!$callable && preg_match('/(.*?)\[(.*)\]/', $rule, $match)) {
					$rule = $match[1];
					$param = $match[2];
				}
				if (!$param) {
					try {
						$available_default_value = 0;
						$method = new ReflectionMethod("MY_Form_validation::$rule");
						foreach ($method->getParameters() as $item) {
							if ($item->isDefaultValueAvailable()) {
								$value = $item->getDefaultValue();
								$param .= $item->isArray() ? implode($value) : " $value";
								$available_default_value++;
							}
						}
						$param = $available_default_value ? trim($param) : $param;
					} catch (\Throwable $e) {
					}
				}

				// Call the function that corresponds to the rule
				if ($callback or $callable) {
					if ($callback) {
						if (!method_exists($this->CI, $rule)) {
							// log_message('debug', 'Unable to find callback validation rule: ' . $rule);
							$result = FALSE;
						} else {
							// Run the function and grab the result
							$result = $this->CI->$rule($postdata, $param);
						}
					} else {
						$result = is_array($rule)
							? $rule[0]->{$rule[1]}($postdata, $param)
							: $rule($postdata, $param);
					}

					// Re-assign the result to the master data array
					if ($_in_array == TRUE) {
						$this->_field_data[$row['field']]['postdata'][$cycles] = (is_bool($result)) ? $postdata : $result;
					} else {
						$this->_field_data[$row['field']]['postdata'] = (is_bool($result)) ? $postdata : $result;
					}

					// If the field isn't required and we just processed a callback we'll move on...
					if (!in_array('file', $rules, TRUE) and $result !== FALSE) {
						return;
					}
				} elseif (!method_exists($this, $rule)) {
					// log_message('debug', 'Unable to find validation rule: ' . $rule);
					$result = FALSE;
					// If our own wrapper function doesn't exist we see if a native PHP function does.
					// Users can use any native PHP function call that has one param.
					if (method_exists($this, $rule)) {
						// Native PHP functions issue warnings if you pass them more parameters than they use
						$result = ($param !== FALSE) ? $this->$rule($postdata, $param) : $this->$rule($postdata);

						if ($_in_array === TRUE) {
							$this->_field_data[$row['field']]['postdata'][$cycles] = is_bool($result) ? $postdata : $result;
						} else {
							$this->_field_data[$row['field']]['postdata'] = is_bool($result) ? $postdata : $result;
						}
					} else {
						// log_message('debug', 'Unable to find validation rule: ' . $rule);
						$result = FALSE;
					}

					// log_message('debug', 'Unable to find validation rule: ' . $rule);
					$result = TRUE;
				} else {
					$refl = new ReflectionClass($this);
					if ($refl->getMethod($rule)->class == 'CI_Form_validation') {
						return false;
					}

					$result = $this->$rule($postdata, $param);
					if ($_in_array === TRUE) {
						$this->_field_data[$row['field']]['postdata'][$cycles] = is_bool($result) ? $postdata : $result;
					} else {
						$this->_field_data[$row['field']]['postdata'] = is_bool($result) ? $postdata : $result;
					}
				}

				// Did the rule test negatively?  If so, grab the error.
				if ($result === FALSE) {
					// Check if a custom message is defined
					if (isset($this->_field_data[$row['field']]['errors'][$rule])) {
						$line = $this->_field_data[$row['field']]['errors'][$rule];
					} elseif (!isset($this->_error_messages[$rule])) {
						if (
							FALSE === ($line = $this->CI->lang->line('form_validation_' . $rule))
							// DEPRECATED support for non-prefixed keys
							&& FALSE === ($line = $this->CI->lang->line($rule, FALSE))
						) {
							$line = 'Unable to access an error message corresponding to your field name.';
						}
					} else {
						$line = $this->_error_messages[$rule];
					}
					// Is the parameter we are inserting into the error message the name
					// of another field? If so we need to grab its "field label"
					if (isset($this->_field_data[$param], $this->_field_data[$param]['label'])) {
						$param = $this->_translate_fieldname($this->_field_data[$param]['label']);
					}

					if ($message === '') {
						// Build the error message
						$message = $this->_build_error_msg($line, $this->_translate_fieldname($row['label']), $param);

						// Save the error message
						$this->_field_data[$row['field']]['error'] = $message;

						$this->_error_array[$row['field']] = $message;
					}
				}
			}
		} else {
			parent::_execute($row, $rules, $postdata, $cycles);
		}
	}

	private function _normalizeFiles($keys = null)
	{
		$out = [];
		foreach ($_FILES as $key => $file) {
			if (isset($file['name']) && is_array($file['name'])) {
				$new = [];
				foreach (['name', 'type', 'tmp_name', 'error', 'size'] as $k) {
					array_walk_recursive($file[$k], function (&$data, $key, $k) {
						$data = [$k => $data];
					}, $k);
					$new = array_replace_recursive($new, $file[$k]);
				}
				$out[$key] = $new;
			} else {
				$out[$key] = $file;
			}
		}
		if ($keys) {
			foreach ($keys as $key) {
				$out = $out[$key];
			}
		}
		return $out;
	}

	public function run($group = '')
	{
		$this->_nestedField();
		return parent::run($group);
	}

	private function _nestedField()
	{
		foreach ($this->_field_data as $field => $row) {
			if ($row['is_array']) {
				unset($this->_field_data[$field]);
				$parent = $row['keys'][0];
				$rules = $row['rules'];
				$label = $row['label'];
				$errors = $row['errors'];
				$posts = $_POST[$parent] ?? null;
				if (isset($_FILES[$parent])) {
					$posts = $_FILES[$parent]['name'];
				}
				$this->_setNestedRule($posts, $field, $parent, $rules, $label, $errors);
			}
		}
	}

	private function _setNestedRule($posts, $fields, $parent, $rules, $label, $errors)
	{
		foreach ($posts as $key => $post) {
			if (is_array($post)) {
				$this->_setNestedRule($posts[$key], $fields, "{$parent}[$key]", $rules, $label, $errors);
			} else {
				$this->set_rules("{$parent}[$key]", $label, $rules, $errors);
			}
		}
	}

	private function _fileUploadErrorMessage($field, $error_code)
	{
		$param = '';
		switch ($error_code) {
			case UPLOAD_ERR_INI_SIZE:
				$message = $this->CI->lang->line("form_validation_error_max_filesize_phpini");
				break;
			case UPLOAD_ERR_FORM_SIZE:
				$message = $this->CI->lang->line("form_validation_error_max_filesize_form");
				break;
			case UPLOAD_ERR_PARTIAL:
				$message = $this->CI->lang->line("form_validation_error_partial_upload");
				break;
			case UPLOAD_ERR_NO_FILE:
				$message = $this->CI->lang->line("form_validation_file");
				break;
			case UPLOAD_ERR_NO_TMP_DIR:
				$message = $this->CI->lang->line("form_validation_error_temp_dir");
				break;
			case UPLOAD_ERR_CANT_WRITE:
				$message = $this->CI->lang->line("form_validation_error_disk_write");
				break;
			case UPLOAD_ERR_EXTENSION:
				$message = $this->CI->lang->line("form_validation_error_stopped");
				break;
			default:
				return $this->_build_error_msg($this->CI->lang->line("form_validation_error_unexpected"), $this->_translate_fieldname($field), $param) . $error_code;
				break;
		}
		return $this->_build_error_msg($message, $this->_translate_fieldname($field), $param);
	}

	private function _toByte(string $value)
	{
		$value = strtoupper($value);
		// Split value from name
		if (!preg_match('/([0-9]+)([ptgmkb]{1,2}|)/ui', $value, $aMatches)) { // Invalid input
			return FALSE;
		}

		if (empty($aMatches[2])) { // No name -> Enter default value
			$aMatches[2] = 'KB';
		}

		if (strlen($aMatches[2]) == 1) { // Shorted name -> full name
			$aMatches[2] .= 'B';
		}

		$iBit = (substr($aMatches[2], -1) == 'B') ? 1024 : 1000;
		// Calculate bits:

		switch (strtoupper(substr($aMatches[2], 0, 1))) {
			case 'P':
				$aMatches[1] *= $iBit;
			case 'T':
				$aMatches[1] *= $iBit;
			case 'G':
				$aMatches[1] *= $iBit;
			case 'M':
				$aMatches[1] *= $iBit;
			case 'K':
				$aMatches[1] *= $iBit;
				break;
		}
		// Return the value in bits
		return $aMatches[1];
	}

	public function max_size($file, $max_size = '2MB')
	{
		$max_size = str_replace(' ', '', $max_size);
		if (is_array($file)) {
			$max_size_bytes = $this->_toByte($max_size);
			return $file['size'] < $max_size_bytes || !$max_size_bytes ? true : false;
		}
		return false;
	}

	public function min_size($file, $min_size = '2MB')
	{
		$min_size = str_replace(' ', '', $min_size);
		if (is_array($file)) {
			$min_size_bytes = $this->_toByte($min_size);
			return $file['size'] > $min_size_bytes || !$min_size_bytes ? true : false;
		}
		return false;
	}

	public function mimes($file, $type)
	{
		if (is_array($file) && $type) {
			//is type of format a,b,c,d? -> convert to array
			$allow_type = array_map('strtolower', explode(',', $type));
			$mime_config = &get_mimes();
			$file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
			$file_mime = strtolower($file['tmp_name'] ? mime_content_type($file['tmp_name']) : '');
			$list_mime = $mime_config[$file_ext] ?? [];
			$list_mime = is_array($list_mime) ? $list_mime : [$list_mime];
			$valid_ext = in_array($file_ext, $allow_type);
			$valid_mime = in_array($file_mime, $list_mime);
			if ((!$valid_ext || !$valid_mime)) {
				return false;
			}
			return true;
		}
		return false;
	}

	public function except_mimes($file, $type)
	{
		return !$this->mimes($file, $type);
	}

	public function max_dim($file, $dim)
	{
		if (is_array($file) && $dim) {
			$max_dim = explode(',', $dim);
			$file_dim = getimagesize($file['tmp_name']);
			$unl_dim_width = $max_dim[0] != '*';
			$unl_dim_height = ($max_dim[1] ?? '*') != '*';
			if ($unl_dim_width && $unl_dim_height) {
				if ($file_dim[0] <= $max_dim[0] && $file_dim[1] <= $max_dim[1]) {
					return TRUE;
				}
			} elseif ($unl_dim_width && !$unl_dim_height) {
				if ($file_dim[0] <= $max_dim[0]) {
					return TRUE;
				}
			} elseif (!$unl_dim_width && $unl_dim_height) {
				if ($file_dim[1] <= $max_dim[1]) {
					return TRUE;
				}
			}
		}
		return false;
	}

	public function min_dim($file, $dim)
	{
		if (is_array($file) && $dim) {
			$min_dim = explode(',', $dim);
			$file_dim = getimagesize($file['tmp_name']);
			$unl_dim_width = $min_dim[0] != '*';
			$unl_dim_height = ($min_dim[1] ?? '*') != '*';

			if ($unl_dim_width && $unl_dim_height) {
				if ($file_dim[0] >= $min_dim[0] && $file_dim[1] >= $min_dim[1]) {
					return TRUE;
				}
			} elseif ($unl_dim_width && !$unl_dim_height) {
				if ($file_dim[0] >= $min_dim[0]) {
					return TRUE;
				}
			} elseif (!$unl_dim_width && $unl_dim_height) {
				if ($file_dim[1] >= $min_dim[1]) {
					return TRUE;
				}
			}
		}
		return FALSE;
	}

	public function exact_dim($file, $dim)
	{
		if (is_array($file) && $dim) {
			$max_dim = explode(',', $dim);
			$file_dim = getimagesize($file['tmp_name']);
			$unl_dim_width = $max_dim[0] != '*';
			$unl_dim_height = ($max_dim[1] ?? '*') != '*';
			if ($unl_dim_width && $unl_dim_height) {
				if ($file_dim[0] == $max_dim[0] && $file_dim[1] == $max_dim[1]) {
					return TRUE;
				}
			} elseif ($unl_dim_width && !$unl_dim_height) {
				if ($file_dim[0] == $max_dim[0]) {
					return TRUE;
				}
			} elseif (!$unl_dim_width && $unl_dim_height) {
				if ($file_dim[1] == $max_dim[1]) {
					return TRUE;
				}
			}
		}
		return FALSE;
	}
}
