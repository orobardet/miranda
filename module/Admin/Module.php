<?php
namespace Admin;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
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
    			),
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