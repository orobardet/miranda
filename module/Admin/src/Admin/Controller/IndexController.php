<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\ConfigAwareInterface;
use Zend\Config\Config as ZendConfig;

class IndexController extends AbstractActionController implements ConfigAwareInterface
{
	protected $config;

	public function setConfig(ZendConfig $config)
	{
		$this->config = $config;
	}

	public function indexAction()
	{
		return new ViewModel();
	}
}