<?php
namespace User\Controller;

use Application\ConfigAwareInterface;
use User\Model\User;
use Acl\Controller\AclConsoleControllerInterface;
use Application\Toolbox\String as StringTools;
use Zend\Console\ColorInterface;
use Zend\Console\Prompt\Confirm;

class ConsoleController extends AbstractUserController implements ConfigAwareInterface, AclConsoleControllerInterface
{

	public function aclConsoleIsAllowed($action)
	{
		return true;
	}

	protected function _userList($users)
	{
		$translator = $this->getServiceLocator()->get('translator');
		
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

	public function listAction()
	{
		$type = $this->getRequest()->getParam('type', 'all');
		
		$this->_userList($this->getUserTable()->fetchAll($type));
	}

	public function searchAction()
	{
		$q = $this->getRequest()->getParam('search', 0);
		
		$this->_userList($this->getUserTable()->searchUsers($q));
	}

	public function showAction()
	{
		$translator = $this->getServiceLocator()->get('translator');
		
		$id = $this->getRequest()->getParam('id', 0);
		
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
			$this->console()->writeLine(
					$translator->translate("Created on: ") . $user->getCreationDate($translator->translate('full_text_date_time')));
			$this->console()->writeLine(
					$translator->translate("Last modified: ") . $user->getLastModificationDate($translator->translate('full_text_date_time')));
			$this->console()->writeLine(
					$translator->translate("Last login: ") . $user->getLastLoginDate($translator->translate('full_text_date_time')));
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

	public function enableAction()
	{
		$translator = $this->getServiceLocator()->get('translator');
		
		$id = $this->getRequest()->getParam('id', 0);
		$forceYes = $this->getRequest()->getParam('yes', false);
		
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
			if ($user->isActive()) {
				$this->console()->writeLine($translator->translate('User is already enabled!'));
				return;
			}
			
			$confirmed = false;
			
			if ($forceYes) {
				$confirmed = true;
			} else {
				$this->console()->writeLine($user->getDisplayName());
				$this->console()->writeLine($user->getEmail());
				
				if (Confirm::prompt($translator->translate('Do you want to enable this user? [y/n] '), $translator->translate('y'), 
						$translator->translate('n'))) {
					$confirmed = true;
				}
			}
			
			if ($confirmed) {
				if ($this->getUserTable()->enableUser($user)) {
					$this->console()->writeLine($translator->translate('User enabled.'));
					return;
				} else {
					$this->console()->writeLine($translator->translate('Operation failed.'), ColorInterface::RED);
				}
			}
		} else {
			$this->console()->writeLine($translator->translate('User not found.'));
		}
	}

	public function disableAction()
	{
		$translator = $this->getServiceLocator()->get('translator');
		
		$id = $this->getRequest()->getParam('id', 0);
		$forceYes = $this->getRequest()->getParam('yes', false);
		
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
			if (!$user->isActive()) {
				$this->console()->writeLine($translator->translate('User is already disabled!'));
				return;
			}
			
			$confirmed = false;
			
			if ($forceYes) {
				$confirmed = true;
			} else {
				$this->console()->writeLine($user->getDisplayName());
				$this->console()->writeLine($user->getEmail());
				
				if (Confirm::prompt($translator->translate('Do you want to disable this user? [y/n] '), $translator->translate('y'), 
						$translator->translate('n'))) {
					$confirmed = true;
				}
			}
			
			if ($confirmed) {
				if ($this->getUserTable()->disableUser($user)) {
					$this->console()->writeLine($translator->translate('User disabled.'));
					return;
				} else {
					$this->console()->writeLine($translator->translate('Operation failed.'), ColorInterface::RED);
				}
			}
		} else {
			$this->console()->writeLine($translator->translate('User not found.'));
		}
	}
}