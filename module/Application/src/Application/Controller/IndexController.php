<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Application\ConfigAwareInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
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
        $view = new ViewModel();

        if ($this->acl()->isAllowed('list_costumes')) {
            $costumeTable = $this->getServiceLocator()->get('Costume\Model\CostumeTable');
            $view->lastCostumes = $costumeTable->getLastCreatedCostumes(5);
        }

        return $view;
    }
}
