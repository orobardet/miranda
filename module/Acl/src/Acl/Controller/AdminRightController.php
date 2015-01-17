<?php
namespace Acl\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\ConfigAwareInterface;
use Zend\Config\Config as ZendConfig;
use Acl\Controller\AclControllerInterface;

class AdminRightController extends AbstractActionController implements ConfigAwareInterface, AclControllerInterface
{

	protected $config;

	public function setConfig(ZendConfig $config)
	{
		$this->config = $config;
	}

	public function aclIsAllowed($action, \Zend\Permissions\Acl\Acl $acl, $user)
	{
		switch ($action) {
			case "index":
				return "admin_list_rights";
				break;
			case "update":
				return "admin_update_acl";
				break;
			default:
				return false;
				break;
		}
		
		return false;
	}

	public function indexAction()
	{
		return new ViewModel(array(
			'rights' => $this->getServiceLocator()->get('Acl\Model\RightsManager')->getGroupedRights()
		));
	}

	public function updateAction()
	{
		$this->getServiceLocator()->get('Acl\Model\AclManager')->aclNeedsUpdate();
		$this->resultStatus()->addResultStatus(
				$this->getServiceLocator()->get('translator')->translate("ACL updated"), 'success');
		return $this->redirect()->toRoute('admin/right');
	}
}