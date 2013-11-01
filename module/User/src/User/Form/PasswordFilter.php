<?php
namespace User\Form;

use Zend\InputFilter\InputFilter;

class PasswordFilter extends InputFilter
{

	public function __construct()
	{
		$this->add(array(
			'name' => 'current_password',
			'required' => true
		));
		
		$this->add(
				array(
					'name' => 'password',
					'required' => true,
					'validators' => array(
						array(
							'name' => 'StringLength',
							'options' => array(
								'encoding' => 'UTF-8',
								'min' => 6
							)
						),
						array(
							'name' => 'Callback',
							'options' => array(
								'callback' => function ($password)
								{
									$score = 0;
									$regexpMatch = array(
										'/[a-z]+/', // Au moins une lettre minuscule
										'/[A-Z]+/', // Au moins une lettre majuscule
										'/[0-9]+/', // Au moins un chiffre
										'/[^a-zA-Z0-9 ]/' // Au moins un caractère spécial (c-a-d hors chiffres et lettres et l'espace)
																		);
									
									foreach ($regexpMatch as $regexp) {
										if (preg_match($regexp, $password)) {
											$score++;
										}
									}
									
									if ($score < 2) {
										return false;
									} else {
										return true;
									}
								},
								'messages' => array(
									\Zend\Validator\Callback::INVALID_VALUE => "Not strong enough"
								)
							)
						)
					)
				));
		
		$this->add(
				array(
					'name' => 'password_verification',
					'required' => true,
					'validators' => array(
						array(
							'name' => 'Identical',
							'options' => array(
								'token' => 'password',
								'messages' => array(
									\Zend\Validator\Identical::NOT_SAME => "Passwords are differents"
								)
							)
						)
					)
				));
	}
}
