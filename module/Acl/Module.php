<?php
namespace Acl;

use Acl\Model\AclManager;
use Acl\Model\RightsManager;
use Acl\Model\RoleTable;
use Acl\Model\RolesManager;
use Application\ConfigAwareInterface;
use Zend\EventManager\Event;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\Feature;
use Zend\Db\TableGateway\TableGateway;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\Mvc\MvcEvent;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;
use Zend\Permissions\Acl\Role\GenericRole as Role;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface, ServiceProviderInterface
{

	protected $config;

	public function onBootstrap(MvcEvent $e)
	{
		$this->config = $e->getApplication()->getServiceManager()->get('Miranda\Service\Config');
		$acl = $e->getApplication()->getServiceManager()->get('Miranda\Service\Acl');
		$e->getViewModel()->acl = $acl;
		
		$e->getApplication()->getEventManager()->attach('route', array(
			$this,
			'checkAcl'
		));
	}

	public function checkAcl(Event $e)
	{
		
		$acl = $e->getApplication()->getServiceManager()->get('Miranda\Service\Acl');
		// TODO: Check ACL
		// Regarder s'il existe dans le contrôleur demandé une méthode actionAclAllowed (action a remplacer par l'action demandée).
		// Si non, accès refusée (sécuritée max)
		// Si oui, appeller la methode qui peut retourner true ou false selon que l'utilisateur à le droit d'accès ou non,
		// ou un array qui contient la liste des droits tester (plusieurs possibles, considérés comme un AND)
		
		
// 		if ($acl->hasResource($route) && !$acl->isAllowed($userRole, $route)) {
// 			$response = $e->getResponse();
// 			$response->getHeaders()->addHeaderLine('Location', $e->getRequest()->getBaseUrl() . '/404');
// 			$response->setStatusCode(303);
// 		}
	}

	public function getConfig()
	{
		return include __DIR__ . '/config/module.config.php';
	}

	public function getAutoloaderConfig()
	{
		return array(
			'Zend\Loader\ClassMapAutoloader' => array(
				__DIR__ . '/autoload_classmap.php'
			),
			'Zend\Loader\StandardAutoloader' => array(
				'namespaces' => array(
					__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__
				)
			)
		);
	}

	public function getServiceConfig()
	{
		return array(
			'factories' => array(
				'Miranda\Service\Acl' => function ($sm)
				{
					// Récupération de tous les noms des rôles et les noms des droits qu'ils autorisent
					$roles = $sm->get('Acl\Model\AclManager')->getRolesAndRights();
					
					$acl = new Acl();
					// Construction des ACL de l'application
					foreach ($roles as $role => $resources) {
						// On ajoute d'abord les rôles
						$acl->addRole(new Role($role));
						
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
					
					// Récupération de l'utilisateur connecté et de ses rôles
					$authService = $sm->get('Miranda\Service\AuthService');
					if ($authService->hasIdentity()) {
						$user = $authService->getIdentity();
						$acl->addRole(new Role($user->getIdentity()), $sm->get('Acl\Model\RolesManager')->getRoleNames($user->getRoles()));
						
						// Le rôle 'Miranda\CurrentUser' est ajouté comme un alias pour accéder au l'utilisateur courant 
						// dans les isAllowed
						$acl->addRole(new Role('Miranda\CurrentUser'), $user->getIdentity());
					} else {
						// Si pas d'utilisateur connecté, on ajoute quand même dans les rôles l'alias
						// d'utilisateur courant mais sans rôles parents, et donc sans aucun droit.
						$acl->addRole(new Role('Miranda\CurrentUser'));
					}
					
					return $acl;
				},
				'Acl\Model\RightsManager' => function ($sm)
				{
					return new RightsManager($sm->get('Acl\TableGateway\Rights'), $sm->get('Acl\TableGateway\RightsGroups'));
				},
				'Acl\Model\RolesManager' => function ($sm)
				{
					return new RolesManager($sm->get('Acl\TableGateway\Roles'));
				},
				'Acl\Model\AclManager' => function ($sm)
				{
					$tablePrefix = $sm->get('Miranda\Service\Config')->db->get('table_prefix', '');
					return new AclManager($sm->get('app_zend_db_adapter'), array(
						AclManager::TABLE_ROLES => $tablePrefix . 'roles',
						AclManager::TABLE_RIGHTS => $tablePrefix . 'rights',
						AclManager::TABLE_ROLES_RIGHTS => $tablePrefix . 'roles_rights'
					));
				},
				'Acl\TableGateway\Rights' => function ($sm)
				{
					$dbAdapter = $sm->get('acl_zend_db_adapter');
					return new TableGateway($sm->get('Miranda\Service\Config')->db->get('table_prefix', '') . 'rights', $dbAdapter, 
							new Feature\RowGatewayFeature('id'));
				},
				'Acl\TableGateway\RightsGroups' => function ($sm)
				{
					$dbAdapter = $sm->get('acl_zend_db_adapter');
					return new TableGateway($sm->get('Miranda\Service\Config')->db->get('table_prefix', '') . 'rights_groups', $dbAdapter, 
							new Feature\RowGatewayFeature('id'));
				},
				'Acl\TableGateway\Roles' => function ($sm)
				{
					$dbAdapter = $sm->get('acl_zend_db_adapter');
					$resultSetPrototype = new ResultSet();
					$resultSetPrototype->setArrayObjectPrototype(new Model\Role());
					return new TableGateway($sm->get('Miranda\Service\Config')->db->get('table_prefix', '') . 'roles', $dbAdapter, null, 
							$resultSetPrototype);
				},
				'Acl\TableGateway\RolesRights' => function ($sm)
				{
					$dbAdapter = $sm->get('acl_zend_db_adapter');
					return new TableGateway($sm->get('Miranda\Service\Config')->db->get('table_prefix', '') . 'roles_rights', $dbAdapter, 
							new Feature\RowGatewayFeature('role_id'));
				},
				'Acl\Model\RoleTable' => function ($sm)
				{
					return new RoleTable($sm->get('Acl\TableGateway\Roles'), $sm->get('Acl\TableGateway\RolesRights'));
				},
				'Acl\Form\Role' => function ($sm)
				{
					$form = new Form\Role(null, $sm->get('translator'));
					$form->setInputFilter(new Form\RoleFilter());
					return $form;
				}
			)
		);
	}

	public function getControllerConfig()
	{
		return array(
			'initializers' => array(
				function ($instance, $cm)
				{
					if ($instance instanceof ConfigAwareInterface) {
						$instance->setConfig($cm->getServiceLocator()->get('Miranda\Service\Config'));
					}
				}
			)
		);
	}

	public function getControllerPluginConfig()
	{
		return array(
			'factories' => array(
				'acl' => function ($sm)
				{
					return new \Acl\Controller\Plugin\Acl($sm->getServiceLocator()->get('Miranda\Service\Acl'));
				}
			)
		);
	}

	public function getViewHelperConfig()
	{
		return array(
			'factories' => array(
				'acl' => function ($sm)
				{
					return new \Acl\View\Helper\Acl($sm->getServiceLocator()->get('Miranda\Service\Acl'));
				}
			)
		);
	}
}