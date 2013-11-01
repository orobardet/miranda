<?php
namespace User\Form;

use Zend\Form\Form;

class Password extends Form
{

	public function __construct($name = null, $translator = null)
	{
		parent::__construct($name);
		
		$this->setAttribute('method', 'post');
		
		$this->add(array(
			'name' => 'id',
			'type' => 'Hidden',
			'attributes' => array(
				'id' => 'input-id'
			)
		));
		
		$this->add(
				array(
					'name' => 'current_password',
					'type' => 'Password',
					'options' => array(
						'label' => 'Password: '
					),
					'attributes' => array(
						'id' => 'input-current-password',
						'title' => $translator->translate('Password')
					)
				));
		$this->add(
				array(
					'name' => 'password',
					'type' => 'Password',
					'options' => array(
						'label' => 'New password: '
					),
					'attributes' => array(
						'id' => 'input-password',
						'title' => $translator->translate('New password')
					)
				));
		
		$this->add(
				array(
					'name' => 'password_verification',
					'type' => 'Password',
					'options' => array(
						'label' => 'Password verification: '
					),
					'attributes' => array(
						'id' => 'input-password-verification',
						'title' => $translator->translate('Password verification')
					)
				));
		
		$this->add(
				array(
					'name' => 'submit',
					'type' => 'Submit',
					'attributes' => array(
						'value' => 'Go',
						'id' => 'submitbutton'
					)
				));
	}
}
