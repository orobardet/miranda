<?php
namespace Acl\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;

class RolesManager
{

	protected $rolesTable;

	public function __construct(TableGateway $rolesTable)
	{
		$this->rolesTable = $rolesTable;
	}

	public function getRoles()
	{
		$groupsResults = $this->$rolesTable->select(function (Select $select)
		{
			$select->order('name');
		});
		
		foreach ($groupsResults as $group) {
			$groupData = new \stdClass();
			$group_id = $group->id;
			$groupData->group_id = $group_id;
			$groupData->descr = $group->descr;
			$groupData->rights = array();
			
			$rightsResults = $this->rightsTable->select(
					function (Select $select) use($group_id)
					{
						$select->where(array(
							'group_id' => $group_id
						));
						$select->order('ord');
					});
			
			foreach ($rightsResults as $right) {
				$rightData = new \stdClass();
				$rightData->id = $right->id;
				$rightData->name = $right->name;
				$rightData->descr = $right->descr;
				
				$groupData->rights[$right->name] = $rightData;
			}
			
			$groupedRoles[] = $groupData;
		}
		
		return $groupedRoles;
	}
}

?>