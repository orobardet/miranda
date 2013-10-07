<?php
namespace Costume\Controller;

class AdminMaterialController extends AdminController
{
	public function aclIsAllowed($action, \Zend\Permissions\Acl\Acl $acl, $user)
	{
		switch ($action) {
			case "index":
			case "add":
			case "edit":
			case "delete":
				return "admin_costumes_materials";
				break;
			default:
				return false;
				break;
		}
		
		return false;
	}

	public function indexAction()
	{
		
		return $this->getProfileViewModel('material');
	}

}
