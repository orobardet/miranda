<?php
namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Session\SessionManager;
use Zend\Session\Container;
use Zend\Config\Config as ZendConfig;
use Zend\Validator\AbstractValidator;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\View\HelperPluginManager;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface, ServiceProviderInterface
{

	public function init(\Zend\ModuleManager\ModuleManager $mm)
	{
		$sem = $mm->getEventManager()->getSharedManager();
		
		$sem->attach('Zend\Mvc\Controller\AbstractActionController', 'dispatch', array(
			$this,
			'addLayoutViewVariables'
		), 200);
	}

	/**
	 * Method où ajouter toutes les variables à passer à la vue du layout
	 *
	 * @param Zend\Mvc\MvcEvent $e
	 */
	public function addLayoutViewVariables($e)
	{
		$route = $e->getRouteMatch();
		$viewModel = $e->getViewModel();
		$variables = $viewModel->getVariables();
		
		if (false === isset($variables['controller'])) {
			$viewModel->setVariable('controller', $route->getParam('controller'));
		}
		if (false === isset($variables['action'])) {
			$viewModel->setVariable('action', $route->getParam('action'));
		}
		
		$viewModel->setVariable('module', strtolower(__NAMESPACE__));
	}

	public function onBootstrap(MvcEvent $e)
	{
		$eventManager = $e->getApplication()->getEventManager();
		$moduleRouteListener = new ModuleRouteListener();
		$moduleRouteListener->attach($eventManager);
		
		$translator = $e->getApplication()->getServiceManager()->get('translator');
		$translator->setLocale(\Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']))->setFallbackLocale('fr_FR');
		
		AbstractValidator::setDefaultTranslator($translator);
		
		$this->bootstrapSession($e);
		
		$config = $e->getApplication()->getServiceManager()->get('Miranda\Service\Config');
		
		$viewModel = $e->getViewModel();
		if ($config->layout->get('compile_less', false)) {
			$css = array();
			$less = $config->layout->get('less', array());
			if (count($less)) {
				$css = array($config->layout->get('less_compiler', 'less_compiler.php').'?f='.join(',',$less->toArray()));
			}
			$viewModel->setVariable('css', $css);
		} else {
			$viewModel->setVariable('css', $config->layout->get('css', array()));
		}
		$viewModel->setVariable('js', $config->layout->get('js', array()));
	}

	public function bootstrapSession($e)
	{
		$session = $e->getApplication()->getServiceManager()->get('Zend\Session\SessionManager');
		$session->start();
		
		$container = new Container('initialized');
		if (!isset($container->init)) {
			$session->regenerateId(true);
			$container->init = 1;
		}
	}

	public function getConfig()
	{
		return include __DIR__ . '/config/module.config.php';
	}

	public function getAutoloaderConfig()
	{
		return array(
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
				'Miranda\Service\Config' => function ($sm)
				{
					$config = new ZendConfig($sm->get('config'));
					return $config->application;
				},
				'Zend\Session\SessionManager' => function ($sm)
				{
					$config = $sm->get('config');
					if (isset($config['session'])) {
						$session = $config['session'];
						
						$sessionConfig = null;
						if (isset($session['config'])) {
							$class = isset($session['config']['class']) ? $session['config']['class'] : 'Zend\Session\Config\SessionConfig';
							$options = isset($session['config']['options']) ? $session['config']['options'] : array();
							$sessionConfig = new $class();
							$sessionConfig->setOptions($options);
						}
						
						$sessionStorage = null;
						if (isset($session['storage'])) {
							$class = $session['storage'];
							$sessionStorage = new $class();
						}
						
						$sessionSaveHandler = null;
						if (isset($session['save_handler'])) {
							$sessionSaveHandler = $sm->get($session['save_handler']);
						}
						
						$sessionManager = new SessionManager($sessionConfig, $sessionStorage, $sessionSaveHandler);
						
						if (isset($session['validators'])) {
							$chain = $sessionManager->getValidatorChain();
							foreach ($session['validators'] as $validator) {
								$validator = new $validator();
								$chain->attach('session.validate', array(
									$validator,
									'isValid'
								));
							}
						}
					} else {
						$sessionManager = new SessionManager();
					}
					Container::setDefaultManager($sessionManager);
					return $sessionManager;
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
				'resultStatus' => function ($sm)
				{
					return new \Application\Controller\Plugin\ResultStatus($sm->getServiceLocator()->get('Zend\Session\SessionManager')->getStorage());
				},
				'refererUrl' => function ($sm)
				{
					return new \Application\Controller\Plugin\RefererUrl($sm->getServiceLocator()->get('Zend\Session\SessionManager')->getStorage());
				}
			)
		);
	}

	public function getViewHelperConfig()
	{
		return array(
			'invokables' => array(
				'translateReplace' => 'Application\View\Helper\TranslateReplace'
			),
			'factories' => array(
				'resultStatus' => function (HelperPluginManager $pm)
				{
					return new \Application\View\Helper\ResultStatus($pm->getServiceLocator()->get('Zend\Session\SessionManager')->getStorage());
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
