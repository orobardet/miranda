<?php
namespace Application\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Application\Model\Paginator\ItemsPerPageManager;

class ItemsPerPage extends AbstractPlugin
{

	/**
	 * Manager d'item par page
	 *
	 * @var Application\Model\Paginator\ItemsPerPageManager
	 */
	protected $manager;

	public function __construct(ItemsPerPageManager $manager)
	{
		$this->manager = $manager;
	}

	/**
	 * Accès au manager d'item par page
	 */
	public function __invoke()
	{
		return $this->manager;
	}
}
