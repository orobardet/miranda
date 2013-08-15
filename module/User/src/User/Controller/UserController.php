<?php
namespace User\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\ConfigAwareInterface;

class UserController extends AbstractActionController implements ConfigAwareInterface
{
	protected $config;
	
	protected $userTable;
	
	public function setConfig($config)
	{
		$this->config = $config;
	}

	public function showAction()
	{
		return new ViewModel(array(
				'user' => $this->userAuthentication()->getIdentity()
		));
	}

	public function getUserTable()
	{
		if (!$this->userTable) {
			$this->userTable = $this->getServiceLocator()->get('User\Model\UserTable');
		}
		return $this->userTable;
	}
}