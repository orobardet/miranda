<?php
namespace Costume\Controller\InputFilter;

use Zend\InputFilter\InputFilter;

class IndexFilter extends InputFilter
{

	protected $defaults;

	protected $originalData;

	public function __construct($defaults = array())
	{
		$this->defaults = $defaults;
		
		$this->add(
				array(
					'name' => 'page',
					'required' => false,
					'allowEmpty' => true,
					'validators' => array(
						array(
							'name' => 'Digits'
						),
						array(
							'name' => 'GreaterThan',
							'options' => array(
								'min' => 0
							)
						)
					),
					'filters' => array(
						array(
							'name' => 'Int'
						),
						array(
							'name' => 'Null'
						)
					)
				));
		
		$this->add(
				array(
					'name' => 'sort',
					'required' => false,
					'allowEmpty' => true,
					'filters' => array(
						array(
							'name' => 'StripTags'
						),
						array(
							'name' => 'PregReplace',
							'options' => array(
								'pattern' => '/[^a-z0-9\-_]/i',
								'replacement' => ''
							)
						),
						array(
							'name' => 'Callback',
							'options' => array(
								'callback' => function ($value)
								{
									if (!in_array($value, array(
										'code',
										'label',
										'type',
										'gender',
										'size',
										'quantity'
									))) {
										return null;
									}
									
									return $value;
								}
							)
						),
						array(
							'name' => 'Null'
						)
					)
				));
		$this->add(
				array(
					'name' => 'direction',
					'required' => false,
					'allowEmpty' => true,
					'validators' => array(
						array(
							'name' => 'InArray',
							'options' => array(
								'haystack' => array(
									'up',
									'down'
								)
							)
						)
					),
					'filters' => array(
						array(
							'name' => 'StringToLower'
						),
						array(
							'name' => 'Callback',
							'options' => array(
								'callback' => function ($value)
								{
									if (!in_array($value, array(
										'up',
										'down'
									))) {
										return null;
									}
									
									return $value;
								}
							)
						),
						array(
							'name' => 'Null'
						)
					)
				));
	}

	public function setData($data)
	{
		$this->originalData = $data;
		return parent::setData($data);
	}

	public function validate()
	{
		$data = $this->getRawValues();
		if ($data) {
			$inputs = $this->validationGroup ?  : array_keys($this->inputs);
			$this->validateInputs($inputs, $data);
		}
	}
	
	/*
	 * (non-PHPdoc) @see \Zend\InputFilter\BaseInputFilter::getValues()
	 */
	public function getValues()
	{
		$values = parent::getValues();
		
		$givenValues = array();
		foreach ($values as $key => $val) {
			if (array_key_exists($key, $this->originalData)) {
				if (($val === null) && $this->defaults && array_key_exists($key, $this->defaults)) {
					$val = $this->defaults[$key];
				}
				$givenValues[$key] = $val;
			}
		}
		
		return $givenValues;
	}
}
