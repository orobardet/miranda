<?php
namespace User\Model;

class User
{
	/**
	 * ID en BDD de l'utilisateur
	 * @var integer
	 */
	private $id;
	/**
	 * Email (qui sert de login)
	 * @var string
	 */
	private $email;
	/**
	 * Chaine cryptée (par bcrypt) représentant le mot de passe
	 * @var string
	 */
	private $password;
	/**
	 * Prénom
	 * @var string
	 */
	private $firstname;
	/**
	 * Nom
	 * @var string
	 */
	private $lastname;
	/**
	 * Indique si le compte est activé on non
	 * @var boolean
	 */
	private $active;

	/**
	 * @return integer $id
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 * @return string $email
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * Retourne le nom d'affichage : "prenom nom"
	 * 
	 * @return string Nom d'affichage
	 */
	public function getDisplayName()
	{
		return $this->firstname.' '.$this->lastname;
	}
	
	/**
	 * @return string $firstname
	 */
	public function getFirstname()
	{
		return $this->firstname;
	}

	/**
	 * @return string $lastname
	 */
	public function getLastname()
	{
		return $this->lastname;
	}

	/**
	 * @return boolean $active
	 */
	public function isActive()
	{
		return $this->active?true:false;
	}

	public function setId($id) {
		$this->id = $id;
	}
	
	/**
	 * @param string $email
	 */
	public function setEmail($email)
	{
		$this->email = $email;
	}

	/**
	 * @param string $password
	 */
	public function setPassword($password)
	{
		// TODO: encoder en bcrypt
		$this->password = $password;
	}

	/**
	 * @param string $firstname
	 */
	public function setFirstname($firstname)
	{
		$this->firstname = $firstname;
	}

	/**
	 * @param string $lastname
	 */
	public function setLastname($lastname)
	{
		$this->lastname = $lastname;
	}

	/**
	 * @param boolean $active
	 */
	public function setActive($active)
	{
		$this->active = $active?1:0;
	}

	public function exchangeArray($data)
	{
		$this->id = (array_key_exists('id', $data)) ? $data['id'] : null;
		$this->email = (array_key_exists('email', $data)) ? $data['email'] : null;
		$this->password = (array_key_exists('password', $data)) ? $data['password'] : null;
		$this->firstname = (array_key_exists('firstname', $data)) ? $data['firstname'] : null;
		$this->lastname = (array_key_exists('lastname', $data)) ? $data['lastname'] : null;
		$this->active = (array_key_exists('active', $data)) ? $data['active'] : null;
	}
}