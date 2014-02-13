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
					'name' => 'picture_file',
					'type' => 'File',
					'options' => array(
						'label' => 'Picture: '
					),
					'attributes' => array(
						'id' => 'input-picture',
						'accept' => 'image/jpeg'
					)
				));
		
		$this->add(
				array(
					'name' => 'submit',
					'type' => 'Submit',
					'attributes' => array(
						'value' => 'Save picture',
						'id' => 'picture-save-button'
					)
				));
	}
}
