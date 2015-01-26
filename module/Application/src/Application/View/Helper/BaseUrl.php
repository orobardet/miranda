<?php
namespace Application\View\Helper;

use Zend\Form\View\Helper\AbstractHelper;
use Application\Uri\HttpDefaultPort as HttpUri;

class BaseUrl extends AbstractHelper
{
	/**
	 * Manager d'item par page
	 *
	 * @var \Zend\Uri\Http
	 */
	protected $rootUri;

	public function __construct($rootUrl)
	{
		$this->rootUri = new HttpUri($rootUrl);
	}

	public function __invoke ($url = null)
	{
		if ($url && (trim((string)$url) != '')) {
			$uri = new HttpUri($url);
			$uri->resolve($this->rootUri);
			return (string)$uri;
		} else {
			return (string)$this->rootUri;
		}
	}
}
