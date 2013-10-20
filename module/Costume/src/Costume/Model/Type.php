<?php
namespace Costume\Model;

use Application\Model\ObjectModelBase;

class Type extends ObjectModelBase
{

	public function __construct($name=null)
	{
		if ($name) {
			$this->setName($name);
		}
	}
	
	/**
	 * ID en BDD du type
	 * 
	 * @var integer
	 */
	protected $id;

	/**
	 * Nom du type
	 * 
	 * @var string
	 */
	protected $name;

	/**
	 *
	 * @return $id
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 *
	 * @return $name
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 *
	 * @param number $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 *
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	public function exchangeArray($data)
	{
		$this->id = (array_key_exists('id', $data)) ? $data['id'] : null;
		$this->name = (array_key_exists('name', $data)) ? $data['name'] : null;
	}

	public function getArrayCopy()
	{
		return array(
			'id' => $this->id,
			'name' => $this->name
		);
	}
	
	public function __toString()
	{
		return $this->name;
	}
}