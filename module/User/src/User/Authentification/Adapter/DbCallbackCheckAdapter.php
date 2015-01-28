<?php
namespace User\Authentification\Adapter;

use Zend\Authentication\Adapter\DbTable\CallbackCheckAdapter;
use Zend\Authentication\Result as AuthenticationResult;

class DbCallbackCheckAdapter extends CallbackCheckAdapter
{

	/**
	 * Compte non activé
	 */
	const FAILURE_NOT_ACTIVE = -100;

	/**
	 * Surcharge de la method pour valider que le compte est bien activé
	 *
	 * @param array $resultIdentity
	 * @return AuthenticationResult
	 */
	protected function authenticateValidateResult($resultIdentity)
	{
		$result = parent::authenticateValidateResult($resultIdentity);
		
		if (is_array($this->resultRow) &&
				 (!array_key_exists('active', $this->resultRow) || !$this->resultRow['active'] ||
				 (array_key_exists('registration_token', $this->resultRow) && $this->resultRow['registration_token']))) {
			$this->resultRow = null;
			$this->authenticateResultInfo['code'] = self::FAILURE_NOT_ACTIVE;
			$this->authenticateResultInfo['messages'][] = 'User account is not activated.';
			return $this->authenticateCreateAuthResult();
		}
		
		return $this->authenticateCreateAuthResult();
	}

	/**
	 * Surcharge de la methode pour renvoyer une valeur 'identity' un peu plus
	 * complète que le simple email/login par défaut (ici au moins l'ID dans la
	 * base pour permettre de retrouver facilement toutes les données de l'utilisateur)
	 *
	 * @return AuthenticationResult
	 */
	protected function authenticateCreateAuthResult()
	{
		$identity = new \stdClass();
		$identity->id = $this->resultRow['id'];
		$identity->identity = $this->resultRow['email'];
		
		return new AuthenticationResult($this->authenticateResultInfo['code'], $identity, $this->authenticateResultInfo['messages']);
	}
}
