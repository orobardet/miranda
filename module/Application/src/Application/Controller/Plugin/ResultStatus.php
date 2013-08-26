<?php
namespace Application\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Session\Storage\StorageInterface;

class ResultStatus extends AbstractPlugin
{

	/**
	 * Objet de session
	 *
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
	 * @param string $message
	 * @param string $type "success" ou "error" ou "warning"
	 */
	public function addResultStatus($message, $type = "success")
	{
		switch ($type) {
			case "error":
				if (!isset($this->session->errorResults)) {
					$this->session->errorResults = array();
				}
				$this->session->errorResults[] = $message;
				break;
			
			case "warning":
				if (!isset($this->session->warningResults)) {
					$this->session->warningResults = array();
				}
				$this->session->warningResults[] = $message;
				break;
			
			case "success":
			default:
				if (!isset($this->session->successResults)) {
					$this->session->successResults = array();
				}
				$this->session->successResults[] = $message;
				break;
		}
	}

	public function clearResultsStatus()
	{
		unset($this->session->successResults);
		unset($this->session->warningResults);
		unset($this->session->errorResults);
	}
}
