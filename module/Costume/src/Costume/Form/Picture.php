<?php
namespace Costume\Form;

use Zend\Form\Form;

class Picture extends Form
{

	public function __construct($name = null)
	{
		parent::__construct($name);
		
		$this->setAttribute('method', 'post');
		
		$this->add(
				array(
					'name' => 'picture',
					'type' => 'File',
					'options' => array(
						'label' => 'Picture: '
					),
					'attributes' => array(
						'id' => 'input-picture'
					)
				));
		
		$this->add(
				array(
					'name' => 'submit',
					'type' => 'Submit',
					'attributes' => array(
						'value' => 'Edit',
						'id' => 'submitbutton'
					)
				));
	}
}
