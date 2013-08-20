<?php
namespace Application;

use Zend\EventManager\StaticEventManager;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Session\SessionManager;
use Zend\Session\Container;
use Zend\Config\Config as ZendConfig;
use Zend\Validator\AbstractValidator;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface, ServiceProviderInterface
{

	public function init()
	{
		$events = StaticEventManager::getInstance();
		$events->attach('Zend\Mvc\Controller\AbstractActionController', 'dispatch', array(
			$this,
			'authPreDispatch'
		), 110);
		
		$events->attach('Zend\Mvc\Controller\AbstractActionController', 'dispatch', array($this, 'addLayoutViewVariables'), 201);
	}

	public function authPreDispatch($event)
	{
		// Verification si l'utilisateur est connecté
		$authService = $event->getApplication()->getServiceManager()->get('MirandaAuthService');
		
		// Lecture dans la conf des page autorisées sans être connecté
		$config = new ZendConfig($event->getApplication()->getServiceManager()->get('config')['application']);
		$allowedPages = $config->authentification->get('not_login_page', array(
			'login',
			'authenticate',
			'logout'
		));
		if (!is_array($allowedPages))
			$allowedPages = $allowedPages->toArray();
			
			// Si on est sur une page non accessible si pas connecté, et qu'il n'y a
			// pas d'utilisateur connecté
		if (!in_array($event->getRouteMatch()->getMatchedRouteName(), $allowedPages) && !$authService->hasIdentity()) {
			// Calcul de l'URL demandée, pour redirection après connexion
			$requestUri = $event->getRequest()->getUri();
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
				return $event->getTarget()->plugin('redirect')->toRoute('login', array(), 
						array(
							'query' => array(
								'redirect' => urlencode($redirect)
							)
						));
			} else {
				return $event->getTarget()->plugin('redirect')->toRoute('login');
			}
		}
		
		// Si l'utilisateur connecté n'est plus activé, on le déconnecte
		if ($authService->hasIdentity() && !$authService->getIdentity()->isActive() && ($event->getRouteMatch()->getMatchedRouteName() != 'logout')) {
			$session = $event->getApplication()->getServiceManager()->get('Zend\Session\SessionManager')->getStorage();
			$session->auth_error_message = 'This user account is not activated.';				
			return $event->getTarget()->plugin('redirect')->toRoute('logout');
		}
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
		
		$config = new ZendConfig($e->getApplication()->getServiceManager()->get('config')['application']);
		
		$viewModel->setVariable('css', $config->layout->get('css', array()));
		$viewModel->setVariable('js', $config->layout->get('js', array()));
	}
	
	public function onBootstrap(MvcEvent $e)
	{
		$this->config = new ZendConfig($e->getApplication()->getServiceManager()->get('config')['application']);
		
		$eventManager = $e->getApplication()->getEventManager();
		$moduleRouteListener = new ModuleRouteListener();
		$moduleRouteListener->attach($eventManager);
		
		$translator = $e->getApplication()->getServiceManager()->get('translator');
		$translator->setLocale(\Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']))->setFallbackLocale('fr_FR');
		
		AbstractValidator::setDefaultTranslator($translator);
		
		$this->bootstrapSession($e);
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
							// class should be fetched from service manager since it will require constructor arguments
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
				function ($instance, $sm)
				{
					if ($instance instanceof ConfigAwareInterface) {
						$instance->setConfig($this->config);
					}
				}
			)
		);
	}
	
	public function getViewHelperConfig()
	{
		return array(
			'invokables' => array(
				'translateReplace' => 'Application\View\Helper\TranslateReplace'
			)
		);
	}
}
