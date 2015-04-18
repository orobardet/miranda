<?php
namespace Acl\Model;

use Zend\Db\TableGateway\TableGateway;

class RoleTable extends Role
{

	protected $tableGateway;

	protected $rightsTableGateway;

	protected $usersRolesTableGateway;

	public function __construct(TableGateway $tableGateway, TableGateway $rightsTableGateway = null, TableGateway $usersRolesTableGateway = null)
	{
		$this->tableGateway = $tableGateway;
		$this->rightsTableGateway = $rightsTableGateway;
		$this->usersRolesTableGateway = $usersRolesTableGateway;
	}

	public function select($where)
	{
		return $this->tableGateway->select($where);
	}

	public function fetchAll($where = null, $order = null)
	{
		return $this->tableGateway->select(
				function ($select) use($where, $order)
				{
					if ($where) {
						$select->where($where);
					}
					if ($order) {
						$select->order($order);
					}
				});
	}

	public function getRole($id)
	{
		$id = (int)$id;
		$rowset = $this->tableGateway->select(array(
			'id' => $id
		));
		$row = $rowset->current();
		if (!$row) {
			throw new \Exception("Could not find role $id");
		}
		
		$rights = array();
		if ($this->rightsTableGateway) {
			$rightset = $this->rightsTableGateway->select(array(
				'role_id' => $id
			));
			foreach ($rightset as $right) {
				$rights[] = $right->right_id;
			}
		}
		$row->setRights($rights);
		
		return $row;
	}

	public function getRoleByName($name, $exceptionIfNotFound = true)
	{
		$rowset = $this->tableGateway->select(function ($select) use($name)
		{
			$select->where->like('name', (string)$name);
		});
		$row = $rowset->current();
		if (!$row) {
			if ($exceptionIfNotFound) {
				throw new \Exception("Could not find role $name");
			} else {
				return false;
			}
		}
		
		$rights = array();
		if ($this->rightsTableGateway) {
			$rightset = $this->rightsTableGateway->select(array(
				'role_id' => $row->getId()
			));
			foreach ($rightset as $right) {
				$rights[] = $right->right_id;
			}
		}
		$row->setRights($rights);
		
		return $row;
	}

	public function saveRole(Role $role)
	{
		$data = array(
			'name' => $role->getName(),
			'descr' => $role->getDescr()
		);
		
		$id = (int)$role->getId();
		if (!$id) {
			$this->tableGateway->insert($data);
			$id = $this->tableGateway->getLastInsertValue();
		} else {
			if ($this->getRole($id)) {
				$this->tableGateway->update($data, array(
					'id' => $id
				));
			} else {
				throw new \Exception("Role id $id does not exist");
			}
		}
		
		// Sauvegarde des droits du rôle
		if ($this->rightsTableGateway) {
			$this->rightsTableGateway->delete(array(
				'role_id' => $id
			));
			$rights = $role->getRights();
			foreach ($rights as $right_id) {
				$this->rightsTableGateway->insert(array(
					'role_id' => $id,
					'right_id' => $right_id
				));
			}
		}
	}

	public function deleteRole($id)
	{
		if (!$this->rightsTableGateway) {
			throw new \Exception("Can't delete a role, no roles_rights TableGateway given.");
		}
		if (!$this->usersRolesTableGateway) {
			throw new \Exception("Can't delete a role, no user_roles TableGateway given.");
		}
		
		// Suppression des droits du rôle
		$this->rightsTableGateway->delete(array(
			'role_id' => $id
		));
		
		// Suppression de l'affectation du rôle aux utilisateurs
		$this->usersRolesTableGateway->delete(array(
			'role_id' => $id
		));
		
		// Puis suppression du rôle
		$this->tableGateway->delete(array(
			'id' => $id
		));
	}
}