<?php
namespace User\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Application\ConfigAwareInterface;
use Zend\Config\Config as ZendConfig;

abstract class AbstractUserController extends AbstractActionController implements ConfigAwareInterface
{
	/**
	 * @var \Zend\Config\Config
	 */
	protected $config;

	protected $userTable;

	public function setConfig(ZendConfig $config)
	{
		$this->config = $config;
	}
	
	/**
	 * @return \Zend\Config\Config
	 */
	public function getConfig()
	{
		return $this->config;
	}

	public function getUserTable()
	{
		if (!$this->userTable) {
			$this->userTable = $this->getServiceLocator()->get('User\Model\UserTable');
		}
		return $this->userTable;
	}
	
	/**
	 * @param \User\Model\User $user
	 */
	protected function sendAccountCreationMail($user)
	{
		$translator = $this->getServiceLocator()->get('translator');
		
		/* @var $mailer \Application\Mail\Mailer  */
		$mailer = $this->getServiceLocator()->get('Miranda\Service\Mailer');
		$mailer->setFromNoReply();
		$mailer->addTo($user->getEmail(), $user->getDisplayName());
		$mailer->setSubject($translator->translate('Creating your account'));
		$mailer->token = $user->getRegistrationToken();
		$mailer->setTemplate('account_creation');
		$mailer->send();
	}
}
