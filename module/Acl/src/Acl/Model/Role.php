<?php
namespace Acl\Model;

class Role
{
	/**
	 * ID en BDD du rôle
	 * @var integer
	 */
	protected $id;
	/**
	 * Nom du rôle (qui sert dans le code comme ressource pour les acl)
	 * @var string
	 */
	protected $name;
	/**
	 * Description du rôle
	 * @var string
	 */
	protected $descr;
	/**
	 * Liste des ID de droits du rôle. 
	 * @var array
	 */
	protected $rights;
	
	/**
	 * @return integer $id
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 * @return string $name
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return string $descr
	 */
	public function getDescr()
	{
		return $this->descr;
	}
	
	/**
	 * @return string $descr
	 */
	public function getRights()
	{
		return $this->rights;
	}
	
	/**
	 * @param integer $id
	 */
	public function setId($id) {
		$this->id = $id;
	}
	
	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}
	
	/**
	 * @param string $descr
	 */
	public function setDescr($descr) {
		$this->descr = $descr;
	}
	
	/**
	 * @param array $rights
	 */
	public function setRights($rights)
	{
		$this->rights = $rights;
	}

	public function exchangeArray($data)
	{
		$this->id = (array_key_exists('id', $data)) ? $data['id'] : null;
		$this->name = (array_key_exists('name', $data)) ? $data['name'] : null;
		$this->descr = (array_key_exists('descr', $data)) ? $data['descr'] : null;
		$this->rights = (array_key_exists('rights', $data)) ? $data['rights'] : null;
	}
	
	public function getArrayCopy()
	{
		return array(
			'id' => $this->id,
			'name' => $this->name,
			'descr' => $this->descr,
			'rights' => $this->rights
		);
	}
}