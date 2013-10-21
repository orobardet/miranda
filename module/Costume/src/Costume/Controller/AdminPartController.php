<?php
namespace Costume\Controller;

use Zend\View\Model\JsonModel;
use Application\Toolbox\String as StringTools;
use Costume\Form\PartFilter;

class AdminPartController extends AdminController
{

	protected $typeTable;

	public function aclIsAllowed($action,\Zend\Permissions\Acl\Acl $acl, $user)
	{
		switch ($action) {
			case "index":
			case "edit":
			case "delete":
				return "admin_costumes_parts";
				break;
			default:
				return false;
				break;
		}
		
		return false;
	}

	public function indexAction()
	{
		return $this->getProfileViewModel('part', array(
			'parts' => $this->getTypeTable()->fetchAll()
		));
	}

	public function editAction()
	{
		$translator = $this->getServiceLocator()->get('translator');
		
		$isAjax = $this->requestAcceptJson();
		$ajaxVariables = array(
			'status' => 1,
			'message' => ''
		);
		$viewVariables = array();
		
		$id = (int)$this->params()->fromRoute('id', 0);
		if (!$id) {
			if ($isAjax) {
				$ajaxVariables['status'] = 0;
				$ajaxVariables['message'] = 'Invalid id $id';
			} else {
				return $this->redirect()->toRoute('costume-admin/part');
			}
		}
		
		/* @var $part \Costume\Model\Part */
		$part = $this->getTypeTable()->getType($id, false);
		if (!$part) {
			$message = StringTools::varprintf($translator->translate("Part ID %id% does not exists."), 
					array(
						'id' => $id
					));
			if ($isAjax) {
				$ajaxVariables['status'] = 0;
				$ajaxVariables['message'] = $message;
			} else {
				$this->resultStatus()->addResultStatus($message, 'error');
				return $this->redirect()->toRoute('costume-admin/part');
			}
		}
		
		$partFilter = new PartFilter();
		$partFilter->setData($this->getRequest()->getPost());
		if ($partFilter->isValid()) {
			$values = $partFilter->getValues();
			// On vérifie que le nom n'est pas déjà utilisé
			$existingPart = $this->getTypeTable()->getTypeByName($values['name'], false);
			if ($existingPart && ($existingPart->getId() != $part->getId())) {
				if ($isAjax) {
					$ajaxVariables['status'] = 0;
					$ajaxVariables['message'] = StringTools::varprintf($translator->translate("A part with name '%name%' already exists."), array('name' => $values['name']));
				}
			} else {
				$part->setName($values['name']);
				$this->getTypeTable()->saveType($part);
				$ajaxVariables['part'] = $part->getArrayCopy();
			}
		} else {
			if ($isAjax) {
				$ajaxVariables['status'] = 0;
				$ajaxVariables['message'] = $translator->translate('Invalid values');
				$ajaxVariables['errors'] = array();
				foreach ($partFilter->getInvalidInput() as $error) {
					$field = $translator->translate($error->getName());
					$ajaxVariables['errors'][$field] = array();
					foreach ($error->getMessages() as $message) {
						$ajaxVariables['errors'][$field][] = $translator->translate($message);
					}
				}
			}
		}
		
		if ($isAjax) {
			return new JsonModel($ajaxVariables);
		} else {
			return $this->getProfileViewModel('part', $viewVariables);
		}
	}
	
	public function deleteAction()
	{
		$translator = $this->getServiceLocator()->get('translator');
	
		$isAjax = $this->requestAcceptJson();
		$ajaxVariables = array(
			'status' => 1,
			'message' => ''
		);
		$viewVariables = array();
	
		$id = (int)$this->params()->fromRoute('id', 0);
		if (!$id) {
			if ($isAjax) {
				$ajaxVariables['status'] = 0;
				$ajaxVariables['message'] = 'Invalid id $id';
			} else {
				return $this->redirect()->toRoute('costume-admin/part');
			}
		}
	
		/* @var $part \Costume\Model\Part */
		$part = $this->getTypeTable()->getType($id, false);
		if (!$part) {
			$message = StringTools::varprintf($translator->translate("Part ID %id% does not exists."),
					array(
						'id' => $id
					));
			if ($isAjax) {
				$ajaxVariables['status'] = 0;
				$ajaxVariables['message'] = $message;
			} else {
				$this->resultStatus()->addResultStatus($message, 'error');
				return $this->redirect()->toRoute('costume-admin/part');
			}
		}
	
		$partId = $part->getId();
		$this->getCostumeTable()->removeType($partId);
		$this->getTypeTable()->deleteType($partId);
		$ajaxVariables['deleted_part_id'] = $partId;
	
		if ($isAjax) {
			return new JsonModel($ajaxVariables);
		} else {
			return $this->getProfileViewModel('part', $viewVariables);
		}
	}
	
	protected function getTypeTable()
	{
		if (!$this->typeTable) {
			$this->typeTable = $this->getServiceLocator()->get('Costume\Model\TypeTable');
		}
		return $this->typeTable;
	}
}
