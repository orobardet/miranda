<?php
namespace Costume\Controller;

use Acl\Controller\AclControllerInterface;
use Zend\View\Model\ViewModel;

class CostumeController extends AbstractCostumeController implements AclControllerInterface
{

	public function aclIsAllowed($action, \Zend\Permissions\Acl\Acl $acl, $user)
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
		
		$page = (int)$this->getRequest()->getQuery('page', 1);
		
		$costumes = $this->getCostumeTable()
			->fetchAll(true)
			->setItemCountPerPage($this->itemsPerPage()->getItemsPerPage('costume-list', 10))
			->setCurrentPageNumber($page)
			->setPageRange(10);
		
		return new ViewModel(
				array(
					'page' => $page,
					'costumes' => $costumes,
					'get_parameters' => $this->getRequest()->getQuery()->toArray()
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
