<?php
namespace Acl\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\Permissions\Acl\Acl as ZendAcl;
use Acl\Helper\AclHelper;

class Acl extends AbstractHelper
{

	/**
	 * Objet de session
	 *
	 * @var Zend\Permissions\Acl\Acl
	 */
	protected $acl;

	public function __construct(ZendAcl $acl)
	{
		$this->acl = $acl;
	}

	/**
	 * Retourne l'objet ACL
	 *
	 * @return \Acl\Controller\Plugin\Zend\Permissions\Acl\Acl
	 */
	public function getAcl()
	{
		return $this->acl;
	}

	/**
	 * Retourne l'utilisateur connecté sous forme d'une chaine utilisable directement
	 * comme rôle dans les ACL de l'application
	 *
	 * @return string
	 */
	public function getCurrentUser()
	{
		return 'Miranda\CurrentUser';
	}

	/**
	 * Vérifie si l'utilisateur connecté à les droits ou non
	 *
	 * @see AclHelper::isAllowed()
	 *
	 * @param string|string[]|array[] $rights Droits demandés
	 *       
	 * @return bool true si autorisé, false sinon
	 */
	public function isAllowed($rights)
	{
		return AclHelper::isAllowed($this->acl, $this->getCurrentUser(), $rights);
	}
}
