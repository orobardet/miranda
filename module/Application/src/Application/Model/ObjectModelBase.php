<?php
namespace Application\Model;

class ObjectModelBase
{
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
}
