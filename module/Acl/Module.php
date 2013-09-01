<?php
namespace Acl;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\Mvc\MvcEvent;
use Zend\Config\Config as ZendConfig;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\Feature;
use Application\ConfigAwareInterface;
use Acl\Model\RightsManager;
use Acl\Model\RoleTable;
use Zend\Db\ResultSet\ResultSet;

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
				'Acl\Model\RightsManager' => function ($sm) {
					return new RightsManager($sm->get('Acl\TableGateway\Rights'), $sm->get('Acl\TableGateway\RightsGroups'));
				},
				'Acl\Model\RolesManager' => function ($sm) {
					return new RightsManager($sm->get('Acl\TableGateway\Roles'));
				},
				'Acl\TableGateway\Rights' => function ($sm)
				{
					$dbAdapter = $sm->get('acl_zend_db_adapter');
					return new TableGateway('rights', $dbAdapter, new Feature\RowGatewayFeature('id'));
				},
				'Acl\TableGateway\RightsGroups' => function ($sm)
				{
					$dbAdapter = $sm->get('acl_zend_db_adapter');
					return new TableGateway('rights_groups', $dbAdapter, new Feature\RowGatewayFeature('id'));
				},
				'Acl\TableGateway\Roles' => function ($sm)
				{
					$dbAdapter = $sm->get('acl_zend_db_adapter');
					$resultSetPrototype = new ResultSet();
					$resultSetPrototype->setArrayObjectPrototype(new Model\Role());
					return new TableGateway('roles', $dbAdapter, null, $resultSetPrototype);
				},
				'Acl\TableGateway\RolesRights' => function ($sm)
				{
					$dbAdapter = $sm->get('acl_zend_db_adapter');
					return new TableGateway('roles_rights', $dbAdapter, new Feature\RowGatewayFeature('role_id'));
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
				},
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