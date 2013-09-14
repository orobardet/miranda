<?php
namespace User\Controller;

use Zend\View\Model\ViewModel;
use User\Model\User;
use Application\Toolbox\String as StringTools;
use Acl\Controller\AclControllerInterface;

class AdminController extends AbstractUserController implements AclControllerInterface
{

	public function aclIsAllowed($action,\Zend\Permissions\Acl\Acl $acl, $user)
	{
		switch ($action) {
			case "index":
				return "admin_list_users";
				break;
			case "show":
				return "admin_show_user";
				break;
			case "add":
				return "admin_add_user";
				break;
			case "edit":
				return "admin_edit_user";
				break;
			case "delete":
				return "admin_delete_user";
				break;
			default:
				return false;
				break;
		}
		
		return false;
	}

	public function indexAction()
	{
		$this->refererUrl()->setReferer('admin-user-show');
		$this->refererUrl()->setReferer('admin-user-add');
		$this->refererUrl()->setReferer('admin-user-edit');
		$this->refererUrl()->setReferer('admin-user-delete');
		
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
		
		$this->refererUrl()->setReferer('admin-user-edit');
		$this->refererUrl()->setReferer('admin-user-delete');
		$this->refererUrl()->setReferer('admin-role-show');
		
		return new ViewModel(
				array(
					'user' => $user,
					'all_roles' => $this->getServiceLocator()->get('Acl\Model\RoleTable')->fetchAll(),
					'return_url' => $this->refererUrl('admin-user-show')
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
				$data = $form->getData();
				$user->setPassword($data['password'], $this->getServiceLocator()->get('Miranda\Service\AuthBCrypt'));
				$this->getUserTable()->saveUser($user, true);
				
				return $this->redirect()->toRoute('admin/user');
			}
		} else {
			$form->setData($defaultData);
		}
		
		return array(
			'form' => $form,
			'cancel_url' => $this->refererUrl('admin-user-add')
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
					$user->setPassword($request->getPost('password'), $this->getServiceLocator()->get('Miranda\Service\AuthBCrypt'));
				}
				$this->getUserTable()->saveUser($user, $passwordChanged);
				
				$this->resultStatus()->addResultStatus(
						StringTools::varprintf($this->getServiceLocator()->get('translator')->translate("User '%name%' edited."), 
								array(
									'name' => $user->getDisplayName()
								)), "success");
				return $this->redirect()->toRoute('admin/user');
			}
		}
		
		return array(
			'id' => $id,
			'form' => $form,
			'user' => $user,
			'cancel_url' => $this->refererUrl('admin-user-edit')
		);
	}

	public function deleteAction()
	{
		$id = (int)$this->params()->fromRoute('id', 0);
		if (!$id) {
			return $this->redirect()->toRoute('admin/user');
		}
		
		// On refuse la suppression de son propre compte
		if ($this->userAuthentication()->getIdentity()->getId() == $id) {
			return array(
				'own_user' => true,
				'user' => $this->getUserTable()->getUser($id),
				'return_url' => $this->refererUrl('admin-user-delete')
			);
		}
		
		$request = $this->getRequest();
		if ($request->isPost()) {
			$del = $request->getPost('del', 'no');
			
			if ($del == 'yes') {
				$id = (int)$request->getPost('id');
				$this->getUserTable()->deleteUser($id);
			}
			
			$return_url = $this->refererUrl('admin-user-delete');
			if ($return_url) {
				return $this->redirect()->toUrl($return_url);
			} else {
				return $this->redirect()->toRoute('admin/user');
			}
		}
		
		return array(
			'id' => $id,
			'user' => $this->getUserTable()->getUser($id)
		);
	}
}