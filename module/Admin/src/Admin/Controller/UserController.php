<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class UserController extends AbstractActionController
{
	protected $userTable;
	
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
			$sm = $this->getServiceLocator();
			$this->userTable = $sm->get('Admin\Model\UserTable');
		}
		return $this->userTable;
	}
}