<?php
namespace Costume\Controller;

use Zend\View\Model\ViewModel;
use Acl\Controller\AclControllerInterface;

class AdminController extends AbstractCostumeController implements AclControllerInterface
{
	public function aclIsAllowed($action, \Zend\Permissions\Acl\Acl $acl, $user)
	{
		switch ($action) {
			case "index":
				return "admin_costumes";
				break;
			default:
				return false;
				break;
		}
		
		return false;
	}

	protected function getProfileViewModel($action, $variables = null, $options = null)
	{
		if (!is_array($variables)) {
			$variables = array();
		}
		$viewModel = new ViewModel($variables + array(
			'admin_action' => $action
		), $options);
		$viewModel->setTemplate('costume/admin/admin-layout');
		
		$viewModelChild = new ViewModel($variables, $options);
		
		$viewModelChild->setTemplate('costume/admin/' . $action);
		$viewModel->addChild($viewModelChild);
		
		return $viewModel;
	}
	
	public function indexAction()
	{
		return $this->redirect()->toRoute('costume-admin/color');
	}

}
