<?php
namespace Costume\Controller;

use Zend\View\Model\JsonModel;
use Application\Toolbox\String as StringTools;
use Costume\Form\TagFilter;

class AdminTagController extends AdminController
{

	protected $tagTable;

	public function aclIsAllowed($action,\Zend\Permissions\Acl\Acl $acl, $user)
	{
		switch ($action) {
			case "index":
			case "edit":
			case "delete":
				return "admin_costumes_tags";
				break;
			default:
				return false;
				break;
		}
		
		return false;
	}

	public function indexAction()
	{
		return $this->getProfileViewModel('tag', array(
			'tags' => $this->getTagTable()->fetchAll()
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
				return $this->redirect()->toRoute('costume-admin/tag');
			}
		}
		
		/* @var $tag \Costume\Model\Tag */
		$tag = $this->getTagTable()->getTag($id, false);
		if (!$tag) {
			$message = StringTools::varprintf($translator->translate("Tag ID %id% does not exists."), 
					array(
						'id' => $id
					));
			if ($isAjax) {
				$ajaxVariables['status'] = 0;
				$ajaxVariables['message'] = $message;
			} else {
				$this->resultStatus()->addResultStatus($message, 'error');
				return $this->redirect()->toRoute('costume-admin/tag');
			}
		}
		
		$tagFilter = new TagFilter();
		$tagFilter->setData($this->getRequest()->getPost());
		if ($tagFilter->isValid()) {
			$values = $tagFilter->getValues();
			// On vérifie que le nom n'est pas déjà utilisé
			$existingTag = $this->getTagTable()->getTagByName($values['name'], false);
			if ($existingTag && ($existingTag->getId() != $tag->getId())) {
				if ($isAjax) {
					$ajaxVariables['status'] = 0;
					$ajaxVariables['message'] = StringTools::varprintf($translator->translate("A tag with name '%name%' already exists."), array('name' => $values['name']));
				}
			} else {
				$tag->setName($values['name']);
				$this->getTagTable()->saveTag($tag);
				$ajaxVariables['tag'] = $tag->getArrayCopy();
			}
		} else {
			if ($isAjax) {
				$ajaxVariables['status'] = 0;
				$ajaxVariables['message'] = $translator->translate('Invalid values');
				$ajaxVariables['errors'] = array();
				foreach ($tagFilter->getInvalidInput() as $error) {
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
			return $this->getProfileViewModel('tag', $viewVariables);
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
				return $this->redirect()->toRoute('costume-admin/tag');
			}
		}
	
		/* @var $tag \Costume\Model\Tag */
		$tag = $this->getTagTable()->getTag($id, false);
		if (!$tag) {
			$message = StringTools::varprintf($translator->translate("Tag ID %id% does not exists."),
					array(
						'id' => $id
					));
			if ($isAjax) {
				$ajaxVariables['status'] = 0;
				$ajaxVariables['message'] = $message;
			} else {
				$this->resultStatus()->addResultStatus($message, 'error');
				return $this->redirect()->toRoute('costume-admin/tag');
			}
		}
	
		$tagId = $tag->getId();
		$this->getCostumeTable()->removeTag($tagId);
		$this->getTagTable()->deleteTag($tagId);
		$ajaxVariables['deleted_tag_id'] = $tagId;
	
		if ($isAjax) {
			return new JsonModel($ajaxVariables);
		} else {
			return $this->getProfileViewModel('tag', $viewVariables);
		}
	}
	
	protected function getTagTable()
	{
		if (!$this->tagTable) {
			$this->tagTable = $this->getServiceLocator()->get('Costume\Model\TagTable');
		}
		return $this->tagTable;
	}
}
