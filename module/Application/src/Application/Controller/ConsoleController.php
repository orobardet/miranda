<?php
namespace Application\Controller;

use Application\ConfigAwareInterface;
use User\Model\User;
use Acl\Controller\AclConsoleControllerInterface;
use Zend\Config\Config as ZendConfig;
use Zend\Mvc\Controller\AbstractActionController;
use Application\Toolbox\String as StringTools;
use Application\TraversableConfig;
use Zend\Console\ColorInterface;
use Application\Toolbox\String as StringTool;

class ConsoleController extends AbstractActionController implements ConfigAwareInterface, AclConsoleControllerInterface
{

	/**
	 *
	 * @var \Application\TraversableConfig
	 */
	protected $config;

	public function setConfig(ZendConfig $config)
	{
		$this->config = $config;
	}

	public function aclConsoleIsAllowed($action)
	{
		return true;
	}

	public function testemailAction()
	{
		$translator = $this->getServiceLocator()->get('translator');
		$config = $this->getServiceLocator()->get('Miranda\Service\Config');
		
		$email = $this->getRequest()->getParam('email', null);
		
		if (!$email || (trim($email) == '')) {
			$this->console()->writeLine($translator->translate('No email address given.'));
			return;
		}
		
		$validator = new \Zend\Validator\EmailAddress([
			'allow' => \Zend\Validator\Hostname::ALLOW_ALL
		]);
		if (!$validator->isValid($email)) {
			$this->console()->writeLine($translator->translate('Email address is invalid.'));
			return;
		}
		
		/* @var $mailer \Application\Mail\Mailer  */
		$mailer = $this->getServiceLocator()->get('Miranda\Service\Mailer');
		
		$mailer->setFromNoReply();
		$mailer->addTo($email);
		$mailer->setSubject($translator->translate('Test email'));
		$mailer->setTemplate('test_message');
		
		$mailer->sent_timestamp = time();
		
		$mailer->send();
		
		$this->console()->writeLine(StringTools::varprintf($translator->translate('Test email sent to %email%.'), [
			'email' => $email
		]));
	}

	public function cleanappcacheAction()
	{
		$translator = $this->getServiceLocator()->get('translator');
		$appConfig = new TraversableConfig($this->getServiceLocator()->get('ApplicationConfig', []));
		$cache_dir = $appConfig->get('module_listener_options->cache_dir', null);
		
		if ($cache_dir) {
			$fileFound = false;
			$this->console()->writeLine();
			$this->console()->writeLine($translator->translate("Cleaning '$cache_dir'..."), ColorInterface::CYAN);
			
			$configCacheKey = $appConfig->get('module_listener_options->config_cache_key', '');
			$moduleMapCacheKey = $appConfig->get('module_listener_options->module_map_cache_key', '');
			
			foreach (glob($cache_dir . '/*' . $configCacheKey . '.php') as $filename) {
				$fileFound = true;
				$this->console()->write(StringTool::varprintf($translator->translate("   Deleting '%filename%'..."), [
					'filename' => $filename
				]));
				if (unlink($filename)) {
					$this->console()->writeLine($translator->translate(" OK"), ColorInterface::GREEN);
				} else {
					$this->console()->writeLine($translator->translate(" Failed"), ColorInterface::RED);
				}
			}
			
			foreach (glob($cache_dir . '/*' . $moduleMapCacheKey . '.php') as $filename) {
				$fileFound = true;
				$this->console()->write(StringTool::varprintf($translator->translate("   Deleting '%filename%'..."), [
					'filename' => $filename
				]));
				if (unlink($filename)) {
					$this->console()->writeLine($translator->translate(" OK"), ColorInterface::GREEN);
				} else {
					$this->console()->writeLine($translator->translate(" Failed"), ColorInterface::RED);
				}
			}
			if ($fileFound) {
				$this->console()->writeLine($translator->translate("Cache cleaned."), ColorInterface::GREEN);
			} else {
				$this->console()->writeLine($translator->translate("Nothing to clean."), ColorInterface::YELLOW);
			}
			$this->console()->writeLine();
		} else {
			$this->console()->writeLine($translator->translate("No cache directory found in application config."), ColorInterface::YELLOW);
		}
	}
}