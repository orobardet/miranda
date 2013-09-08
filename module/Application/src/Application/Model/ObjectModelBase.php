<?php
namespace Application\Model;

class ObjectModelBase
{
	protected $baseAttributes = array();

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
