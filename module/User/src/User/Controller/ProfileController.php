<?php
namespace User\Controller;

use Application\ConfigAwareInterface;
use Zend\View\Model\ViewModel;

class ProfileController extends AbstractUserController implements ConfigAwareInterface
{

	private function getProfileViewModel($action, $variables = null, $options = null)
	{
		$viewModel = new ViewModel($variables + array(
			'profile_action' => $action
		), $options);
		$viewModel->setTemplate('user/profile/profile-layout');
		
		$viewModelChild = new ViewModel($variables, $options);
		
		$viewModelChild->setTemplate('user/profile/' . $action);
		$viewModel->addChild($viewModelChild);
		
		return $viewModel;
	}

	public function showAction()
	{
		return $this->getProfileViewModel('show', array(
			'user' => $this->userAuthentication()->getIdentity()
		));
	}

	public function editAction()
	{
		$user = $this->userAuthentication()->getIdentity();
		$id = $user->getId();
		
		$form = $this->getServiceLocator()->get('User\Form\Profile');
		$form->getInputFilter()->setUserId($id);
		$form->bind($user);
		$form->setAttribute('action', $this->url()->fromRoute('profile', array(
			'action' => 'edit'
		)));
		$form->setAttribute('method', 'post');
		$form->get('submit')->setValue($this->getServiceLocator()->get('translator')->translate('Edit'));
		
		$request = $this->getRequest();
		if ($request->isPost()) {
			$form->setData($request->getPost());
			if ($form->isValid()) {
				$this->getUserTable()->saveUser($user);
				
				$this->resultStatus()->addResultStatus($this->getServiceLocator()->get('translator')->translate("Profile edited."), "success");
				return $this->redirect()->toRoute('profile');
			}
		}
		
		return $this->getProfileViewModel('edit', 
				array(
					'id' => $id,
					'form' => $form,
					'user' => $user,
					'cancel_url' => $this->refererUrl('profile-edit')
				));
	}

	public function changepasswordAction()
	{
		/* @var $user \User\Model\User */
		$user = $this->userAuthentication()->getIdentity();
		$id = $user->getId();
		
		$form = $this->getServiceLocator()->get('User\Form\Password');
		$form->setAttribute('action', $this->url()->fromRoute('profile', array(
			'action' => 'changepassword'
		)));
		$form->setAttribute('method', 'post');
		$form->get('submit')->setValue($this->getServiceLocator()->get('translator')->translate('Edit'));
		
		$request = $this->getRequest();
		if ($request->isPost()) {
			$form->setData($request->getPost());

			$passwordChanged = false;
			if (($request->getPost('password', '') == '') && ($request->getPost('password-verification', '') == '')) {
				$passwordChanged = false;
			} else {
				$passwordChanged = true;
			}
			
			if ($passwordChanged) {
				if ($form->isValid()) {
					$bcrypt = $this->getServiceLocator()->get('Miranda\Service\AuthBCrypt');
					if ($user->verifyPassword($request->getPost('current_password'), $bcrypt)) {
						$user->setPassword($request->getPost('password'), $bcrypt);
						$this->getUserTable()->saveUser($user, true);
						
						$this->resultStatus()->addResultStatus($this->getServiceLocator()->get('translator')->translate("New password defined."), "success");
						return $this->redirect()->toRoute('profile');
					} else {
						$form->setMessages(array('current_password' => array('Invalid password')));
					}
				}
			} else {
				$this->resultStatus()->addResultStatus($this->getServiceLocator()->get('translator')->translate("No password change."), "info");
				return $this->redirect()->toRoute('profile');
			}
		}
		
		return $this->getProfileViewModel('changepassword', 
				array(
					'id' => $id,
					'form' => $form,
					'user' => $user,
					'cancel_url' => $this->refererUrl('profile-edit')
				));
	}
}