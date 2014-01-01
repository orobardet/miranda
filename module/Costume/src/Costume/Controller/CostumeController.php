<?php
namespace Costume\Controller;

use Acl\Controller\AclControllerInterface;
use Zend\View\Model\ViewModel;
use Application\Toolbox\String as StringTools;
use Costume\Model\Costume;

class CostumeController extends AbstractCostumeController implements AclControllerInterface
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
		
		$page = (int)$this->getRequest()->getQuery('page', 1);
		
		$costumes = $this->getCostumeTable()
			->fetchAll(true)
			->setItemCountPerPage($this->itemsPerPage()
			->getItemsPerPage('costume-list', 10))
			->setCurrentPageNumber($page)
			->setPageRange(10);
		
		return new ViewModel(
				array(
					'page' => $page,
					'costumes' => $costumes,
					'get_parameters' => $this->getRequest()->getQuery()->toArray()
				));
	}

	public function showAction()
	{
		$id = (int)$this->params()->fromRoute('id', 0);
		if (!$id) {
			return $this->redirect()->toRoute('costume', array(
				'action' => 'add'
			));
		}
		
		$costume = $this->getCostumeTable()->getCostume($id, false);
		if (!$costume) {
			$this->resultStatus()->addResultStatus(
					StringTools::varprintf($this->getServiceLocator()->get('translator')->translate("Costume ID %id% does not exists."), 
							array(
								'id' => $id
							)), 'error');
			return $this->redirect()->toRoute('costume');
		}
		
		$this->refererUrl()->setReferer('costume-edit');
		$this->refererUrl()->setReferer('costume-delete');
		
		return new ViewModel(array(
			'costume' => $costume,
			'return_url' => $this->refererUrl('costume-show')
		));
	}

	public function addAction()
	{
		$defaultData = array(
			'gender' => 'None'
		);
		
		$form = $this->getServiceLocator()->get('Costume\Form\Costume');
		$form->setAttribute('action', $this->url()->fromRoute('costume', array(
			'action' => 'add'
		)));
		$form->setAttribute('method', 'post');
		$form->get('submit')->setValue($this->getServiceLocator()->get('translator')->translate('Add'));
		
		$request = $this->getRequest();
		if ($request->isPost()) {
			$form->setData($request->getPost());
				
			if ($form->isValid()) {
				$costume = new Costume();
				
				$costume->exchangeArray($form->getData());
				// TODO: faire un Costume\Hydrator qui va peupler un objet costume passé en paramète avec le contenu d'un tableau en paramètre aussi.
				// Fera les rapprochements dans la BDD avec les couleurs/matériaux/tag... et les ajoutant éventuellement (comme fait lors de l'import)
 				$data = $form->getData();
// 				$this->getUserTable()->saveUser($user, true);
				print_r($form->getData());
				die;
				//return $this->redirect()->toRoute('costume');
			}
		} else {
			$form->setData($defaultData);
		}
		
		return array(
			'form' => $form,
			'cancel_url' => $this->refererUrl('costume-add')
		);
	}

	public function editAction()
	{
		return array(
			'form' => null,
			'cancel_url' => $this->refererUrl('costume-edit')
		);
	}

	public function deleteAction()
	{
		return array(
			'form' => null,
			'cancel_url' => $this->refererUrl('costume-delete')
		);
	}
}
