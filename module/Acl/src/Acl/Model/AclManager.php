<?php
namespace Acl\Model;

use Zend\Db\Adapter\Adapter as DbAdapter;

class AclManager
{
	const TABLE_ROLES = 'roles';
	const TABLE_RIGHTS = 'rights';
	const TABLE_ROLES_RIGHTS = 'roles_rights';

	protected $dbAdapter;

	protected $tableNames;

	public function __construct(DbAdapter $dbAdapter, $tableNames)
	{
		$this->dbAdapter = $dbAdapter;
		$this->tableNames = array_merge(
				array(
					self::TABLE_ROLES => self::TABLE_ROLES,
					self::TABLE_RIGHTS => self::TABLE_RIGHTS,
					self::TABLE_ROLES_RIGHTS => self::TABLE_ROLES_RIGHTS
				), $tableNames);
	}

	public function getRolesAndRights()
	{
		$sqlQuery = <<<EOSQL
SELECT rl.name AS role, ri.name AS 'right'
FROM {$this->tableNames[self::TABLE_RIGHTS]} AS ri
LEFT JOIN {$this->tableNames[self::TABLE_ROLES_RIGHTS]} AS r_r 
	ON ( r_r.right_id = ri.id )
LEFT JOIN {$this->tableNames[self::TABLE_ROLES]} AS rl 
	ON ( rl.id = r_r.role_id )		
EOSQL;

		$dbResults = $this->dbAdapter->query($sqlQuery, DbAdapter::QUERY_MODE_EXECUTE);
		$roles = array();
		
		foreach ($dbResults as $result) {
			$roles[$result['role']][] = $result['right'];
		}
		
		return $roles;
	}
}
