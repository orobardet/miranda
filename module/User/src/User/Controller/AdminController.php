<?php
namespace User\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\ConfigAwareInterface;
use Zend\Config\Config as ZendConfig;
use User\Model\User;
use Application\Toolbox\String as StringTools;

class AdminController extends AbstractActionController implements ConfigAwareInterface
{

	protected $config;

	protected $userTable;

	public function setConfig(ZendConfig $config)
	{
		$this->config = $config;
	}

	public function indexAction()
	{
		return new ViewModel(array(
			'users' => $this->getUserTable()->fetchAll()
		));
	}

	public function showAction()
	{
		$id = (int)$this->params()->fromRoute('id', 0);
		if (!$id) {
			return $this->redirect()->toRoute('admin/user', array(
				'action' => 'add'
			));
		}
		
		try {
			$user = $this->getUserTable()->getUser($id);
		} catch (\Exception $ex) {
			return $this->redirect()->toRoute('admin/user');
		}
		
		return new ViewModel(array(
			'user' => $user,
			'all_roles' => $this->getServiceLocator()->get('Acl\Model\RoleTable')->fetchAll(),
			'return_url' => $this->url()->fromRoute('admin/user')				
		));
	}

	public function addAction()
	{
		$defaultData = array(
			'active' => true
		);
		
		$form = $this->getServiceLocator()->get('User\Form\User');
		$form->prepare();
		$form->setAttribute('action', $this->url()->fromRoute('admin/user', array(
			'action' => 'add'
		)));
		$form->setAttribute('method', 'post');
		$form->get('submit')->setValue($this->getServiceLocator()->get('translator')->translate('Add'));
		
		$request = $this->getRequest();
		if ($request->isPost()) {
			$form->setData($request->getPost());
			
			if ($form->isValid()) {
				$user = new User();
				
				$user->exchangeArray($form->getData(), false);
				$user->setPassword($form->getData()['password'], $this->getServiceLocator()->get('MirandaAuthBCrypt'));
				$this->getUserTable()->saveUser($user, true);
				
				return $this->redirect()->toRoute('admin/user');
			}
		} else {
			$form->setData($defaultData);
		}
		
		return array(
			'form' => $form,
			'cancel_url' => $this->url()->fromRoute('admin/user')
		);
	}

	public function editAction()
	{
		$id = (int)$this->params()->fromRoute('id', 0);
		if (!$id) {
			return $this->redirect()->toRoute('admin/user', array(
				'action' => 'add'
			));
		}
		
		try {
			$user = $this->getUserTable()->getUser($id);
		} catch (\Exception $ex) {
			return $this->redirect()->toRoute('admin/user');
		}
		
		$form = $this->getServiceLocator()->get('User\Form\User');
		$form->getInputFilter()->setUserId($id);
		$form->bind($user);
		$form->setAttribute('action', $this->url()->fromRoute('admin/user', array(
			'action' => 'edit',
			'id' => $id
		)));
		$form->setAttribute('method', 'post');
		$form->get('submit')->setValue($this->getServiceLocator()->get('translator')->translate('Edit'));
		
		$request = $this->getRequest();
		if ($request->isPost()) {
			$form->setData($request->getPost());
			
			$passwordChanged = false;
			if (($request->getPost('password', '') == '') && ($request->getPost('password-verification', '') == '')) {
				$form->getInputFilter()->noPasswordValidation();
				$passwordChanged = false;
			} else {
				$passwordChanged = true;
			}
			
			if ($form->isValid()) {
				if ($passwordChanged) {
					$user->setPassword($request->getPost('password'), $this->getServiceLocator()->get('MirandaAuthBCrypt'));
				}
				$this->getUserTable()->saveUser($user, $passwordChanged);
				
				$this->resultStatus()->addResultStatus(
						StringTools::varprintf($this->getServiceLocator()->get('translator')->translate("User '%name%' edited."), array(
							'name' => $user->getDisplayName()
						)), "success");
				return $this->redirect()->toRoute('admin/user');
			}
		}
		
		return array(
			'id' => $id,
			'form' => $form,
			'cancel_url' => $this->url()->fromRoute('admin/user')
		);
	}

	public function deleteAction()
	{
		//  TODO: empecher la suppression de son propre compte (par contrôle d'ID ici, et en retirant l'option de suppression dans la liste)
		$id = (int)$this->params()->fromRoute('id', 0);
		if (!$id) {
			return $this->redirect()->toRoute('admin/user');
		}
		
		$request = $this->getRequest();
		if ($request->isPost()) {
			$del = $request->getPost('del', 'no');
			
			if ($del == 'yes') {
				$id = (int)$request->getPost('id');
				$this->getUserTable()->deleteUser($id);
			}
			
			return $this->redirect()->toRoute('admin/user');
		}
		
		return array(
			'id' => $id,
			'user' => $this->getUserTable()->getUser($id)
		);
	}

	public function getUserTable()
	{
		if (!$this->userTable) {
			$this->userTable = $this->getServiceLocator()->get('User\Model\UserTable');
		}
		return $this->userTable;
	}
}