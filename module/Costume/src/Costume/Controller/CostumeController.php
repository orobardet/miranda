<?php
namespace Costume\Controller;

use Acl\Controller\AclControllerInterface;
use Zend\View\Model\ViewModel;
use Application\Toolbox\String as StringTools;
use Costume\Model\Costume;
use Zend\View\Model\JsonModel;
use Zend\Stdlib\Parameters;
use Costume\Controller\InputFilter\IndexFilter;

class CostumeController extends AbstractCostumeController implements AclControllerInterface
{

	public function aclIsAllowed($action,\Zend\Permissions\Acl\Acl $acl, $user)
	{
		switch ($action) {
			case "index":
				return "list_costumes";
				break;
			case "searchcmpl":
				return "list_costumes";
				break;
			case "show":
				return "show_costume";
				break;
			case "add":
				return "add_costume";
				break;
			case "edit":
			case "picture":
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
		
		$defaults = array(
			'page' => 1,
			'sort' => 'code',
			'direction' => 'up'
		);
		
		$inputFilter = $this->getIndexInputFilter($defaults);
		$inputFilter->setData((array)$this->getRequest()->getQuery())->validate();
		
		$filteredInput = $inputFilter->getValues();
		$indexParams = new Parameters(array_merge($defaults, $filteredInput));
		$linkParams = new Parameters(array_merge((array)$this->getRequest()->getQuery(), $filteredInput));
		
		$page = (int)$indexParams->get('page', 1);
		$sortStatement = $indexParams->get('sort').($indexParams->get('direction')=='down'?' DESC':' ASC');

		$doSearch = false;
		$extendedSearch = false;
		$form = $this->getServiceLocator()->get('Costume\Form\Search');
		$form->setData($linkParams);
		if ($form->isValid()) {
			$searchData = $form->getSearchData();
			if (count($searchData)) {
				$doSearch = true;
				$extendedSearchData = $searchData;
				unset($extendedSearchData['q']);
				if (count($extendedSearchData)) {
					$extendedSearch = true;
				}
				
				array_walk($searchData, function(&$value) {
					if ($value == '##none##') {
						$value = null;
					}
				});
			}
		}		
		
		if ($doSearch) {
			$costumes = $this->getCostumeTable()->search($searchData, true, $sortStatement);
		} else {
			$costumes = $this->getCostumeTable()->fetchAll(true, $sortStatement);
		}
		
		$costumes->setItemCountPerPage($this->itemsPerPage()
			->getItemsPerPage('costume-list', 25))
			->setCurrentPageNumber($page)
			->setPageRange(10); // Nombre max de lien dans le pager
		
		return new ViewModel(
				array(
					'page' => $page,
					'costumes' => $costumes,
					'get_parameters' => $this->getRequest()->getQuery()->toArray(),
					'link_params' => $linkParams,
					'sort' => $indexParams->get('sort'),
					'direction' => $indexParams->get('direction'),
					'form' => $form,
					'has_search' => $doSearch,
					'has_extended_search' => $extendedSearch
				));
	}
	
	public function searchcmplAction()
	{
		$results = [];
		
		$q = $this->getRequest()->getQuery('q', false);
		if ($q) {
			$costumes = $this->getLightCostumeTable()->search(array('q' => $q), false, null, $this->getRequest()->getQuery('max', 10));
			if (count($costumes)) {
				/* @var $costume \Costume\Model\Costume  */
				foreach ($costumes as $costume) {
					$results[] = $costume->getArrayCopy();
				}
			}
		}
		
		return new JsonModel(array(
			'results' => $results
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
		$this->refererUrl()->setReferer('costume-picture');
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
				
				$referer = $this->refererUrl('costume-add');
				if ($referer) {
					return $this->redirect()->toUrl($referer);
				} else {
					return $this->redirect()->toRoute('costume', array(
						'action' => 'show',
						'id' => $costume->getId()
					));
				}
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
				$formData = $form->getData();
				if (array_key_exists('id', $formData)) {
					unset($formData['id']);
				}
				$costume = $costumeHydrator->hydrate($formData, $costume);
				$costume->setId($costumeId);
				
				$this->dbTransaction()->begin();
				$this->getCostumeTable()->saveCostume($costume);
				$this->dbTransaction()->commit();
				
				$this->resultStatus()->addResultStatus(
						StringTools::varprintf($this->getServiceLocator()->get('translator')->translate("Costume '%label%' edited."), 
								array(
									'label' => $costume->getLabel()
								)), "success");
				
				$referer = $this->refererUrl('costume-edit');
				if ($referer) {
					return $this->redirect()->toUrl($referer);
				} else {
					return $this->redirect()->toRoute('costume', array(
						'action' => 'show',
						'id' => $costumeId
					));
				}
			}
		} else {
			$form->setData(array_merge($defaultData, $costumeHydrator->extract($costume)));
		}
		
		return array(
			'form' => $form,
			'cancel_url' => $this->refererUrl('costume-edit')
		);
	}

	public function pictureAction()
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
		
		$max_upload_size = min(StringTools::return_bytes(ini_get('post_max_size')), StringTools::return_bytes(ini_get('upload_max_filesize')));
		
		$form = $this->getServiceLocator()->get('Costume\Form\Picture');
		$form->setAttribute('action', $this->url()->fromRoute('costume', array(
			'action' => 'picture',
			'id' => $costumeId
		)));
		$form->setAttribute('method', 'post');
		
		$request = $this->getRequest();
		$postData = array_merge_recursive($request->getPost()->toArray(), $request->getFiles()->toArray());
		if ($request->isPost()) {
			$form->setData($postData);
			if ($form->isValid()) {
				$data = $form->getData();
				
				$this->dbTransaction()->begin();
				if (array_key_exists('picture_file', $data) && is_array($data['picture_file']) && array_key_exists('tmp_name', $data['picture_file']) &&
						 ($data['picture_file']['tmp_name'] != '')) {
					$pictureSource = $data['picture_file']['tmp_name'];
					
					$pictureFilter = new \Costume\Filter\File\CostumePicture(array(
						'max_width' => 1000,
						'max_height' => 1000
					));
					$pictureSource = $pictureFilter->filter($pictureSource);
					
					$picture = $this->getCostumePictureTable()->pictureFactory();
					$picture->setPath(basename($pictureSource));
					if ($picture->copyFromFile($pictureSource)) {
						$costume->setPictures(array(
							$picture
						));
					}
					
					unlink($pictureSource);
				} else {
					$costume->setPictures(array());
				}
				
				$this->getCostumeTable()->saveCostume($costume);
				$this->dbTransaction()->commit();
				
				$this->resultStatus()->addResultStatus(
						StringTools::varprintf($this->getServiceLocator()->get('translator')->translate("Picture changed for costume '%label%'."), 
								array(
									'label' => $costume->getLabel()
								)), "success");
				
				$referer = $this->refererUrl('costume-edit');
				
				if (array_key_exists('isAjax', $postData) && !empty($postData['isAjax'])) {
					if ($referer) {
						return new JsonModel(array(
							'status' => true,
							'redirect' => $referer
						));
					} else {
						return new JsonModel(
								array(
									'status' => true,
									'redirect' => $this->url()->fromRoute('costume', 
											array(
												'action' => 'show',
												'id' => $costumeId
											))
								));
					}
				} else {
					if ($referer) {
						return $this->redirect()->toUrl($referer);
					} else {
						return $this->redirect()->toRoute('costume', array(
							'action' => 'show',
							'id' => $costumeId
						));
					}
				}
			}
		}
		
		if (array_key_exists('isAjax', $postData) && !empty($postData['isAjax'])) {
			return new JsonModel(array(
				'status' => false,
				'errors' => $form->getMessages()
			));
		} else {
			return array(
				'costume' => $costume,
				'form' => $form,
				'cancel_url' => $this->refererUrl('costume-picture'),
				'max_upload_size' => $max_upload_size
			);
		}
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

	/**
	 *
	 * @return \Zend\InputFilter\InputFilter
	 */
	protected function getIndexInputFilter($defaults = array())
	{
		return new IndexFilter($defaults);
	}
}
