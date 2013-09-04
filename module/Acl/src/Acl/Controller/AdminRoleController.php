<?php
namespace Acl\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\ConfigAwareInterface;
use Zend\Config\Config as ZendConfig;
use Acl\Model\Role;
use Acl\Controller\AclControllerInterface;

class AdminRoleController extends AbstractActionController implements ConfigAwareInterface, AclControllerInterface
{

	protected $config;

	protected $roleTable;

	public function setConfig (ZendConfig $config)
	{
		$this->config = $config;
	}
	
	public function aclIsAllowed($action, \Zend\Permissions\Acl\Acl $acl, $user)
	{
		switch ($action) {
			case "index":
				return "admin_list_roles";
				break;
			case "show":
				return "admin_show_role";
				break;
			case "add":
				return "admin_add_role";
				break;
			case "edit":
				return "admin_edit_role";
				break;
			case "delete":
				return "admin_delete_role";
				break;
			default:
				return false;
				break;
		}
		
		return false;
	}
	
	public function indexAction ()
	{
		return new ViewModel(array(
			'roles' => $this->getRoleTable()->fetchAll()
		));
	}
	
	public function showAction ()
	{
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/role');
        }
        
        try {
            $role = $this->getRoleTable()->getRole($id);
        }
        catch (\Exception $ex) {
            return $this->redirect()->toRoute('admin/role');
        }
        
		return new ViewModel(array(
			'role' => $role,
			'return_url' => $this->url()->fromRoute('admin/role'),
			'all_rights' => $this->getServiceLocator()->get('Acl\Model\RightsManager')->getGroupedRights(),
		));
	}
	
	public function addAction ()
	{
		$checked_rights = array();
		
		$form = $this->getServiceLocator()->get('Acl\Form\Role');
		$form->prepare();
		$form->setAttribute('action', $this->url()->fromRoute('admin/role', array(
			'action' => 'add'
		)));
		$form->setAttribute('method', 'post');
		$form->get('submit')->setValue($this->getServiceLocator()->get('translator')->translate('Add'));
		
		$request = $this->getRequest();
		if ($request->isPost()) {
			$form->setData($request->getPost());
			$checked_rights = $request->getPost('rights', array());
		
			if ($form->isValid()) {
				$role = new Role();
		
				$data = $form->getData();
				$role->exchangeArray($form->getData());
				$this->getRoleTable()->saverole($role, true);
				 
				return $this->redirect()->toRoute('admin/role');
			}
		}
		
		return array(
			'form' => $form,
			'cancel_url' => $this->url()->fromRoute('admin/role'),
			'all_rights' => $this->getServiceLocator()->get('Acl\Model\RightsManager')->getGroupedRights(),
			'checked_rights' => $checked_rights
		);
	}
	
	public function editAction ()
	{
		$checked_rights = array();
		
		$id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/role', array(
                'action' => 'add'
            ));
        }

        try {
            $role = $this->getRoleTable()->getRole($id);
			$checked_rights = $role->getRights();
        }
        catch (\Exception $ex) {
            return $this->redirect()->toRoute('admin/role', array(
                'action' => 'index'
            ));
        }

		$form = $this->getServiceLocator()->get('Acl\Form\Role');
		$form->prepare();
		$form->setAttribute('action', $this->url()->fromRoute('admin/role', array(
			'action' => 'edit',
			'id' => $id
		)));
		$form->setAttribute('method', 'post');
		$form->get('submit')->setValue($this->getServiceLocator()->get('translator')->translate('Edit'));
        $form->bind($role);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        if ($request->isPost()) {
			$checked_rights = $request->getPost('rights', array());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getRoleTable()->saveRole($role);

                return $this->redirect()->toRoute('admin/role');
            }
        }

        return array(
            'id' => $id,
			'cancel_url' => $this->url()->fromRoute('admin/role'),
        	'form' => $form,
			'all_rights' => $this->getServiceLocator()->get('Acl\Model\RightsManager')->getGroupedRights(),
			'checked_rights' => $checked_rights
        );
	}
	
	public function deleteAction ()
	{
		$id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admin/role');
        }
        
        // TODO: Vérifier qu'aucun utilisateur n'a ce rôle avant de le supprimer
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'no');
            
            if ($del == 'yes') {
                $id = (int) $request->getPost('id');
                $this->getRoleTable()->deleteRole($id);
            }

            return $this->redirect()->toRoute('admin/role');
        }

        return array(
            'id'    => $id,
            'role' => $this->getRoleTable()->getRole($id)
        );	
	}
	
	public function getRoleTable ()
	{
		if (!$this->roleTable) {
			$this->roleTable = $this->getServiceLocator()->get('Acl\Model\RoleTable');
		}
		return $this->roleTable;
	}
}