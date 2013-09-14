<?php
namespace User\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Application\ConfigAwareInterface;
use Zend\Config\Config as ZendConfig;

class AbstractUserController extends AbstractActionController implements ConfigAwareInterface
{
	protected $config;

	protected $userTable;

	public function setConfig(ZendConfig $config)
	{
		$this->config = $config;
	}

	public function getUserTable()
	{
		if (!$this->userTable) {
			$this->userTable = $this->getServiceLocator()->get('User\Model\UserTable');
		}
		return $this->userTable;
	}
}
