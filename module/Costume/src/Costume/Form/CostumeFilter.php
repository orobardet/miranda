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
