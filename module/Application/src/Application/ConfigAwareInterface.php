<?php
namespace Application;

use Zend\Config\Config as ZendConfig;

interface ConfigAwareInterface
{
	public function setConfig(ZendConfig $config);
}
?>