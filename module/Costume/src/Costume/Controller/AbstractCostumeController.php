<?php
namespace Costume\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Application\ConfigAwareInterface;
use Zend\Config\Config as ZendConfig;

abstract class AbstractCostumeController extends AbstractActionController implements ConfigAwareInterface
{
	protected $config;

	protected $costumeTable;
	
	protected $costumePictureTable;
	
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
	
	public function getCostumePictureTable()
	{
		if (!$this->costumePictureTable) {
			$this->costumePictureTable = $this->getServiceLocator()->get('Costume\Model\CostumePictureTable');
		}
		return $this->costumePictureTable;
	}
}
