<?php defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('asset')) {
	/**
	 * Generate an asset url for the application.
	 *
	 * @param  string  $path
	 * @return string
	 */
	function asset($path = "")
	{
		return base_url("assets/$path");
	}
}

if (!function_exists('storage')) {
	/**
	 * Generate an storage url for the application.
	 *
	 * @param  string  $path
	 * @return string
	 */
	function storage($path = "")
	{
		return base_url("assets/storage/$path");
	}
}

if (!function_exists('to_bytes')) {
	/**
	 * to_bytes
	 *
	 * @param  string $value
	 * @return string
	 */
	function to_bytes(string $value)
	{
		$value = strtoupper($value);
		// Split value from name
		if (!preg_match('/([0-9]+)([ptgmkb]{1,2}|)/ui', $value, $aMatches)) { // Invalid input
			return '';
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
}

/* End of file core_helper.php */
