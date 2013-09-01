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

class Module implements AutoloaderProviderInterface, ConfigProviderInterface, ServiceProviderInterface
{

	protected $config;

	public function onBootstrap(MvcEvent $e)
	{
		$this->config = $e->getApplication()->getServiceManager()->get('Miranda\Service\Config');
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
					$form->setInputFilter(new Form\UserFilter($sm->get('Zend\Db\Adapter\Adapter'), $sm->get('Miranda\Service\Config')));
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
}