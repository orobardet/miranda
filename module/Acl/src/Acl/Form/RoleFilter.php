<?php
namespace Acl\Form;

use Zend\InputFilter\InputFilter;

class RoleFilter extends InputFilter
{
	private $emailNotExistsValidator;
	
	public function __construct ()
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
								'min' => 3,
								'max' => 100
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
					'name' => 'descr',
					'required' => false,
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
