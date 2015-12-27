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
use Zend\Cache\StorageFactory;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface, ServiceProviderInterface, ConsoleBannerProviderInterface, 
		ConsoleUsageProviderInterface
{

	public function init(\Zend\ModuleManager\ModuleManager $mm)
	{
		$sem = $mm->getEventManager()->getSharedManager();
		
		$sem->attach('Zend\Mvc\Controller\AbstractActionController', 'dispatch', [
			$this,
			'addLayoutViewVariables'
		], 200);
	}

	/**
	 * Method où ajouter toutes les variables à passer à la vue du layout
	 *
	 * @param \Zend\Mvc\MvcEvent $e
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
		
		$controllerClass = get_class($e->getTarget());
		$viewModel->setVariable('module', substr($controllerClass, 0, strpos($controllerClass, '\\')));
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
			$css = [];
			$less = $config->get('layout->less', []);
			if (count($less)) {
				$css = [
					$config->get('layout->less_wrapper', 'less_wrapper.php') . '?f=' . join(',', $less->toArray()) . '&c=' .
							 $config->get('layout->less_compiler', 'lessphp')
				];
			}
			$viewModel->setVariable('css', $css);
		} else {
			$viewModel->setVariable('css', $config->get('layout->css', [])->toArray());
		}
		$viewModel->setVariable('js', $config->get('layout->js', [])->toArray());
		
		$viewModel->setVariable('config', $config);

        // get navigation plugin from service manager
        $viewManager = $e->getApplication()->getServiceManager()->get('viewmanager');
        $navigation = $viewManager->getRenderer()->plugin('navigation');

        // overwrite default menu plugin
        $navigation->getPluginManager()->setInvokableClass(
            'menu', 'Application\View\Helper\Navigation\IconMenu', true
        );
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
		return [
			'Zend\Loader\ClassMapAutoloader' => [
				__DIR__ . '/autoload_classmap.php'
			],
			'Zend\Loader\StandardAutoloader' => [
				'namespaces' => [
					__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__
				]
			]
		];
	}

	public function getServiceConfig()
	{
		return [
			'factories' => [
				'Miranda\Service\Config' => function ($sm)
				{
					$config = new TraversableConfig($sm->get('config'));
					return $config->application;
				},
				'Miranda\Service\Cache' => function ($sm)
				{
					$config = $sm->get('Miranda\Service\Config');

					try {
						$storageCache = StorageFactory::factory([
								'adapter' => [
									'name' => 'xcache',
									'options' => [
										'namespace' => $config->get('cache->namespace', 'miranda')
									]
								]
							]);
					} catch (\Zend\Cache\Exception\ExtensionNotLoadedException $e) {
                        $storageCache = StorageFactory::factory([
                                'adapter' => [
                                    'name' => 'memory',
                                    'options' => [
                                        'namespace' => $config->get('cache->namespace', 'miranda')
                                    ]
                                ]
                            ]);
					}

					return $storageCache;
				},
				'Miranda\Service\Mailer' => function ($sm)
				{
					return new Mail\Mailer(new \Zend\Mail\Transport\Sendmail(), $sm->get('Miranda\Service\Config'), 
							$sm->get('Zend\View\Renderer\RendererInterface'));
				},
				'Zend\Session\SessionManager' => function ($sm)
				{
					$config = $sm->get('config');
					if (isset($config['session'])) {
						$session = $config['session'];
						
						$sessionConfig = null;
						if (isset($session['config'])) {
							$class = isset($session['config']['class']) ? $session['config']['class'] : 'Zend\Session\Config\SessionConfig';
							$options = isset($session['config']['options']) ? $session['config']['options'] : [];
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
								$chain->attach('session.validate', [
									$validator,
									'isValid'
								]);
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
			],
			'shared' => [
				'Miranda\Service\Mailer' => false
			]
		];
	}

	public function getControllerConfig()
	{
		return [
			'initializers' => [
				function ($instance, $cm)
				{
					if ($instance instanceof ConfigAwareInterface) {
						$instance->setConfig($cm->getServiceLocator()->get('Miranda\Service\Config'));
					}
				}
			]
		];
	}

	public function getControllerPluginConfig()
	{
		return [
			'invokables' => [
				'requestAcceptJson' => 'Application\Controller\Plugin\RequestAcceptJson'
			],
			'factories' => [
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
			]
		];
	}

	public function getViewHelperConfig()
	{
		return [
			'invokables' => [
				'text2Html' => 'Application\View\Helper\Text2Html',
				'markdown' => 'Application\View\Helper\Markdown',
				'translateReplace' => 'Application\View\Helper\TranslateReplace',
				'formUxSpinner' => 'Application\Form\View\Helper\FormUxSpinner'
			],
			'factories' => [
				'resultStatus' => function (HelperPluginManager $pm)
				{
					return new \Application\View\Helper\ResultStatus($pm->getServiceLocator()->get('Zend\Session\SessionManager')->getStorage());
				},
				'itemsPerPage' => function (HelperPluginManager $pm)
				{
					return new \Application\View\Helper\ItemsPerPage($pm->getServiceLocator()->get('Miranda\Service\Paginator\ItemsPerPageManager'));
				},
				'baseUrl' => function (HelperPluginManager $pm)
				{
					return new \Application\View\Helper\BaseUrl(
							$pm->getServiceLocator()->get('Miranda\Service\Config')->get('app->base_url', 'http://localhost'));
				}
			]
		];
	}

	public function getConsoleBanner(ConsoleAdapterInterface $console)
	{
		return 'Miranda';
	}

	public function getConsoleUsage(ConsoleAdapterInterface $console)
	{
		return [
			'Application management',
			'clean app cache' => 'Clear all application cache, force reloading conf',
			'Tests',
			'[send] test email [to] <email>' => 'Send a test email',
			[
				'<email>',
				'Valid email address'
			]
		];
	}
}
