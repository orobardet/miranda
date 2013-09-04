<?php
namespace Admin;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Application\ConfigAwareInterface;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{

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