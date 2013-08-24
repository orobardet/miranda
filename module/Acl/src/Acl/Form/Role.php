<?php
namespace Acl\Form;

use Zend\Form\Form;

class Role extends Form
{

	public function __construct ($name = null, $translator)
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
					'name' => 'name',
					'type' => 'Text',
					'options' => array(
						'label' => 'Name: '
					),
					'attributes' => array(
						'id' => 'input-name',
						'title' => $translator->translate('Name')
					)
				));
		
		$this->add(
				array(
					'name' => 'descr',
					'type' => 'Text',
					'options' => array(
						'label' => 'Description: '
					),
					'attributes' => array(
						'id' => 'input-descr',
						'title' => $translator->translate('Description')
					)
				));
		
		$this->add(
				array(
					'name' => 'rights',
					'type' => 'Text',
					'options' => array(
						'label' => 'Rights: '
					),
					'attributes' => array(
						'id' => 'input-rights',
						'title' => $translator->translate('Rights')
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
