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
}
