<?php
namespace Acl\Model;

use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;
use Zend\Permissions\Acl\Role\GenericRole as ZendRole;

class AclManager
{

	const ACL_CACHE_NAME = 'acl';

	const TABLE_ROLES = 'roles';

	const TABLE_RIGHTS = 'rights';

	const TABLE_ROLES_RIGHTS = 'roles_rights';

	protected $dbAdapter;

	protected $tableNames;

	/**
	 * @var \Zend\Cache\Storage\Adapter\AbstractAdapter
	 */
	protected $cache;

	public function __construct(DbAdapter $dbAdapter, $tableNames, $cache = null)
	{
		$this->dbAdapter = $dbAdapter;
		$this->tableNames = array_merge(
				array(
					self::TABLE_ROLES => self::TABLE_ROLES,
					self::TABLE_RIGHTS => self::TABLE_RIGHTS,
					self::TABLE_ROLES_RIGHTS => self::TABLE_ROLES_RIGHTS
				), $tableNames);
		$this->cache = $cache;
	}

	public function aclNeedsUpdate()
	{
		if ($this->cache && $this->cache->hasItem(self::ACL_CACHE_NAME)) {
			$this->cache->removeItem(self::ACL_CACHE_NAME);
		}
	}
	
	public function getAcl()
	{
		$acl = null;
		
		if ($this->cache && $this->cache->hasItem(self::ACL_CACHE_NAME)) {
			$acl = unserialize($this->cache->getItem(self::ACL_CACHE_NAME));
		}
		
		if (!$acl || !$acl instanceof Acl) {
			// Récupération de tous les noms des rôles et les noms des droits qu'ils autorisent
			$roles = $this->getRolesAndRights();
			
			$acl = new Acl();
			// Construction des ACL de l'application
			foreach ($roles as $role => $resources) {
				// On ajoute d'abord les rôles
				$acl->addRole(new ZendRole($role));
				
				// On ajoute une ressource, si elle n'est pas déjà déclarée
				foreach ($resources as $resource) {
					if (!$acl->hasResource($resource))
						$acl->addResource(new Resource($resource));
				}
				
				// On autorise le rôle sur la ressource
				foreach ($resources as $resource) {
					$acl->allow($role, $resource);
				}
			}
			
			if ($this->cache) {
				$this->cache->setItem(self::ACL_CACHE_NAME, serialize($acl));
			}
		}
		
		return $acl;
	}

	public function getRolesAndRights()
	{
		// Récupération des droits avec les rôles qui les utilisent (avec une union pour les rôles sans droits
		$sqlQuery = <<<EOSQL
(
SELECT rl.name AS role, ri.name AS 'right'
FROM {$this->tableNames[self::TABLE_RIGHTS]} AS ri
LEFT JOIN {$this->tableNames[self::TABLE_ROLES_RIGHTS]} AS r_r 
	ON (r_r.right_id = ri.id)
LEFT JOIN {$this->tableNames[self::TABLE_ROLES]} AS rl 
	ON (rl.id = r_r.role_id)
) UNION (
SELECT DISTINCT rl.name AS role, NULL AS 'right'
FROM {$this->tableNames[self::TABLE_ROLES]} AS rl
LEFT JOIN {$this->tableNames[self::TABLE_ROLES_RIGHTS]} AS r_r
	ON (r_r.role_id = rl.id)
LEFT JOIN {$this->tableNames[self::TABLE_RIGHTS]} AS ri
	ON (ri.id = r_r.right_id)
WHERE ri.id IS NULL
)	
EOSQL;
		$dbResults = $this->dbAdapter->query($sqlQuery, DbAdapter::QUERY_MODE_EXECUTE);
		$roles = array();
		
		foreach ($dbResults as $result) {
			if ($result['right']) {
				$roles[$result['role']][] = $result['right'];
			} else {
				$roles[$result['role']] = array();
			}
		}
		
		return $roles;
	}
}
