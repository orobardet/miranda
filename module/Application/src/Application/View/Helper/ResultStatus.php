<?php

namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\Session\Storage\StorageInterface;

class ResultStatus extends AbstractHelper
{
	/**
	 * Objet de session
	 * @var Zend\Session\Storage\StorageInterface
	 */
	protected $session;

	public function __construct(StorageInterface $session)
	{
		$this->session = $session;
	}

	/**
	 * Ajout un message de résultat
	 *
	 * @param string $type "success" ou "error" ou "warning"
	 * @return array Liste des messages de status
	 */
	public function getResultsStatus($type = "success")
	{
		$messages = array();
		switch ($type) {
			case "error":
				if (isset($this->session->errorResults) && count($this->session->errorResults)) {
					$messages = $this->session->errorResults;
					unset($this->session->errorResults);
				}
				break;

			case "warning":
				if (isset($this->session->warningResults) && count($this->session->warningResults)) {
					$messages = $this->session->warningResults;
					unset($this->session->warningResults);
				}
				break;

			case "info":
				if (isset($this->session->infoResults) && count($this->session->infoResults)) {
					$messages = $this->session->infoResults;
					unset($this->session->infoResults);
				}
				break;
			
			case "success":
			default:
				if (isset($this->session->successResults) && count($this->session->successResults)) {
					$messages = $this->session->successResults;
					unset($this->session->successResults);
				}
				break;
		}

		return $messages;
	}
	
	public function clearResultsStatus()
	{
		unset($this->session->successResults);
		unset($this->session->infoResults);
		unset($this->session->warningResults);
		unset($this->session->errorResults);
	}
}
