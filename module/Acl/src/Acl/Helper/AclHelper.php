<?php
namespace Acl\Helper;

use Zend\Permissions\Acl\Acl as ZendAcl;
use Zend\Permissions\Acl\Role\RoleInterface;

class AclHelper
{

	/**
	 * Vérifie si un utilisateur/rôle connecté à les droits ou non
	 *
	 * $rights
	 * =======
	 *
	 * Le paramètre $rights contient la combinaison de droits demandée.
	 *
	 * Il peut prendre plusieurs formes.
	 *
	 * Forme complète
	 * --------------
	 *
	 * Sa forme complète est un tableau à 2 dimensions.
	 * - Les premiers niveaux du tableau seront combinés en OU
	 * - Les seconds niveaux du tableau seront combinés en AND
	 * - La valeur finale est une chaine ou un objet Zend\Permissions\Acl\Role\RoleInterface
	 * représentant un droit existant et demandé
	 *
	 * **Exemple :**
	 * La tableau :
	 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	 * array(
	 * array(
	 * 'admin_list_users',
	 * 'admin_show_user'
	 * ),
	 * array(
	 * 'admin_list_roles',
	 * 'admin_show_role'
	 * )
	 * )
	 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	 * revient à demandé les droits
	 * (admin_list_users ET admin_show_user) OU (admin_list_roles ET admin_show_roles)
	 *
	 * Formes raccourcies
	 * ------------------
	 *
	 * Si on n'a pas besoin de définir une structure de droits complète, des formes simplifiées sont acceptées :
	 *
	 * **Un seul droit**
	 * Passer directement une chaine correspond à tester ce droit uniquement.
	 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	 * 'admin_list_users'
	 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	 * est donc équivalent à :
	 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	 * array(
	 * array(
	 * 'admin_list_users'
	 * )
	 * )
	 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	 *
	 * **Un seul niveau dans le tableau**
	 * Il sera considéré comme un ET, pas de combination OU.
	 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	 * array(
	 * 'admin_list_users', 'admin_list_roles'
	 * )
	 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	 * est donc équivalent à :
	 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	 * array(
	 * array(
	 * 'admin_list_users',
	 * 'admin_list_roles'
	 * )
	 * )
	 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	 *
	 * **Tableau a 2 niveaux dont certains élément sont une simple chaine**
	 * Si certains éléments du premier niveau sont des chaines et non pas un tableau, ils seront traités
	 * comme s'ils étaient seuls dans un sous tableau.
	 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	 * array(
	 * 'admin_list_users',
	 * array(
	 * 'admin_list_roles',
	 * 'admin_show_role'
	 * )
	 * )
	 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	 * est donc équivalent à :
	 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	 * array(
	 * array(
	 * 'admin_list_users',
	 * ),
	 * array(
	 * 'admin_list_roles',
	 * 'admin_show_role'
	 * )
	 * )
	 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	 *
	 * Tout droit inconnu sera considéré comme false.
	 *
	 * @param ZendAcl $acl ACL de l'application, préremplis et utilisables
	 * @param string|Zend\Permissions\Acl\Role\RoleInterface $user Utilisateur/rôle dont dont veut tester les droits
	 * @param string|string[]|array[] $rights Droits demandés
	 *       
	 * @return bool true si les droits sont autorisés, false sinon
	 */
	public static function isAllowed(ZendAcl $acl, $user, $rights)
	{
		$tabRights = static::prepareRightsTab($rights);
		
		$allowed = false; // False par défaut, car c'est un OR, il suffit de la combiner à un true pour qu'il puisse passer true
		if (is_array($tabRights) && count($tabRights)) {
			foreach ($tabRights as $orRights) {
				$andAllowed = true; // True par défaut, car c'est un AND, il ne peut rester true que si combiné à des true
				foreach ($orRights as $andRight) {
					if ($acl->hasResource($andRight)) {
						$rightAllowed = $acl->isAllowed($user, $andRight);
					} else {
						$rightAllowed = false;
					}
					$andAllowed &= $rightAllowed;
				}
				$allowed |= $andAllowed;
			}
		}
		
		return $allowed;
	}

	/**
	 * Développe un paramètre de droits requis dans sa forme complète
	 *
	 * Développe un paramètre $rights qui peut être une chaine de caractère, ou un tableau à
	 * une ou deux dimension en un tableau à 2 dimension.
	 * Voir la méthode {@link isAllowed()} pour le détail des formats accepté, et du format
	 * renvoyé (le format complet).
	 *
	 * @see isAllowed()
	 *
	 * @param string|string[]|array[] $rights Droits demandés
	 * @return boolean array[] bi-dimensionnel des droits demandés
	 */
	public static function prepareRightsTab($rights)
	{
		$tabRights = array();
		// Reconstruction d'un double tableau associatif pour $rights en fonction de ce qui est passé
		if (is_array($rights)) {
			$oneLevelTab = array();
			$oneLevel = true;
			foreach ($rights as $ri) {
				if (is_array($ri)) {
					$oneLevel = false;
					$subTab = array();
					foreach ($ri as $r) {
						if ($r && (is_string($r) || ($r instanceof RoleInterface))) {
							$subTab[] = $r;
						} else {
							return false;
						}
					}
					if (count($subTab)) {
						$tabRights[] = $subTab;
					}
				} else {
					if ($ri && (is_string($ri) || ($ri instanceof RoleInterface))) {
						$tabRights[] = array(
							$ri
						);
						$oneLevelTab[] = $ri;
					} else {
						return false;
					}
				}
			}
			if ($oneLevel) {
				$tabRights = array(
					$oneLevelTab
				);
			}
		} else {
			if ($rights && (is_string($rights) || ($rights instanceof RoleInterface))) {
				$tabRights = array(
					array(
						$rights
					)
				);
			} else {
				return false;
			}
		}
		
		return $tabRights;
	}
}
