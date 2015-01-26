<?php
namespace User\Form;

use Zend\InputFilter\InputFilter;

class ForgotPasswordFilter extends InputFilter
{

	public function __construct()
	{
		$this->add(
				[
					'name' => 'email',
					'required' => true,
					'validators' => [
						[
							'name' => 'EmailAddress'
						]
					],
					'filters' => [
						[
							'name' => 'StringTrim'
						]
					]
				]);
	}
}
