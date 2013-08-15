<?php
namespace User\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\ConfigAwareInterface;

class AdminController extends AbstractActionController implements ConfigAwareInterface
{
	protected $config;
	
	protected $userTable;
	
	public function setConfig($config)
	{
		$this->config = $config;
	}

	public function indexAction()
	{
		return new ViewModel(array(
				'users' => $this->getUserTable()->fetchAll()
		));
	}

	public function addAction()
	{
	}

	public function editAction()
	{
	}

	public function deleteAction()
	{
	}
	
	public function getUserTable()
	{
		if (!$this->userTable) {
			$this->userTable = $this->getServiceLocator()->get('User\Model\UserTable');
		}
		return $this->userTable;
	}
}