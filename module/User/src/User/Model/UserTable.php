<?php
namespace User\Model;

use Zend\Db\TableGateway\TableGateway;

class UserTable
{
	protected $tableGateway;

	public function __construct(TableGateway $tableGateway)
	{
		$this->tableGateway = $tableGateway;
	}

	public function fetchAll()
	{
		$resultSet = $this->tableGateway->select();
		return $resultSet;
	}

	public function getUser($id)
	{
		$id  = (int) $id;
		$rowset = $this->tableGateway->select(array('id' => $id));
		$row = $rowset->current();
		if (!$row) {
			throw new \Exception("Could not find user $id");
		}
		return $row;
	}

	public function saveUser(User $user, $savePassword = false)
	{
		$data = array(
				'email' => $user->getEmail(),
				'firstname'  => $user->getFirstname(),
				'lastname'  => $user->getLastname(),
				'active'  => $user->isActive()?1:0,
		);
		
		if ($savePassword) {
			$data['password'] = $user->getPassword();
		}

		$id = (int)$user->id;
		if (!$id) {
			$this->tableGateway->insert($data);
		} else {
			if ($this->getUser($id)) {
				$this->tableGateway->update($data, array('id' => $id));
			} else {
				throw new \Exception("User id $id does not exist");
			}
		}
	}

	public function deleteUser($id)
	{
		$this->tableGateway->delete(array('id' => $id));
	}
}