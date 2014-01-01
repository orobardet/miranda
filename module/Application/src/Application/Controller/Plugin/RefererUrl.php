<?php
namespace Application\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Session\Storage\StorageInterface;

class RefererUrl extends AbstractPlugin
{

	/**
	 * Objet de session
	 *
	 * @var Zend\Session\Storage\StorageInterface
	 */
	protected $session;

	public function __construct(StorageInterface $session)
	{
		$this->session = $session;
		if (!isset($this->session->refererUrls)) {
			$this->session->refererUrls = array();
		}
	}

	public function __invoke($name = null, $url = null) {
		if ($name && $url) {
			return $this->setReferer($name, $url);
		} else if ($name) {
			return $this->getReferer($name);
		} else {
			return $this;
		}
	}
	
	/**
	 * Ajout un message de résultat
	 *
	 * @param string $message
	 * @param string $type "success" ou "error" ou "warning"
	 */
	public function setReferer($name, $url = null, $skipable = false)
	{
		if ($url === null) {
			$request = $this->getController()->getRequest();

			// Calcul de l'URL courante
			$requestUri = $request->getUri();
			$uriPath = $requestUri->getPath();
			$uriQuery = $requestUri->getQuery();
			$uriFragment = $requestUri->getFragment();
				
			$url = $uriPath;
			if (!empty($uriQuery)) {
				$url .= '?' . $uriQuery;
			}
			if (!empty($uriFragment)) {
				$url .= '#' . $uriFragment;
			}
		}
		$this->session->refererUrls[$name] = array('url' => $url, 'skipable' => $skipable);
	}

	public function getReferer($name, $skip = false)
	{
		if (array_key_exists($name, $this->session->refererUrls)) {
			if ($skip) {
				if (!$this->session->refererUrls[$name]['skipable']) {
					return $this->session->refererUrls[$name]['url'];
				}
			} else {
				return $this->session->refererUrls[$name]['url'];
			}
		}
		return false;
	}
}
