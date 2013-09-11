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
	public function fetchAll()
	{
		// TODO: Lors de la mise en place de la suppression logique, fitrer le select pour ne pas utiliser les comptes supprimés logiquement
		$resultSet = $this->tableGateway->select();
		return $resultSet;
	}
	
	/**
	 * Retourn tous les utilisateurs appartenant possedant un ID role donné
	 * 
	 * @param integer $roleId ID du rôle
	 * @return User[] Liste des utilisateurs (sous forme d'un iterable)
	 */
	public function fetchByRole($roleId)
	{
		$users = array();
		$roleSet = $this->rolesTableGateway->select(array('role_id' => $roleId));
		foreach ($roleSet as $role) { 
			$users[] = $this->getUser($role->user_id);
		}
		return $users;
	}
	
	public function getUser($id)
	{
		$id  = (int) $id;
		$rowset = $this->tableGateway->select(array('id' => $id));
		$row = $rowset->current();
		if (!$row) {
			throw new \Exception("Could not find user $id");
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

	public function getUserByEmail($email)
	{
		$rowset = $this->tableGateway->select(array('email' => $email));
		$row = $rowset->current();
		if (!$row) {
			throw new \Exception("Could not find user with email '$email'");
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
	
	public function saveUser(User $user, $savePassword = false)
	{
		$data = array(
				'email' => $user->getEmail(),
				'firstname'  => $user->getFirstname(),
				'lastname'  => $user->getLastname(),
				'active'  => $user->isActive()?1:0,
				'modification_ts' => time(),
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
				$this->tableGateway->update($data, array('id' => $id));
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
			'last_activity_ts' => time(),
		);
		
		if ($lastLogin) {
			$data['last_login_ts'] = time();
		}
		
		if ($this->getUser($id)) {
			$this->tableGateway->update($data, array('id' => $id));
		} else {
			throw new \Exception("User id $id does not exist");
		}
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
		$this->tableGateway->delete(array('id' => $id));
		
		// Suppression logique:
		// Utiliser une colonne dédiée (bool) indiquant que le compte est supprimer logiquement.
		// Modifier le champ email en del_ID_EMAIL où ID et l'id du compte, et EMAIL l'email actuel (pour éviter les problèmes d'unicité)
		// Supprimer tous les rôles affecté au compte mis en suppression logique 
		// Ne pas lister les comptes supprimés logiquement dans le fetchAll et autres
	}
}