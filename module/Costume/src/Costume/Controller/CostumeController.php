<?php
namespace Costume\Controller;

use Acl\Controller\AclControllerInterface;
use Zend\View\Model\ViewModel;
use Application\Toolbox\String as StringTools;
use Costume\Model\Costume;

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
		$this->refererUrl()->setReferer('costume-delete', null, true);
		
		return new ViewModel(array(
			'costume' => $costume,
			'return_url' => $this->refererUrl('costume-show')
		));
	}

	public function addAction()
	{
		$defaultData = array(
			'gender' => ''
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
				$costumeHydrator = $this->getServiceLocator()->get('Costume\Hydrator\CostumeForm');
				
				$costume = $costumeHydrator->hydrate($form->getData(), new Costume());
				
				$this->dbTransaction()->begin();
				$this->getCostumeTable()->saveCostume($costume);
				$this->dbTransaction()->commit();
				
				$this->resultStatus()->addResultStatus(
						StringTools::varprintf($this->getServiceLocator()->get('translator')->translate("Costume '%label%' added."), 
								array(
									'label' => $costume->getLabel()
								)), "success");
				
				return $this->redirect()->toUrl($this->refererUrl('costume-add'));
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
		$costumeId = (int)$this->params()->fromRoute('id', 0);
		if (!$costumeId) {
			$this->resultStatus()->addResultStatus(
					StringTools::varprintf($this->getServiceLocator()->get('translator')->translate("Costume ID %id% does not exists."), 
							array(
								'id' => $costumeId
							)), 'error');
			return $this->redirect()->toRoute('costume');
		}
		
		$costume = $this->getCostumeTable()->getCostume($costumeId, false);
		if (!$costume) {
			$this->resultStatus()->addResultStatus(
					StringTools::varprintf($this->getServiceLocator()->get('translator')->translate("Costume ID %id% does not exists."), 
							array(
								'id' => $costumeId
							)), 'error');
			return $this->redirect()->toRoute('costume');
		}
		
		$defaultData = array(
			'gender' => ''
		);
		
		$form = $this->getServiceLocator()->get('Costume\Form\Costume');
		$form->getInputFilter()->setCostumeId($costumeId);
		$form->setAttribute('action', $this->url()->fromRoute('costume', array(
			'action' => 'edit',
			'id' => $costumeId
		)));
		$form->setAttribute('method', 'post');
		$form->get('submit')->setValue($this->getServiceLocator()->get('translator')->translate('Edit'));
		
		$costumeHydrator = $this->getServiceLocator()->get('Costume\Hydrator\CostumeForm');
		
		$request = $this->getRequest();
		if ($request->isPost()) {
			$form->setData($request->getPost());
			
			if ($form->isValid()) {
				$costume = $costumeHydrator->hydrate($form->getData(), $costume);
				$costume->setId($costumeId);
				
				$this->dbTransaction()->begin();
				$this->getCostumeTable()->saveCostume($costume);
				$this->dbTransaction()->commit();
				
				$this->resultStatus()->addResultStatus(
						StringTools::varprintf($this->getServiceLocator()->get('translator')->translate("Costume '%label%' edited."), 
								array(
									'label' => $costume->getLabel()
								)), "success");
				
				return $this->redirect()->toUrl($this->refererUrl('costume-edit'));
			}
		} else {
			$form->setData(array_merge($defaultData, $costumeHydrator->extract($costume)));
		}
		
		return array(
			'form' => $form,
			'cancel_url' => $this->refererUrl('costume-edit')
		);
	}

	public function deleteAction()
	{
		$id = (int)$this->params()->fromRoute('id', 0);
		if (!$id) {
			return $this->redirect()->toRoute('costume');
		}
		$costume = $this->getCostumeTable()->getCostume($id, false);
		if (!$costume) {
			return $this->redirect()->toRoute('costume');
		}
		
		$request = $this->getRequest();
		if ($request->isPost()) {
			$del = $request->getPost('del', 'no');
			$return_url = $this->refererUrl('costume-delete');
			
			if ($del == 'yes') {
				$id = (int)$request->getPost('id');
				$this->getCostumeTable()->deleteCostume($id);
				$this->resultStatus()->addResultStatus(
						StringTools::varprintf($this->getServiceLocator()->get('translator')->translate("Costume '%label%' deleted."), 
								array(
									'label' => $costume->getLabel()
								)), "success");
				
				$return_url = $this->refererUrl()->getReferer('costume-delete', true);
			}
			
			if ($return_url) {
				return $this->redirect()->toUrl($return_url);
			} else {
				return $this->redirect()->toRoute('costume');
			}
		}
		
		return array(
			'form' => null,
			'costume' => $costume,
			'cancel_url' => $this->refererUrl('costume-delete')
		);
	}
}
