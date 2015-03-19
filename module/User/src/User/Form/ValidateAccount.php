<?php
namespace User\Form;

use Zend\Form\Form;

class ValidateAccount extends Form
{

	public function __construct($name = null, $translator = null)
	{
		parent::__construct($name);
		
		$this->setAttribute('method', 'post');
		
		$this->add(
				array(
					'name' => 'firstname',
					'type' => 'Text',
					'options' => array(
						'label' => 'Firstname: '
					),
					'attributes' => array(
						'id' => 'input-firstname',
						'title' => $translator->translate('Firstname')
					)
				));
		
		$this->add(
				array(
					'name' => 'lastname',
					'type' => 'Text',
					'options' => array(
						'label' => 'Lastname: '
					),
					'attributes' => array(
						'id' => 'input-lastname',
						'title' => $translator->translate('Lastname')
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
