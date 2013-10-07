<?php
namespace Application\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class RequestAcceptJson extends AbstractPlugin
{

	/**
	 * Test si la requête accepte le json, c'est à dire si on a reçu une requête HTTP
	 * avec un header Accept contenant "application/json"
	 */
	public function __invoke($type = 'application/json')
	{
		$request = $this->getController()->getRequest();
		$headers = $request->getHeaders();
		
		if ($headers->has('Accept')) {
			$match = $headers->get('Accept')->match($type);
			if ($match->getTypeString() == $type) {
				return true;
			}
		}
		
		return false;
	}
}
