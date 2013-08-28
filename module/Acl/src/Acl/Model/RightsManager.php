<?php
namespace Acl\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;

class RightsManager
{

	protected $rightsTable;

	protected $rightsGroupsTable;

	public function __construct(TableGateway $rightsTable, TableGateway $rightsGroupsTable)
	{
		$this->rightsTable = $rightsTable;
		$this->rightsGroupsTable = $rightsGroupsTable;
	}

	public function getGroupedRights()
	{
		$groupedRoles = array();
		
		$groupsResults = $this->rightsGroupsTable->select(function (Select $select)
		{
			$select->order('ord');
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