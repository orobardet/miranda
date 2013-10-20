<?php
namespace Costume\Form;

use Zend\InputFilter\InputFilter;

class TagFilter extends InputFilter
{

	public function __construct()
	{
		$this->add(
				array(
					'name' => 'name',
					'required' => true,
					'validators' => array(
						array(
							'name' => 'StringLength',
							'options' => array(
								'encoding' => 'UTF-8',
								'max' => 255
							)
						)
					),
					'filters' => array(
						array(
							'name' => 'StripTags'
						),
						array(
							'name' => 'StringTrim'
						)
					)
				));
	}
}
