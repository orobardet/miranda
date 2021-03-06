<?php
namespace User\Model;

use Application\Model\ObjectModelBase;
use Application\Model\FormatDataTrait;

class User extends ObjectModelBase
{
	use FormatDataTrait;

	/**
	 * ID en BDD de l'utilisateur
	 * 
	 * @var integer
	 */
	protected $id;

	/**
	 * Email (qui sert de login)
	 * 
	 * @var string
	 */
	protected $email;

	/**
	 * Chaine cryptée (par bcrypt) représentant le mot de passe
	 * 
	 * @var string
	 */
	protected $password;

	/**
	 * Prénom
	 * 
	 * @var string
	 */
	protected $firstname;

	/**
	 * Nom
	 * 
	 * @var string
	 */
	protected $lastname;

	/**
	 * Indique si le compte est activé on non
	 * 
	 * @var boolean
	 */
	protected $active;

	/**
	 * Timestamp de la date de création du compte
	 * 
	 * @var integer
	 */
	protected $creation_ts;

	/**
	 * Timestamp de la date de dernière modification du compte
	 * 
	 * @var integer
	 */
	protected $modification_ts;

	/**
	 * Timestamp de la date de dernière activité de l'utilisateur
	 * 
	 * @var integer
	 */
	protected $last_activity_ts;

	/**
	 * Timestamp de la date de dernière connexion de l'utilisateur
	 * 
	 * @var integer
	 */
	protected $last_login_ts;

	/**
	 * Tableau d'ID des rôles affectés à l'utilisateur
	 * 
	 * @var array
	 */
	protected $roles;

	/**
	 * Token de récupération du mot de passe
	 * 
	 * @var string
	 */
	protected $password_token;
	
	/**
	 * Timestamp de la date de création du token de récupération du mot de passe
	 * 
	 * @var integer
	 */
	protected $password_token_ts;
	
	/**
	 * Token de création de compte
	 * 
	 * @var string
	 */
	protected $registration_token;
	
	/**
	 * Timestamp de la date de création du token de création de compte
	 * 
	 * @var integer
	 */
	protected $registration_token_ts;
	
	/**
	 *
	 * @return integer $id
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 *
	 * @return string $email
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 *
	 * @return string $email
	 */
	public function getIdentity()
	{
		return $this->email;
	}

	/**
	 * Retourne le nom d'affichage : "prenom nom"
	 *
     * Si $withLastName = false et qu'il y a un prénom, retourne "prenom"
     * Sinon s'il y a un prénom et un nom, retourne "prenom nom"
     * Sinon s'il y a un nom retourne "nom"
     * En dernier recours retourne l'adresse email
     *
     * @param boolean $withLastName Si false, le prenom est retourné sans le nom (dans le cas où il y a un prénom)
     *
	 * @return string Nom d'affichage
	 */
	public function getDisplayName($withLastName = true)
	{
		if (!$withLastName && (trim($this->firstname) != '')) {
			return $this->firstname;
		} else if ((trim($this->firstname) != '') && (trim($this->lastname) != '')) {
			return $this->firstname . ' ' . $this->lastname;
		} else if ((trim($this->lastname) != '')) {
			return $this->lastname;
		} else {
			return $this->email;
		}
	}

	/**
	 *
	 * @return string $firstname
	 */
	public function getFirstname()
	{
		return $this->firstname;
	}

	/**
	 *
	 * @return string $lastname
	 */
	public function getLastname()
	{
		return $this->lastname;
	}

	/**
	 *
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
	 * $this->getCreationDate(); // Retourne le timestamp
	 * $this->getCreationDate("d/m/Y H:i:s"); // Retourne une chaine "17/08/2013 10:16:27"
	 * ~~~~~~~~
	 *
	 * @param string $format Une chaine de formatage de date accepté par la fonction PHP date()
	 *       
	 * @return string|int
	 */
	public function getCreationDate($format = null)
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
	 * $this->getLastModificationDate(); // Retourne le timestamp
	 * $this->getLastModificationDate("d/m/Y H:i:s"); // Retourne une chaine "17/08/2013 10:16:27"
	 * ~~~~~~~~
	 *
	 * @param string $format Une chaine de formatage de date accepté par la fonction PHP date()
	 *       
	 * @return string|int
	 */
	public function getLastModificationDate($format = null)
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
	 * $this->getLastActivityDate(); // Retourne le timestamp
	 * $this->getLastActivityDate("d/m/Y H:i:s"); // Retourne une chaine "17/08/2013 10:16:27"
	 * ~~~~~~~~
	 *
	 * @param string $format Une chaine de formatage de date accepté par la fonction PHP date()
	 *       
	 * @return string|int
	 */
	public function getLastActivityDate($format = null)
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
	 * $this->getLastLoginDate(); // Retourne le timestamp
	 * $this->getLastLoginDate("d/m/Y H:i:s"); // Retourne une chaine "17/08/2013 10:16:27"
	 * ~~~~~~~~
	 *
	 * @param string $format Une chaine de formatage de date accepté par la fonction PHP date()
	 *       
	 * @return string|int
	 */
	public function getLastLoginDate($format = null)
	{
		return $this->getFormatedDate($this->last_login_ts, $format);
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
	 * $this->getPasswordTokenDate(); // Retourne le timestamp
	 * $this->getPasswordTokenDate("d/m/Y H:i:s"); // Retourne une chaine "17/08/2013 10:16:27"
	 * ~~~~~~~~
	 *
	 * @param string $format Une chaine de formatage de date accepté par la fonction PHP date()
	 *       
	 * @return string|int
	 */
	public function getPasswordTokenDate($format = null)
	{
		return $this->getFormatedDate($this->password_token_ts, $format);
	}

	public function getPasswordToken()
	{
		return $this->password_token;
	}
	
	public function createPasswordToken()
	{
		$this->password_token = sha1($this->getEmail().uniqid());
		$this->password_token_ts = time();
	}
	
	public function resetPasswordToken() 
	{
		$this->password_token = null;
		$this->password_token_ts = null;
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
	 * $this->getRegistrationTokenDate(); // Retourne le timestamp
	 * $this->getRegistrationTokenDate("d/m/Y H:i:s"); // Retourne une chaine "17/08/2013 10:16:27"
	 * ~~~~~~~~
	 *
	 * @param string $format Une chaine de formatage de date accepté par la fonction PHP date()
	 *
	 * @return string|int
	 */
	public function getRegistrationTokenDate($format = null)
	{
		return $this->getFormatedDate($this->registration_token_ts, $format);
	}
	
	public function getRegistrationToken()
	{
		return $this->registration_token;
	}
	
	public function createRegistrationToken()
	{
		$this->registration_token = sha1($this->getEmail().uniqid());
		$this->registration_token_ts = time();
	}
	
	public function resetRegistrationToken()
	{
		$this->registration_token = null;
		$this->registration_token_ts = null;
	}
	
	
	/**
	 *
	 * @return array $roles
	 */
	public function getRoles()
	{
		return $this->roles;
	}

	/**
	 *
	 * @return boolean $active
	 */
	public function isActive()
	{
		return $this->active ? true : false;
	}

	/**
	 *
	 * @param integer $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 *
	 * @param string $email
	 */
	public function setEmail($email)
	{
		$this->email = $email;
	}

	/**
	 *
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
	 *
	 * @param string $firstname
	 */
	public function setFirstname($firstname)
	{
		$this->firstname = $firstname;
	}

	/**
	 *
	 * @param string $lastname
	 */
	public function setLastname($lastname)
	{
		$this->lastname = $lastname;
	}

	/**
	 *
	 * @param boolean $active
	 */
	public function setActive($active)
	{
		$this->active = $active ? 1 : 0;
	}

	/**
	 *
	 * @param array $roles
	 */
	public function setRoles($roles)
	{
		if (is_array($roles) || ($roles === null)) {
			$this->roles = $roles;
		}
	}

	/**
	 * Verifie si un mot de passe donnée (non crypté) correspond à
	 * celui de l'utilisateur
	 *
	 * @param string $password
	 * @param \Zend\Crypt\Password\Bcrypt $bcrypt
	 */
	public function verifyPassword($password, $bcrypt)
	{
		return $bcrypt->verify($password, $this->password);
	}

	public function exchangeArray($data, $getPassword = true)
	{
		$this->id = (array_key_exists('id', $data)) ? $data['id'] : $this->id;
		$this->email = (array_key_exists('email', $data)) ? $data['email'] : $this->email;
		if ($getPassword) {
			$this->password = (array_key_exists('password', $data)) ? $data['password'] : $this->password;
		}
		$this->firstname = (array_key_exists('firstname', $data)) ? $data['firstname'] : $this->firstname;
		$this->lastname = (array_key_exists('lastname', $data)) ? $data['lastname'] : $this->lastname;
		$this->active = (array_key_exists('active', $data)) ? $data['active'] : $this->active;
		$this->roles = (array_key_exists('roles', $data)) ? $data['roles'] : $this->roles;
		$this->creation_ts = (array_key_exists('creation_ts', $data)) ? $data['creation_ts'] : $this->creation_ts;
		$this->modification_ts = (array_key_exists('modification_ts', $data)) ? $data['modification_ts'] : $this->modification_ts;
		$this->last_activity_ts = (array_key_exists('last_activity_ts', $data)) ? $data['last_activity_ts'] : $this->last_activity_ts;
		$this->last_login_ts = (array_key_exists('last_login_ts', $data)) ? $data['last_login_ts'] : $this->last_login_ts;
		$this->password_token = (array_key_exists('password_token', $data)) ? $data['password_token'] : $this->password_token;
		$this->password_token_ts = (array_key_exists('password_token_ts', $data)) ? $data['password_token_ts'] : $this->password_token_ts;
		$this->registration_token = (array_key_exists('registration_token', $data)) ? $data['registration_token'] : $this->registration_token;
		$this->registration_token_ts = (array_key_exists('registration_token_ts', $data)) ? $data['registration_token_ts'] : $this->registration_token_ts;
	}

	public function getArrayCopy()
	{
		return array(
			'id' => $this->id,
			'email' => $this->email,
			'firstname' => $this->firstname,
			'lastname' => $this->lastname,
			'active' => $this->isActive(),
			'roles' => $this->roles,
			'password_token' => $this->password_token,
			'password_token_ts' => $this->password_token_ts,
			'registration_token' => $this->registration_token,
			'registration_token_ts' => $this->registration_token_ts
		);
	}
}