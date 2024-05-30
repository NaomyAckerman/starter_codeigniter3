<?php
trait CI_Instance
{
	protected function get_instance_property($property)
	{
		// Cek apakah properti yang diminta adalah instance CI
		if (property_exists($this, $property)) {
			return $this->$property;
		} else {
			// Jika tidak, coba akses dari CI instance
			$CI =& get_instance();
			return $CI->$property;
		}
	}

	public function __get($property)
	{
		return $this->get_instance_property($property);
	}
}
