<?php
namespace Acl\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\ConfigAwareInterface;
use Zend\Config\Config as ZendConfig;

class AdminRightController extends AbstractActionController implements ConfigAwareInterface
{

	protected $config;

	public function setConfig (ZendConfig $config)
	{
		$this->config = $config;
	}

	public function indexAction ()
	{
		return new ViewModel(array(
			'rights' => $this->getServiceLocator()->get('RightsManager')->getGroupedRights()
		));
	}
}