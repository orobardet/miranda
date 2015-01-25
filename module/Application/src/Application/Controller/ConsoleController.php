<?php
namespace Application\Controller;

use Application\ConfigAwareInterface;
use User\Model\User;
use Acl\Controller\AclConsoleControllerInterface;
use Zend\Config\Config as ZendConfig;
use Zend\Mvc\Controller\AbstractActionController;
use Application\Toolbox\String as StringTools;

class ConsoleController extends AbstractActionController implements ConfigAwareInterface, AclConsoleControllerInterface
{

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
}