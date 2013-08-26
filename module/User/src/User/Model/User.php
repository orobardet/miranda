<?php
namespace User\Model;

class User
{
	/**
	 * ID en BDD de l'utilisateur
	 * @var integer
	 */
	protected $id;
	/**
	 * Email (qui sert de login)
	 * @var string
	 */
	protected $email;
	/**
	 * Chaine cryptée (par bcrypt) représentant le mot de passe
	 * @var string
	 */
	protected $password;
	/**
	 * Prénom
	 * @var string
	 */
	protected $firstname;
	/**
	 * Nom
	 * @var string
	 */
	protected $lastname;
	/**
	 * Indique si le compte est activé on non
	 * @var boolean
	 */
	protected $active;
	/**
	 * Timestamp de la date de création du compte
	 * @var integer
	 */
	protected $creation_ts;
	/**
	 * Timestamp de la date de dernière modification du compte
	 * @var integer
	 */
	protected $modification_ts;
	/**
	 * Timestamp de la date de dernière activité de l'utilisateur
	 * @var integer
	 */
	protected $last_activity_ts;
	/**
	 * Timestamp de la date de dernière connexion de l'utilisateur
	 * @var integer
	 */
	protected $last_login_ts;
	/**
	 * Tableau d'ID des rôles affectés à l'utilisateur
	 * @var array
	 */
	protected $roles;
	
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
	 * @return string $lastname
	 */
	protected function getPassword()
	{
		return $this->password;
	}
	
	/**
	 * Retourne la date de création du compte, éventuellement formatée
	 * 
	 * Si le paramètre $format est null ou faut, renvoi la date sous forme d'un timestamp (integer).
	 * Sinon, renvoie une chaine représentant la date, formaté en utilisant la fonction PHP date() 
	 * et selon le format du paramètre $format.
	 * 
	 * Si le timestamp est invalid ou null (pas un nombre positif) et qu'un format a été demandé, 
	 * retourne la chaine "N/A".
	 * 
	 * __Exemples :__
	 * 
	 * ~~~~~~~~
	 * $this->getCreationDate();               // Retourne le timestamp
	 * $this->getCreationDate("d/m/Y H:i:s");  // Retourne une chaine "17/08/2013 10:16:27"
	 * ~~~~~~~~
	 * 
	 * @param string $format Une chaine de formatage de date accepté par la fonction PHP date()
	 * 
	 * @return string|int $creation_ts
	 */
	public function getCreationDate($format=null)
	{
		return $this->getFormatedDate($this->creation_ts, $format);
	}
	
	/**
	 * Retourne la date de dernière modification du compte, éventuellement formatée
	 * 
	 * Si le paramètre $format est null ou faut, renvoi la date sous forme d'un timestamp (integer).
	 * Sinon, renvoie une chaine représentant la date, formaté en utilisant la fonction PHP date() 
	 * et selon le format du paramètre $format.
	 * 
	 * Si le timestamp est invalid ou null (pas un nombre positif) et qu'un format a été demandé, 
	 * retourne la chaine "N/A".
	 * 
	 * __Exemples :__
	 * 
	 * ~~~~~~~~
	 * $this->getLastModificationDate();               // Retourne le timestamp
	 * $this->getLastModificationDate("d/m/Y H:i:s");  // Retourne une chaine "17/08/2013 10:16:27"
	 * ~~~~~~~~
	 * 
	 * @param string $format Une chaine de formatage de date accepté par la fonction PHP date()
	 * 
	 * @return string|int $creation_ts
	 */
	public function getLastModificationDate($format=null)
	{
		return $this->getFormatedDate($this->modification_ts, $format);
	}
	
	/**
	 * Retourne la date de dernière activité du compte, éventuellement formatée
	 * 
	 * Si le paramètre $format est null ou faut, renvoi la date sous forme d'un timestamp (integer).
	 * Sinon, renvoie une chaine représentant la date, formaté en utilisant la fonction PHP date() 
	 * et selon le format du paramètre $format.
	 * 
	 * Si le timestamp est invalid ou null (pas un nombre positif) et qu'un format a été demandé, 
	 * retourne la chaine "N/A".
	 * 
	 * __Exemples :__
	 * 
	 * ~~~~~~~~
	 * $this->getLastActivityDate();               // Retourne le timestamp
	 * $this->getLastActivityDate("d/m/Y H:i:s");  // Retourne une chaine "17/08/2013 10:16:27"
	 * ~~~~~~~~
	 * 
	 * @param string $format Une chaine de formatage de date accepté par la fonction PHP date()
	 * 
	 * @return string|int $creation_ts
	 */
	public function getLastActivityDate($format=null)
	{
		return $this->getFormatedDate($this->last_activity_ts, $format);
	}
	
	/**
	 * Retourne la date de dernière connexion du compte, éventuellement formatée
	 * 
	 * Si le paramètre $format est null ou faut, renvoi la date sous forme d'un timestamp (integer).
	 * Sinon, renvoie une chaine représentant la date, formaté en utilisant la fonction PHP date() 
	 * et selon le format du paramètre $format.
	 * 
	 * Si le timestamp est invalid ou null (pas un nombre positif) et qu'un format a été demandé, 
	 * retourne la chaine "N/A".
	 * 
	 * __Exemples :__
	 * 
	 * ~~~~~~~~
	 * $this->getLastLoginDate();               // Retourne le timestamp
	 * $this->getLastLoginDate("d/m/Y H:i:s");  // Retourne une chaine "17/08/2013 10:16:27"
	 * ~~~~~~~~
	 * 
	 * @param string $format Une chaine de formatage de date accepté par la fonction PHP date()
	 * 
	 * @return string|int $creation_ts
	 */
	public function getLastLoginDate($format=null)
	{
		return $this->getFormatedDate($this->last_login_ts, $format);
	}
	
	/**
	 * Retourne une date stockée sous forme de timestampt, en lui appliquant un éventuelle formatage
	 * 
	 * Si le paramètre $format est null ou faut, renvoi la date sous forme d'un timestamp (integer).
	 * Sinon, renvoie une chaine représentant la date, formaté en utilisant la fonction PHP date() 
	 * et selon le format du paramètre $format.
	 * 
	 * Si le timestamp est invalid ou null (pas un nombre positif) et qu'un format a été demandé, 
	 * retourne la chaine "N/A".
	 * 
	 * __Exemples :__
	 * 
	 * ~~~~~~~~
	 * $this->getFormatedDate();               // Retourne le timestamp
	 * $this->getFormatedDate("d/m/Y H:i:s");  // Retourne une chaine "17/08/2013 10:16:27"
	 * ~~~~~~~~
	 * 
	 * @param int $ts Le timestamp représentant la date.
	 * @param string $format Une chaine de formatage de date accepté par la fonction PHP date()
	 * 
	 * @return string|int $creation_ts
	 */
	protected function getFormatedDate($ts, $format=null)
	{
		if (!$format) {
			return $ts;
		} else {
			if ($ts > 0) {
				return date($format, $ts);
			} else {
				return "N/A";
			}
		}
	}

	/**
	 * @return array $roles
	 */
	public function getRoles()
	{
		return $this->roles;
	}
	
	/**
	 * @return boolean $active
	 */
	public function isActive()
	{
		return $this->active?true:false;
	}

	/**
	 * @param integer $id
	 */
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
	public function setPassword($password, $bcrypt = null)
	{
		if ($bcrypt) {
			$this->password = $bcrypt->create($password);
		} else {
			$this->password = $password;
		}
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

	/**
	 * @param array $roles
	 */
	public function setRoles($roles)
	{
		if (is_array($roles) || ($roles === null)) {
			$this->roles = $roles;
		}
	}
	
	public function exchangeArray($data, $getPassword = true)
	{
		$this->id = (array_key_exists('id', $data)) ? $data['id'] : null;
		$this->email = (array_key_exists('email', $data)) ? $data['email'] : null;
		if ($getPassword) {
			$this->password = (array_key_exists('password', $data)) ? $data['password'] : null;
		}
		$this->firstname = (array_key_exists('firstname', $data)) ? $data['firstname'] : null;
		$this->lastname = (array_key_exists('lastname', $data)) ? $data['lastname'] : null;
		$this->active = (array_key_exists('active', $data)) ? $data['active'] : null;
		$this->roles = (array_key_exists('roles', $data)) ? $data['roles'] : null;
		$this->creation_ts = (array_key_exists('creation_ts', $data)) ? $data['creation_ts'] : $this->creation_ts;
		$this->modification_ts = (array_key_exists('modification_ts', $data)) ? $data['modification_ts'] : $this->modification_ts;
		$this->last_activity_ts = (array_key_exists('last_activity_ts', $data)) ? $data['last_activity_ts'] : $this->last_activity_ts;
		$this->last_login_ts = (array_key_exists('last_login_ts', $data)) ? $data['last_login_ts'] : $this->last_login_ts;
	}
	
	public function getArrayCopy()
	{
		return array(
			'id' => $this->id,
			'email' => $this->email,
			'firstname' => $this->firstname,
			'lastname' => $this->lastname,
			'active' => $this->isActive(),
			'roles' => $this->roles
		);
	}
}