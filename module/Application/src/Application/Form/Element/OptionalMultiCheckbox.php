<?php
namespace Application\Form\Element;

use Zend\Form\Element\MultiCheckbox;

class OptionalMultiCheckbox extends MultiCheckbox
{
    /**
     * Provide default input rules for this element
     *
     * Attaches the captcha as a validator.
     *
     * @return array
     */
    public function getInputSpecification()
    {
    	$spec = parent::getInputSpecification();
    	$spec['required'] = false;

        return $spec;
    }
}

?>