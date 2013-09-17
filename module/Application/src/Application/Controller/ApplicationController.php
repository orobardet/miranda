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
use Zend\Config\Config as ZendConfig;

class ApplicationController extends AbstractActionController implements ConfigAwareInterface
{
    protected $config;
 
    public function setConfig(ZendConfig $config)
    {
        $this->config = $config;
    }
    
    public function setitemsperpageAction()
    {
    	$context = $this->params('context', null);
    	$items = $this->params('items', null);
    	$redirect = $this->params('redirect', null);
    	
    	if ($context && $redirect) {
    		$redirect = base64_decode($redirect);
    		
    		if ($redirect) {
    			$this->itemsPerPage()->setItemsPerPage($context, $items);
    			return $this->redirect()->toUrl($redirect);
    		}
    	}
    	
    	throw new \Exception('No valid redirect parameters given for setitemsperpage');
    }
}
