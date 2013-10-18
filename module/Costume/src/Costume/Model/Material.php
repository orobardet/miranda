<?php
namespace Costume\Model;

use Application\Model\ObjectModelBase;

class Material extends ObjectModelBase
{

	/**
	 * ID en BDD de la matière
	 *
	 * @var integer
	 */
	protected $id;

	/**
	 * Nom de la matière
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
}