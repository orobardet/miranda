<?php
namespace User\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class Login extends Form
{
    public function __construct($name = null, $translator)
    {
        parent::__construct($name);
        
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name' => 'identity',
            'options' => array(
                'label' => 'Email',
            ),
            'attributes' => array(
                'type' => 'email',
                'title' => $translator->translate('Email'),
        		'placeholder' => $translator->translate('Email')
            ),
        ));

        $this->add(array(
            'name' => 'credential',
            'options' => array(
                'label' => 'Password',
            ),
            'attributes' => array(
                'type' => 'password',
            	'title' => $translator->translate('Password'),
                'placeholder' => $translator->translate('Password')
            ),
        ));

        $submitElement = new Element\Button('submit');
        $submitElement
            ->setLabel('Login')
            ->setAttributes(array(
                'type'  => 'submit',
            ));

        $this->add($submitElement, array(
            'priority' => -100,
        ));
    }
}
