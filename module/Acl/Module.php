<?php
namespace Acl;

use Acl\Model\AclManager;
use Acl\Model\RightsManager;
use Acl\Model\RoleTable;
use Acl\Model\RolesManager;
use Application\ConfigAwareInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\Feature;
use Zend\Db\TableGateway\TableGateway;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\Mvc\MvcEvent;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\GenericRole as Role;
use Acl\Controller\AclControllerInterface;
use Zend\View\Model\ViewModel;
use Acl\Helper\AclHelper;
use Zend\Console\Request as ConsoleRequest;
use Acl\Controller\AclConsoleControllerInterface;
use Zend\View\HelperPluginManager;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface, ServiceProviderInterface
{

	public function init(\Zend\ModuleManager\ModuleManager $mm)
	{
		$sem = $mm->getEventManager()->getSharedManager();
		
		$sem->attach('Zend\Mvc\Controller\AbstractActionController', 'dispatch', array(
			$this,
			'checkAcl'
		), 90);
	}

	public function checkAcl(MvcEvent $e)
	{
		if ($e->getRequest() instanceof ConsoleRequest) {
			$controller = $e->getTarget();
			if (!$controller instanceof AclConsoleControllerInterface) {
				/*
				 * // Le controlleur n'implémente pas l'interface qui est obligatoire pour déclarer des actions console
				 * // Erreur et on arrête là
				 * $console = $e->getApplication()->getServiceManager()->get('console');
				 * $console->writeLine('No console access allowed for this controller!');
				 * // On arrête la propagation de l'évenement, pour empecher que l'action initialement demandée soient exécutée
				 * $e->stopPropagation();
				 */
				return;
			}
			$route = $e->getRouteMatch();
			if (!$controller->aclConsoleIsAllowed($route->getParam('action'))) {
				// L'action demandée n'est pas autorisée en mode console
				// Erreur et on arrête là
				$console = $e->getApplication()->getServiceManager()->get('console');
				$console->writeLine('No console access allowed for this action!');
				// On arrête la propagation de l'évenement, pour empecher que l'action initialement demandée soient exécutée
				$e->stopPropagation();
				return;
			}
			return;
		}
		
		$acl = $e->getApplication()->getServiceManager()->get('Miranda\Service\Acl');
		$controller = $e->getTarget();
		$route = $e->getRouteMatch();
		
		$accessAllowed = true;
		if ($controller instanceof AclControllerInterface) {
			$accessAllowed = false;
			
			$neededRights = $controller->aclIsAllowed($route->getParam('action'), $acl, 'Miranda\CurrentUser');
			if ($neededRights === true) {
				$accessAllowed = true;
			} else 
				if (empty($neededRights)) {
					$accessAllowed = false;
				} else {
					$accessAllowed = AclHelper::isAllowed($acl, 'Miranda\CurrentUser', $neededRights);
				}
		}
		
		if (!$accessAllowed) {
			// Construction de la vue 403
			$userIdentity = $e->getApplication()->getServiceManager()->get('Miranda\Service\AuthService')->getIdentity();
			$viewParams = array(
				'controller' => $route->getParam('controller'),
				'action' => $route->getParam('action'),
				'message' => "You've tried to access to an unauthorized resource.",
				'user' => $userIdentity->getIdentity()
			);
			$viewModel = new ViewModel($viewParams);
			$viewModel->setTemplate('error/403');
			$e->getViewModel()->addChild($viewModel);
			
			// Réponse 403
			$response = $e->getResponse();
			$response->setStatusCode(403);
			$e->setResponse($response);
			
			// On arrête la propagation de l'évenement, pour empecher que l'action initialement demandée soient exécutée
			$e->stopPropagation();
		}
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
					$acl = $sm->get('Acl\Model\AclManager')->getAcl();
					
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
					$tablePrefix = $sm->get('Miranda\Service\Config')->get('db->table_prefix', '');
					return new AclManager($sm->get('app_zend_db_adapter'), 
							[
								AclManager::TABLE_ROLES => $tablePrefix . 'roles',
								AclManager::TABLE_RIGHTS => $tablePrefix . 'rights',
								AclManager::TABLE_ROLES_RIGHTS => $tablePrefix . 'roles_rights'
							], $sm->get('Miranda\Service\Cache'));
				},
				'Acl\TableGateway\Rights' => function ($sm)
				{
					$dbAdapter = $sm->get('acl_zend_db_adapter');
					return new TableGateway($sm->get('Miranda\Service\Config')->get('db->table_prefix', '') . 'rights', $dbAdapter, 
							new Feature\RowGatewayFeature('id'));
				},
				'Acl\TableGateway\RightsGroups' => function ($sm)
				{
					$dbAdapter = $sm->get('acl_zend_db_adapter');
					return new TableGateway($sm->get('Miranda\Service\Config')->get('db->table_prefix', '') . 'rights_groups', $dbAdapter, 
							new Feature\RowGatewayFeature('id'));
				},
				'Acl\TableGateway\Roles' => function ($sm)
				{
					$dbAdapter = $sm->get('acl_zend_db_adapter');
					$resultSetPrototype = new ResultSet();
					$resultSetPrototype->setArrayObjectPrototype(new Model\Role());
					return new TableGateway($sm->get('Miranda\Service\Config')->get('db->table_prefix', '') . 'roles', $dbAdapter, null, 
							$resultSetPrototype);
				},
				'Acl\TableGateway\RolesRights' => function ($sm)
				{
					$dbAdapter = $sm->get('acl_zend_db_adapter');
					return new TableGateway($sm->get('Miranda\Service\Config')->get('db->table_prefix', '') . 'roles_rights', $dbAdapter, 
							new Feature\RowGatewayFeature('role_id'));
				},
				'Acl\Model\RoleTable' => function ($sm)
				{
					return new RoleTable($sm->get('Acl\TableGateway\Roles'), $sm->get('Acl\TableGateway\RolesRights'), 
							$sm->get('User\TableGateway\UsersRoles'));
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
				'acl' => function (HelperPluginManager $pm)
				{
					return new \Acl\View\Helper\Acl($pm->getServiceLocator()->get('Miranda\Service\Acl'));
				},
				
				// Surcharge la factory native du Navigation View Helper, pour obtenir une version préconfigurée
				// avec les ACL de l'application
				'navigation' => function (HelperPluginManager $pm)
				{
					$navigation = $pm->get('Zend\View\Helper\Navigation');
					$navigation->setAcl($pm->getServiceLocator()->get('Miranda\Service\Acl'))->setRole('Miranda\CurrentUser');
					return $navigation;
				}
			)
		);
	}
}