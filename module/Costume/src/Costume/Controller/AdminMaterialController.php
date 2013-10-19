<?php
namespace Costume\Controller;

use Zend\View\Model\JsonModel;
use Application\Toolbox\String as StringTools;
use Costume\Form\MaterialFilter;

class AdminMaterialController extends AdminController
{

	protected $materialTable;

	public function aclIsAllowed($action,\Zend\Permissions\Acl\Acl $acl, $user)
	{
		switch ($action) {
			case "index":
			case "edit":
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
		return $this->getProfileViewModel('material', array(
			'materials' => $this->getMaterialTable()->fetchAll()
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
				return $this->redirect()->toRoute('costume-admin/material');
			}
		}
		
		/* @var $material \Costume\Model\Material */
		$material = $this->getMaterialTable()->getMaterial($id, false);
		if (!$material) {
			$message = StringTools::varprintf($translator->translate("Material ID %id% does not exists."), 
					array(
						'id' => $id
					));
			if ($isAjax) {
				$ajaxVariables['status'] = 0;
				$ajaxVariables['message'] = $message;
			} else {
				$this->resultStatus()->addResultStatus($message, 'error');
				return $this->redirect()->toRoute('costume-admin/material');
			}
		}
		
		$materialFilter = new MaterialFilter();
		$materialFilter->setData($this->getRequest()->getPost());
		if ($materialFilter->isValid()) {
			$values = $materialFilter->getValues();
			// On vérifie que le nom n'est pas déjà utilisé
			$existingMaterial = $this->getMaterialTable()->getMaterialByName($values['name'], true, false);
			if ($existingMaterial && ($existingMaterial->getId() != $material->getId())) {
				if ($isAjax) {
					$ajaxVariables['status'] = 0;
					$ajaxVariables['message'] = StringTools::varprintf($translator->translate("A material with name '%name%' already exists."), array('name' => $values['name']));
				}
			} else {
				$material->setName($values['name']);
				$this->getMaterialTable()->saveMaterial($material);
				$ajaxVariables['material'] = $material->getArrayCopy();
			}
		} else {
			if ($isAjax) {
				$ajaxVariables['status'] = 0;
				$ajaxVariables['message'] = $translator->translate('Invalid values');
				$ajaxVariables['errors'] = array();
				foreach ($materialFilter->getInvalidInput() as $error) {
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
			return $this->getProfileViewModel('material', $viewVariables);
		}
	}
	
	protected function getMaterialTable()
	{
		if (!$this->materialTable) {
			$this->materialTable = $this->getServiceLocator()->get('Costume\Model\MaterialTable');
		}
		return $this->materialTable;
	}
}
