<?php
namespace User\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\ConfigAwareInterface;
use Zend\Config\Config as ZendConfig;

class UserController extends AbstractActionController implements ConfigAwareInterface
{
	protected $config;
	
	protected $userTable;
	
	public function setConfig(ZendConfig $config)
	{
		$this->config = $config;
	}

	private function getProfileViewModel($action, $variables = null, $options = null) {
		$viewModel = new ViewModel($variables + array('profile_action' => $action), $options);
		$viewModel->setTemplate('user/user/profile-layout');
		
		$viewModelChild = new ViewModel($variables, $options);
		
		$viewModelChild->setTemplate('user/user/'.$action);
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
	
	public function getUserTable()
	{
		if (!$this->userTable) {
			$this->userTable = $this->getServiceLocator()->get('User\Model\UserTable');
		}
		return $this->userTable;
	}
}