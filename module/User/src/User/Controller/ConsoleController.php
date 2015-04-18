<?php
namespace User\Controller;

use Application\ConfigAwareInterface;
use User\Model\User;
use Acl\Controller\AclConsoleControllerInterface;
use Application\Toolbox\String as StringTools;
use Zend\Console\ColorInterface;
use Zend\Console\Prompt\Confirm;
use Zend\Console\Prompt\Line;
use Application\Console\Prompt\Password as PromptPassword;

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

	public function changepasswordAction()
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
			$this->console()->writeLine($user->getDisplayName());
			$this->console()->writeLine($user->getEmail());
			
			$password = PromptPassword::prompt($translator->translate('Input new password (or empty to abort): '), true);
			if (trim($password) == '') {
				return;
			}
			$passwordConfirm = PromptPassword::prompt($translator->translate('Confirm new password: '), true);
			
			if ($password != $passwordConfirm) {
				$this->console()->writeLine($translator->translate('Passwords does not match.'));
				return;
			}
			
			$user->setPassword($password, $this->getServiceLocator()->get('Miranda\Service\AuthBCrypt'));
			$this->getUserTable()->saveUser($user, true);
			
			$this->console()->writeLine($translator->translate('Password changed.'));
		} else {
			$this->console()->writeLine($translator->translate('User not found.'));
		}
	}

	public function adduserAction()
	{
		$translator = $this->getServiceLocator()->get('translator');
		
		$email = $this->getRequest()->getParam('email', null);
		$firstname = $this->getRequest()->getParam('firstname', null);
		$lastname = $this->getRequest()->getParam('lastname', null);
		$role = $this->getRequest()->getParam('role', null);
		
		$roleIds = array(); 
		if ($role) {
			$roleNames = explode(',', $role);
			$roleNames = array_filter($roleNames, function ($value) {
				return trim($value) != ''; 
			});
			if (count($roleNames)) {
				/* @var $roleTable \Acl\Model\RoleTable */
				$roleTable = $this->getServiceLocator()->get('Acl\Model\RoleTable');
				foreach ($roleNames as $roleName) {
					$role = $roleTable->getRoleByName($roleName, false);
					if ($role) {
						$roleIds[] = $role->getId();
					} else {
						$this->console()->writeLine($translator->translate("'$roleName' is not a known role, ignoring."), ColorInterface::YELLOW);
						
					}
				} 	
			}				
		}
		
		if (!$email || (trim($email) == '')) {
			$email = Line::prompt($translator->translate('User email: '));
		}
		
		$validator = new \Zend\Validator\EmailAddress([
			'allow' => \Zend\Validator\Hostname::ALLOW_DNS,
			'useMxCheck' => true,
			'useDeepMxCheck' => true
		]);
		if (!$validator->isValid($email)) {
			$this->console()->writeLine($translator->translate('Email address is invalid.'));
			return;
		}
		
		if ($this->getUserTable()->getUserByEmail($email, false)) {
			$this->console()->writeLine($translator->translate('A user account already exists with this email.'), ColorInterface::RED);
			return;
		}
		
		if (!$firstname || (trim($firstname) == '')) {
			$firstname = Line::prompt($translator->translate('Firstname: '), true);
		}
		
		if (!$lastname || (trim($lastname) == '')) {
			$lastname = Line::prompt($translator->translate('Lastname: '), true);
		}
		
		$this->dbTransaction()->begin();
		try {
			$user = new User();
			
			$user->setEmail($email);
			$user->setFirstname($firstname);
			$user->setLastname($lastname);
			$user->setPassword(sha1(uniqid()), $this->getServiceLocator()->get('Miranda\Service\AuthBCrypt'));
			$user->setActive(true);
			if (count($roleIds)) {
				$user->setRoles($roleIds);
			}
			$user->createRegistrationToken();
			$this->getUserTable()->saveUser($user, true);
			
			$this->sendAccountCreationMail($user);
			
			$this->dbTransaction()->commit();
			
			$this->console()->writeLine($translator->translate('User created.'), ColorInterface::GREEN);
		} catch (\Exception $ex) {
			$this->dbTransaction()->rollback();
			throw $ex;
		}				
	}
}