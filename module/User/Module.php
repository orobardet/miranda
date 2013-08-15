<?php
namespace User;

use User\Model\User;
use User\Model\UserTable;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Mvc\MvcEvent;
use Zend\Authentication\AuthenticationService;
use User\Authentification\Adapter\DbCallbackCheckAdapter as AuthDbTable;
use User\Authentification\Storage\Session as AuthSessionStorage;
use Zend\Config\Config as ZendConfig;
use Application\ConfigAwareInterface;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface, ServiceProviderInterface
{

	protected $config;

	public function onBootstrap(MvcEvent $e)
	{
		$this->config = new ZendConfig($e->getApplication()->getServiceManager()->get('config')['application']);
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
					$tableGateway = $sm->get('UserTableGateway');
					$table = new UserTable($tableGateway);
					return $table;
				},
				'UserTableGateway' => function ($sm)
				{
					$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
					$resultSetPrototype = new ResultSet();
					$resultSetPrototype->setArrayObjectPrototype(new User());
					return new TableGateway('users', $dbAdapter, null, $resultSetPrototype);
				},
				'UserLoginForm' => function ($sm)
				{
					$form = new Form\Login(null, $sm->get('translator'));
					$form->setInputFilter(new Form\LoginFilter());
					return $form;
				},
				'MirandaAuthService' => function ($sm)
				{
					return new AuthenticationService($sm->get('MirandaAuthSessionStorage'), $sm->get('MirandaAuthDb'));
				},
				'MirandaAuthSessionStorage' => function ($sm)
				{
					return new AuthSessionStorage($sm->get('User\Model\UserTable'), 'whoislogged', 'identity', $sm->get('Zend\Session\SessionManager'));
				},
				'MirandaAuthDb' => function ($sm)
				{
					$authAdapter = new AuthDbTable($sm->get('MirandaDbAdapter'));
					$authAdapter->setTableName($this->config->db->get('table_prefix', '') . 'users')->setIdentityColumn('email')->setCredentialColumn(
							'password');
					return $authAdapter;
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
					$authService = $serviceLocator->get('MirandaAuthService');
					$authAdapter = $serviceLocator->get('MirandaAuthDb');
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
					$viewHelper->setAuthService($serviceLocator->get('MirandaAuthService'));
					return $viewHelper;
				}
			)
		);
	}

	public function getControllerConfig()
	{
		return array(
			'initializers' => array(
				function ($instance, $sm)
				{
					if ($instance instanceof ConfigAwareInterface) {
						$instance->setConfig($this->config);
					}
				}
			)
		);
	}
}