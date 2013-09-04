<?php
namespace Acl\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\ConfigAwareInterface;
use Zend\Config\Config as ZendConfig;

class UnauthorizedController extends AbstractActionController implements ConfigAwareInterface
{

	protected $config;

	public function setConfig (ZendConfig $config)
	{
		$this->config = $config;
	}
	
	public function indexAction ()
	{
		$this->getResponse()->setStatusCode(403);
		return new ViewModel();
	}
}