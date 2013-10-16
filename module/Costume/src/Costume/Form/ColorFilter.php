<?php
namespace Costume\Form;

use Zend\InputFilter\InputFilter;

class ColorFilter extends InputFilter
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
								'max' => 30
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
		
		$this->add(
				array(
					'name' => 'color',
					'required' => true,
					'validators' => array(
						array(
							'name' => 'StringLength',
							'options' => array(
								'encoding' => 'UTF-8',
								'max' => 6,
								'min' => 6
							)
						),
						array(
							'name' => 'Regex',
							'options' => array(
								'pattern' => '/[0-9A-Fa-f]{6}/'
							)
						)
					),
					'filters' => array(
						array(
							'name' => 'StripTags'
						),
						array(
							'name' => 'StringToUpper'
						),
						array(
							'name' => 'PregReplace',
							'options' => array(
								'pattern' => '/[^0-9A-Fa-f]/',
								'replacement' => ''
							)
						)
					)
				));
	}
}
