<?php
namespace User\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class ForgotPassword extends Form
{

	public function __construct($name = null, $translator)
	{
		parent::__construct($name);
		
		$this->setAttribute('method', 'post');
		
		$this->add(
				[
					'name' => 'email',
					'options' => [
						'label' => 'Email'
					],
					'attributes' => [
						'type' => 'email',
						'title' => $translator->translate('Email'),
						'placeholder' => $translator->translate('Email')
					]
				]);
		
		$submitElement = new Element\Button('submit');
		$submitElement->setLabel('Ok')->setAttributes([
			'type' => 'submit'
		]);
		
		$this->add($submitElement, [
			'priority' => -100
		]);
	}
}
