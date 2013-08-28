<?php
namespace User\Form;

use Zend\Form\Form;

class User extends Form
{

	public function __construct ($name = null, $translator = null, $roles = array())
	{
		parent::__construct($name);
		
		$this->setAttribute('method', 'post');
		
		$this->add(
				array(
					'name' => 'id',
					'type' => 'Hidden',
					'attributes' => array(
						'id' => 'input-id'
					)
				));
		
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
					'name' => 'email',
					'type' => 'Text',
					'options' => array(
						'label' => 'Email: '
					),
					'attributes' => array(
						'type' => 'email',
						'id' => 'input-lastname',
						'title' => $translator->translate('Email')
					)
				));
		
		$this->add(
				array(
					'name' => 'password',
					'type' => 'Password',
					'options' => array(
						'label' => 'Password: '
					),
					'attributes' => array(
						'id' => 'input-password',
						'title' => $translator->translate('Password')
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
					'name' => 'active',
					'type' => 'Checkbox',
					'options' => array(
						'label' => 'Activated: ',
					),
					'attributes' => array(
						'id' => 'input-active',
						'title' => $translator->translate('Activated')
					)
				));
		
		$this->add(
				array(
					'name' => 'roles',
					'type' => 'Application\Form\Element\OptionalMultiCheckbox',
					'options' => array(
						'label' => 'Roles: ',
						'value_options' => $roles,
					),
					'attributes' => array(
						'id' => 'input-roles',
						'title' => $translator->translate('Roles')
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
