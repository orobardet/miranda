<?php
namespace User;

use User\Model\User;
use User\Model\UserTable;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\Feature;
use Zend\Mvc\MvcEvent;
use Zend\Authentication\AuthenticationService;
use User\Authentification\Adapter\DbCallbackCheckAdapter as AuthDbTable;
use User\Authentification\Storage\Session as AuthSessionStorage;
use Application\ConfigAwareInterface;
use Zend\Crypt\Password\Bcrypt;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Console\Adapter\AdapterInterface as ConsoleAdapterInterface;
use Zend\Console\Request as ConsoleRequest;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface, ServiceProviderInterface, ConsoleUsageProviderInterface
{

	public function init(\Zend\ModuleManager\ModuleManager $mm)
	{
		$sem = $mm->getEventManager()->getSharedManager();
		
		$sem->attach('Zend\Mvc\Controller\AbstractActionController', 'dispatch', array(
			$this,
			'checkLoggedUser'
		), 110);
	}

	public function checkLoggedUser(MvcEvent $e)
	{
		// Pas de vérification d'utilisateur connecté si on est en mode CLI
		if ($e->getRequest() instanceof ConsoleRequest) {
			return;
		}
		
		// Verification si l'utilisateur est connecté
		$authService = $e->getApplication()->getServiceManager()->get('Miranda\Service\AuthService');
		
		// Lecture dans la conf des page autorisées sans être connecté
		$config = $e->getApplication()->getServiceManager()->get('Miranda\Service\Config');
		$allowedPages = $config->authentification->get('not_login_page', array(
			'login',
			'authenticate',
			'logout'
		));
		if (!is_array($allowedPages))
			$allowedPages = $allowedPages->toArray();
			
			// Si on est sur une page non accessible si pas connecté, et qu'il n'y a
			// pas d'utilisateur connecté
		if (!in_array($e->getRouteMatch()->getMatchedRouteName(), $allowedPages) && !$authService->hasIdentity()) {
			// Calcul de l'URL demandée, pour redirection après connexion
			$requestUri = $e->getRequest()->getUri();
			$uriPath = $requestUri->getPath();
			$uriQuery = $requestUri->getQuery();
			$uriFragment = $requestUri->getFragment();
			
			$redirect = $uriPath;
			if (!empty($uriQuery)) {
				$redirect .= '?' . $uriQuery;
			}
			if (!empty($uriFragment)) {
				$redirect .= '#' . $uriFragment;
			}
			
			// Redirection vers la page de login
			if (!empty($redirect) && ($redirect != '/')) {
				return $e->getTarget()->plugin('redirect')->toRoute('login', array(), 
						array(
							'query' => array(
								'redirect' => urlencode($redirect)
							)
						));
			} else {
				return $e->getTarget()->plugin('redirect')->toRoute('login');
			}
		}
		
		// Si l'utilisateur connecté n'est plus activé, on le déconnecte
		if ($authService->hasIdentity() && !$authService->getIdentity()->isActive() && ($e->getRouteMatch()->getMatchedRouteName() != 'logout')) {
			$session = $e->getApplication()->getServiceManager()->get('Zend\Session\SessionManager')->getStorage();
			$session->auth_error_message = 'This user account is not activated.';
			return $e->getTarget()->plugin('redirect')->toRoute('logout');
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
				'User\Model\UserTable' => function ($sm)
				{
					return new UserTable($sm->get('User\TableGateway\Users'), $sm->get('User\TableGateway\UsersRoles'));
				},
				'User\TableGateway\Users' => function ($sm)
				{
					$dbAdapter = $sm->get('user_zend_db_adapter');
					$resultSetPrototype = new ResultSet();
					$resultSetPrototype->setArrayObjectPrototype(new User());
					return new TableGateway($sm->get('Miranda\Service\Config')->db->get('table_prefix', '') . 'users', $dbAdapter, null, 
							$resultSetPrototype);
				},
				'User\TableGateway\UsersRoles' => function ($sm)
				{
					$dbAdapter = $sm->get('acl_zend_db_adapter');
					return new TableGateway($sm->get('Miranda\Service\Config')->db->get('table_prefix', '') . 'users_roles', $dbAdapter, 
							new Feature\RowGatewayFeature('user_id'));
				},
				'User\Form\Login' => function ($sm)
				{
					$form = new Form\Login(null, $sm->get('translator'));
					$form->setInputFilter(new Form\LoginFilter());
					return $form;
				},
				'User\Form\User' => function ($sm)
				{
					$roles = array();
					$rolesResults = $sm->get('Acl\Model\RoleTable')->fetchAll();
					foreach ($rolesResults as $role) {
						$roles[$role->getId()] = $role->getName();
					}
					$form = new Form\User(null, $sm->get('translator'), $roles);
					$form->setInputFilter(new Form\UserFilter($sm->get('user_zend_db_adapter'), $sm->get('Miranda\Service\Config')));
					return $form;
				},
				'Miranda\Service\AuthService' => function ($sm)
				{
					return new AuthenticationService($sm->get('Miranda\Service\AuthSessionStorage'), $sm->get('Miranda\Service\AuthDb'));
				},
				'Miranda\Service\AuthSessionStorage' => function ($sm)
				{
					return new AuthSessionStorage($sm->get('User\Model\UserTable'), 'whoislogged', 'identity', $sm->get('Zend\Session\SessionManager'));
				},
				'Miranda\Service\AuthDb' => function ($sm)
				{
					$authAdapter = new AuthDbTable($sm->get('Miranda\Service\DbAdapter'));
					$authAdapter->setTableName($sm->get('Miranda\Service\Config')->db->get('table_prefix', '') . 'users')->setIdentityColumn('email')->setCredentialColumn(
							'password');
					return $authAdapter;
				},
				'Miranda\Service\AuthBCrypt' => function ($sm)
				{
					$bcrypt = new Bcrypt();
					$bcrypt->setCost($sm->get('Miranda\Service\Config')->authentification->bcrypt->get('cost', 14));
					
					return $bcrypt;
				}
			),
			'alias' => array(
				'Zend\Authentication\AuthenticationService' => 'Miranda\Service\AuthService'
			)
		);
	}

	public function getControllerPluginConfig()
	{
		return array(
			'factories' => array(
				'userAuthentication' => function ($sm)
				{
					$serviceLocator = $sm->getServiceLocator();
					$authService = $serviceLocator->get('Miranda\Service\AuthService');
					$authAdapter = $serviceLocator->get('Miranda\Service\AuthDb');
					$controllerPlugin = new Controller\Plugin\UserAuthentication();
					$controllerPlugin->setAuthService($authService);
					$controllerPlugin->setAuthAdapter($authAdapter);
					return $controllerPlugin;
				}
			)
		);
	}

	public function getViewHelperConfig()
	{
		return array(
			'factories' => array(
				'userIdentity' => function ($sm)
				{
					$serviceLocator = $sm->getServiceLocator();
					$viewHelper = new View\Helper\UserIdentity();
					$viewHelper->setAuthService($serviceLocator->get('Miranda\Service\AuthService'));
					return $viewHelper;
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

	public function getConsoleUsage(ConsoleAdapterInterface $console)
	{
		return array(
			'List and display users',
			'show [all] users' => 'List all users',
			'show user <id>|<email>' => 'Show a user by ID or email',
			array(
				'<id>',
				'user ID',
				'ID of the user'
			),
			array(
				'<email>',
				'user email',
				'Full email address of the user'
			)
		);
	}
}