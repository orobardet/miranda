<?php
namespace User\Controller;

use Zend\View\Model\ViewModel;
use Application\ConfigAwareInterface;

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
		return $this->getProfileViewModel('edit', array(
			'user' => $this->userAuthentication()->getIdentity()
		));
	}

	public function changepasswordAction()
	{
		return $this->getProfileViewModel('changepassword', array(
			'user' => $this->userAuthentication()->getIdentity()
		));
	}
}