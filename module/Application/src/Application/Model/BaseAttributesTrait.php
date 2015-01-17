<?php
namespace Application\Model;

trait BaseAttributesTrait
{
	protected $baseAttributes = array();
	
	public function filterDbId($value)
	{
		$filtered = filter_var($value, FILTER_VALIDATE_INT, array(
			'min_range' => 1,
			'default' => null
		));
		if ($filtered === false) {
			return null;
		}
		
		return $filtered;
	}

	public function setBaseAttribute($name, $value)
	{
		$this->baseAttributes[$name] = $value;
	}

	public function hasBaseAttribute($name)
	{
		return array_key_exists($name, $this->baseAttributes);
	}

	public function getBaseAttribute($name, $default = null)
	{
		if (array_key_exists($name, $this->baseAttributes)) {
			return $this->baseAttributes[$name];
		} else {
			return $default;
		}
	}

	public function unsetBaseAttribute($name)
	{
		if (array_key_exists($name, $this->baseAttributes)) {
			unset($this->baseAttributes[$name]);
		}
	}
}
