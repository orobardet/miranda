<?php
namespace User\Authentification\Storage;

use Zend\Authentication\Storage\Session as AuthSessionStorage;
use User\Model\UserTable;
use User\Model\User;
use Zend\Session\SessionManager;

class Session extends AuthSessionStorage
{
	/**
	 * @var UserTable
	 */
	protected $userTable;
	
	/**
	 * @var User
	 */
	protected $user = null;
	
	/**
	 * Sets session storage options and initializes session namespace object
	 *
	 * @param UserTable $userTable
	 * @param  mixed $namespace
	 * @param  mixed $member
	 * @param  SessionManager $manager
	 */
	public function __construct(UserTable $userTable, $namespace = null, $member = null, SessionManager $manager = null)
	{
		$this->userTable = $userTable;
		parent::__construct($namespace, $member, $manager);
	}
	
	/**
	 * Surcharge de pour permettre de lire les données utilisateurs dans la BDD,
	 * pour qu'elles soient plus complète et plus fraîches.
	 * 
	 * Lors de la connexion, l'adaptateur personnalisé (User\Authentification\Adapter\DbCallbackCheckAdapter) 
	 * a renvoyé comme identité un objet stdClass PHP contenant uniquement l'ID BDD et l'email de l'utilisateur 
	 * connecté. C'est ce qui est sauvegardé dans la session et récupérer par lors de l'appel de read().
	 * 
	 * On surchage read() pour construire une objet User complet à partir de ce qu'il y a en session, et le 
	 * retourner à la place.
	 *
	 * @return mixed
	 */
	public function read()
	{
		return $this->getUser($this->session->{$this->member});
	}
	
	protected function getUser($identity)
	{
		if (!$this->user) {
			// Mise à jour de la date de dernière activité de l'utilisateur
			$this->userTable->updateLastActivity($this->userTable->getUser($identity->id));
			// Récupération de l'utilisateur
			$this->user = $this->userTable->getUser($identity->id);
		}
		
		return $this->user;
	}
}

?>