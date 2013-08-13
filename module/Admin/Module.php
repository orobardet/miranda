<?php
namespace Admin;

use Admin\Model\User;
use Admin\Model\UserTable;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Mvc\MvcEvent;
use Zend\Config\Config as ZendConfig;
use Application\ConfigAwareInterface;

class Module implements
    AutoloaderProviderInterface,
    ConfigProviderInterface,
    ServiceProviderInterface
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
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig()
    {
    	return array(
    			'factories' => array(
    					'Admin\Model\UserTable' =>  function($sm) {
    						$tableGateway = $sm->get('UserTableGateway');
    						$table = new UserTable($tableGateway);
    						return $table;
    					},
    					'UserTableGateway' => function ($sm) {
    						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    						$resultSetPrototype = new ResultSet();
    						$resultSetPrototype->setArrayObjectPrototype(new User());
    						return new TableGateway($this->config->db->get('table_prefix', '').'users', $dbAdapter, null, $resultSetPrototype);
    					},
    			),
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