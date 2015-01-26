<?php
namespace Application\Uri;

use Zend\Uri\Http;

class HttpDefaultPort extends Http
{
    public function toString()
    {
    	$oldPort = null;
    	
    	$scheme = 'http';
    	if ($this->scheme) {
    		$scheme = strtolower($scheme);
    	}
    	if (array_key_exists($scheme, self::$defaultPorts) && ($this->port == self::$defaultPorts[$scheme])) {
    		$oldPort = $this->port;
    		$this->port = null;
    	}
    	
    	$uri = parent::toString();
    	
    	if ($oldPort) {
    		$this->port = $oldPort;
    	}
    	
    	return $uri;
    }
}
