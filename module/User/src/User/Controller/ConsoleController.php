<?php
namespace User\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Application\ConfigAwareInterface;
use Zend\Config\Config as ZendConfig;
use User\Model\User;
use Acl\Controller\AclConsoleControllerInterface;
use Application\Toolbox\String as StringTools;
use Zend\Console\ColorInterface;

class ConsoleController extends AbstractActionController implements ConfigAwareInterface, AclConsoleControllerInterface
{

	protected $config;

	protected $userTable;

	public function setConfig(ZendConfig $config)
	{
		$this->config = $config;
	}

	public function aclConsoleIsAllowed($action)
	{
		return true;
	}

	public function listAction()
	{
		$translator = $this->getServiceLocator()->get('translator');
		
		$users = $this->getUserTable()->fetchAll();
		
		if (count($users)) {
			$userTable = array();
			foreach ($users as $user) {
				$userTable[] = array(
					$user->getId(),
					$user->getLastname(),
					$user->getFirstname(),
					$user->getEmail(),
					$user->isActive() ? $translator->translate('Yes') : $translator->translate('No')
				);
			}
			$this->console()->writeTable($userTable, 
					array(
						$translator->translate('ID'),
						$translator->translate('Lastname'),
						$translator->translate('Firstname'),
						$translator->translate('Email'),
						$translator->translate('Enabled?')
					));
		} else {
			$this->console()->writeLine($translator->translate('No users.'));
		}
	}

	public function showAction()
	{
		$translator = $this->getServiceLocator()->get('translator');
		
		$id = $this->getRequest()->getParam('id');
		
		$user = null;
		try {
			if (filter_var($id, FILTER_VALIDATE_INT)) {
				$user = $this->getUserTable()->getUser($id);
			} else {
				$user = $this->getUserTable()->getUserByEmail($id);
			}
		} catch (\Exception $e) {
		}
		
		if ($user) {
			$this->console()->writeLine($user->getDisplayName());
			$this->console()->writeLine(StringTools::mb_str_pad('', strlen($user->getDisplayName()), '-'));
			if ($user->isActive()) {
				$this->console()->writeLine($translator->translate('Account enabled'), ColorInterface::GREEN);
			} else {
				$this->console()->writeLine($translator->translate('Account disabled'), ColorInterface::RED);
			}
			$this->console()->writeLine();
			$this->console()->writeLine($translator->translate("Created on: ") . $user->getCreationDate(
					$translator->translate('full_text_date_time')));
			$this->console()->writeLine(
					$translator->translate("Last modified: ") . $user->getLastModificationDate($translator->translate('full_text_date_time')));
			$this->console()->writeLine($translator->translate("Last login: ") .
					 $user->getLastLoginDate($translator->translate('full_text_date_time')));
			$this->console()->writeLine(
					$translator->translate("Last activity: ") . $user->getLastActivityDate($translator->translate('full_text_date_time')));
			$this->console()->writeLine();
			$this->console()->writeLine($translator->translate("Email: ") . $user->getEmail());
			$this->console()->writeLine();
			$this->console()->writeLine($translator->translate('Roles: '));
			$user_roles = $user->getRoles();
			$all_roles = $this->getServiceLocator()->get('Acl\Model\RoleTable')->fetchAll();
			if (count($user_roles)) {
				foreach ($all_roles as $role) {
					if (in_array($role->getId(), $user_roles)) {
						$this->console()->writeLine(' - ' . $role->getName());
					}
				}
			} else {
				$this->console()->writeLine($translator->translate('None'));
			}
		} else {
			$this->console()->writeLine($translator->translate('User not found.'));
		}
	}

	public function getUserTable()
	{
		if (!$this->userTable) {
			$this->userTable = $this->getServiceLocator()->get('User\Model\UserTable');
		}
		return $this->userTable;
	}
}