<?php
namespace Costume\Model;

use Application\Model\ObjectModelBase;

class Costume extends ObjectModelBase
{
	const GENDER_MIXED = 'Mixte';
	const GENDER_MAN = 'Homme';
	const GENDER_WOMAN = 'Femme';
	const GENDER_NONE = null;
	
	/**
	 * ID en BDD du costume
	 * @var integer
	 */
	protected $id;
	/**
	 * Code (référence, côte)
	 * @var string
	 */
	protected $code;
	/**
	 * Libellé
	 * @var string
	 */
	protected $label;
	/**
	 * Description
	 * @var string
	 */
	protected $descr;
	/**
	 * Genre (Homme/Femme/Mixte)
	 * @var string
	 */
	protected $gender;
	/**
	 * Taille du costume
	 * @var string
	 */
	protected $size;
	/**
	 * Etat du costume
	 * @var string
	 */
	protected $state;
	/**
	 * Quantité
	 * @var integer
	 */
	protected $quantity;
	
	/**
	 * @return integer $id
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 * @return string $code
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * @return string $label
	 */
	public function getLabel()
	{
		return $this->label;
	}
	
	/**
	 * @return string $descr
	 */
	public function getDescr()
	{
		return $this->descr;
	}
	
	/**
	 * @return string $gender
	 */
	public function getGender()
	{
		return $this->gender;
	}

	/**
	 * @return string $size
	 */
	public function getSize()
	{
		return $this->size;
	}

	/**
	 * @return string $state
	 */
	public function getState()
	{
		return $this->state;
	}
	
	/**
	 * @return string $quantity
	 */
	public function getQuantity()
	{
		return $this->quantity;
	}
	
	/**
	 * @param integer $id
	 */
	public function setId($id) {
		$this->id = intval($id);
	}
	
	/**
	 * @param string $code
	 */
	public function setCode($code)
	{
		$this->code = $code;
	}

	/**
	 * @param string $label
	 */
	public function setLabel($label)
	{
		$this->label = $label;
	}

	/**
	 * @param  $descr
	 */
	public function setDescr($descr)
	{
		$this->descr = $descr;
	}

	/**
	 * @param string $gender
	 */
	public function setGender($gender)
	{
		$this->gender = $gender;
	}

	/**
	 * @param string $size
	 */
	public function setSize($size)
	{
		$this->size = $size;
	}
	
	/**
	 * @param string $state
	 */
	public function setState($state)
	{
		$this->state = $state;
	}
	
	/**
	 * @param integer $quantity
	 */
	public function setQuantity($quantity)
	{
		$this->quantity = intval($quantity);
	}
	
	public function exchangeArray($data)
	{
		$this->id = (array_key_exists('id', $data)) ? $data['id'] : null;
		$this->code = (array_key_exists('code', $data)) ? $data['code'] : null;
		$this->label = (array_key_exists('label', $data)) ? $data['label'] : null;
		$this->descr = (array_key_exists('descr', $data)) ? $data['descr'] : null;
		$this->gender = (array_key_exists('gender', $data)) ? $data['gender'] : null;
		$this->size = (array_key_exists('size', $data)) ? $data['size'] : null;
		$this->state = (array_key_exists('state', $data)) ? $data['state'] : $this->state;
		$this->quantity = (array_key_exists('quantity', $data)) ? $data['quantity'] : $this->quantity;
	}
	
	public function getArrayCopy()
	{
		return array(
			'id' => $this->id,
			'code' => $this->code,
			'label' => $this->label,
			'descr' => $this->descr,
			'gender' => $this->gender,
			'size' => $this->size,
			'state' => $this->state,
			'quantity' => $this->quantity
		);
	}
}