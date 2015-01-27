<?php
namespace User\Model;

use Zend\Db\TableGateway\TableGateway;

class UserTable extends User
{

	protected $tableGateway;

	protected $rolesTableGateway;

	public function __construct(TableGateway $tableGateway, TableGateway $rolesTableGateway = null)
	{
		$this->tableGateway = $tableGateway;
		$this->rolesTableGateway = $rolesTableGateway;
	}

	/**
	 * Retoune tous les utilisateurs existants
	 *
	 * @return User[] Liste des utilisateurs (sous forme d'un iterable)
	 */
	public function fetchAll($type = null)
	{
		$where = [];
		
		switch ($type) {
			case 'disabled':
				$where['active'] = 0;
				break;
			case 'enabled':
				$where['active'] = 1;
				break;
			case 'all':
			default:
				$where = [];
				break;
		}
		// TODO: Lors de la mise en place de la suppression logique, fitrer le select pour ne pas utiliser les comptes supprimés logiquement
		return $this->tableGateway->select($where);
	}

	/**
	 * Retourne tous les utilisateurs appartenant possedant un ID role donné
	 *
	 * @param integer $roleId ID du rôle
	 *       
	 * @return \User\Model\User[] Liste des utilisateurs (sous forme d'un iterable)
	 */
	public function fetchByRole($roleId)
	{
		$users = array();
		$roleSet = $this->rolesTableGateway->select(array(
			'role_id' => $roleId
		));
		foreach ($roleSet as $role) {
			$users[] = $this->getUser($role->user_id);
		}
		return $users;
	}

	/**
	 * Cherche parmi les utilisateurs
	 *
	 * Retourne tous les utilisateurs dont $q est trouvé au moins un des éléments suivants :
	 * - email
	 * - nom
	 * - prenom
	 * - prenom nom
	 * - nom prenom
	 *
	 * @param string $q
	 *
	 * @return \User\Model\User[] Liste des utilisateurs (sous forme d'un iterable)
	 */
	public function searchUsers($q)
	{
		$q = '%' . preg_replace('/[\s]+/', ' ', $q) . '%';
		return $this->tableGateway->select(
				function ($select) use($q)
				{
					$select->where->like('email', $q)->or->like('firstname', $q)->or->like('lastname', $q)->or->expression(
							"CONCAT(firstname,' ',lastname) LIKE ?", $q)->or->expression("CONCAT(lastname,' ',firstname) LIKE ?", $q);
				});
	}

	protected function _findUser(array $filterData, $exceptionIfNotFound = true, $exceptionMessage = null)
	{
		$rowset = $this->tableGateway->select($filterData);
		
		$row = $rowset->current();
		if (!$row) {
			if ($exceptionIfNotFound) {
				if (trim((string)$exceptionMessage) == '') {
					$exceptionMessage = "Could not find user";
				}
				throw new \Exception($exceptionMessage);
			} else {
				return false;
			}
		}
		
		$roles = array();
		if ($this->rolesTableGateway) {
			$roleset = $this->rolesTableGateway->select(array(
				'user_id' => $row->getId()
			));
			foreach ($roleset as $role) {
				$roles[] = $role->role_id;
			}
		}
		$row->setRoles($roles);
		
		return $row;
	}

	/**
	 *
	 * @param integer $id
	 *
	 * @throws \Exception
	 *
	 * @return \User\Model\User
	 */
	public function getUser($id, $exceptionIfNotFound = true)
	{
		$id = (int)$id;
		return $this->_findUser([
			'id' => $id
		], $exceptionIfNotFound, "Could not find user $id");
	}

	/**
	 *
	 * @param string $email
	 *
	 * @throws \Exception
	 *
	 * @return \User\Model\User
	 */
	public function getUserByEmail($email, $exceptionIfNotFound = true)
	{
		return $this->_findUser([
			'email' => $email
		], $exceptionIfNotFound, "Could not find user with email '$email'");
	}

	/**
	 *
	 * @param string $token
	 *
	 * @throws \Exception
	 *
	 * @return \User\Model\User
	 */
	public function getUserByPasswordToken($token, $exceptionIfNotFound = true)
	{
		return $this->_findUser([
			'password_token' => $token
		], $exceptionIfNotFound, "Could not find user with password token '$token'");
	}

	/**
	 *
	 * @param \User\Model\User|integer $user
	 */
	public function enableUser($user)
	{
		if ($user instanceof User) {
			$user = (int)$user->getId();
		}
		
		if (!is_integer($user) || ((int)$user <= 0)) {
			throw new \Exception("Invalid user id");
		}
		
		return (boolean)$this->tableGateway->update([
			'active' => 1
		], [
			'id' => $user
		]);
	}

	/**
	 *
	 * @param \User\Model\User|integer $user
	 */
	public function disableUser($user)
	{
		if ($user instanceof User) {
			$user = (int)$user->getId();
		}
		
		if (!is_integer($user) || ((int)$user <= 0)) {
			throw new \Exception("Invalid user id");
		}
		
		return (boolean)$this->tableGateway->update([
			'active' => 0
		], [
			'id' => $user
		]);
	}

	public function saveUser(User $user, $savePassword = false)
	{
		$data = array(
			'email' => $user->getEmail(),
			'firstname' => $user->getFirstname(),
			'lastname' => $user->getLastname(),
			'active' => $user->isActive() ? 1 : 0,
			'modification_ts' => time(),
			'password_token' => $user->getPasswordToken(),
			'password_token_ts' => $user->getPasswordTokenDate()
		);
		
		// Pas de date de création, on la défini à la date courane
		if (!$user->getCreationDate()) {
			$data['creation_ts'] = time();
		}
		
		// S'il a été explicitement demandé de sauvegarder le mot passe, on le fait
		if ($savePassword) {
			$data['password'] = $user->getPassword();
		}
		
		$id = (int)$user->getId();
		if (!$id) {
			$this->tableGateway->insert($data);
			$id = $this->tableGateway->getLastInsertValue();
		} else {
			if ($this->getUser($id)) {
				$this->tableGateway->update($data, array(
					'id' => $id
				));
			} else {
				throw new \Exception("User id $id does not exist");
			}
		}
		
		// Sauvegarde des droits du rôle
		if ($this->rolesTableGateway) {
			$this->rolesTableGateway->delete(array(
				'user_id' => $id
			));
			$roles = $user->getRoles();
			foreach ($roles as $role_id) {
				$this->rolesTableGateway->insert(array(
					'user_id' => $id,
					'role_id' => $role_id
				));
			}
		}
	}

	public function updateLastActivity(User $user, $lastLogin = false)
	{
		$id = (int)$user->getId();
		if (!$id) {
			throw new \Exception("User id $id does not exist");
		}
		
		$data = array(
			'last_activity_ts' => time()
		);
		
		if ($lastLogin) {
			$data['last_login_ts'] = time();
		}
		
		$this->tableGateway->update($data, array(
			'id' => $id
		));
	}

	public function deleteUser($id)
	{
		if (!$this->rolesTableGateway) {
			throw new \Exception("Can't delete an user, no rights TableGateway given.");
		}
		// Suppression des rôles de l'utilisateur
		$this->rolesTableGateway->delete(array(
			'user_id' => $id
		));
		
		// TODO: Tenter un delete, et s'il échoue cause clé étrangère, réaliser une suppression logique du compte
		$this->tableGateway->delete(array(
			'id' => $id
		));
		
		// Suppression logique:
		// Utiliser une colonne dédiée (bool) indiquant que le compte est supprimer logiquement.
		// Modifier le champ email en del_ID_EMAIL où ID et l'id du compte, et EMAIL l'email actuel (pour éviter les problèmes d'unicité)
		// Supprimer tous les rôles affecté au compte mis en suppression logique
		// Ne pas lister les comptes supprimés logiquement dans le fetchAll et autres
	}
}