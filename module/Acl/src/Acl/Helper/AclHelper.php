<?php
namespace Acl\Helper;

use Zend\Permissions\Acl\Acl as ZendAcl;

class AclHelper
{

	/**
	 * Vérifie si un utilisateur/rôle connecté à les droits ou non
	 *
	 * @param ZendAcl $acl ACL de l'application, préremplis et utilisables
	 * @param string|Zend\Permissions\Acl\Role\RoleInterface $user Utilisateur/rôle dont dont veut tester les droits
	 * @param string|string[]|array[] $rights Droits demandés
	 *       
	 * @return bool true si les droits sont autorisés, false sinon
	 */
	public static function isAllowed(ZendAcl $acl, $user, $rights)
	{
		// TODO: vérifier que chaque droit est bien présent dans les ACL, sinon c'est false pour le droit
		

		// TODO: Reconstruire un double tableau associatif pour $rights en fonction de ce qui est passé
		

		// TODO: Calculer l'autorisation en parcourant le tableau $rights pour tous les droits demandé 
		return $acl->isAllowed($user, $rights);
	}
}
