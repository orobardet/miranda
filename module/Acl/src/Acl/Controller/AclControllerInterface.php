<?php
namespace Acl\Controller;

interface AclControllerInterface
{
	public function aclIsAllowed($action, \Zend\Permissions\Acl\Acl $acl, $user);
}

?>