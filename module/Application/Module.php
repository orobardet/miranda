<?php
namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Session\SessionManager;
use Zend\Session\Container;
use Zend\Validator\AbstractValidator;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\View\HelperPluginManager;
use Zend\ModuleManager\Feature\ConsoleBannerProviderInterface;
use Zend\Console\Adapter\AdapterInterface as ConsoleAdapterInterface;
use Application\Model\Paginator\ItemsPerPageManager;
use Application\Model\PictureTable;
use Application\Model\Picture;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface, ServiceProviderInterface, ConsoleBannerProviderInterface
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
		$languageString = '';
		if (isset($_SERVER) && array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER)) {
			$languageString = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
		}
		$translator->setLocale(\Locale::acceptFromHttp($languageString))->setFallbackLocale('fr_FR');
		
		AbstractValidator::setDefaultTranslator($translator);
		
		$this->bootstrapSession($e);
		
		$config = $e->getApplication()->getServiceManager()->get('Miranda\Service\Config');
		
		$viewModel = $e->getViewModel();
		if ($config->get('layout->compile_less', false)) {
			$css = array();
			$less = $config->get('layout->less', array());
			if (count($less)) {
				$css = array(
					$config->get('layout->less_wrapper', 'less_wrapper.php') . '?f=' . join(',', $less->toArray()) . '&c=' .
							 $config->get('layout->less_compiler', 'lessphp')
				);
			}
			$viewModel->setVariable('css', $css);
		} else {
			$viewModel->setVariable('css', $config->get('layout->css', array())->toArray());
		}
		$viewModel->setVariable('js', $config->get('layout->js', array())->toArray());
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
				'Miranda\Service\Config' => function ($sm)
				{
					$config = new TraversableConfig($sm->get('config'));
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
				},
				'Miranda\Service\Paginator\ItemsPerPageManager' => function ($sm)
				{
					return new ItemsPerPageManager($sm->get('Zend\Session\SessionManager')->getStorage());
				},
				'Miranda\Model\PictureTable' => function ($sm)
				{
					return new PictureTable($sm->get('Miranda\TableGateway\Pictures'));
				},
				'Miranda\TableGateway\Pictures' => function ($sm)
				{
					$dbAdapter = $sm->get('app_zend_db_adapter');
					$resultSetPrototype = new ResultSet();
					$resultSetPrototype->setArrayObjectPrototype(new Picture());
					return new TableGateway($sm->get('Miranda\Service\Config')->get('db->table_prefix', '') . 'pictures', $dbAdapter, null, 
							$resultSetPrototype);
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
			'invokables' => array(
				'requestAcceptJson' => 'Application\Controller\Plugin\RequestAcceptJson'
			),
			'factories' => array(
				'resultStatus' => function ($sm)
				{
					return new \Application\Controller\Plugin\ResultStatus($sm->getServiceLocator()->get('Zend\Session\SessionManager')->getStorage());
				},
				'refererUrl' => function ($sm)
				{
					return new \Application\Controller\Plugin\RefererUrl($sm->getServiceLocator()->get('Zend\Session\SessionManager')->getStorage());
				},
				'console' => function ($sm)
				{
					return new \Application\Controller\Plugin\Console($sm->getServiceLocator()->get('console'));
				},
				'itemsPerPage' => function ($sm)
				{
					return new \Application\Controller\Plugin\ItemsPerPage(
							$sm->getServiceLocator()->get('Miranda\Service\Paginator\ItemsPerPageManager'));
				},
				'dbTransaction' => function ($sm)
				{
					return new \Application\Controller\Plugin\DbTransaction($sm->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
				}
			)
		);
	}

	public function getViewHelperConfig()
	{
		return array(
			'invokables' => array(
				'text2Html' => 'Application\View\Helper\Text2Html',
				'translateReplace' => 'Application\View\Helper\TranslateReplace',
				'formUxSpinner' => 'Application\Form\View\Helper\FormUxSpinner'
			),
			'factories' => array(
				'resultStatus' => function (HelperPluginManager $pm)
				{
					return new \Application\View\Helper\ResultStatus($pm->getServiceLocator()->get('Zend\Session\SessionManager')->getStorage());
				},
				'itemsPerPage' => function (HelperPluginManager $pm)
				{
					return new \Application\View\Helper\ItemsPerPage($pm->getServiceLocator()->get('Miranda\Service\Paginator\ItemsPerPageManager'));
				}
			)
		);
	}

    public function getConsoleBanner(ConsoleAdapterInterface $console)
	{
		return 'Miranda';
	}
}
