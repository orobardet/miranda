<?php
namespace Costume\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Application\ConfigAwareInterface;
use Zend\Config\Config as ZendConfig;

abstract class AbstractCostumeController extends AbstractActionController implements ConfigAwareInterface
{
	protected $config;

	protected $costumeTable;
	
	public function setConfig(ZendConfig $config)
	{
		$this->config = $config;
	}

	public function getCostumeTable()
	{
		if (!$this->costumeTable) {
			$this->costumeTable = $this->getServiceLocator()->get('Costume\Model\CostumeTable');
		}
		return $this->costumeTable;
	}
}
