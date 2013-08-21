<?php
namespace User\Form;

use Zend\InputFilter\InputFilter;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Validator\Db\NoRecordExists as DbNoRecordExists;

class UserFilter extends InputFilter
{

	public function __construct (DbAdapter $dbAdapter)
	{
		$this->add(
				array(
					'name' => 'firstname',
					'required' => false,
					'validators' => array(
						array(
							'name' => 'StringLength',
							'options' => array(
								'encoding' => 'UTF-8',
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
					'name' => 'lastname',
					'required' => false,
					'validators' => array(
						array(
							'name' => 'StringLength',
							'options' => array(
								'encoding' => 'UTF-8',
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
					'name' => 'email',
					'required' => true,
					'validators' => array(
						array(
							'name' => 'EmailAddress'
						),
						new DbNoRecordExists(
								array(
									'table' => 'users',
									'field' => 'email',
									'adapter' => $dbAdapter,
									'messages' => array(
										DbNoRecordExists::ERROR_RECORD_FOUND => "Email address already used"
									)
								))
					),
					'filters' => array(
						array(
							'name' => 'StringTrim'
						)
					)
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
										'/[a-z]+/',			// Au moins une lettre minuscule 
							        	'/[A-Z]+/',			// Au moins une lettre majuscule
							        	'/[0-9]+/',   		// Au moins un chiffre
							   			'/[^a-zA-Z0-9 ]/' 	// Au moins un caractère spécial (c-a-d hors chiffres et lettres et l'espace)
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