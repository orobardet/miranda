<?php
namespace Costume\Form;

use Zend\InputFilter\InputFilter;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Validator\Db\NoRecordExists as DbNoRecordExists;
use Zend\Config\Config as ZendConfig;

class CostumeFilter extends InputFilter
{

	private $codeNotExistsValidator;

	public function __construct(DbAdapter $dbAdapter, ZendConfig $config)
	{
		$this->codeNotExistsValidator = new DbNoRecordExists(
				array(
					'table' => $config->get('db->table_prefix', '') . 'costumes',
					'field' => 'code',
					'adapter' => $dbAdapter,
					'messages' => array(
						DbNoRecordExists::ERROR_RECORD_FOUND => "Code already used"
					)
				));
		
		$this->add(
				array(
					'name' => 'code',
					'required' => true,
					'validators' => array(
						array(
							'name' => 'StringLength',
							'options' => array(
								'encoding' => 'UTF-8',
								'min' => 1,
								'max' => 20
							)
						),
						$this->codeNotExistsValidator
					),
					'filters' => array(
						array(
							'name' => 'StripTags'
						),
						array(
							'name' => 'StringTrim'
						)
					)
				));
		
		$this->add(
				array(
					'name' => 'label',
					'required' => true,
					'validators' => array(
						array(
							'name' => 'StringLength',
							'options' => array(
								'encoding' => 'UTF-8',
								'min' => 1,
								'max' => 255
							)
						)
					),
					'filters' => array(
						array(
							'name' => 'StripTags'
						),
						array(
							'name' => 'StringTrim'
						)
					)
				));
		
		$this->add(
				array(
					'name' => 'descr',
					'required' => false,
					'validators' => array(
						array(
							'name' => 'StringLength',
							'options' => array(
								'encoding' => 'UTF-8',
								'max' => 65535
							)
						)
					),
					'filters' => array(
						array(
							'name' => 'StripTags'
						)
					)
				));
		
		$this->add(
				array(
					'name' => 'quantity',
					'required' => true,
					'validators' => array(
						array(
							'name' => 'Digits'
						),
						array(
							'name' => 'GreaterThan',
							'options' => array(
								'min' => 0
							)
						),
						array(
							'name' => 'LessThan',
							'options' => array(
								'max' => 255,
								'inclusive' => true
							)
						)
					),
					'filters' => array(
						array(
							'name' => 'Int'
						)
					)
				));
		
		$this->add(
				array(
					'name' => 'size',
					'required' => false,
					'validators' => array(
						array(
							'name' => 'StringLength',
							'options' => array(
								'encoding' => 'UTF-8',
								'max' => 20
							)
						)
					),
					'filters' => array(
						array(
							'name' => 'StripTags'
						),
						array(
							'name' => 'StringTrim'
						)
					)
				));
		
		$this->add(array(
			'name' => 'gender',
			'required' => false
		));
		
		$this->add(
				array(
					'name' => 'type',
					'required' => false,
					'validators' => array(
						array(
							'name' => 'StringLength',
							'options' => array(
								'encoding' => 'UTF-8',
								'max' => 100
							)
						)
					),
					'filters' => array(
						array(
							'name' => 'StripTags'
						),
						array(
							'name' => 'StringTrim'
						)
					)
				));
		
		$this->add(
				array(
					'name' => 'parts_selector',
					'required' => false,
					'validators' => array(
						array(
							'name' => 'StringLength',
							'options' => array(
								'encoding' => 'UTF-8',
								'max' => 100
							)
						)
					),
					'filters' => array(
						array(
							'name' => 'StripTags'
						),
						array(
							'name' => 'StringTrim'
						)
					)
				));
		
		$this->add(
				array(
					'name' => 'parts',
					'required' => false,
					'validators' => array(
						array(
							'name' => 'Callback',
							'options' => array(
								'callback' => function ($parts)
								{
									$strLenValidator = new \Zend\Validator\StringLength(
											array(
												'encoding' => 'UTF-8',
												'min' => 1,
												'max' => 100
											));
									if (count($parts)) {
										foreach ($parts as $part) {
											if (!$strLenValidator->isValid($part)) {
												return false;
											}
										}
									}
									return true;
								}
							)
						)
					),
					'filters' => array(
						array(
							'name' => 'Callback',
							'options' => array(
								'callback' => function ($parts)
								{
									if (count($parts)) {
										$strip = new \Zend\Filter\StripTags();
										$trim = new \Zend\Filter\StringTrim();
										foreach ($parts as $key => $part) {
											$part = $strip->filter($part);
											$part = $trim->filter($part);
											$parts[$key] = $part;
										}
									}
									return $parts;
								}
							)
						)
					)
				));
		
		$this->add(
				array(
					'name' => 'primary_material',
					'required' => false,
					'validators' => array(
						array(
							'name' => 'StringLength',
							'options' => array(
								'encoding' => 'UTF-8',
								'max' => 100
							)
						)
					),
					'filters' => array(
						array(
							'name' => 'StripTags'
						),
						array(
							'name' => 'StringTrim'
						)
					)
				));
		
		$this->add(
				array(
					'name' => 'secondary_material',
					'required' => false,
					'validators' => array(
						array(
							'name' => 'StringLength',
							'options' => array(
								'encoding' => 'UTF-8',
								'max' => 100
							)
						)
					),
					'filters' => array(
						array(
							'name' => 'StripTags'
						),
						array(
							'name' => 'StringTrim'
						)
					)
				));
		
		$this->add(
				array(
					'name' => 'primary_color_id',
					'required' => false,
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
					'name' => 'secondary_color_id',
					'required' => false,
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
					'name' => 'state',
					'required' => false,
					'validators' => array(
						array(
							'name' => 'StringLength',
							'options' => array(
								'encoding' => 'UTF-8',
								'max' => 255
							)
						)
					),
					'filters' => array(
						array(
							'name' => 'StripTags'
						),
						array(
							'name' => 'StringTrim'
						)
					)
				));
		
		$this->add(array(
			'name' => 'origin',
			'required' => false
		));
		
		$this->add(
				array(
					'name' => 'tags_selector',
					'required' => false,
					'validators' => array(
						array(
							'name' => 'StringLength',
							'options' => array(
								'encoding' => 'UTF-8',
								'max' => 100
							)
						)
					),
					'filters' => array(
						array(
							'name' => 'StripTags'
						),
						array(
							'name' => 'StringTrim'
						)
					)
				));
		
		$this->add(
				array(
					'name' => 'tags',
					'required' => false,
					'validators' => array(
						array(
							'name' => 'Callback',
							'options' => array(
								'callback' => function ($tags)
								{
									$strLenValidator = new \Zend\Validator\StringLength(
											array(
												'encoding' => 'UTF-8',
												'min' => 1,
												'max' => 255
											));
									if (count($tags)) {
										foreach ($tags as $tag) {
											if (!$strLenValidator->isValid($tag)) {
												return false;
											}
										}
									}
									return true;
								}
							)
						)
					),
					'filters' => array(
						array(
							'name' => 'Callback',
							'options' => array(
								'callback' => function ($tags)
								{
									if (count($tags)) {
										$strip = new \Zend\Filter\StripTags();
										$trim = new \Zend\Filter\StringTrim();
										foreach ($tags as $key => $tag) {
											$tag = $strip->filter($tag);
											$tag = $trim->filter($tag);
											$tags[$key] = $tag;
										}
									}
									return $tags;
								}
							)
						)
					)
				));
	}

	public function setCostumeId($id)
	{
		if (isset($this->codeNotExistsValidator)) {
			$this->codeNotExistsValidator->setExclude(array(
				'field' => 'id',
				'value' => $id
			));
		}
	}
}
