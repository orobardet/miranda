<?php
namespace Costume\Controller;

use Acl\Controller\AclControllerInterface;
use Zend\View\Model\ViewModel;

class CostumeController extends AbstractCostumeController  implements AclControllerInterface
{

	public function aclIsAllowed($action,\Zend\Permissions\Acl\Acl $acl, $user)
	{
		switch ($action) {
			case "index":
				return "list_costumes";
				break;
			case "show":
				return "show_costume";
				break;
			case "add":
				return "add_costume";
				break;
			case "edit":
				return "edit_costume";
				break;
			case "delete":
				return "delete_costume";
				break;
			default:
				return false;
				break;
		}
		
		return false;
	}
	
	public function indexAction()
	{
		$this->refererUrl()->setReferer('costume-show');
		$this->refererUrl()->setReferer('costume-add');
		$this->refererUrl()->setReferer('costume-edit');
		$this->refererUrl()->setReferer('costume-delete');
		
		return new ViewModel(array(
			'costumes' => $this->getCostumeTable()->fetchAll()
		));
	}
	
	public function addAction()
	{
	
		return array(
			'form' => null,
			'cancel_url' => $this->refererUrl('costume-add')
		);
	}	
}
