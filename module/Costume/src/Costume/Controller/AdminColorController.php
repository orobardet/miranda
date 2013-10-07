<?php
namespace Costume\Controller;

use Zend\View\Model\JsonModel;
use Application\Toolbox\String as StringTools;
use Costume\Form\ColorFilter;
use Costume\Model\Color;

class AdminColorController extends AdminController
{

	protected $colorTable;

	public function aclIsAllowed($action,\Zend\Permissions\Acl\Acl $acl, $user)
	{
		switch ($action) {
			case "index":
			case "edit":
			case "add":
			case "delete":
			case "reorder":
				return "admin_costumes_colors";
				break;
			default:
				return false;
				break;
		}
		
		return false;
	}

	public function indexAction()
	{
		return $this->getProfileViewModel('color', array(
			'colors' => $this->getColorTable()->fetchAll()
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
				return $this->redirect()->toRoute('costume-admin/color');
			}
		}
		
		/* @var $color \Costume\Model\Color */
		$color = $this->getColorTable()->getColor($id, false);
		if (!$color) {
			$message = StringTools::varprintf($translator->translate("Color ID %id% does not exists."), 
					array(
						'id' => $id
					));
			if ($isAjax) {
				$ajaxVariables['status'] = 0;
				$ajaxVariables['message'] = $message;
			} else {
				$this->resultStatus()->addResultStatus($message, 'error');
				return $this->redirect()->toRoute('costume-admin/color');
			}
		}
		
		$colorFilter = new ColorFilter();
		$colorFilter->setData($this->getRequest()->getPost());
		if ($colorFilter->isValid()) {
			$values = $colorFilter->getValues();
			$color->setName($values['name']);
			$color->setColorCode($values['color']);
			$this->getColorTable()->saveColor($color);
			$ajaxVariables['color'] = $color->getArrayCopy();
		} else {
			if ($isAjax) {
				$ajaxVariables['status'] = 0;
				$ajaxVariables['message'] = $translator->translate('Invalid values');
				$ajaxVariables['errors'] = array();
				foreach ($colorFilter->getInvalidInput() as $error) {
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
			return $this->getProfileViewModel('color', $viewVariables);
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
				return $this->redirect()->toRoute('costume-admin/color');
			}
		}
		
		/* @var $color \Costume\Model\Color */
		$color = $this->getColorTable()->getColor($id, false);
		if (!$color) {
			$message = StringTools::varprintf($translator->translate("Color ID %id% does not exists."), 
					array(
						'id' => $id
					));
			if ($isAjax) {
				$ajaxVariables['status'] = 0;
				$ajaxVariables['message'] = $message;
			} else {
				$this->resultStatus()->addResultStatus($message, 'error');
				return $this->redirect()->toRoute('costume-admin/color');
			}
		}
		
		$colorId = $color->getId();
		$this->getCostumeTable()->removeColor($colorId);
		$this->getColorTable()->deleteColor($colorId);
		$ajaxVariables['deleted_color_id'] = $colorId;
		
		if ($isAjax) {
			return new JsonModel($ajaxVariables);
		} else {
			return $this->getProfileViewModel('color', $viewVariables);
		}
	}

	public function addAction()
	{
		$translator = $this->getServiceLocator()->get('translator');
		
		$isAjax = $this->requestAcceptJson();
		$ajaxVariables = array(
			'status' => 1,
			'message' => ''
		);
		$viewVariables = array();
		
		$colorFilter = new ColorFilter();
		$colorFilter->setData($this->getRequest()->getPost());
		if ($colorFilter->isValid()) {
			$values = $colorFilter->getValues();
			$color = new Color();
			$color->setName($values['name']);
			$color->setColorCode($values['color']);
			$this->getColorTable()->saveColor($color);
			$ajaxVariables['color'] = $color->getArrayCopy();
		} else {
			if ($isAjax) {
				$ajaxVariables['status'] = 0;
				$ajaxVariables['message'] = $translator->translate('Invalid values');
				$ajaxVariables['errors'] = array();
				foreach ($colorFilter->getInvalidInput() as $error) {
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
			return $this->getProfileViewModel('color', $viewVariables);
		}
	}
	
	public function reorderAction()
	{
		$isAjax = $this->requestAcceptJson();
		$ajaxVariables = array(
			'status' => 1,
			'message' => ''
		);
		$viewVariables = array();
		
		$string_order = $this->getRequest()->getPost('order', '[]');
		$order = @json_decode($string_order);
		if ($order && is_array($order) && count($order)) {
			$this->getColorTable()->reorderColors($order);
		}
		
		if ($isAjax) {
			return new JsonModel($ajaxVariables);
		} else {
			return $this->getProfileViewModel('color', $viewVariables);
		}
	}
	
	protected function getColorTable()
	{
		if (!$this->colorTable) {
			$this->colorTable = $this->getServiceLocator()->get('Costume\Model\ColorTable');
		}
		return $this->colorTable;
	}
}
