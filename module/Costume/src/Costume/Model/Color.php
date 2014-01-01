<?php
namespace Costume\Model;

use Application\Model\ObjectModelBase;

class Color extends ObjectModelBase
{

	/**
	 * ID en BDD de la couleur
	 * 
	 * @var integer
	 */
	protected $id;

	/**
	 * Nom de la couleur
	 * 
	 * @var string
	 */
	protected $name;

	/**
	 * Code couleur
	 * 
	 * @var string
	 */
	protected $color;

	/**
	 * Ordre d'affichage de la couleur
	 * 
	 * @var integer
	 */
	protected $ord;

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
	 * @return $color
	 */
	public function getColorCode()
	{
		return $this->color;
	}

	/**
	 *
	 * @return $ord
	 */
	public function getOrd()
	{
		return $this->ord;
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

	/**
	 *
	 * @param string $color
	 */
	public function setColorCode($color)
	{
		$color = strtoupper(ltrim($color, '#'));
		if (preg_match('/[0-9A-F]{6}/', $color)) {
			$this->color = $color;
		}
	}

	/**
	 *
	 * @param number $ord
	 */
	public function setOrd($ord)
	{
		$this->ord = $ord;
	}

	public function exchangeArray($data)
	{
		$this->id = (array_key_exists('id', $data)) ? $data['id'] : null;
		$this->name = (array_key_exists('name', $data)) ? $data['name'] : null;
		$this->color = (array_key_exists('color', $data)) ? $data['color'] : null;
		$this->ord = (array_key_exists('ord', $data)) ? $data['ord'] : null;
	}

	public function getArrayCopy()
	{
		return array(
			'id' => $this->id,
			'name' => $this->name,
			'color' => $this->color,
			'ord' => $this->ord
		);
	}
}